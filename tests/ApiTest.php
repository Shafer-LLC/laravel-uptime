<?php

namespace Codedeploy\Uptime\Tests;

use Codedeploy\Uptime\Tests\TestCase;
use Codedeploy\Uptime\Uptime;

class ApiTest extends TestCase
{
    use MocksApiRequests;

    /** @test */
    public function it_can_do_a_get_request()
    {
        $this->mockApiRequest('GET', null, null, $response = ['data' => ['app_id' => 1]], 200);

        $this->assertEquals($response, Uptime::application()->json());
    }

    /** @test */
    public function it_can_do_a_post_request()
    {
        $this->mockApiRequest('POST', 'check/token/composer', ['lock_contents' => ['hash' => '123']], $response = ['data' => ['app_id' => 1]], 200);

        $this->assertEquals($response, Uptime::composer('token', ['hash' => '123'])->json());
    }
}
