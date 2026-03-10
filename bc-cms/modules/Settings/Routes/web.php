<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'user/'.config('settings.settings_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/collection','CollectionTimerController@indexTimerCollection')->name('settings.vendor.collection-timer');
    Route::post('/store/collection_timer','CollectionTimerController@store')->name('settings.vendor.collection-timer.store');

    Route::get('/beds','CollectionTimerController@indexTimerBeds')->name('settings.vendor.beds-timer');
    Route::post('/store/bed_timer','CollectionTimerController@store')->name('settings.vendor.beds-timer.store');

    Route::get('/paid','CollectionTimerController@indexTimerPaid')->name('settings.vendor.paid-timer');
    Route::post('/store/paid_timer','CollectionTimerController@store')->name('settings.vendor.paid-timer.store');
});
