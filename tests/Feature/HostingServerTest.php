<?php

use App\Models\HostingPlan;
use App\Models\HostingServer;
use App\Models\HostingService;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\Hosting\CustomSshAdapter;

function createAdminUser(): User
{
    $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin can view hosting servers page', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)->get(route('admin.hosting-servers'));

    $response->assertOk();
});

test('admin can store custom ssh hosting server without api url and token', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)->post(route('admin.hosting-servers.store'), [
        'name' => 'Server DC1',
        'hostname' => 'srv1.hostku.id',
        'ip_address' => '192.168.1.100',
        'panel_type' => 'custom_ssh',
        'api_url' => '',
        'api_token' => '',
        'ssh_port' => 22,
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'web_server' => 'nginx',
        'php_version' => '8.4',
        'base_path' => '/var/www',
        'ssl_email' => 'admin@hostku.id',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('hosting_servers', [
        'name' => 'Server DC1',
        'hostname' => 'srv1.hostku.id',
        'ip_address' => '192.168.1.100',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'web_server' => 'nginx',
        'php_version' => '8.4',
        'base_path' => '/var/www',
        'ssl_email' => 'admin@hostku.id',
        'api_url' => null,
        'api_token' => null,
    ]);
});

test('admin can store cpanel hosting server with api url and token', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)->post(route('admin.hosting-servers.store'), [
        'name' => 'cPanel Server 1',
        'hostname' => 'cpanel.hostku.id',
        'ip_address' => '192.168.1.200',
        'panel_type' => 'cpanel',
        'api_url' => 'https://cpanel.hostku.id:2087',
        'api_token' => 'whm-secret-token',
        'api_username' => 'root',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('hosting_servers', [
        'name' => 'cPanel Server 1',
        'hostname' => 'cpanel.hostku.id',
        'ip_address' => '192.168.1.200',
        'panel_type' => 'cpanel',
        'api_url' => 'https://cpanel.hostku.id:2087',
        'api_token' => 'whm-secret-token',
        'api_username' => 'root',
    ]);
});

test('storing custom ssh hosting server requires ssh_user and ssh_key_path', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)->post(route('admin.hosting-servers.store'), [
        'name' => 'Server DC1',
        'hostname' => 'srv1.hostku.id',
        'ip_address' => '192.168.1.100',
        'panel_type' => 'custom_ssh',
    ]);

    $response->assertSessionHasErrors(['ssh_user', 'ssh_key_path']);
});

test('custom ssh adapter can be instantiated', function () {
    $adapter = new CustomSshAdapter;
    expect($adapter)->toBeInstanceOf(CustomSshAdapter::class);
});

test('custom ssh adapter throws exception when key file not found', function () {
    $server = HostingServer::create([
        'name' => 'Server Test',
        'hostname' => 'srv.test.id',
        'ip_address' => '127.0.0.1',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/nonexistent/path/to/key',
    ]);

    $adapter = new CustomSshAdapter;
    $adapter->createAccount($server, [
        'username' => 'testuser',
        'domain' => 'testuser.hostku.id',
        'password' => 'secret',
    ]);
})->throws(RuntimeException::class);

test('admin can update hosting server', function () {
    $admin = createAdminUser();
    $server = HostingServer::create([
        'name' => 'Original Name',
        'hostname' => 'orig.hostku.id',
        'ip_address' => '10.0.0.1',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku_provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'web_server' => 'nginx',
        'php_version' => '8.3',
        'base_path' => '/var/www',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.hosting-servers.update', $server), [
        'name' => 'Updated Name',
        'hostname' => 'updated.hostku.id',
        'ip_address' => '10.0.0.2',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'web_server' => 'nginx',
        'php_version' => '8.4',
        'base_path' => '/var/www',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $server->refresh();
    expect($server->name)->toBe('Updated Name')
        ->and($server->ssh_user)->toBe('hostku-provision')
        ->and($server->php_version)->toBe('8.4');
});

test('admin can delete hosting server without active services', function () {
    $admin = createAdminUser();
    $server = HostingServer::create([
        'name' => 'Server to Delete',
        'hostname' => 'delete.hostku.id',
        'ip_address' => '10.0.0.3',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'web_server' => 'nginx',
        'php_version' => '8.4',
        'base_path' => '/var/www',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.hosting-servers.destroy', $server));

    $response->assertRedirect();
    $this->assertDatabaseMissing('hosting_servers', ['id' => $server->id]);
});

test('admin cannot delete hosting server with services', function () {
    $admin = createAdminUser();
    $server = HostingServer::create([
        'name' => 'Busy Server',
        'hostname' => 'busy.hostku.id',
        'ip_address' => '10.0.0.4',
        'panel_type' => 'custom_ssh',
        'ssh_user' => 'hostku-provision',
        'ssh_key_path' => '/var/www/hostku/storage/keys/hostku_provision',
        'web_server' => 'nginx',
        'php_version' => '8.4',
        'base_path' => '/var/www',
        'is_active' => true,
    ]);

    $product = Product::factory()->hosting()->create();
    $plan = HostingPlan::create([
        'product_id' => $product->id,
        'disk_space_mb' => 5000,
        'bandwidth_mb' => 50000,
        'max_websites' => 1,
        'max_databases' => 1,
        'max_emails' => 1,
        'server_type' => 'custom_ssh',
    ]);

    HostingService::create([
        'hosting_plan_id' => $plan->id,
        'hosting_server_id' => $server->id,
        'domain' => 'client.com',
        'username' => 'client',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.hosting-servers.destroy', $server));

    $response->assertRedirect();
    $this->assertDatabaseHas('hosting_servers', ['id' => $server->id]);
});
