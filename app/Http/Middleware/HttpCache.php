<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class HttpCache
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
        $response = $next($request);
        $response->header('Expires', substr(Carbon::now()->addWeek()->toRfc2822String(), 0, -5) . 'GMT');
        $response->header('Cache-Control', 'max-age=2628000, public');

        return $response;
    }
}
