<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['domain', 'hosting', 'vps']);
        $name = match ($type) {
            'domain' => '.'.fake()->tld(),
            'hosting' => fake()->randomElement(['Starter', 'Business', 'Pro']).' Hosting',
            'vps' => 'VPS-'.fake()->numberBetween(1, 8),
        };

        return [
            'type' => $type,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'features' => [],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function hosting(): static
    {
        return $this->state(fn () => ['type' => 'hosting']);
    }

    public function vps(): static
    {
        return $this->state(fn () => ['type' => 'vps']);
    }

    public function domain(): static
    {
        return $this->state(fn () => ['type' => 'domain']);
    }
}
