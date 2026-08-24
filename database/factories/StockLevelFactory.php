<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'location_name' => fake()->city() . ' Warehouse',
            'quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
