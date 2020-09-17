<?php

namespace Codedeploy\Uptime;

use Illuminate\Support\Facades\Facade;

class Uptime extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'uptime.api';
    }
}
