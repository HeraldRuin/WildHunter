<?php

use Illuminate\Support\Facades\Route;
use Themes\BC\Car\Pages\CarIndexPage;

// Routes for Guest

// Tour
Route::group(['prefix' => config('car.car_route_prefix')], function () {
    Route::get('/', CarIndexPage::class)->name('car.search'); // Search
    //Route::get('/{slug}','\Modules\Tour\Controllers\TourController@detail');// Detail
});
