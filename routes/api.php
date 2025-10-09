<?php

use App\Http\Controllers\ApiControllers\PersonInfoController;
use Illuminate\Support\Facades\Route;

// API routes are loaded here
// Module-specific routes are loaded via their ServiceProviders

Route::get('/get-person-info', [PersonInfoController::class, 'getPersonInfo']);
