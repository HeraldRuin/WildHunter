<?php
use \Illuminate\Support\Facades\Route;
Route::get('/','AnimalController@index')->name('animal.admin.index');
Route::get('/create','AnimalController@create')->name('animal.admin.create');
Route::get('/edit/{id}','AnimalController@edit')->name('animal.admin.edit');
Route::post('/store/{id}','AnimalController@store')->name('animal.admin.store');
Route::post('/bulkEdit','AnimalController@bulkEdit')->name('animal.admin.bulkEdit');
Route::get('/recovery','AnimalController@recovery')->name('animal.admin.recovery');

Route::get('/getForSelect2','AnimalController@getForSelect2')->name('animal.admin.getForSelect2');

Route::group(['prefix'=>'availability'],function(){
    Route::get('/','AvailabilityController@index')->name('animal.admin.availability');
    Route::get('/loadDates','AvailabilityController@loadDates')->name('animal.admin.availability.loadDates');
    Route::post('/store','AvailabilityController@store')->name('animal.admin.availability.store');
});
