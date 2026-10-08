<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => 'password',
            'phone' => '081200000001',
            'address' => 'Jl. Pelaku Kulit No. 1, Bandung',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Rifan Habibi',
            'email' => 'customer@gmail.com',
            'password' => 'password',
            'phone' => '081200000002',
            'address' => 'Jl. Merdeka No. 10, Jakarta',
            'role' => 'customer',
        ]);

        // One row only; PaymentSetting::current() falls back to an empty model
        // when this is missing, so it is deliberately not required for boot.
        PaymentSetting::query()->firstOrCreate([], [
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT Lex Leather',
            'ewallet_provider' => 'DANA',
            'ewallet_number' => '081298765432',
            'ewallet_holder' => 'PT Lex Leather',
        ]);

        $this->call(DemoCatalogSeeder::class);
    }
}
