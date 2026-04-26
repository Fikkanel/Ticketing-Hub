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

        // Share settings globally
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
            \Illuminate\Support\Facades\View::share('globalSettings', $settings);
        }
    }
}