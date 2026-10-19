<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

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

        $artist = User::factory()->create([
            'name' => 'Featured Artist',
            'email' => 'artist@example.com',
            'role' => 'artist',
        ]);

        Product::factory(5)->create([
            'user_id' => $artist->id,
        ])->each(function ($product) {
            StockLevel::factory(rand(1, 2))->create([
                'product_id' => $product->id,
            ]);
        });
    }
}
