<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payments\Click\ClickController;
use App\Http\Controllers\Payments\PayMe\PaymeController;
use App\Http\Controllers\ApiControllers\PersonInfoController;

// API routes are loaded here
// Module-specific routes are loaded via their ServiceProviders

Route::get('/get-person-info', [PersonInfoController::class, 'getPersonInfo']);



//Payments

//Payme
Route::get('payment', [PaymeController::class, 'payment'])->name('payment.payme');

Route::post('payme/callback', [PaymeController::class, 'handleCallback'])
    ->middleware('payme')
    ->name('payment.payme.callback');


//Click
Route::get('payment/click', [ClickController::class, 'payment'])->name('payment.click');
Route::post('/prepare', [ClickController::class, 'prepare']);
Route::post('/complete', [ClickController::class, 'complete']);
