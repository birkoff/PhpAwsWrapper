<?php

namespace PhpAwsWrapper\Aws;

use Aws\Credentials\CredentialProvider;
use Aws\Sts\StsClient;

class CredentialProviderWrapper
{
    private $config;

    public function __construct()
    {
        $this->config = include_once('config.php');
    }

    public function getAwsClientParams()
    {
        return [
            'region'      => $this->getAwsRegion(),
            'version'     => 'latest',
            'credentials' => $this->getProviderFromAssumeRole()
        ];
    }

    private function getProviderFromAssumeRole()
    {
        $config = $this->config;
        return CredentialProvider::assumeRole([
            'client' => new StsClient(['region' => $this->getAwsRegion(), 'version' => 'latest']),
            'assume_role_params' => [
                'RoleArn' => $this->getRoleArn(),
                'RoleSessionName' => 'CloudWatchWrapper',
            ]
        ]);
    }

    private function getAwsRegion()
    {
        return $this->config['awsRegion'];
    }

    private function getRoleArn()
    {
        return $this->config['roleArn'];
    }
}