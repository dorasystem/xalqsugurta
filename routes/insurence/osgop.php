<?php

use App\Http\Controllers\Insurence\OsgopController;
use Illuminate\Support\Facades\Route;

// OSGOP (Обязательное Страхование Гражданской Ответственности Перевозчиков)

Route::group(['prefix' => 'osgop', 'middleware' => 'product.on-sale:osgop'], function () {

    // Step 1: Applicant (person or organization)
    Route::get('/', [OsgopController::class, 'index'])->name('osgop.index');
    Route::post('/store-applicant-company',    [OsgopController::class, 'storeCompanyApplicant'])->name('osgop.storeCompanyApplicant');
    Route::post('/store-applicant-individual', [OsgopController::class, 'storeIndividualApplicant'])->name('osgop.storeIndividualApplicant');

    // Step 2: Vehicle
    Route::get('/get-vehicle',    [OsgopController::class, 'getVehicle'])->name('osgop.getVehicle');
    Route::post('/store-vehicle', [OsgopController::class, 'storeVehicle'])->name('osgop.storeVehicle');

    // Step 3: Term + premium
    Route::get('/get-calculator', [OsgopController::class, 'getCalculator'])->name('osgop.getCalculator');
    Route::post('/calculate',     [OsgopController::class, 'calculate'])->name('osgop.calculate');
    Route::post('/calculator',    [OsgopController::class, 'storeCalculation'])->name('osgop.storeCalculation');

    // Step 4: Confirm + Submit
    Route::get('/confirm',           [OsgopController::class, 'getConfirm'])->name('osgop.getConfirm');
    Route::post('/store-application',[OsgopController::class, 'storeApplication'])->name('osgop.storeApplication');

});
