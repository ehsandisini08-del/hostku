<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

test('user can view their own orders', function () {
    $user = User::factory()->create();
    $product = Product::factory()->hosting()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.time(),
        'status' => 'pending',
        'subtotal' => 100000,
        'total' => 100000,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Test',
        'billing_cycle' => 'monthly',
        'unit_price' => 100000,
        'total' => 100000,
    ]);

    $this->actingAs($user)
        ->get("/customer/orders/{$order->id}")
        ->assertOk();
});

test('user cannot view other user orders', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $product = Product::factory()->hosting()->create();

    $order = Order::create([
        'user_id' => $user1->id,
        'order_number' => 'ORD2-'.time(),
        'status' => 'pending',
        'subtotal' => 100000,
        'total' => 100000,
    ]);

    $this->actingAs($user2)
        ->get("/customer/orders/{$order->id}")
        ->assertForbidden();
});
