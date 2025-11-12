<?php
use Illuminate\Support\Facades\Route;
use Themes\BC\Tour\Pages\TourIndexPage;

// Routes for Guest

// Tour
Route::group(['prefix'=>config('tour.tour_route_prefix')],function(){
    Route::get('/',TourIndexPage::class)->name('tour.search'); // Search
    //Route::get('/{slug}','\Modules\Tour\Controllers\TourController@detail');// Detail
});
