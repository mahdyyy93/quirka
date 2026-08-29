<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $customerUser = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);

        Customer::create([
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
