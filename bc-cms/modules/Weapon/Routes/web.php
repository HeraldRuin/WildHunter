<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>config('weapon.weapon_route_prefix')],function(){
    Route::get('/','WeaponController@index')->name('weapon.search'); // Search
    Route::get('/{slug}','AnimalController@detail')->name('weapon.detail');// Detail
});
