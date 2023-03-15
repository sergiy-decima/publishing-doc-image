<?php
declare(strict_types=1);

$filename   = $argv[1];
$minPercent = filter_var($argv[2], FILTER_VALIDATE_FLOAT);
$failIfLow  = filter_var($argv[3], FILTER_VALIDATE_BOOLEAN);

print_r($_ENV);
$lineHits   = 0;
$lineTotals = 0;
foreach (simplexml_load_file($filename)->xpath('*/package/classes') as $classesElement) {
    $lines = [];
    $classes = ((array)$classesElement)['class'];
    foreach ($classes as $class) {
        parseLines($class->lines, $lines);
    }

    $lineHits   += array_sum($lines);
    $lineTotals += count($lines);
}

function parseLines(SimpleXMLElement $linesElement, array &$return): void
{
    $lines = (array)$linesElement ? ((array)$linesElement)['line'] : [];
    if (is_object($lines)) {
        $lines = [$lines];
    }

    foreach ($lines as $line) {
        $return[(int)$line->attributes()->number] = (int)$line->attributes()->hits;
    }
}

echo 'GITHUB_OUTPUT: ' . $_ENV['GITHUB_OUTPUT'] . "\n";
$linePercent = sprintf('%.02f', $lineHits / $lineTotals * 100);
shell_exec('echo ' . sprintf('"percent=%s" >> $GITHUB_OUTPUT', $linePercent));

if ($linePercent >= $minPercent) {
    echo sprintf("Summary Line Coverage: %s%% ($lineHits / $lineTotals)", $linePercent) . PHP_EOL;
} else {
    echo sprintf("::error::Code Coverage is %s%% (%s / %s), which is below the accepted %s%%.", $linePercent, $lineHits, $lineTotals, $minPercent) . PHP_EOL;
    exit($failIfLow ? 1 : 0);
}
