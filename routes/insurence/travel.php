<?php

use App\Http\Controllers\Insurence\TravelController;
use Illuminate\Support\Facades\Route;

// Travel insurance abroad (ВЗР): trip + program → applicant → travelers → confirm → payment

Route::group(['prefix' => 'travel', 'middleware' => 'product.on-sale:travel'], function () {

    // Step 1: Trip + program
    Route::get('/',                         [TravelController::class, 'index'])->name('travel.index');
    Route::post('/calculate',               [TravelController::class, 'calculate'])->middleware('throttle:30,1')->name('travel.calculate');
    Route::post('/trip',                    [TravelController::class, 'storeTrip'])->name('travel.storeTrip');

    // Step 2: Applicant
    Route::get('/applicant',                [TravelController::class, 'getApplicant'])->name('travel.getApplicant');
    Route::post('/applicant',               [TravelController::class, 'storeApplicant'])->name('travel.storeApplicant');

    // Step 3: Travelers
    Route::get('/travelers',                [TravelController::class, 'getTravelers'])->name('travel.getTravelers');
    Route::post('/travelers/add',           [TravelController::class, 'addTraveler'])->name('travel.addTraveler');
    Route::post('/travelers/remove/{index}', [TravelController::class, 'removeTraveler'])->name('travel.removeTraveler')->where('index', '[0-9]+');
    Route::post('/travelers/confirm',       [TravelController::class, 'confirmTravelers'])->name('travel.confirmTravelers');

    // Step 4: Confirm + sale
    Route::get('/confirm',                  [TravelController::class, 'getConfirm'])->name('travel.getConfirm');
    Route::post('/confirm',                 [TravelController::class, 'storeApplication'])->name('travel.storeApplication');
});

// Back from the insurer's Payme / Click (policy.return_url): open even if the product was taken off sale
Route::get('/travel/paid', [TravelController::class, 'paid'])->name('travel.paid');
