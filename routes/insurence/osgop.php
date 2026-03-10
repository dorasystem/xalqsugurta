<?php

use App\Http\Controllers\Insurence\OsgopController;
use App\Http\Controllers\Insurence\OsgopStepController;
use Illuminate\Support\Facades\Route;

// OSGOP (Обязательное Страхование Гражданской Ответственности Перевозчиков)

Route::group(['prefix' => 'osgop'], function () {

    // ── Index ─────────────────────────────────────────────────────────────────
    Route::get('/', [OsgopController::class, 'index'])->name('osgop.index');

    // ── Applicant ─────────────────────────────────────────────────────────────
    Route::get('/get-applicant',      [OsgopController::class, 'getApplicant'])->name('osgop.getApplicant');
    Route::post('/store-applicant-company',   [OsgopController::class, 'storeCompanyApplicant'])->name('osgop.storeCompanyApplicant');
    Route::post('/store-applicant-individual',   [OsgopController::class, 'storeIndividualApplicant'])->name('osgop.storeIndividualApplicant');



    Route::post('/confirm-applicant', [OsgopController::class, 'confirmApplicant'])->name('osgop.confirmApplicant');

    // ── Vehicle ───────────────────────────────────────────────────────────────
    Route::get('/get-vehicle',    [OsgopController::class, 'getVehicle'])->name('osgop.getVehicle');
    Route::post('/store-vehicle', [OsgopController::class, 'storeVehicle'])->name('osgop.storeVehicle');

    // ── Calculator ────────────────────────────────────────────────────────────
    Route::get('/get-calculator',    [OsgopController::class, 'getCalculator'])->name('osgop.getCalculator');
    Route::post('/calculate',        [OsgopController::class, 'calculate'])->name('osgop.calculate');
    Route::post('/store-application',[OsgopController::class, 'storeApplication'])->name('osgop.storeApplication');

});
