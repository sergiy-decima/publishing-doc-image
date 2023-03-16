<?php
declare(strict_types=1);

// @link https://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
$NORMAL_COLOR    = "\033[0m";
$BOLD_COLOR      = "\033[1m";
$BLACK_COLOR     = "\e[30m";
$GREEN_COLOR     = "\e[32m";
$YELLOW_COLOR    = "\e[33m";
$BG_RED_COLOR    = "\e[41m";
$BG_GREEN_COLOR  = "\e[42m";
$BG_YELLOW_COLOR = "\e[43m";
$BLINK_COLOR     = "\e[5m";

$SUCCESS_COLOR  = $BG_GREEN_COLOR . $BLACK_COLOR;
$ERROR_COLOR    = $BG_RED_COLOR . $BLACK_COLOR;
$WARNING_COLOR  = $BG_YELLOW_COLOR . $BLACK_COLOR;

$filename   = $argv[1];
$thresholds = $argv[2];
$failIfLow  = filter_var($argv[3], FILTER_VALIDATE_BOOLEAN);
if (preg_match('/(\d+)([\s\.\-_]+(\d+\.{0,1}\d*))?/', $thresholds, $m)) {
    $lowThreshold   = (float)$m[1];
    $upperThreshold = (float)$m[3] ?: $lowThreshold;
} else {
    $lowThreshold   = 50;
    $upperThreshold = 75;
}
$minPercent = $upperThreshold;

function generateHealthIndicator(int|float|string $percent): string
{
    global $lowThreshold, $upperThreshold;

    return $percent < $lowThreshold ? "❌" : ($percent < $upperThreshold ? "💩": "️👍");
}

function generateHealthColor(int|float|string $percent): string
{
    global $lowThreshold, $upperThreshold, $ERROR_COLOR, $WARNING_COLOR, $SUCCESS_COLOR;

    return $COLOR = $percent < $lowThreshold ? $ERROR_COLOR : ($percent < $upperThreshold ? $WARNING_COLOR : $SUCCESS_COLOR);
}

$classSummary = [];
$classHits   = $methodHits   = $lineHits   = 0;
$classTotals = $methodTotals = $lineTotals = 0;
foreach ((array)simplexml_load_file($filename)->xpath('*/file') as $fileElement) {
    $classes = ((array)$fileElement)['class'];
    if (is_object($classes)) {
        $classes = [$classes];
    }

    foreach ($classes as $class) {
        if ($class->metrics && ((int)$class->metrics['methods']) > 0) {
            $classHits += (((int)$class->metrics['methods']) == ((int)$class->metrics['coveredmethods']) ? 1 : 0);
            $classTotals += 1;
            $classSummary[(string)$class['name']] = [
                'methods' => (int)$class->metrics['methods'],
                'covered_methods' => (int)$class->metrics['coveredmethods'],
                'lines' => (int)$class->metrics['elements'] - (int)$class->metrics['methods'],
                'covered_lines' => (int)$class->metrics['coveredelements'] - (int)$class->metrics['coveredmethods'],
            ];
        }
    }

    $methodHits   += (int)($fileElement->metrics['coveredmethods']);
    $methodTotals += (int)($fileElement->metrics['methods']);

    foreach (((array)$fileElement)['line'] as $line) {
        if ('stmt' == (string)$line['type']) {
            $lineHits   += (int)$line['count'] > 0 ? 1 : 0;
            $lineTotals += 1;
        }
    }
}

$classPercent  = $classTotals ? sprintf('%.02f', $classHits / $classTotals * 100) : 0;
$methodPercent = $methodTotals ? sprintf('%.02f', $methodHits / $methodTotals * 100) : 0;
$linePercent   = $lineTotals ? sprintf('%.02f', $lineHits / $lineTotals * 100) : 0;
shell_exec('echo ' . sprintf('"percent=%s" >> $GITHUB_OUTPUT', $linePercent));
//file_put_contents($_ENV['GITHUB_OUTPUT'], sprintf("percent=%s", $linePercent) . PHP_EOL, FILE_APPEND);

$classMark   = generateHealthIndicator($classPercent);
$methodMark  = generateHealthIndicator($methodPercent);
$lineMark    = generateHealthIndicator($linePercent);

printf("${BOLD_COLOR}Summary Coverage Report:${NORMAL_COLOR}" . PHP_EOL);
printf("  Classes: %' 8.2f%%  (%d/%d)\t$classMark" . PHP_EOL, $classPercent, $classHits, $classTotals);
printf("  Methods: %' 8.2f%%  (%d/%d)\t$methodMark" . PHP_EOL, $methodPercent, $methodHits, $methodTotals);
printf("  Lines:   %' 8.2f%%  (%d/%d)\t$lineMark" . PHP_EOL, $linePercent, $lineHits, $lineTotals);
print PHP_EOL;

foreach ($classSummary as $name => $info) {
    $classHitsItem    = $info['covered_methods'];
    $classTotalsItem  = $info['methods'];
    $classPercentItem = $classTotalsItem ? sprintf('%.02f', $classHitsItem / $classTotalsItem * 100) : 0;
    $lineHitsItem     = $info['covered_lines'];
    $lineTotalsItem   = $info['lines'];
    $linePercentItem  = $lineTotalsItem ? sprintf('%.02f', $lineHitsItem / $lineTotalsItem * 100) : 0;

    $COLOR = $linePercentItem < $lowThreshold ? $ERROR_COLOR : ($linePercentItem < $upperThreshold ? $WARNING_COLOR : $SUCCESS_COLOR);
    $lineMarkItem = generateHealthIndicator($linePercentItem);
    printf("${BOLD_COLOR}%s${NORMAL_COLOR}" . PHP_EOL, $name);
    printf("${COLOR}  Methods: %' 8.2f%%  (%d/%d) ${NORMAL_COLOR}\t${COLOR} Lines: %' 8.2f%%  (%d/%d) ${NORMAL_COLOR}" . PHP_EOL,
        $classPercentItem, $classHitsItem, $classTotalsItem,
        $linePercentItem, $lineHitsItem, $lineTotalsItem
    );
}
$classSummary && print PHP_EOL;

if ($linePercent >= $upperThreshold) {
    printf("${GREEN_COLOR}${BOLD_COLOR}> Summary Line Coverage: %s%% ($lineHits/$lineTotals)${NORMAL_COLOR}" . PHP_EOL, $linePercent);
} else {
    printf("::error::Code coverage is %s%% (%d/%d), which is below the accepted %s%%." . PHP_EOL, (float)$linePercent, $lineHits, $lineTotals, $upperThreshold);
    exit($failIfLow ? 1 : 0);
}
