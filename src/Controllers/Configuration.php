<?php

namespace Codedeploy\Uptime\Controllers;

use Codedeploy\Uptime\ConfigurationCollector;

class Configuration extends Controller
{
    /**
     * Responds with the monitoring options for this application.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        return $this->data(ConfigurationCollector::get());
    }
}
