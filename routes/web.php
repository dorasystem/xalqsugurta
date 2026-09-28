<?php

use App\Http\Controllers\Insurence\PaymentController;
use App\Http\Controllers\MyPoliciesController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ApiControllers\PropertyInfoController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
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

    // Product info pages (texts / FAQ / rules from the admin panel)
    Route::get('/products/{product}', [ProductPageController::class, 'show'])->where('product', '[a-z0-9_-]+')->name('product.show');

    // "Mening polislarim": phone + SMS code, then the orders placed with that phone
    Route::get('/my-policies', [MyPoliciesController::class, 'index'])->name('my-policies');
    Route::post('/my-policies/code', [MyPoliciesController::class, 'sendCode'])->middleware('throttle:10,1')->name('my-policies.send');
    Route::post('/my-policies/verify', [MyPoliciesController::class, 'verify'])->middleware('throttle:20,1')->name('my-policies.verify');
    Route::post('/my-policies/logout', [MyPoliciesController::class, 'logout'])->name('my-policies.logout');

    require __DIR__ . '/insurence/osago.php';
    require __DIR__ . '/insurence/accident.php';
    require __DIR__ . '/insurence/property.php';
    require __DIR__ . '/insurence/gas.php';
    require __DIR__ . '/insurence/osgor.php';
    require __DIR__ . '/insurence/osgop.php';
    require __DIR__ . '/insurence/kasko.php';
    require __DIR__ . '/insurence/tourist.php';
});

Route::post('fetch-cadaster', [PropertyInfoController::class, 'fetchPropertyInfo']);
Route::get('/get-regions', [ReferenceController::class, 'getRegions'])->name('get-regions');
Route::get('/get-districts', [ReferenceController::class, 'getDistricts'])->name('get-districts');

// Search engines: products on sale in every locale; payment and "my policies" pages are private
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

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

// Developer helpers: they print session data and change an order, so they exist only locally
if (app()->isLocal()) {
    Route::get('/debug-session', function () {
        return view('debug-session');
    })->name('debug.session');

    Route::get('/test', function () {
        $order = Order::query()->find(9);
        $order?->update(['status' => Order::STATUS_NEW]);
        return $order;
    })->name('test');
}
