<?php

use App\Http\Controllers\Insurence\AccidentController;
use Illuminate\Support\Facades\Route;

// OSAGO routes with language support

Route::get('/accident', [AccidentController::class, 'main'])->name('accident.main');
Route::get('/accident/application', [AccidentController::class, 'applicationView'])->name('accident.application.view');
Route::post('/accident/application', [AccidentController::class, 'application'])->name('accident.application');
Route::post('/accident/storage', [AccidentController::class, 'storage'])->name('accident.storage');
Route::get('/accident/payment/{orderId}', [AccidentController::class, 'payment'])->name('accident.payment');
Route::post('/accident/calculation', [AccidentController::class, 'calculation'])->name('accident.calculation');
