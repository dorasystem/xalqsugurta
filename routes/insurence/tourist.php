<?php

use App\Http\Controllers\Insurence\TouristController;
use Illuminate\Support\Facades\Route;

// Tourist (Mehmonxona turistlari) accident insurance routes

Route::group(['prefix' => 'tourist'], function () {

    // Step 1: Applicant
    Route::get('/',                            [TouristController::class, 'index'])->name('tourist.index');
    Route::post('/applicant',                  [TouristController::class, 'storeApplicant'])->name('tourist.storeApplicant');

    // Step 2: Persons list
    Route::get('/persons',                     [TouristController::class, 'getPersons'])->name('tourist.getPersons');
    Route::post('/find-person',                [TouristController::class, 'findPerson'])->name('tourist.findPerson');
    Route::post('/calculate-premium',          [TouristController::class, 'calculatePremium'])->name('tourist.calculatePremium');
    Route::post('/persons/add',                [TouristController::class, 'addPerson'])->name('tourist.addPerson');
    Route::post('/persons/remove/{index}',     [TouristController::class, 'removePerson'])->name('tourist.removePerson')->where('index', '[0-9]+');
    Route::post('/persons/confirm',            [TouristController::class, 'confirmPersons'])->name('tourist.confirmPersons');

    // Step 3: Calculator (dates)
    Route::get('/calculator',                  [TouristController::class, 'getCalculator'])->name('tourist.getCalculator');
    Route::post('/calculator',                 [TouristController::class, 'storeCalculation'])->name('tourist.storeCalculation');

    // Step 4: Confirm + Submit
    Route::get('/confirm',                     [TouristController::class, 'getConfirm'])->name('tourist.getConfirm');
    Route::post('/confirm',                    [TouristController::class, 'storeApplication'])->name('tourist.storeApplication');

});
