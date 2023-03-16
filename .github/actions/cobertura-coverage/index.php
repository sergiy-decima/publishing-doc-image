<?php
declare(strict_types=1);

$filename   = $argv[1];
$minPercent = filter_var($argv[2], FILTER_VALIDATE_FLOAT);
$failIfLow  = filter_var($argv[3], FILTER_VALIDATE_BOOLEAN);

$lineHits   = 0;
$lineTotals = 0;
foreach (simplexml_load_file($filename)->xpath('*/package/classes') as $classesElement) {
    $result = [];
    $classes = (array)((array)$classesElement)['class'];
    if (!empty($classes['lines'])) {
        $lines = (array)$classes['lines'];
        $lines = !empty($lines['line']) ? (array)$lines['line'] : [];
    } else {
        $lines = [];
    }
    foreach ($lines as $line) {
        $result[(int)$line['number']] = (int)$line['hits'] > 0 ? 1 : 0;
    }

    $lineHits   += array_sum($result);
    $lineTotals += count($result);
}

$linePercent = $lineTotals ? sprintf('%.02f', $lineHits / $lineTotals * 100) : 0;
//shell_exec('echo ' . sprintf('"percent=%s" >> $GITHUB_OUTPUT', $linePercent));
file_put_contents($_ENV['GITHUB_OUTPUT'], sprintf("percent=%s", $linePercent) . PHP_EOL, FILE_APPEND);

if ($linePercent >= $minPercent) {
    echo sprintf("Summary Line Coverage: %s%% ($lineHits/$lineTotals)", $linePercent) . PHP_EOL;
} else {
    echo sprintf("::error::Code coverage is %s%% (%s/%s), which is below the accepted %s%%.", $linePercent, $lineHits, $lineTotals, $minPercent) . PHP_EOL;
    exit($failIfLow ? 1 : 0);
}
