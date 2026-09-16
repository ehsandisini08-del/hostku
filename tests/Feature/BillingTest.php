<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Service;
use App\Models\User;

test('billing generate creates invoices for expiring services', function () {
    $user = User::factory()->create();
    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create(['product_id' => $product->id, 'billing_cycle' => 'monthly', 'price' => 100000]);

    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.time(),
        'status' => 'active',
        'subtotal' => 100000,
        'total' => 100000,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Test Hosting',
        'billing_cycle' => 'monthly',
        'quantity' => 1,
        'unit_price' => 100000,
        'total' => 100000,
    ]);

    $service = Service::create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'serviceable_type' => 'App\Models\HostingService',
        'serviceable_id' => 1,
        'status' => 'active',
        'expired_at' => now()->addDays(10),
    ]);

    $this->artisan('billing:generate')->assertSuccessful();

    $invoice = Invoice::where('user_id', $user->id)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe('pending')
        ->and((float) $invoice->total)->toBe(100000.0);
});

test('billing generate does not create duplicate invoices', function () {
    $user = User::factory()->create();
    $invoice = Invoice::create([
        'user_id' => $user->id,
        'invoice_number' => 'INV-'.time(),
        'status' => 'pending',
        'subtotal' => 50000,
        'total' => 50000,
        'due_date' => now()->addDays(10),
    ]);

    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create(['product_id' => $product->id, 'billing_cycle' => 'monthly', 'price' => 50000]);

    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD2-'.time(),
        'status' => 'active',
        'subtotal' => 50000,
        'total' => 50000,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Test',
        'billing_cycle' => 'monthly',
        'quantity' => 1,
        'unit_price' => 50000,
        'total' => 50000,
    ]);

    $service = Service::create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'serviceable_type' => 'App\Models\HostingService',
        'serviceable_id' => 2,
        'status' => 'active',
        'expired_at' => now()->addDays(10),
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'service_id' => $service->id,
        'description' => 'Existing invoice',
        'quantity' => 1,
        'unit_price' => 50000,
        'total' => 50000,
    ]);

    $this->artisan('billing:generate')->assertSuccessful();

    expect(Invoice::where('user_id', $user->id)->count())->toBe(1);
});
