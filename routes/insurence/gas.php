<?php

use App\Http\Controllers\Insurence\GasBallonController;
use Illuminate\Support\Facades\Route;

// Gas Cylinder insurance routes with language support (mirrors Accident)
Route::get('/gas', [GasBallonController::class, 'main'])->name('gas.main');
Route::get('/gas/application/{orderId}', [GasBallonController::class, 'applicationView'])->name('gas.application.view');
Route::post('/gas/application', [GasBallonController::class, 'application'])->name('gas.application');
// Storage endpoint if you want separate finalization step later
// Route::post('/gas/storage', [GasBallonController::class, 'storage'])->name('gas.storage');
