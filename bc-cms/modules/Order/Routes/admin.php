<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 7/1/2019
 * Time: 10:02 AM
 */
use Illuminate\Support\Facades\Route;
Route::get('/','OrderController@index')->name('order.admin.index');
Route::get('/edit/{order}','OrderController@edit')->name('order.admin.edit');
Route::get('/create','OrderController@edit')->name('order.admin.create');
Route::post('/store/{order?}','OrderController@store')->name('order.admin.store');
Route::post('bulkEdit','OrderController@bulkEdit')->name('order.admin.bulkEdit');
