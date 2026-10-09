<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        View::composer('*', function ($view) {
            // $view->with('setting', Setting::first());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Deteksi N+1 sejak dini di local/staging.
        Model::preventLazyLoading(! app()->isProduction());

        // Limiter untuk `throttle:api` (dipakai routes/api.php).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Paksa https bila akses berjalan di atas https (langsung maupun
        // via X-Forwarded-Proto dari proxy) atau APP_URL memakai https.
        // Mencegah mixed content: semua url()/route()/asset() jadi https.
        $request = $this->app['request'] ?? null;
        if (($request && $request->secure())
            || ($request && $request->header('X-Forwarded-Proto') === 'https')
            || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
