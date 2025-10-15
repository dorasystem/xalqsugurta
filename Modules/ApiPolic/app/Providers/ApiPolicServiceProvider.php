<?php

namespace Modules\ApiPolic\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ApiPolicServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->registerApiRoutes();
    }

    /**
     * Register API routes.
     */
    protected function registerApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/../../routes/api/api.php');
    }
}









