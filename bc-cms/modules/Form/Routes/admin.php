<?php
use Illuminate\Support\Facades\Route;
use Modules\Form\Admin\FormPage;

Route::get('/edit/{id}', FormPage::class)->name('form.admin.edit');
Route::post('/store/{id}', FormPage::class)->name('form.admin.store');
