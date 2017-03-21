<?php

namespace PhpAwsWrapper\Aws;
use Aws\Sdk;

date_default_timezone_set('UTC');

class S3Wrapper
{
    /** @var \Aws\S3\S3Client  */
    private $s3;

    public function  __construct()
    {
        $sdk = new Sdk([
            'profile'  => 'default',
            'region'   => 'eu-west-1',
            'version'  => 'latest'
        ]);

        $this->s3 = $sdk->createS3();
    }

    /**
     * @param $sourceFile
     * @param $key
     * @param $bucket
     * @return string
     * @throws \Exception
     */
    public function save($sourceFile, $key, $bucket)
    {
        $result = $this->s3->putObject([
            'Bucket'     => $bucket,
            'Key'        => $key,
            'SourceFile' => $sourceFile
        ]);

        if(!$result || !isset($result['ObjectURL'])) {
            throw new \Exception('Error publishing uploading object to S3');
        }

        $url = $this->s3->getObjectUrl($bucket, $key);

        return $url;
    }
}