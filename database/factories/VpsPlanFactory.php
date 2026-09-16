<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\VpsPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VpsPlan>
 */
class VpsPlanFactory extends Factory
{
    public function definition(): array
    {
        $tiers = [
            ['cpu' => 1, 'ram' => 1024, 'disk' => 20480],
            ['cpu' => 2, 'ram' => 2048, 'disk' => 40960],
            ['cpu' => 4, 'ram' => 4096, 'disk' => 81920],
            ['cpu' => 8, 'ram' => 8192, 'disk' => 163840],
        ];
        $tier = fake()->randomElement($tiers);

        return [
            'product_id' => Product::factory()->vps(),
            'cpu_cores' => $tier['cpu'],
            'ram_mb' => $tier['ram'],
            'disk_mb' => $tier['disk'],
            'bandwidth_mb' => fake()->numberBetween(1000000, 10000000),
            'ipv4_count' => 1,
            'ipv6_count' => 1,
            'os_templates' => ['ubuntu-22.04', 'ubuntu-24.04', 'debian-12', 'centos-9-stream'],
            'network_bridge' => 'vmbr0',
            'storage_pool' => 'local-lvm',
        ];
    }
}
