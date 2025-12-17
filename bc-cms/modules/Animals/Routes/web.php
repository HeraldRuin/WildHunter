<?php
use \Illuminate\Support\Facades\Route;

Route::group(['prefix'=>config('animal.animal_route_prefix')],function(){
    Route::get('/','AnimalController@index')->name('animal.search'); // Search
    Route::get('/{slug}','AnimalController@detail')->name('animal.detail');// Detail
});

Route::group(['prefix'=>'user/'.config('animal.animal_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/','ManageAnimalController@manageAnimal')->name('animal.vendor.index');
    Route::get('/create','ManageAnimalController@create')->name('animal.vendor.create');
    Route::get('/edit/{id}','ManageAnimalController@edit')->name('animal.vendor.edit');
    Route::get('/del/{id}','ManageAnimalController@delete')->name('animal.vendor.delete');
    Route::post('/store/{id}','ManageAnimalController@store')->name('animal.vendor.store');
    Route::get('bulkEdit/{id}','ManageAnimalController@bulkEdit')->name("animal.vendor.bulk_edit");
    Route::get('/booking-report/bulkEdit/{id}','ManageAnimalController@bookingReportBulkEdit')->name("animal.vendor.booking_report.bulk_edit");
    Route::get('/recovery','ManageAnimalController@recovery')->name('animal.vendor.recovery');
    Route::get('/restore/{id}','ManageAnimalController@restore')->name('animal.vendor.restore');
});

Route::group(['prefix'=>'user/'.config('animal.animal_route_prefix')],function(){
    Route::group(['prefix'=>'hunting'],function(){
        Route::get('/','HuntingController@index')->name('animal.vendor.hunting.index');
        Route::get('/loadDates','HuntingController@loadDates')->name('animal.vendor.hunting.loadDates');
        Route::post('/store','HuntingController@store')->name('animal.vendor.hunting.store');
    });
});

Route::post(config('animal.animal_route_prefix').'/checkAvailability','AvailabilityController@checkAvailability')->name('animal.checkAvailability');
