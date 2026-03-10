<?php

use App\Http\Controllers\Insurence\GasBallonController;
use Illuminate\Support\Facades\Route;

// Gas Balloon insurance routes

Route::group(['prefix' => 'gas'], function () {

    // Step 1: Applicant
    Route::get('/',              [GasBallonController::class, 'index'])->name('gas.index');
    Route::post('/applicant',    [GasBallonController::class, 'storeApplicant'])->name('gas.storeApplicant');

    // Step 2: Property + Calculation
    Route::get('/property',      [GasBallonController::class, 'getProperty'])->name('gas.getProperty');
    Route::post('/property',     [GasBallonController::class, 'storeProperty'])->name('gas.storeProperty');

    // Step 3: Confirm + Submit
    Route::get('/confirm',       [GasBallonController::class, 'getConfirm'])->name('gas.getConfirm');
    Route::post('/confirm',      [GasBallonController::class, 'storeApplication'])->name('gas.storeApplication');

    // AJAX
    Route::post('/find-person',  [GasBallonController::class, 'findPerson'])->name('gas.findPerson');

});

// Cadaster AJAX (re-uses the same PropertyService endpoint)
Route::post('/fetch-cadaster-gas', [GasBallonController::class, 'fetchCadaster'])->name('fetch.cadaster.gas');
