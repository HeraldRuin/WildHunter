<?php
use Illuminate\Support\Facades\Route;
use Modules\Form\Controllers\SimpleFormUploadFileController;

Route::post('/simple-form/upload-file', [SimpleFormUploadFileController::class, 'index'])->name('simple-form.upload-file');
Route::get('/simple-form/upload-preview', [SimpleFormUploadFileController::class, 'preview'])->name('simple-form.upload-preview');

