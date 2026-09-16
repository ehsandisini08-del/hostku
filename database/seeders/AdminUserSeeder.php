<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $superRole = Role::where('slug', 'super_admin')->firstOrFail();

        User::updateOrCreate(['email' => 'admin@hostku.id'], [
            'name' => 'Admin HostKu',
            'password' => 'password123',
            'email_verified_at' => now(),
            'role_id' => $adminRole->id,
        ]);

        User::updateOrCreate(['email' => 'superadmin@hostku.id'], [
            'name' => 'Super Admin',
            'password' => 'password123',
            'email_verified_at' => now(),
            'role_id' => $superRole->id,
        ]);

        $customerRole = Role::where('slug', 'customer')->firstOrFail();

        User::updateOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => 'password',
            'email_verified_at' => now(),
            'role_id' => $customerRole->id,
        ]);

        $this->command?->info('Akun siap:');
        $this->command?->info('  Admin:      admin@hostku.id / password123');
        $this->command?->info('  SuperAdmin: superadmin@hostku.id / password123');
        $this->command?->info('  Customer:   test@example.com / password');
    }
}
