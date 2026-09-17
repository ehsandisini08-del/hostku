<?php

use App\Models\HostingEmail;
use App\Models\HostingPlan;
use App\Models\HostingServer;
use App\Models\HostingService;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\Hosting\HostingProvisioningService;

function createHostingCustomer(): array
{
    $role = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-'.time(),
        'status' => 'active',
        'subtotal' => 50000,
        'total' => 50000,
    ]);

    $server = HostingServer::create([
        'name' => 'Production Server',
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
        'max_emails' => 2,
        'server_type' => 'custom_ssh',
    ]);

    $hostingService = HostingService::create([
        'hosting_plan_id' => $plan->id,
        'hosting_server_id' => $server->id,
        'domain' => 'tokosaya.com',
        'username' => 'tokosaya1',
        'server_ip' => $server->ip_address,
        'db_name' => 'h_tokosaya1',
        'db_user' => 'tokosaya1',
        'db_pass' => 'dbpass123',
        'php_version' => '8.4',
        'ssl_active' => false,
    ]);

    $service = Service::create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'serviceable_type' => HostingService::class,
        'serviceable_id' => $hostingService->id,
        'status' => 'active',
        'expired_at' => now()->addMonth(),
    ]);

    return compact('user', 'order', 'server', 'plan', 'hostingService', 'service');
}

test('customer can view hosting detail panel', function () {
    ['user' => $user, 'hostingService' => $hosting] = createHostingCustomer();

    $response = $this->actingAs($user)->get("/customer/hosting-services/{$hosting->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('customer/HostingDetail')
        ->has('hosting')
        ->has('dnsCheck')
        ->has('webmailUrl')
        ->has('phpMyAdminUrl')
    );
});

test('customer cannot view other customer hosting panel', function () {
    ['hostingService' => $hosting] = createHostingCustomer();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->get("/customer/hosting-services/{$hosting->id}");

    $response->assertForbidden();
});

test('customer can change php version', function () {
    ['user' => $user, 'hostingService' => $hosting] = createHostingCustomer();

    $mockProvision = Mockery::mock(HostingProvisioningService::class)->makePartial();
    $mockProvision->shouldReceive('changePhpVersion')->once()->andReturnUsing(function ($hs, $ver) {
        $hs->update(['php_version' => $ver]);

        return ['success' => true];
    });
    app()->instance(HostingProvisioningService::class, $mockProvision);

    $response = $this->actingAs($user)->post("/customer/hosting-services/{$hosting->id}/php-version", [
        'php_version' => '8.2',
    ]);

    $response->assertRedirect();
    $hosting->refresh();
    expect($hosting->php_version)->toBe('8.2');
});

test('customer can reset mysql database password', function () {
    ['user' => $user, 'hostingService' => $hosting] = createHostingCustomer();

    $mockProvision = Mockery::mock(HostingProvisioningService::class)->makePartial();
    $mockProvision->shouldReceive('resetDatabasePassword')->once()->andReturnUsing(function ($hs, $pwd) {
        $hs->update(['db_pass' => $pwd]);

        return ['success' => true];
    });
    app()->instance(HostingProvisioningService::class, $mockProvision);

    $response = $this->actingAs($user)->post("/customer/hosting-services/{$hosting->id}/database/reset-password", [
        'password' => 'NewDbSecret123!',
        'password_confirmation' => 'NewDbSecret123!',
    ]);

    $response->assertRedirect();
    $hosting->refresh();
    expect($hosting->db_pass)->toBe('NewDbSecret123!');
});

test('customer can create email account up to plan limit', function () {
    ['user' => $user, 'hostingService' => $hosting] = createHostingCustomer();

    $mockProvision = Mockery::mock(HostingProvisioningService::class)->makePartial();
    $mockProvision->shouldReceive('createMailbox')->andReturnUsing(function ($hs, $user, $pwd, $quota = 500) {
        $max = $hs->plan?->max_emails ?? 1;
        if ($max > 0 && $hs->emails()->count() >= $max) {
            throw new RuntimeException('Batas pembuatan email tercapai.');
        }
        $email = $hs->emails()->create([
            'email_address' => "{$user}@{$hs->domain}",
            'mailbox_user' => $user,
            'domain' => $hs->domain,
            'quota_mb' => $quota,
        ]);

        return ['success' => true, 'email' => $email];
    });
    app()->instance(HostingProvisioningService::class, $mockProvision);

    $response = $this->actingAs($user)->post("/customer/hosting-services/{$hosting->id}/emails", [
        'username' => 'info',
        'password' => 'MailPassword123!',
        'quota_mb' => 500,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('hosting_emails', [
        'hosting_service_id' => $hosting->id,
        'email_address' => 'info@tokosaya.com',
        'mailbox_user' => 'info',
        'domain' => 'tokosaya.com',
    ]);

    // Create 2nd email (plan allows max 2)
    $this->actingAs($user)->post("/customer/hosting-services/{$hosting->id}/emails", [
        'username' => 'support',
        'password' => 'MailPassword123!',
    ]);

    // Attempt 3rd email should fail
    $failResponse = $this->actingAs($user)->post("/customer/hosting-services/{$hosting->id}/emails", [
        'username' => 'admin',
        'password' => 'MailPassword123!',
    ]);

    $failResponse->assertSessionHas('error');
    $this->assertDatabaseMissing('hosting_emails', [
        'email_address' => 'admin@tokosaya.com',
    ]);
});

test('customer can delete email account', function () {
    ['user' => $user, 'hostingService' => $hosting] = createHostingCustomer();

    $mockProvision = Mockery::mock(HostingProvisioningService::class)->makePartial();
    $mockProvision->shouldReceive('deleteMailbox')->andReturnUsing(function ($hs, $em) {
        $em->delete();
    });
    app()->instance(HostingProvisioningService::class, $mockProvision);

    $email = HostingEmail::create([
        'hosting_service_id' => $hosting->id,
        'email_address' => 'sales@tokosaya.com',
        'mailbox_user' => 'sales',
        'domain' => 'tokosaya.com',
        'quota_mb' => 500,
    ]);

    $response = $this->actingAs($user)->delete("/customer/hosting-services/{$hosting->id}/emails/{$email->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('hosting_emails', ['id' => $email->id]);
});
