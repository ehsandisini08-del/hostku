<?php

use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/midtrans', [WebhookController::class, 'midtrans'])->name('webhook.midtrans');
Route::post('/webhooks/xendit', [WebhookController::class, 'xendit'])->name('webhook.xendit');
Route::post('/webhooks/doku', [WebhookController::class, 'doku'])->name('webhook.doku');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout/{invoice}', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/{invoice}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
});
