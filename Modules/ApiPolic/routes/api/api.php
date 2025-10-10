<?php

use Illuminate\Support\Facades\Route;
use Modules\ApiPolic\Http\Controllers\ApiVehicleController;

Route::prefix('v1')->group(function () {
    Route::apiResource('vehicles', ApiVehicleController::class);
});
