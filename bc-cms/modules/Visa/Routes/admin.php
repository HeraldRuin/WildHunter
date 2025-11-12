<?php
use Illuminate\Support\Facades\Route;
use Modules\Visa\Admin\Service\ServicePage;
use Modules\Visa\Admin\Service\ServiceController;
use Modules\Visa\Admin\Type\TypePage;
use Modules\Visa\Admin\Type\TypeEditPage;

Route::group([
    'prefix' => 'module/visa',
], function () {
    Route::get('/', ServicePage::class)->name('visa.admin.index');
    Route::get('/create', [ServiceController::class, 'create'])->name('visa.admin.create');
    Route::get('/edit/{id}', [ServiceController::class, 'edit'])->name('visa.admin.edit');
    Route::post('/store/{id?}', [ServiceController::class, 'store'])->name('visa.admin.store');

    Route::group([
        'prefix' => 'type',
    ], function () {
        Route::get('/', TypePage::class)->name('visa.admin.type.index');
        Route::get('/edit/{id}', TypeEditPage::class)->name('visa.admin.type.edit');
    });
});