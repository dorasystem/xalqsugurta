<?php

use App\Http\Controllers\Insurence\OsgorController;
use Illuminate\Support\Facades\Route;

// OSGOR — Mandatory Employer Liability Insurance

Route::group(['prefix' => 'osgor'], function () {

    // Step 1: Organization search
    Route::get('/',                [OsgorController::class, 'index'])->name('osgor.index');
    Route::post('/applicant',      [OsgorController::class, 'storeApplicant'])->name('osgor.storeApplicant');

    // Step 2: Calculator
    Route::get('/calculator',      [OsgorController::class, 'getCalculator'])->name('osgor.getCalculator');
    Route::post('/calculate',      [OsgorController::class, 'calculate'])->name('osgor.calculate');
    Route::post('/calculator',     [OsgorController::class, 'storeCalculation'])->name('osgor.storeCalculation');

    // Step 3: Confirm + Submit
    Route::get('/confirm',         [OsgorController::class, 'getConfirm'])->name('osgor.getConfirm');
    Route::post('/confirm',        [OsgorController::class, 'storeApplication'])->name('osgor.storeApplication');

});
