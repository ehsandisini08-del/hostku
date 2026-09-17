<?php

use App\Models\HostingPlan;
use App\Models\HostingServer;
use App\Models\HostingService;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use App\Services\Hosting\HostingProvisioningService;

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

test('authenticated user can order hosting and get redirected to checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'price' => 50000,
        'setup_fee' => 0,
    ]);

    $response = $this->actingAs($user)->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'domain' => 'tokobaru.com',
    ]);

    $invoice = Invoice::where('user_id', $user->id)->latest()->first();
    expect($invoice)->not->toBeNull();

    $response->assertRedirect(route('checkout', $invoice->id));

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 50000,
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'total' => 50000,
    ]);
});

test('guest placing order is redirected to login and pending order is saved in session', function () {
    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'price' => 50000,
    ]);

    $response = $this->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'domain' => 'tokobaru.com',
        'auth_action' => 'login',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('pending_hosting_order');
});

test('guest placing order with register action is redirected to register', function () {
    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'billing_cycle' => 'annually',
        'price' => 500000,
    ]);

    $response = $this->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'annually',
        'domain' => 'tokobaru.com',
        'auth_action' => 'register',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHas('pending_hosting_order');
});

test('guest order redirects to checkout upon successful login', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);
    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'price' => 50000,
    ]);

    // Guest configures order
    $this->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'domain' => 'clientorder.com',
    ]);

    // User logs in
    $loginResponse = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $invoice = Invoice::where('user_id', $user->id)->first();
    expect($invoice)->not->toBeNull();

    $loginResponse->assertRedirect(route('checkout', $invoice->id));
    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'total' => 50000,
    ]);
});

test('guest order redirects to checkout upon successful registration', function () {
    $product = Product::factory()->hosting()->create(['is_active' => true]);
    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'price' => 50000,
    ]);

    // Guest configures order and chooses register
    $this->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'domain' => 'newclient.com',
        'auth_action' => 'register',
    ]);

    // User registers
    $registerResponse = $this->post(route('register.store'), [
        'name' => 'New Customer',
        'email' => 'newcustomer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'newcustomer@example.com')->firstOrFail();
    $invoice = Invoice::where('user_id', $user->id)->first();
    expect($invoice)->not->toBeNull();

    $registerResponse->assertRedirect(route('checkout', $invoice->id));
    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'total' => 50000,
    ]);
});

test('hosting order requires valid domain name format', function () {
    $user = User::factory()->create();
    $product = Product::factory()->hosting()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'domain' => 'invalid-domain-without-tld',
    ]);

    $response->assertSessionHasErrors('domain');
});

test('paying invoice via simulation completes order and auto provisions hosting', function () {
    $user = User::factory()->create();
    $server = HostingServer::create([
        'name' => 'Main Server',
        'hostname' => 'srv.hostku.id',
        'ip_address' => '103.165.253.244',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'is_active' => true,
    ]);

    $product = Product::factory()->hosting()->create(['is_active' => true]);
    $plan = HostingPlan::create([
        'product_id' => $product->id,
        'disk_space_mb' => 5000,
        'bandwidth_mb' => 50000,
        'max_websites' => 1,
        'max_databases' => 1,
        'max_emails' => 1,
        'server_type' => 'custom_ssh',
    ]);

    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'price' => 75000,
    ]);

    $mockProvision = Mockery::mock(HostingProvisioningService::class);
    $mockProvision->shouldReceive('provision')->once()->andReturn([
        'username' => 'tokobaru123',
        'domain' => 'tokobaru.com',
        'password' => 'secret123',
    ]);
    app()->instance(HostingProvisioningService::class, $mockProvision);

    // Create order
    $this->actingAs($user)->post('/order/hosting', [
        'product_id' => $product->id,
        'billing_cycle' => 'monthly',
        'domain' => 'tokobaru.com',
    ]);

    $invoice = Invoice::where('user_id', $user->id)->firstOrFail();

    // Pay invoice
    $payResponse = $this->actingAs($user)->post("/checkout/{$invoice->id}/pay", [
        'gateway' => 'simulation',
        'payment_method' => 'instant',
        'payment_channel' => 'instant_approval',
    ]);

    $payResponse->assertRedirect(route('customer.hosting'));

    $invoice->refresh();
    expect($invoice->status)->toBe('paid')
        ->and($invoice->order->status)->toBe('paid');

    $this->assertDatabaseHas('hosting_services', [
        'domain' => 'tokobaru.com',
        'hosting_plan_id' => $plan->id,
    ]);

    $this->assertDatabaseHas('services', [
        'user_id' => $user->id,
        'status' => 'active',
        'serviceable_type' => HostingService::class,
    ]);
});
