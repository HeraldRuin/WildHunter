<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'user/'.config('settings.settings_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/collection','CollectionTimerController@indexTimerCollection')->name('settings.vendor.collection-timer');
    Route::post('/store/collection','CollectionTimerController@store')->name('settings.vendor.collection-timer.store');

    Route::get('/beds','CollectionTimerController@indexTimerBeds')->name('settings.vendor.beds-timer');
    Route::post('/store/bed','CollectionTimerController@store')->name('settings.vendor.beds-timer.store');
});
