<?php

namespace PhpAwsWrapper\Aws;
use Aws\Sdk;

date_default_timezone_set('UTC');

class CloudWatchWrapper
{
    /** @var \Aws\CloudWatchLogs\CloudWatchLogsClient */
    private $cloudWatchLogs;

    private $logGroupName;

    private $logStreamName;

    private $sequenceToken;

    public function __construct($logGroupName, $logStreamName, $sequenceToken = null)
    {
        $sdk = new Sdk([
            'profile' => 'sandbox',
            'region'  => 'eu-west-1',
            'version' => 'latest'
        ]);

        $this->cloudWatchLogs = $sdk->createCloudWatchLogs();

        $this->logGroupName = $logGroupName;
        $this->logStreamName = $logStreamName;
        $this->sequenceToken = $sequenceToken;
    }

    public function putLogEvents($message)
    {
        $timestamp = time() * 1000;
        $event = [
            'logEvents' => [
                [
                    'message'   => $message,
                    'timestamp' => $timestamp,
                ],
            ],
            'logGroupName' => $this->logGroupName,
            'logStreamName' => $this->logStreamName,
        ];

        if ($this->sequenceToken) {
            $event['sequenceToken'] = $this->sequenceToken;
        }
        $result = $this->cloudWatchLogs->putLogEvents($event);
        $this->sequenceToken = $result['nextSequenceToken'];

        return $result;
    }

    public function describeLogsStream()
    {
        return $this->cloudWatchLogs->describeLogStreams([
            'descending' => true,
            'limit' => 15,
            'logGroupName' => $this->logGroupName,
        ]);
    }

    public function getSequenceToken()
    {
        return $this->sequenceToken;
    }
}