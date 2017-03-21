<?php

namespace PhpAwsWrapper\Aws;
use Aws\Exception\AwsException;
use Aws\Sdk;

date_default_timezone_set('UTC');

class SqsWrapper
{
    /** @var \Aws\Sqs\SqsClient  */
    private $sqs;

    /** @var  String */
    private $queueUrl;

    public function  __construct()
    {
        $sdk = new Sdk([
            'profile'  => 'default',
            'region'   => 'eu-west-1',
            'version'  => 'latest'
        ]);

        $this->sqs = $sdk->createSqs();
    }

    /**
     * @param $message
     * @return mixed
     * @throws \Exception
     */
    public function sendMessage($message)
    {
        $result = $this->sqs->sendMessage(array(
            'QueueUrl'    => $this->queueUrl,
            'MessageBody' => $message,
        ));

        if(!$result || !isset($result['MessageId'])) {
            throw new \Exception('Error sending message to SQS Queue: ' . $this->queueUrl);
        }

        return $result['MessageId'];
    }

    /**
     * @param $queueName
     * @return \Aws\Result
     */
    public function getQueueUrl($queueName)
    {

        try {
            $queue = $this->sqs->getQueueUrl([
                'QueueName' => $queueName
            ]);
        } catch (AwsException $e) {
            return null;
        }

        return $queue['QueueUrl'];
    }
}