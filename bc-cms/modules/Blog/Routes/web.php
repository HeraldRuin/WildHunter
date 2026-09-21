<?php

use Illuminate\Support\Facades\Route;

Route::prefix('user/' . config('blog.blog_route_prefix'))
    ->name('blog.vendor.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', 'BlogController@index')->name('index');
        Route::get('/create', 'BlogController@create')->name('create');
        Route::get('/edit/{id}', 'BlogController@edit')->name('edit');
        Route::post('/store/{id}', 'BlogController@store')->name('store');
        Route::post('/bulkEdit', 'BlogController@bulkEdit')->name('bulkEdit');
    });
