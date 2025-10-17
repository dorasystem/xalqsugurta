<?php

use App\Http\Controllers\Insurence\OsagoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

// OSAGO routes with language support

Route::get('/osago', [OsagoController::class, 'main'])->name('osago.main');
Route::get('/osago/application', [OsagoController::class, 'application'])->name('osago.application');
Route::post('/osago/prepare', [OsagoController::class, 'prepare'])->name('osago.prepare');
Route::get('/osago/payment/{order}', [OsagoController::class, 'payment'])->name('osago.payment');
Route::post('/osago/calculation', [OsagoController::class, 'calculation'])->name('osago.calculation');


// AAG 0756068 01L097RB
// Ac 1253321
