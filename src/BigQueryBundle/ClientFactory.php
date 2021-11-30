<?php

namespace CCMBenchmark\BigQueryBundle;

use GuzzleHttp\Client;

class ClientFactory
{
    /**
     * @var string
     */
    private $applicationName;

    /**
     * @var string
     */
    private $credentialsFile;

    /**
     * @var array
     */
    private $proxy;

    /**
     * ClientFactory constructor.
     * @param string $applicationName
     * @param $credentialsFile
     * @param array $proxy
     */
    public function __construct($applicationName, $credentialsFile, array $proxy = [])
    {
        $this->applicationName = $applicationName;
        $this->credentialsFile = $credentialsFile;
        $this->proxy = $proxy;
    }

    /**
     * Create a configured google client, using an eventually defined proxy and the configured credentials
     *
     * @return \Google\Client
     * @throws \Google_Exception
     */
    public function getClient(): \Google\Client
    {
        $client = new \Google\Client();
        $client->setApplicationName($this->applicationName);
        $client->setAuthConfig($this->credentialsFile);
        $client->addScope(\Google\Service\Storage::CLOUD_PLATFORM);
        if ($this->proxy !== [] && $this->proxy['host'] !== null) {
            $guzzle = new Client([
                'base_uri' => $client->getConfig('base_path'),
                'proxy' => [
                    'http' => $this->proxy['host'] . ':' . $this->proxy['port'],
                    'https' => $this->proxy['host'] . ':' . $this->proxy['port'],
                ],
                'curl.options' => [
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                ]
            ]);
            $client->setHttpClient($guzzle);
        }

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithAssertion();
        }
        return $client;
    }
}
