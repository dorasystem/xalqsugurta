<?php

use App\Http\Controllers\Insurence\AccidentController;
use Illuminate\Support\Facades\Route;

// Accident (Baxtsiz hodisa) insurance routes

Route::group(['prefix' => 'accident'], function () {

    // Step 1: Applicant
    Route::get('/',                            [AccidentController::class, 'index'])->name('accident.index');
    Route::post('/applicant',                  [AccidentController::class, 'storeApplicant'])->name('accident.storeApplicant');

    // Step 2: Persons list
    Route::get('/persons',                     [AccidentController::class, 'getPersons'])->name('accident.getPersons');
    Route::post('/find-person',                [AccidentController::class, 'findPerson'])->name('accident.findPerson');
    Route::post('/calculate-premium',          [AccidentController::class, 'calculatePremium'])->name('accident.calculatePremium');
    Route::post('/persons/add',                [AccidentController::class, 'addPerson'])->name('accident.addPerson');
    Route::post('/persons/remove/{index}',     [AccidentController::class, 'removePerson'])->name('accident.removePerson')->where('index', '[0-9]+');
    Route::post('/persons/confirm',            [AccidentController::class, 'confirmPersons'])->name('accident.confirmPersons');

    // Step 3: Calculator (dates)
    Route::get('/calculator',                  [AccidentController::class, 'getCalculator'])->name('accident.getCalculator');
    Route::post('/calculator',                 [AccidentController::class, 'storeCalculation'])->name('accident.storeCalculation');

    // Step 4: Confirm + Submit
    Route::get('/confirm',                     [AccidentController::class, 'getConfirm'])->name('accident.getConfirm');
    Route::post('/confirm',                    [AccidentController::class, 'storeApplication'])->name('accident.storeApplication');

});
