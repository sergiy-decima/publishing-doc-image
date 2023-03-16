<?php
declare(strict_types=1);

$NORMAL_COLOR    = "\e[0m";
$BOLD_COLOR      = "\e[1m";
$BLACK_COLOR     = "\e[30m";
$RED_COLOR       = "\e[31m";
$GREEN_COLOR     = "\e[32m";
$YELLOW_COLOR    = "\e[33m";
$BG_RED_COLOR    = "\e[41m";
$BG_GREEN_COLOR  = "\e[42m";
$BG_YELLOW_COLOR = "\e[43m";
$BLINK_COLOR     = "\e[5m";

// Input Data
$filename   = $argv[1];
$thresholds = $argv[2];
$failIfLow  = filter_var($argv[3], FILTER_VALIDATE_BOOLEAN);
$lowThreshold   = 60;
$upperThreshold = 80;
if (preg_match('/(\d+)([\s\.\-_,:;]+(\d+\.{0,1}\d*))?/', $thresholds, $m)) {
    $lowThreshold   = (float)$m[1];
    $upperThreshold = (float)$m[3] ?: $lowThreshold;
}
$minPercentToBuild = $lowThreshold;

// Marker Functions
function generateHealthIndicator(int|float|string $percent): string
{
    global $lowThreshold, $upperThreshold;

    return $percent < $lowThreshold ? "❌" : ($percent < $upperThreshold ? "💩": "️👍");
}

function generateHealthColor(int|float|string $percent): string
{
    global $lowThreshold, $upperThreshold, $BLACK_COLOR, $BG_GREEN_COLOR, $BG_RED_COLOR, $BG_YELLOW_COLOR;

    $SUCCESS_COLOR = $BLACK_COLOR . $BG_GREEN_COLOR;
    $ERROR_COLOR   = $BLACK_COLOR . $BG_RED_COLOR;
    $WARNING_COLOR = $BLACK_COLOR . $BG_YELLOW_COLOR;

    return $percent < $lowThreshold ? $ERROR_COLOR : ($percent < $upperThreshold ? $WARNING_COLOR : $SUCCESS_COLOR);
}

// Parse XML
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

    if (!empty(((array)$fileElement)['line'])) {
        $lines = ((array)$fileElement)['line'];
    } else {
        $lines = [];
    }

    foreach ($lines as $line) {
        if ('stmt' == (string)$line['type']) {
            $lineHits   += (int)$line['count'] > 0 ? 1 : 0;
            $lineTotals += 1;
        }
    }
}

// Percent Calculation
$classPercent  = $classTotals  ? sprintf('%.02f', $classHits / $classTotals * 100) : 0;
$methodPercent = $methodTotals ? sprintf('%.02f', $methodHits / $methodTotals * 100) : 0;
$linePercent   = $lineTotals   ? sprintf('%.02f', $lineHits / $lineTotals * 100) : 0;
file_put_contents($_ENV['GITHUB_OUTPUT'], sprintf("percent=%s", $linePercent) . PHP_EOL, FILE_APPEND);

// Print Summary Data
$classMark  = generateHealthIndicator($classPercent);
$methodMark = generateHealthIndicator($methodPercent);
$lineMark   = generateHealthIndicator($linePercent);
printf("${BOLD_COLOR}Summary Coverage Report:${NORMAL_COLOR}" . PHP_EOL);
printf("  Classes: %' 8.2f%%  (%d/%d)\t$classMark" . PHP_EOL, $classPercent, $classHits, $classTotals);
printf("  Methods: %' 8.2f%%  (%d/%d)\t$methodMark" . PHP_EOL, $methodPercent, $methodHits, $methodTotals);
printf("  Lines:   %' 8.2f%%  (%d/%d)\t$lineMark" . PHP_EOL, $linePercent, $lineHits, $lineTotals);
print PHP_EOL;

// Print Detail Report
foreach ($classSummary as $name => $info) {
    $classHitsItem    = $info['covered_methods'];
    $classTotalsItem  = $info['methods'];
    $classPercentItem = $classTotalsItem ? sprintf('%.02f', $classHitsItem / $classTotalsItem * 100) : 0;
    $lineHitsItem     = $info['covered_lines'];
    $lineTotalsItem   = $info['lines'];
    $linePercentItem  = $lineTotalsItem ? sprintf('%.02f', $lineHitsItem / $lineTotalsItem * 100) : 0;

    $MARKED_COLOR = generateHealthColor($linePercentItem);
    printf("${BOLD_COLOR}%s${NORMAL_COLOR}" . PHP_EOL, $name);
    printf("${MARKED_COLOR}  Methods: %' 8.2f%%  (%d/%d) ${NORMAL_COLOR}\t${MARKED_COLOR} Lines: %' 8.2f%%  (%d/%d) ${NORMAL_COLOR}" . PHP_EOL,
        $classPercentItem, $classHitsItem, $classTotalsItem,
        $linePercentItem, $lineHitsItem, $lineTotalsItem
    );
}
$classSummary && print PHP_EOL;

// Summary Line
if ($linePercent >= $minPercentToBuild) {
    printf("${GREEN_COLOR}${BOLD_COLOR}> Summary Line Coverage: %s%% ($lineHits/$lineTotals)${NORMAL_COLOR}" . PHP_EOL, $linePercent);
} else {
    printf("${RED_COLOR}${BOLD_COLOR}Error: Code coverage is %s%% (%d/%d), which is below the accepted %s%%.${NORMAL_COLOR}" . PHP_EOL, (float)$linePercent, $lineHits, $lineTotals, $minPercentToBuild);
    exit($failIfLow ? 1 : 0);
}
