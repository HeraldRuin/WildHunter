<?php
use \Illuminate\Support\Facades\Route;
Route::get('/','AnimalController@index')->name('animal.admin.index');
Route::get('/create','AnimalController@create')->name('animal.admin.create');
Route::get('/edit/{id}','AnimalController@edit')->name('animal.admin.edit');
Route::post('/store/{id}','AnimalController@store')->name('animal.admin.store');
//Route::post('/bulkEdit','AnimalController@bulkEdit')->name('car.admin.bulkEdit');
//Route::get('/recovery','AnimalController@recovery')->name('car.admin.recovery');
//Route::get('/getForSelect2','AnimalController@getForSelect2')->name('car.admin.getForSelect2');
