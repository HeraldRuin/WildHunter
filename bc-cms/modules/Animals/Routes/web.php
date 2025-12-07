<?php
use \Illuminate\Support\Facades\Route;

Route::group(['prefix'=>config('animal.animal_route_prefix')],function(){
    Route::get('/','AnimalController@index')->name('animal.search'); // Search
    Route::get('/{slug}','AnimalController@detail')->name('animal.detail');// Detail
});

Route::group(['prefix'=>'user/'.config('animal.animal_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/','ManageCarController@manageCar')->name('animal.vendor.index');
    Route::get('/create','ManageCarController@createCar')->name('animal.vendor.create');
    Route::get('/edit/{id}','ManageCarController@editCar')->name('animal.vendor.edit');
    Route::get('/del/{id}','ManageCarController@deleteCar')->name('animal.vendor.delete');
    Route::post('/store/{id}','ManageCarController@store')->name('animal.vendor.store');
    Route::get('bulkEdit/{id}','ManageCarController@bulkEditCar')->name("animal.vendor.bulk_edit");
    Route::get('/booking-report/bulkEdit/{id}','ManageCarController@bookingReportBulkEdit')->name("animal.vendor.booking_report.bulk_edit");
    Route::get('/recovery','ManageCarController@recovery')->name('animal.vendor.recovery');
    Route::get('/restore/{id}','ManageCarController@restore')->name('animal.vendor.restore');
});

Route::group(['prefix'=>'user/'.config('animal.animal_route_prefix')],function(){
    Route::group(['prefix'=>'availability'],function(){
        Route::get('/','AvailabilityController@index')->name('animal.vendor.availability.index');
        Route::get('/loadDates','AvailabilityController@loadDates')->name('animal.vendor.availability.loadDates');
        Route::post('/store','AvailabilityController@store')->name('animal.vendor.availability.store');
    });
});

Route::post(config('animal.animal_route_prefix').'/checkAvailability','AnimalController@checkAvailability')->name('animal.checkAvailability');
