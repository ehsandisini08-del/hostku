<?php

use App\Models\Role;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated customers are redirected to customer dashboard', function () {
    Role::create(['name' => 'Customer', 'slug' => 'customer']);
    $user = User::factory()->create(['role_id' => Role::where('slug', 'customer')->first()->id]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect('/customer/dashboard');
});

test('authenticated admins are redirected to admin dashboard', function () {
    Role::create(['name' => 'Admin', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => Role::where('slug', 'admin')->first()->id]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect('/admin/dashboard');
});
