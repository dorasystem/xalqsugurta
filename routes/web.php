<?php

use App\Http\Controllers\ApiControllers\DriverInfoController;
use Faker\Provider\ar_EG\Person;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\PersonInfoController;
use App\Http\Controllers\ApiControllers\VehicleInfoController;

// Language routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ru|uz|en']], function () {
    Route::get('/', function ($locale) {
        App::setLocale($locale);
        return view('welcome');
    })->name('home');

    require_once 'insurence/osago.php';
    require_once 'insurence/accident.php';
});

Route::post('/get-vehicle-info', [VehicleInfoController::class, 'getVehicleInfo']);
Route::post('/get-person-info', [PersonInfoController::class, 'getPersonInfo']);
Route::post('/get-person-info-by-birthdate', [PersonInfoController::class, 'getPersonInfoByBirthdate']);
Route::post('/get-driver-info', [DriverInfoController::class, 'getDriverInfo']);

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
