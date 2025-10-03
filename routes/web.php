<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

// Language routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ru|uz|en']], function () {
    Route::get('/', function ($locale) {
        App::setLocale($locale);
        return view('welcome');
    })->name('home');

    require_once 'insurence/osago.php';
});

// Default route (redirects to Russian)
Route::get('/', function () {
    return redirect()->route('home', ['locale' => 'uz']);
});

// Fallback routes without language prefix (for backward compatibility)
Route::get('/fallback', function () {
    return view('welcome');
})->name('fallback');
