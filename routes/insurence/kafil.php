<?php

use App\Http\Controllers\Insurence\KafilController;
use Illuminate\Support\Facades\Route;

// Accident insurance routes with language support

Route::get('/gas-cylinders', [KafilController::class, 'main'])->name('kafil.main');
Route::get('/gas/application', [KafilController::class, 'applicationView'])->name('kafil.application.view');
Route::post('/gas/application', [KafilController::class, 'application'])->name('kafil.application');
Route::post('/gas/storage', [KafilController::class, 'storage'])->name('kafil.storage');
Route::post('/gas/calculation', [KafilController::class, 'calculation'])->name('kafil.calculation');
