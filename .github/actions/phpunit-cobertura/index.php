<?php
declare(strict_types=1);

$inputFile = $argv[1];
$inputPercent = (float)$argv[2];
$inputFailBuildOnFailure = filter_var($argv[3], FILTER_VALIDATE_BOOLEAN);

echo "The Best Test!";

$context = file_get_contents($inputFile);

echo $context;

echo sprintf('percent=%s >> $GITHUB_OUTPUT', $inputPercent);
