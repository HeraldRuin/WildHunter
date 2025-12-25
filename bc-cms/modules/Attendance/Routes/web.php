<?php

use Illuminate\Support\Facades\Route;
use Modules\Animals\User\OrganisationController;

Route::group(['prefix'=>config('attendance.attendance_route_prefix')],function(){
    Route::get('/','AttendanceController@index')->name('attendance.search'); // Search
    Route::get('/{slug}','AttendanceController@detail')->name('attendance.detail');// Detail
});

Route::group(['prefix'=>'user/'.config('attendance.attendance_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/','ManageAnimalController@manageAnimal')->name('attendance.vendor.index');
//    Route::get('/create','ManageAnimalController@create')->name('animal.vendor.create');
//    Route::get('/edit/{id}','ManageAnimalController@edit')->name('animal.vendor.edit');
//    Route::get('/del/{id}','ManageAnimalController@delete')->name('animal.vendor.delete');
//    Route::post('/store/{id}','ManageAnimalController@store')->name('animal.vendor.store');
//    Route::get('bulkEdit/{id}','ManageAnimalController@bulkEDetach')->name("animal.vendor.bulk_detach");
//    Route::post('bulkEdit','ManageAnimalController@bulkEAttach')->name("animal.vendor.bulk_attach");
//    Route::get('/booking-report/bulkEdit/{id}','ManageAnimalController@bookingReportBulkEdit')->name("animal.vendor.booking_report.bulk_edit");
//    Route::get('/recovery','ManageAnimalController@recovery')->name('animal.vendor.recovery');
//    Route::get('/restore/{id}','ManageAnimalController@restore')->name('animal.vendor.restore');
});


Route::group(['prefix'=>'organisation'],function(){
    Route::get('/','OrganisationController@index')->name('animal.vendor.organisation');
});

Route::group(['prefix'=>config('attendance.attendance_route_prefix')],function(){
    Route::post('/{animal}/period/create', [OrganisationController::class, 'create'])->name('animal.vendor.period.create');
    Route::post('/period/{period}/update', [OrganisationController::class, 'update']);
    Route::post('/period/{period}', [OrganisationController::class, 'delete'])->name('animal.vendor.period.delete');

});
