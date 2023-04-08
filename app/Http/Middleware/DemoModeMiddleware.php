<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class DemoModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $demo_mode = config('constants.demo_mode');
        if($demo_mode == "1") {
            return back()->withErrors(__('crud.general.demo'));
        }
        return $next($request);
    }
}