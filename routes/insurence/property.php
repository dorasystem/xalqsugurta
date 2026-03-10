<?php

use App\Http\Controllers\Insurence\PropertyController;
use Illuminate\Support\Facades\Route;

// Property insurance routes

Route::group(['prefix' => 'property'], function () {

    // Step 1: Applicant
    Route::get('/',             [PropertyController::class, 'index'])->name('property.index');
    Route::post('/applicant',   [PropertyController::class, 'storeApplicant'])->name('property.storeApplicant');

    // Step 2: Property + Calculation
    Route::get('/property',     [PropertyController::class, 'getProperty'])->name('property.getProperty');
    Route::post('/property',    [PropertyController::class, 'storeProperty'])->name('property.storeProperty');

    // Step 4: Confirm + Submit
    Route::get('/confirm',      [PropertyController::class, 'getConfirm'])->name('property.getConfirm');
    Route::post('/confirm',     [PropertyController::class, 'storeApplication'])->name('property.storeApplication');

    // AJAX
    Route::post('/find-person', [PropertyController::class, 'findPerson'])->name('property.findPerson');

});

// Cadaster AJAX (shared route, outside prefix)
Route::post('/fetch-cadaster', [PropertyController::class, 'fetchCadaster'])->name('fetch.cadaster');
