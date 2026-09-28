<?php

use App\Http\Controllers\Insurence\PaymentController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ClaimFileController;
use App\Http\Controllers\InfoPageController;
use App\Http\Controllers\MyPoliciesController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\SearchController;
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
    Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:60,1')->name('search');
    Route::get('/info/{key}', [InfoPageController::class, 'show'])->where('key', '[a-z0-9-]+')->name('info.show');
    Route::get('/products/{product}', [ProductPageController::class, 'show'])->where('product', '[a-z0-9_-]+')->name('product.show');

    // Insured-event reports and call-back requests (handled in the admin panel: Murojaatlar)
    Route::get('/claims', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/claims', [ClaimController::class, 'store'])->middleware('throttle:5,10')->name('claims.store');
    Route::get('/claims/sent/{number}', [ClaimController::class, 'sent'])->name('claims.sent');
    Route::get('/claims/status', [ClaimController::class, 'status'])->middleware('throttle:20,1')->name('claims.status');
    Route::post('/subscribe', [NewsletterController::class, 'store'])->middleware('throttle:5,10')->name('newsletter.store');
    Route::get('/callback', [CallbackController::class, 'create'])->name('callback');
    Route::post('/callback', [CallbackController::class, 'store'])->middleware('throttle:5,10')->name('callback.store');

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

// Claim attachments for staff (links are signed in the admin panel)
Route::get('/admin-files/claims/{claim}/{index}', ClaimFileController::class)
    ->middleware('signed')->whereNumber('index')->name('claims.file');

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
