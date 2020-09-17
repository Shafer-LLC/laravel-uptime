<?php

namespace Codedeploy\Uptime\Controllers;

use Codedeploy\Uptime\Api;

class Application extends Controller
{
    /**
     * Responds with the configured application ID.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        return $this->data([
            'app_id' => config('monitor.app_id'),

            'monitor_package_version' => Api::PACKAGE_VERSION,
        ]);
    }
}
