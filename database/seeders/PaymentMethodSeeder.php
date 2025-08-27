<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::insert([
            ['type' => 'çash', 'name' => 'Cash', 'is_active' => 1],
            ['type' => 'bank_deposit', 'name' => 'Easypaisa', 'is_active' => 1],
            ['type' => 'bank_deposit', 'name' => 'JazzCash', 'is_active' => 1],
            ['type' => 'bank_deposit', 'name' => 'HBL Bank', 'is_active' => 1],
            ['type' => 'bank_deposit', 'name' => 'UBL Bank', 'is_active' => 1],
            ['type' => 'bank_deposit', 'name' => 'Standard Chartered Bank', 'is_active' => 1],
            ['type' => 'bank_deposit', 'name' => 'Meezan Bank', 'is_active' => 1],
        ]);
    }
}
