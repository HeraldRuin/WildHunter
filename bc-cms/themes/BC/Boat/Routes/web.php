<?php

use Illuminate\Support\Facades\Route;
use Themes\BC\Boat\Pages\BoatIndexPage;

Route::group(['prefix' => config('boat.boat_route_prefix')], function () {
    Route::get('/', BoatIndexPage::class)->name('boat.search'); // Search
});
