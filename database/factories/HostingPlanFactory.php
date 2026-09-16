<?php

namespace Database\Factories;

use App\Models\HostingPlan;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostingPlan>
 */
class HostingPlanFactory extends Factory
{
    public function definition(): array
    {
        $tiers = [
            ['disk' => 5120, 'websites' => 1, 'databases' => 1, 'emails' => 1],
            ['disk' => 20480, 'websites' => 5, 'databases' => 5, 'emails' => 10],
            ['disk' => 51200, 'websites' => 0, 'databases' => 0, 'emails' => 0],
        ];
        $tier = fake()->randomElement($tiers);

        return [
            'product_id' => Product::factory()->hosting(),
            'disk_space_mb' => $tier['disk'],
            'bandwidth_mb' => fake()->numberBetween(100000, 1000000),
            'max_websites' => $tier['websites'],
            'max_databases' => $tier['databases'],
            'max_emails' => $tier['emails'],
            'max_ftp' => $tier['websites'] * 2,
            'max_subdomains' => $tier['websites'] * 3,
            'server_type' => 'cpanel',
        ];
    }
}
