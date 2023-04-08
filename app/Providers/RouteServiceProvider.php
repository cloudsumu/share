<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME          = '/';
    public const ADMIN_HOME    = '/admin/dashboard';
    public const AGENT_HOME    = '/agent/dashboard';
    // public const PROVIDER_HOME = '/provider/dashboard';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api/user')
                ->middleware('api', 'loc')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::prefix('api/provider')
                ->middleware('api', 'loc')
                ->namespace($this->namespace)
                ->group(base_path('routes/providerapi.php'));

            Route::middleware('web', 'loc')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::prefix('admin')
                ->middleware(['web', 'loc'])
                ->namespace($this->namespace)
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::prefix('agent')
                ->middleware(['web', 'loc'])
                ->namespace($this->namespace)
                ->name('agent.')
                ->group(base_path('routes/agent.php'));

            // Route::prefix('provider')
            //     ->middleware(['web', 'loc'])
            //     ->namespace($this->namespace)
            //     ->name('provider.')
            //     ->group(base_path('routes/provider.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
