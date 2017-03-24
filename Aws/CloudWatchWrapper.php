<?php

namespace PhpAwsWrapper\Aws;

include "CredentialProviderWrapper.php";

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;

date_default_timezone_set('UTC');

class CloudWatchWrapper
{
    const MAX_RETRIES = 2;

    /** @var \Aws\CloudWatchLogs\CloudWatchLogsClient */
    private $cloudWatchLogs;

    private $logGroupName;

    private $logStreamName;

    private $sequenceToken;

    private $retries;

    /**
     * CloudWatchWrapper constructor.
     * @param $logGroupName
     * @param null $logStreamName
     * @param null $sequenceToken
     */
    public function __construct($logGroupName, $logStreamName = null , $sequenceToken = null)
    {
        $awsConnect = new CredentialProviderWrapper();
        $this->cloudWatchLogs = new CloudWatchLogsClient($awsConnect->getAwsClientParams());

        $this->logGroupName = $logGroupName;
        $this->logStreamName = $logStreamName;
        $this->sequenceToken = $sequenceToken;
        $this->retries = 0;
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

        if (!empty($this->sequenceToken)) {
            $event['sequenceToken'] = $this->sequenceToken;
        }

        echo "\nSending Message to CloudWatch {".$message."}...";

        try {
            $result = $this->cloudWatchLogs->putLogEvents($event);
            $this->sequenceToken = $result['nextSequenceToken'];
            echo "OK";
            return $result;
        } catch (CloudWatchLogsException $e) {
            echo "FAILED";
            $this->retryIfSequenceTokenOnExceptionMessage($message, $e);
            throw $e;
        }
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

    /**
     * @param $message
     * @param CloudWatchLogsException $e
     * @param $match
     */
    private function retryIfSequenceTokenOnExceptionMessage($message, $e)
    {
        $exceptionMessage = $e->getMessage();
        preg_match('/"expectedSequenceToken":"(.+)","message/', $exceptionMessage, $match);
        $this->sequenceToken = $match[1];
        if ($this->retries < self::MAX_RETRIES) {
            $this->retries = $this->retries + 1;
            echo " (Retrying)";
            $this->putLogEvents($message);
        }
    }
}