<?php

use App\Http\Controllers\ApiControllers\DriverInfoController;
use App\Http\Controllers\Insurence\PaymentController;
use App\Http\Controllers\ApiControllers\PropertyInfoController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\PersonInfoController;
use App\Http\Controllers\ApiControllers\VehicleInfoController;
use App\Http\Controllers\ApiControllers\CompanyController;
use App\Http\Controllers\ApiControllers\ReferenceController;
use App\Models\Order;
use App\Models\Product;

// Language routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ru|uz|en']], function () {
    Route::get('/', function ($locale) {
        App::setLocale($locale);

        $products = Product::where('is_active', true)->orderBy('sort_order')->get();

        return view('welcome', compact('products'));
    })->name('home');

    // Unified payment route for all insurance products
    Route::get('/payment/{orderId}', [PaymentController::class, 'show'])->name('payment.show');

    require_once 'insurence/osago.php';
    require_once 'insurence/accident.php';
    require_once 'insurence/property.php';
    require_once 'insurence/gas.php';
    require_once 'insurence/osgor.php';
    require_once 'insurence/osgop.php';
    require_once 'insurence/kasko.php';
    require_once 'insurence/tourist.php';
});

Route::post('/get-vehicle-info', [VehicleInfoController::class, 'getVehicleInfo']);
Route::post('/get-person-info', [PersonInfoController::class, 'getPersonInfo']);
Route::post('/get-person-info-by-birthdate', [PersonInfoController::class, 'getPersonInfoByBirthdate']);
Route::post('/get-driver-info', [DriverInfoController::class, 'getDriverInfo']);
Route::post('fetch-cadaster', [PropertyInfoController::class, 'fetchPropertyInfo']);
Route::post('/get-company-info', [CompanyController::class, 'getCompanyInfo'])->name('get-company-info');
Route::get('/get-regions', [ReferenceController::class, 'getRegions'])->name('get-regions');
Route::get('/get-districts', [ReferenceController::class, 'getDistricts'])->name('get-districts');

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

    $order = Order::query()->find(9);
    $order?->update(['status' => Order::STATUS_NEW]);
    return $order;
})->name('test');
