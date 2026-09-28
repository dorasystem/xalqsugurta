<?php

use App\Http\Controllers\Insurence\OsagoController;
use Illuminate\Support\Facades\Route;

// OSAGO routes with language support

// New applications close when OSAGO is off sale; the payment page of an existing order stays open
Route::middleware('product.on-sale:osago')->group(function () {
    Route::get('/osago', [OsagoController::class, 'main'])->name('osago.main');
    Route::get('/osago/application', [OsagoController::class, 'applicationView'])->name('osago.application.view');
    Route::post('/osago/application', [OsagoController::class, 'application'])->name('osago.application');
    Route::post('/osago/storage', [OsagoController::class, 'storage'])->name('osago.storage');
});
Route::get('/osago/payment/{order}', [OsagoController::class, 'payment'])->name('osago.payment');
