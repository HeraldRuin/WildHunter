<?php

use Illuminate\Support\Facades\Route;
use Modules\Animals\User\OrganisationController;
use Modules\Attendance\Controllers\AdditionalController;

Route::group(['prefix'=>config('attendance.attendance_route_prefix')],function(){
    Route::get('/','AttendanceController@index')->name('attendance.search'); // Search
    Route::get('/{slug}','AttendanceController@detail')->name('attendance.detail');// Detail
});

Route::group(['prefix'=>'user/'.config('attendance.attendance_route_prefix'),'middleware' => ['auth','verified']],function(){
    Route::get('/','ManageAnimalController@manageAnimal')->name('attendance.vendor.index');
});


Route::group(['prefix'=>'organisation'],function(){
    Route::get('/','OrganisationController@index')->name('animal.vendor.organisation');
});

Route::group(['prefix'=>config('attendance.attendance_route_prefix')],function(){
    Route::post('/{animal}/period/create', [OrganisationController::class, 'create'])->name('animal.vendor.period.create');
    Route::post('/period/{period}/update', [OrganisationController::class, 'update']);
    Route::post('/period/{period}', [OrganisationController::class, 'delete'])->name('animal.vendor.period.delete');
});

Route::group(['prefix'=>config('additional.additionals_route_prefix')],function(){
    Route::get('/',[AdditionalController::class, 'index'])->name('additionals.index');
    Route::post('/store', [AdditionalController::class, 'store'])->name('additionals.store');
    Route::post('/{additional}/update', [AdditionalController::class, 'update'])->name('additionals.update');
    Route::delete('/{additional}', [AdditionalController::class, 'destroy'])->name('additionals.delete');
});
