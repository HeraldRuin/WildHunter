<?php
use \Illuminate\Support\Facades\Route;

Route::get('/services', 'AdditionalSystemController@index')->name('additional_system.admin.index');
Route::get('/services/create', 'AdditionalSystemController@create')->name('additional_system.admin.create');
Route::get('/services/edit/{id}', 'AdditionalSystemController@edit')->name('additional_system.admin.edit');
Route::post('/services/store/{id}', 'AdditionalSystemController@store')->name('additional_system.admin.store');
Route::post('/services/bulkEdit', 'AdditionalSystemController@bulkEdit')->name('additional_system.admin.bulkEdit');

//Route::get('/','AnimalController@index')->name('animal.admin.index');
//Route::get('/create','AnimalController@create')->name('animal.admin.create');
//Route::get('/edit/{id}','AnimalController@edit')->name('animal.admin.edit');
//Route::post('/store/{id}','AnimalController@store')->name('animal.admin.store');
//Route::post('/bulkEdit','AnimalController@bulkEdit')->name('animal.admin.bulkEdit');
//Route::get('/recovery','AnimalController@recovery')->name('animal.admin.recovery');
//
//Route::get('/getForSelect2','AnimalController@getForSelect2')->name('animal.admin.getForSelect2');
//
//Route::group(['prefix'=>'availability'],function(){
//    Route::get('/','AvailabilityController@index')->name('animal.admin.availability');
//    Route::get('/loadDates','AvailabilityController@loadDates')->name('animal.admin.availability.loadDates');
//    Route::post('/store','AvailabilityController@store')->name('animal.admin.availability.store');
//});
