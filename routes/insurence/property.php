<?php

use App\Http\Controllers\Insurence\PropertyController;
use Illuminate\Support\Facades\Route;

// Property insurance routes with language support

Route::get('/property', [PropertyController::class, 'main'])->name('property.main');
Route::get('/property/application', [PropertyController::class, 'applicationView'])->name('property.application.view');
Route::post('/property/application', [PropertyController::class, 'application'])->name('property.application');
Route::post('/property/storage', [PropertyController::class, 'storage'])->name('property.storage');
Route::get('/property/payment/{orderId}', [PropertyController::class, 'payment'])->name('property.payment');

Route::post('/property/calculation', [PropertyController::class, 'calculation'])->name('property.calculation');
Route::post('/fetch-cadaster', [PropertyController::class, 'fetchCadaster'])->name('fetch.cadaster');
