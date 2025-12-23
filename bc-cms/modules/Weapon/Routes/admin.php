<?php
use \Illuminate\Support\Facades\Route;

Route::get('/','WeaponController@index')->name('weapon.admin.index');
Route::get('/create','WeaponController@create')->name('weapon.admin.create');
Route::get('/edit/{id}','WeaponController@edit')->name('weapon.admin.edit');
Route::post('/store/{id}','WeaponController@store')->name('weapon.admin.store');
Route::get('/recovery','WeaponController@recovery')->name('weapon.admin.recovery');

Route::get('/caliber','CaliberController@index')->name('caliber.admin.index');

