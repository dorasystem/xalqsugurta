<?php

namespace App\Providers;

use App\Services\ApiLogger;
use App\Services\PaymentSettings;
use App\Services\ProviderSettings;
use App\Services\SmsSettings;
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

        // Admin panel "To'lov tizimlari": saved Click / Payme settings override .env
        PaymentSettings::apply();

        // Admin panel "Sug'urtachi API": agencyId etc. override .env
        ProviderSettings::apply();

        // Admin panel "SMS xabarlar": Eskiz login and text override .env
        SmsSettings::apply();

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // URL::forceScheme('https');
        // test branch
        // test branch
        // test branch
    }
}
