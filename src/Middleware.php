<?php

namespace Codedeploy\Uptime;

use Closure;
use Illuminate\Support\Str;

class Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $original = hash('sha256', config('monitor.app_token'));

        $signature = Str::after($request->header('Authorization', ''), 'Bearer ');

        if (!hash_equals($original, $signature)) {
            abort(401);
        }

        return $next($request);
    }
}
