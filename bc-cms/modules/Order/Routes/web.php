<?php

use Illuminate\Support\Facades\Route;

use Modules\Order\Controllers\ToCheckoutController;
use Modules\Order\Controllers\CancelController;
use Modules\Order\Controllers\CallbackController;
use Modules\Order\Controllers\ConfirmController;
use Modules\Order\Controllers\OrderModalController;
Route::get('/checkout', [ToCheckoutController::class, 'index'])->name('checkout');

Route::any('/order/cancel/{gateway}', [CancelController::class, 'index'])->name('order.cancel');
Route::any('/order/confirm/{gateway}', [ConfirmController::class, 'index'])->name('order.confirm');


// Route for webhook
Route::any('/order/callback/{gateway}', [CallbackController::class, 'index'])->name('order.callback');

// For display order modal
Route::get('/modal/{code}', [OrderModalController::class, 'index'])->name('order.modal')->middleware('auth');