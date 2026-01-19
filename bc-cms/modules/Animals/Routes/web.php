<?php

use Illuminate\Support\Facades\Route;
use Modules\Animals\User\OrganisationController;

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
    Route::get('bulkEdit/{id}','ManageAnimalController@bulkEDetach')->name("animal.vendor.bulk_detach");
    Route::post('bulkEdit','ManageAnimalController@bulkEAttach')->name("animal.vendor.bulk_attach");
    Route::post('update-hunters-count/{id}','ManageAnimalController@updateHuntersCount')->name("animal.vendor.update_hunters_count");
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

Route::group(['prefix'=>'organisation'],function(){
    Route::get('/','OrganisationController@index')->name('animal.vendor.organisation');
});

Route::group(['prefix'=>config('animal.animal_route_prefix')],function(){
    Route::post('/{animal}/period/create', [OrganisationController::class, 'create'])->name('animal.vendor.period.create');
    Route::post('/period/{period}/update', [OrganisationController::class, 'update']);
    Route::post('/period/{period}', [OrganisationController::class, 'delete'])->name('animal.vendor.period.delete');

});

Route::post(config('animal.animal_route_prefix').'/checkAvailability','AvailabilityController@checkAvailability')->name('animal.checkAvailability');
