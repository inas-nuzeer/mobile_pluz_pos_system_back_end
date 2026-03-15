<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default shop
        $shop = Shop::create([
            'name' => 'Main Shop',
            'address' => '123 Business St, Tech City',
            'phone' => '0112233445',
        ]);

        // Create an owner user for the shop
        User::create([
            'shop_id' => $shop->id,
            'name' => 'John Doe',
            'email' => 'owner@example.com',
            'password' => Hash::make('123456789'),
            'role' => 'owner',
        ]);

        // Create a staff user for the shop
        User::create([
            'shop_id' => $shop->id,
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}
