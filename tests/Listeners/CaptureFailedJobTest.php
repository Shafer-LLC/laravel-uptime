<?php

namespace Codedeploy\Uptime\Tests\Monitors;

use Exception;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Carbon;
use Codedeploy\Uptime\ApiResponse;
use Codedeploy\Uptime\Tests\TestCase;
use Codedeploy\Uptime\Uptime;

class FakeJob extends Job
{
    public function getJobId()
    {
        return 1337;
    }

    public function getQueue()
    {
        return 'default';
    }

    public function getRawBody()
    {
        return '{"job":"Illuminate\\\Queue\\\CallQueuedHandler@call","data":{"commandName":"App\\\Jobs\\\RefreshCache"}}';
    }
}

class CaptureFailedJobTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('monitor', [
            'app_id'      => 1,
            'app_token'   => 'secret',
            'endpoint'    => 'dummy.cronmonitor.dev',
            'failed_jobs' => true,
        ]);
    }

    /** @test */
    public function it_sends_the_failed_job_to_the_monitor_api()
    {
        Carbon::setTestNow(Carbon::parse('2019-08-01 12:00:00'));

        $job = new FakeJob;

        $exception = new Exception('Job not completed!');

        Uptime::shouldReceive('failedJob')->with(
            'database', 'default', 'Codedeploy\Monitor\Tests\Monitors\FakeJob', $exception, now()->toIso8601String()
        )->andReturn(ApiResponse::fromArray([
            'data' => [
                'count'           => 0,
                'vulnerabilities' => [],
            ],
        ]));

        event(new JobFailed('database', $job, $exception));
    }
}
