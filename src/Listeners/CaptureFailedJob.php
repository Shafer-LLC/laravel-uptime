<?php

namespace Codedeploy\Uptime\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Codedeploy\Uptime\Uptime;

class CaptureFailedJob
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Queue\Events\JobFailed  $event
     * @return void
     */
    public function handle(JobFailed $event)
    {
        $job = $event->job;

        $displayName = method_exists($job, 'displayName') ? $job->displayName() : get_class($job);

        Uptime::failedJob(
            $event->connectionName,
            $job->getQueue(),
            $displayName,
            $event->exception,
            now()->toIso8601String()
        );
    }
}
