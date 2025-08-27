<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_ADMIN,
        ]);

        // Create some test customers
        User::create([
            'name' => 'John Customer',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_CUSTOMER,
        ]);

        User::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_CUSTOMER,
        ]);

        // Create some test suppliers
        User::create([
            'name' => 'Gas Supplier Co.',
            'email' => 'supplier1@example.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_SUPPLIER,
        ]);

        User::create([
            'name' => 'Premium Gas Ltd.',
            'email' => 'supplier2@example.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_SUPPLIER,
        ]);

        // Call the GasSalesTestSeeder for additional test data
        $this->call([
            GasSalesTestSeeder::class,
        ]);
    }
}
