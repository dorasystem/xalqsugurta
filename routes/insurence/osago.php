<?php

use App\Http\Controllers\Insurence\OsagoController;
use Illuminate\Support\Facades\Route;

// OSAGO: vehicle → owner + applicant → term + drivers → confirm

// New applications close when OSAGO is off sale; old payment links stay open
Route::group(['prefix' => 'osago', 'middleware' => 'product.on-sale:osago'], function () {

    // Step 1: Vehicle
    Route::get('/',         [OsagoController::class, 'index'])->name('osago.index');
    Route::post('/vehicle', [OsagoController::class, 'storeVehicle'])->name('osago.storeVehicle');

    // Step 2: Owner + applicant
    Route::get('/owner',  [OsagoController::class, 'getOwner'])->name('osago.getOwner');
    Route::post('/owner', [OsagoController::class, 'storeOwner'])->name('osago.storeOwner');

    // Step 3: Term + drivers
    Route::get('/terms',          [OsagoController::class, 'getTerms'])->name('osago.getTerms');
    Route::post('/terms',         [OsagoController::class, 'storeTerms'])->name('osago.storeTerms');
    Route::post('/drivers',                [OsagoController::class, 'addDriver'])->middleware('throttle:20,1')->name('osago.addDriver');
    Route::post('/drivers/remove/{index}', [OsagoController::class, 'removeDriver'])->whereNumber('index')->name('osago.removeDriver');

    // Step 4: Confirm + submit
    Route::get('/confirm',            [OsagoController::class, 'getConfirm'])->name('osago.getConfirm');
    Route::post('/store-application', [OsagoController::class, 'storeApplication'])->name('osago.storeApplication');
});

Route::get('/osago/payment/{order}', [OsagoController::class, 'payment'])->whereNumber('order')->name('osago.payment');
