<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'user/'.config('settings.settings_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/','CollectionTimerController@index')->name('settings.vendor.collection-timer');
    Route::post('/store','CollectionTimerController@store')->name('settings.vendor.collection-timer.store');
});
