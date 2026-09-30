<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Every web request, including the Livewire round trips on
        // /livewire/update. Page loads and Livewire requests are counted
        // separately, because one page can fire several Livewire requests
        // (lazy components, polling). Guests, among them the crawlers, get
        // the lower limits and are counted per IP address.
        RateLimiter::for('web', function (Request $request) {
            $livewire = $request->hasHeader('X-Livewire');
            $user = $request->user();

            if ($user) {
                return Limit::perMinute($livewire ? 600 : 300)
                    ->by(($livewire ? 'livewire:' : 'page:') . 'user:' . $user->id);
            }

            return Limit::perMinute($livewire ? 120 : 60)
                ->by(($livewire ? 'livewire:' : 'page:') . 'ip:' . $request->ip());
        });

        // Downloads that build a PDF or a file from a query in the request,
        // on top of the 'web' limiter.
        RateLimiter::for('exports', function (Request $request) {
            $user = $request->user();
            $key = $user ? 'user:' . $user->id : 'ip:' . $request->ip();

            return $user
                ? [Limit::perMinute(10)->by('minute:' . $key), Limit::perHour(100)->by('hour:' . $key)]
                : [Limit::perMinute(3)->by('minute:' . $key), Limit::perHour(20)->by('hour:' . $key)];
        });
    }
}
