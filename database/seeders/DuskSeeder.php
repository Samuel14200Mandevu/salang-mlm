<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DuskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Admin',
                'user_type' => 'member',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'kyc_status' => 'verified',
            ]
        );

        Wallet::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 1000,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]
        );
    }
}
