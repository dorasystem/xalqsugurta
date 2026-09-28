<?php

namespace App\Providers;

use App\Services\ApiLogger;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Admin panel "API jurnali": every request to the insurer's API
        ApiLogger::register();

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // URL::forceScheme('https');
        // test branch
        // test branch
        // test branch
    }
}
