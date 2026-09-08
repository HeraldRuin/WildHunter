<?php
use Illuminate\Support\Facades\Route;

Route::get('/create', 'BlogController@create')->name('blog.admin.create');
