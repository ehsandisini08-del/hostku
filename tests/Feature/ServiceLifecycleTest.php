<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;

test('services can transition through lifecycle states', function () {
    $user = User::factory()->create();
    $product = Product::factory()->hosting()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-LC-'.time(),
        'status' => 'active',
        'subtotal' => 50000,
        'total' => 50000,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Test Service',
        'billing_cycle' => 'monthly',
        'quantity' => 1,
        'unit_price' => 50000,
        'total' => 50000,
    ]);

    $service = Service::create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'serviceable_type' => 'App\Models\HostingService',
        'serviceable_id' => 99,
        'status' => 'active',
        'expired_at' => now()->subDays(10),
    ]);

    expect($service->status)->toBe('active');

    $service->update(['status' => 'expiring']);
    expect($service->status)->toBe('expiring');

    $service->update(['status' => 'suspended', 'suspended_at' => now()]);
    expect($service->status)->toBe('suspended');

    $service->update(['status' => 'terminated', 'terminated_at' => now()]);
    expect($service->status)->toBe('terminated');
});

test('suspend expired command suspends services past grace period', function () {
    $user = User::factory()->create();
    $product = Product::factory()->hosting()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-SE-'.time(),
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

    Service::create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'serviceable_type' => 'App\Models\HostingService',
        'serviceable_id' => 100,
        'status' => 'expiring',
        'expired_at' => now()->subDays(5),
    ]);

    $this->artisan('services:suspend-expired')->assertSuccessful();

    $service = Service::first();
    expect($service->status)->toBe('suspended')
        ->and($service->suspended_at)->not->toBeNull();
});
