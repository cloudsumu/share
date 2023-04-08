<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SelectLocale
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
        if(auth()->guard('web')->check()) {
            App::setLocale($request->user()->language ?? config('translatable.fallback_locale'));
        }
        elseif(auth()->guard('provider')->check()) {
            App::setLocale($request->user('provider')->language ?? config('translatable.fallback_locale'));
        }
        elseif(auth()->guard('admin')->check()) {
            App::setLocale($request->user('admin')->language ?? config('translatable.fallback_locale'));
        }
        elseif(auth()->guard('agent')->check()) {
            App::setLocale($request->user('agent')->language ?? config('translatable.fallback_locale'));
        }
        else {
            if(session()->has('locale')) {
                App::setLocale(session()->get('locale'));
            }
            else {
                App::setLocale(config('translatable.fallback_locale'));
            }
        }
        return $next($request);
    }
}
