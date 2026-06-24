<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Rank;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@salang.com')->first();
        $ranks = Rank::all();
        $packages = Package::all();
        
        $names = [
            'Jean Dupont', 'Marie Martin', 'Pierre Durand', 'Sophie Lefevre',
            'Lucas Bernard', 'Emma Petit', 'Thomas Robert', 'Julie Richard',
            'Nicolas Dubois', 'Camille Moreau', 'Alexandre Laurent', 'Isabelle Simon',
            'Michel Francois', 'Catherine Michel', 'Philippe Garcia'
        ];

        $createdUsers = [];

        // Créer 15 utilisateurs
        for ($i = 0; $i < 15; $i++) {
            $rank = $ranks->random();
            $package = $packages->random();
            
            $sponsor = null;
            if ($i < 3) {
                $sponsor = $admin;
            } elseif ($i < 7) {
                $firstGen = User::where('sponsor_id', $admin->sponsor_id)->first();
                $sponsor = $firstGen ?? $admin;
            } else {
                $secondGen = User::whereNotNull('sponsor_id')->where('sponsor_id', '!=', $admin->sponsor_id)->first();
                $sponsor = $secondGen ?? $admin;
            }

            $user = User::create([
                'name' => $names[$i % count($names)],
                'email' => 'test' . ($i + 1) . '@salang.com',
                'phone' => '+225 07' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'sponsor_id' => 'SAL' . strtoupper(substr(uniqid(), -6)),
                'rank_id' => $rank->id,
                'package_id' => $package->id,
                'pv_balance' => rand(100, 8000),
                'bv_balance' => rand(100, 8000),
                'total_earnings' => rand(100, 5000),
                'is_active' => true,
                'country' => "Côte d'Ivoire",
                'city' => ['Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro'][rand(0, 4)],
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
            ]);

            $user->assignRole('user');

            Wallet::create([
                'user_id' => $user->id,
                'balance' => rand(50, 1500),
                'pending_balance' => rand(10, 300),
                'total_withdrawn' => rand(0, 500),
                'total_deposited' => rand(50, 2000),
                'currency' => 'USD',
                'is_active' => true,
            ]);

            Genealogy::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor?->id,
                'parent_id' => $sponsor?->id,
                'level' => $sponsor ? ($sponsor->genealogy?->level ?? 0) + 1 : 0,
                'total_children' => rand(0, 5),
            ]);

            $createdUsers[] = $user;
        }

        // Commissions pour l'admin
        $types = ['direct', 'indirect', 'leadership', 'retail'];
        for ($i = 0; $i < 25; $i++) {
            $fromUser = $createdUsers[array_rand($createdUsers)];
            $type = $types[array_rand($types)];
            $amount = rand(20, 300);
            
            Commission::create([
                'user_id' => $admin->id,
                'from_user_id' => $fromUser->id,
                'type' => $type,
                'amount' => $amount,
                'percentage' => $type === 'direct' ? 30 : ($type === 'indirect' ? 15 : ($type === 'leadership' ? 10 : 25)),
                'description' => 'Commission ' . $type . ' de ' . $fromUser->name,
                'status' => rand(0, 1) ? 'paid' : 'pending',
                'paid_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // Commissions pour les utilisateurs
        foreach ($createdUsers as $user) {
            for ($i = 0; $i < 3; $i++) {
                $fromUser = $createdUsers[array_rand($createdUsers)];
                if ($user->id !== $fromUser->id) {
                    Commission::create([
                        'user_id' => $user->id,
                        'from_user_id' => $fromUser->id,
                        'type' => $types[array_rand($types)],
                        'amount' => rand(10, 200),
                        'percentage' => 30,
                        'description' => 'Commission de ' . $fromUser->name,
                        'status' => 'paid',
                        'paid_at' => Carbon::now()->subDays(rand(1, 20)),
                        'created_at' => Carbon::now()->subDays(rand(1, 20)),
                    ]);
                }
            }
        }

        // Transactions pour l'admin
        $wallet = $admin->wallet;
        $txTypes = ['commission', 'withdrawal', 'deposit', 'purchase'];
        for ($i = 0; $i < 20; $i++) {
            $type = $txTypes[array_rand($txTypes)];
            $amount = rand(20, 500);
            Transaction::create([
                'user_id' => $admin->id,
                'wallet_id' => $wallet->id,
                'type' => $type,
                'amount' => $amount,
                'fee' => $type === 'withdrawal' ? $amount * 0.025 : 0,
                'net_amount' => $type === 'withdrawal' ? $amount * 0.975 : $amount,
                'balance_before' => rand(100, 2000),
                'balance_after' => rand(200, 2500),
                'status' => 'completed',
                'description' => ucfirst($type) . ' transaction',
                'completed_at' => Carbon::now()->subDays(rand(1, 15)),
                'created_at' => Carbon::now()->subDays(rand(1, 15)),
            ]);
        }

        // Retraits
        for ($i = 0; $i < 5; $i++) {
            $methods = ['crypto', 'mobile_money', 'bank'];
            $amount = rand(50, 300);
            Withdrawal::create([
                'user_id' => $admin->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'fee' => $amount * 0.025,
                'net_amount' => $amount * 0.975,
                'method' => $methods[array_rand($methods)],
                'status' => ['pending', 'processing', 'completed', 'failed'][rand(0, 3)],
                'notes' => 'Retrait test ' . ($i + 1),
                'created_at' => Carbon::now()->subDays(rand(1, 20)),
            ]);
        }

        // Mettre à jour les statistiques de l'admin
        $admin->total_earnings = Commission::where('user_id', $admin->id)->where('status', 'paid')->sum('amount');
        $admin->total_sponsors = User::where('sponsor_id', $admin->sponsor_id)->count();
        $admin->total_team = User::whereNotNull('sponsor_id')->count();
        $admin->save();

        $this->command->info('✅ Données de test créées avec succès!');
        $this->command->info('👤 Utilisateurs: ' . User::count());
        $this->command->info('💰 Commissions: ' . Commission::count());
        $this->command->info('💳 Transactions: ' . Transaction::count());
        $this->command->info('🔑 Admin: admin@salang.com / password123');
    }
}
