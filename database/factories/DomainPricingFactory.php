<?php

namespace Database\Factories;

use App\Models\DomainPricing;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DomainPricing>
 */
class DomainPricingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->domain(),
            'tld' => '.'.fake()->tld(),
            'registration_price' => fake()->numberBetween(80000, 500000),
            'renewal_price' => fake()->numberBetween(80000, 500000),
            'transfer_price' => fake()->numberBetween(80000, 500000),
            'min_years' => 1,
            'max_years' => 10,
            'is_premium' => false,
        ];
    }
}
