<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

// OSAGO routes with language support
Route::get('/osago', function ($locale = null) {
    if ($locale) {
        App::setLocale($locale);
    }
    return view('pages.insurence.main');
})->name('osago');
