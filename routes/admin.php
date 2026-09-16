<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::get('/customers/{user}', [AdminController::class, 'showCustomer'])->name('customers.show');

    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->name('orders.show');

    Route::get('/services', [AdminController::class, 'services'])->name('services');

    Route::get('/domains', [AdminController::class, 'domains'])->name('domains');
    Route::get('/domains/{domain}', [AdminController::class, 'showDomain'])->name('domains.show');

    Route::get('/hosting', [AdminController::class, 'hosting'])->name('hosting');

    Route::get('/hosting-servers', [AdminController::class, 'hostingServers'])->name('hosting-servers');
    Route::post('/hosting-servers', [AdminController::class, 'storeHostingServer'])->name('hosting-servers.store');

    Route::get('/vps', [AdminController::class, 'vps'])->name('vps');

    Route::get('/proxmox', [AdminController::class, 'proxmox'])->name('proxmox');
    Route::post('/proxmox', [AdminController::class, 'storeProxmoxServer'])->name('proxmox.store');
    Route::post('/proxmox/{server}/test', [AdminController::class, 'testProxmoxConnection'])->name('proxmox.test');
    Route::post('/proxmox/{server}/sync', [AdminController::class, 'syncProxmoxNodes'])->name('proxmox.sync');
    Route::get('/proxmox/{server}', [AdminController::class, 'showProxmoxServer'])->name('proxmox.show');

    Route::get('/invoices', [AdminController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}', [AdminController::class, 'showInvoice'])->name('invoices.show');

    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');

    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
    Route::get('/tickets/{ticket}', [AdminController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [AdminController::class, 'replyTicket'])->name('tickets.reply');

    Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    Route::get('/users', [AdminController::class, 'adminUsers'])->name('users');

    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
});
