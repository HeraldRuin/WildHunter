<?php
use Illuminate\Support\Facades\Route;

Route::get('/', 'BlogController@index')->name('blog.admin.index');
Route::get('/create', 'BlogController@create')->name('blog.admin.create');
Route::get('/edit/{id}', 'BlogController@edit')->name('blog.admin.edit');
Route::post('/store/{id}', 'BlogController@store')->name('blog.admin.store');
Route::post('/bulkEdit', 'BlogController@bulkEdit')->name('blog.admin.bulkEdit');
