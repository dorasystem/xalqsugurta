<?php

use App\Http\Controllers\Insurence\KaskoController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'kasko'], function () {

    // Step 1: Applicant
    Route::get('/',          [KaskoController::class, 'index'])->name('kasko.index');
    Route::post('/applicant',[KaskoController::class, 'storeApplicant'])->name('kasko.storeApplicant');

    // Step 2: Vehicle + Calculation
    Route::get('/vehicle',   [KaskoController::class, 'getVehicle'])->name('kasko.getVehicle');
    Route::post('/vehicle',  [KaskoController::class, 'storeVehicle'])->name('kasko.storeVehicle');

    // Step 3: Confirm + Submit
    Route::get('/confirm',   [KaskoController::class, 'getConfirm'])->name('kasko.getConfirm');
    Route::post('/confirm',  [KaskoController::class, 'storeApplication'])->name('kasko.storeApplication');

    // AJAX
    Route::post('/find-person',   [KaskoController::class, 'findPerson'])->name('kasko.findPerson');
    Route::post('/find-vehicle',  [KaskoController::class, 'findVehicleAjax'])->name('kasko.findVehicle');

});
