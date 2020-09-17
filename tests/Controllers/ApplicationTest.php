<?php

namespace Codedeploy\Uptime\Tests\Monitors;

use Codedeploy\Uptime\Api;
use Codedeploy\Uptime\Controllers\Application;
use Codedeploy\Uptime\Tests\TestCase;

class ApplicationTest extends TestCase
{
    /** @test */
    public function it_returns_the_app_id()
    {
        $controller = new Application;

        $response = $controller();
        $data     = $response->getData(true);

        $this->assertEquals([
            'app_id' => config('monitor.app_id'),

            'monitor_package_version' => Api::PACKAGE_VERSION,
        ], $data['data']);
    }
}
