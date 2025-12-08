<?php

use Illuminate\Support\Facades\Route;
use Themes\BC\Animal\Pages\AnimalIndexPage;

Route::group(['prefix' => config('animal.animal_route_prefix')], function () {
    Route::get('/', AnimalIndexPage::class)->name('animal.search'); // Search
});
