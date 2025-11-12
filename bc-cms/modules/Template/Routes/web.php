<?php

use Illuminate\Support\Facades\Route;
use \Modules\Template\Pages\PagePreview;

Route::get('template/preview/{template}', PagePreview::class)->name('template.preview');
