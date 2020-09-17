<?php

namespace Codedeploy\Uptime\Tests;

use Closure;
use Mockery;
use Codedeploy\Uptime\Monitors\Mail;
use Codedeploy\Uptime\UptimeServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function mock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    protected function mailTransportManager()
    {
        return Mail::transportManager();
    }

    protected function getPackageProviders($app)
    {
        return [UptimeServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('monitor', [
            'app_id'    => 1,
            'app_token' => 'secret',
            'endpoint'  => 'dummy.cronmonitor.dev',
        ]);

        return parent::getEnvironmentSetUp($app);
    }
}
