<?php

namespace Codedeploy\Uptime\Tests\Monitors;

use Codedeploy\Uptime\Controllers\Configuration;
use Codedeploy\Uptime\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /** @test */
    public function it_returns_the_configuration()
    {
        $controller = new Configuration;

        $response = $controller();
        $data     = $response->getData(true);

        $this->assertTrue(array_key_exists('data', $data));
        $this->assertTrue(array_key_exists('database', $data['data']));
    }
}
