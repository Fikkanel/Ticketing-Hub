<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL; // 1. Tambahkan Import URL

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hanya paksa HTTPS jika di production
        // Paksa HTTPS (Fix Mixed Content) - Diaktifkan untuk semua environment remote
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        // Share settings globally - Cached for 1 hour to reduce DB overhead
        try {
            $settings = \Illuminate\Support\Facades\Cache::remember('global_settings', 3600, function () {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    return \App\Models\Setting::all()->pluck('value', 'key')->toArray();
                }
                return [];
            });
            \Illuminate\Support\Facades\View::share('globalSettings', $settings);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\View::share('globalSettings', []);
            \Illuminate\Support\Facades\Log::warning('AppServiceProvider: Failed to load settings. ' . $e->getMessage());
        }
    }
}
