<?php

namespace Codedeploy\Uptime;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Codedeploy\Uptime\Api;
use Codedeploy\Uptime\Commands\VerifyConfig;
use Codedeploy\Uptime\Listeners\CaptureFailedJob;

class UptimeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/cronmonitor-uptime.php' => config_path('cronmonitor-uptime.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                VerifyConfig::class,
            ]);
        }

        if (config('cronmonitor-uptime.failed_jobs')) {
            $this->app['events']->listen(JobFailed::class, CaptureFailedJob::class);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cronmonitor-uptime.php', 'cronmonitor-uptime');

        $this->app->singleton('uptime.api-client', function () {
            return new HttpClient([
                'http_errors' => false,
            ]);
        });

        $this->app->singleton('uptime.api', function () {
            return new Api(
                app('uptime.api-client'),
                config('monitor.app_id') ?: '',
                config('monitor.app_token') ?: '',
                config('monitor.endpoint')
            );
        });

        $this->app->singleton('uptime.monitors', function () {
            return [
                'broadcasting' => Monitors\Pusher::class,
                'cache'        => Monitors\Cache::class,
                'composer'     => Monitors\Composer::class,
                'config'       => Monitors\Config::class,
                'database'     => Monitors\Database::class,
                'horizon'      => Monitors\Horizon::class,
                'mail'         => Monitors\Mail::class,
                'queue'        => Monitors\Queue::class,
                'redis'        => Monitors\Redis::class,
                'storage'      => Monitors\Storage::class,
            ];
        });

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::prefix('_uptime')->group(function () {
            Route::middleware(Middleware::class)->group(function () {
                Route::get('application', Controllers\Application::class);
                Route::post('check', Controllers\Check::class);
                Route::get('configuration', Controllers\Configuration::class);
                Route::get('failedJobs', Controllers\FailedJobs::class);
                Route::post('failedJobs/retry', Controllers\RetryFailedJob::class);
            });
        });
    }

}
