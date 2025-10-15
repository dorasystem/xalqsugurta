<?php

use App\Http\Controllers\Insurence\AccidentController;
use Illuminate\Support\Facades\Route;

// Accident insurance routes with language support

Route::get('/accident', [AccidentController::class, 'main'])->name('accident.main');
Route::get('/accident/application', [AccidentController::class, 'applicationView'])->name('accident.application.view');
Route::post('/accident/application', [AccidentController::class, 'application'])->name('accident.application');
Route::post('/accident/storage', [AccidentController::class, 'storage'])->name('accident.storage');
Route::post('/accident/calculation', [AccidentController::class, 'calculation'])->name('accident.calculation');
