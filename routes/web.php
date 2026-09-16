<?php

use App\Http\Controllers\Customer\DomainController;
use App\Http\Controllers\Customer\HostingController;
use App\Http\Controllers\Customer\VpsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = request()->user();

        return redirect($user->isAdmin() ? '/admin/dashboard' : '/customer/dashboard');
    })->name('dashboard');
});

Route::prefix('customer')->name('customer.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/services', [CustomerController::class, 'services'])->name('services');
    Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [CustomerController::class, 'showOrder'])->name('orders.show');
    Route::get('/invoices', [CustomerController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}', [CustomerController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/payments', [CustomerController::class, 'payments'])->name('payments');
    Route::get('/tickets', [CustomerController::class, 'tickets'])->name('tickets');
    Route::get('/tickets/create', [CustomerController::class, 'createTicket'])->name('tickets.create');
    Route::post('/tickets', [CustomerController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [CustomerController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [CustomerController::class, 'replyTicket'])->name('tickets.reply');
    Route::get('/notifications', [CustomerController::class, 'notifications'])->name('notifications');

    Route::get('/domains', [DomainController::class, 'index'])->name('domains');
    Route::get('/domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
    Route::put('/domains/{domain}/nameservers', [DomainController::class, 'updateNameservers'])->name('domains.nameservers');
    Route::post('/domains/{domain}/renew', [DomainController::class, 'renew'])->name('domains.renew');

    Route::get('/vps', [VpsController::class, 'index'])->name('vps');
    Route::get('/vps/{vps}', [VpsController::class, 'show'])->name('vps.show');
    Route::post('/vps/{vps}/start', [VpsController::class, 'start'])->name('vps.start');
    Route::post('/vps/{vps}/stop', [VpsController::class, 'stop'])->name('vps.stop');
    Route::post('/vps/{vps}/reboot', [VpsController::class, 'reboot'])->name('vps.reboot');
    Route::post('/vps/{vps}/shutdown', [VpsController::class, 'shutdown'])->name('vps.shutdown');

    Route::get('/hosting-services', [HostingController::class, 'index'])->name('hosting');
    Route::get('/hosting-services/{hosting}', [HostingController::class, 'show'])->name('hosting.show');
    Route::post('/hosting-services/{hosting}/change-password', [HostingController::class, 'changePassword'])->name('hosting.password');
});

Route::name('public.')->group(function () {
    Route::get('/home', [PublicController::class, 'home'])->name('home');
    Route::get('/domain', [PublicController::class, 'domain'])->name('domain');
    Route::get('/hosting', [PublicController::class, 'hosting'])->name('hosting');
    Route::get('/vps', [PublicController::class, 'vps'])->name('vps');
    Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
    Route::get('/about', [PublicController::class, 'about'])->name('about');
    Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
    Route::get('/faq', [PublicController::class, 'faq'])->name('faq');
    Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
    Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/payment.php';
