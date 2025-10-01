<?php

use App\Http\Controllers\Insurence\OsagoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

// OSAGO routes with language support

Route::get('/osago', [OsagoController::class, 'main'])->name('osago.main');
Route::get('/osago/application', [OsagoController::class, 'application'])->name('osago.application');
Route::get('/osago/payment', [OsagoController::class, 'payment'])->name('osago.payment');
