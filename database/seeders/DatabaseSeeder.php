<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $customerUser = \App\Models\User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);

        \App\Models\Customer::create([
            'user_id' => $customerUser->id,
            'name' => $customerUser->name,
            'email' => $customerUser->email,
        ]);

        $suppliers = Supplier::factory(2)->create();

        Product::factory(5)->make()->each(function ($product) use ($suppliers) {
            $product->supplier_id = $suppliers->random()->id;
            $product->save();

            StockLevel::factory(rand(1, 2))->create([
                'product_id' => $product->id,
            ]);
        });
    }
}
