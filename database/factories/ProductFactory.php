<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'title' => fake()->words(3, true),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'price' => fake()->randomFloat(2, 10, 500),
            'is_available' => fake()->boolean(80),
            'customizations' => [
                'color' => fake()->safeColorName(),
                'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            ],
        ];
    }
}
