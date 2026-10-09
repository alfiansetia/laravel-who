<?php

namespace App\Providers;

use App\Models\Setting;
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
