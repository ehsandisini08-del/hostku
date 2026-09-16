<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPrice>
 */
class ProductPriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'billing_cycle' => fake()->randomElement(['monthly', 'quarterly', 'semi_annually', 'annually']),
            'price' => fake()->numberBetween(25000, 2500000),
            'setup_fee' => 0,
            'is_promo' => false,
            'promo_price' => null,
            'promo_start' => null,
            'promo_end' => null,
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn () => ['billing_cycle' => 'monthly']);
    }

    public function annually(): static
    {
        return $this->state(fn () => ['billing_cycle' => 'annually']);
    }

    public function promo(float $price): static
    {
        return $this->state(fn () => [
            'is_promo' => true,
            'promo_price' => $price,
            'promo_start' => now()->subWeek(),
            'promo_end' => now()->addMonth(),
        ]);
    }
}
