<?php
use \Illuminate\Support\Facades\Route;

Route::get('/','WeaponController@index')->name('weapon.admin.index');
Route::get('/create','WeaponController@create')->name('weapon.admin.create');
Route::get('/edit/{id}','WeaponController@edit')->name('weapon.admin.edit');
Route::post('/store/{id}','WeaponController@store')->name('weapon.admin.store');
Route::post('/bulkEdit','WeaponController@bulkEdit')->name('weapon.admin.bulkEdit');
Route::get('/recovery','WeaponController@recovery')->name('weapon.admin.recovery');

//Calibers
Route::get('/caliber','CaliberController@index')->name('caliber.admin.index');
Route::get('/create/caliber','CaliberController@create')->name('caliber.admin.create');
Route::get('/caliber/edit/{id}','CaliberController@edit')->name('caliber.admin.edit');
Route::post('/caliber.store/{id}','CaliberController@store')->name('caliber.admin.store');
Route::post('/caliber/bulkEdit','CaliberController@bulkEdit')->name('caliber.admin.bulkEdit');

