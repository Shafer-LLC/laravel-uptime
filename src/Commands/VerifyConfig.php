<?php

namespace Codedeploy\Uptime\Commands;

use Illuminate\Console\Command;
use Codedeploy\Uptime\Uptime;

class VerifyConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifies the cronmonitor.dev configuration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Uptime::application()->isSuccessful()) {
            return $this->info('Application authentication with cronmonitor.dev succeeded.');
        }

        $this->error('Application authentication with cronmonitor.dev failed.');
    }
}
