<?php

use App\Models\Role;
use App\Models\User;

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
