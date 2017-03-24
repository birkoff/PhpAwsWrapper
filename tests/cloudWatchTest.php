<?php
# Examle usage

use PhpAwsWrapper\Aws\CloudWatchWrapper;

$client = new CloudWatchWrapper(
    '/some/log',
    'log-stream'
);

$timestamp = time();

for ($i = 1;$i<=1000;$i++) {
    $message = "TEST # {$timestamp} ";
    $result = $client->putLogEvents($message . " - " . $i);
}