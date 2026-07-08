<?php
// app/Console/Commands/CreateAdmin.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=} {--password=}';
    protected $description = 'Créer un administrateur';

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?? 'Admin';
        $password = $this->option('password') ?? 'password123';

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
            'sponsor_id' => 'ADMIN' . time(),
        ]);

        $user->assignRole('admin');

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->info("Admin créé : {$email}");
        $this->info("Mot de passe : {$password}");

        return 0;
    }
}