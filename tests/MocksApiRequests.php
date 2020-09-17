<?php

namespace Codedeploy\Uptime\Tests;

use GuzzleHttp\Client as HttpClient;
use Codedeploy\Uptime\Api;
use Codedeploy\Uptime\Tests\MocksGuzzleRequests;

trait MocksApiRequests
{
    use MocksGuzzleRequests;

    protected $httpClient;

    protected function mockHttpClient()
    {
        $this->httpClient = $this->mock(HttpClient::class);

        app()->singleton('uptime.api-client', function () {
            return $this->httpClient;
        });
    }

    private function mockApiRequest($method, $path = null, $payload = null, $jsonableContent = null, $statusCode = null)
    {
        if (!$this->httpClient) {
            $this->mockHttpClient();
        };

        $response = $this->mockResponse($jsonableContent);

        if ($statusCode) {
            $response->shouldReceive('getStatusCode')->andReturn($statusCode);
        }

        $this->httpClient
            ->shouldReceive('request')
            ->with($method, "https://dummy.cronmonitor.dev/api/application/1/{$path}", [
                'headers' => [
                    'Authorization'            => 'Bearer secret',
                    'Accept'                   => 'application/json',
                    'Content-Type'             => 'application/json',
                    'Uptime-Package-Version' => Api::PACKAGE_VERSION,
                ],
            ] + ($payload ? ['json' => $payload] : []))
            ->andReturn($response);
    }
}
