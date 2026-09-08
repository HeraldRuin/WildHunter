<?php
use Illuminate\Support\Facades\Route;

Route::get('/', 'BlogController@index')->name('blog.admin.index');
Route::get('/create', 'BlogController@create')->name('blog.admin.create');
