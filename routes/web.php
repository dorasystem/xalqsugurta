<?php

use App\Http\Controllers\ApiControllers\DriverInfoController;
use App\Http\Controllers\Insurence\PaymentController;
use App\Http\Controllers\ApiControllers\PropertyInfoController;
use Faker\Provider\ar_EG\Person;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\PersonInfoController;
use App\Http\Controllers\ApiControllers\VehicleInfoController;
use App\Models\Order;

// Language routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ru|uz|en']], function () {
    Route::get('/', function ($locale) {
        App::setLocale($locale);
        return view('welcome');
    })->name('home');

    // Unified payment routes for all insurance products
    Route::get('/payment/{orderId}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{orderId}/process', [PaymentController::class, 'process'])->name('payment.process');

    require_once 'insurence/osago.php';
    require_once 'insurence/accident.php';
    require_once 'insurence/property.php';
    require_once 'insurence/gas.php';
});

Route::post('/get-vehicle-info', [VehicleInfoController::class, 'getVehicleInfo']);
Route::post('/get-person-info', [PersonInfoController::class, 'getPersonInfo']);
Route::post('/get-person-info-by-birthdate', [PersonInfoController::class, 'getPersonInfoByBirthdate']);
Route::post('/get-driver-info', [DriverInfoController::class, 'getDriverInfo']);
Route::post('fetch-cadaster', [PropertyInfoController::class, 'fetchPropertyInfo']);

// Default route (redirects to Russian)
Route::get('/', function () {
    return redirect()->route('home', ['locale' => 'uz']);
});

// Fallback routes without language prefix (for backward compatibility)
Route::get('/fallback', function () {
    return view('welcome');
})->name('fallback');


Route::get('/icons', function () {
    return view('icons');
})->name('icons');

// Debug route (remove in production)
Route::get('/debug-session', function () {
    return view('debug-session');
})->name('debug.session');

Route::get('/test', function () {
    return  Order::query()->latest()->limit(3)->get();
})->name('test');
