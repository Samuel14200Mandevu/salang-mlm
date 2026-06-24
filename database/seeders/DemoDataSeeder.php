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
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Génération des données de démonstration...');

        // Récupérer l'admin
        $admin = User::where('email', 'admin@salang.com')->first();
        
        if (!$admin) {
            $this->command->error('❌ Admin non trouvé! Créez d\'abord un admin.');
            return;
        }

        // Récupérer les rangs et packages
        $ranks = Rank::all();
        $packages = Package::all();
        
        if ($ranks->isEmpty() || $packages->isEmpty()) {
            $this->command->error('❌ Rangs ou packages manquants!');
            return;
        }

        $cities = ['Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro', 'Korhogo'];
        $createdUsers = [];
        $userCount = 0;

        // Créer 15 utilisateurs de test
        $names = [
            'Jean Dupont', 'Marie Martin', 'Pierre Durand', 'Sophie Lefevre', 
            'Lucas Bernard', 'Emma Petit', 'Thomas Robert', 'Julie Richard',
            'Nicolas Dubois', 'Camille Moreau', 'Alexandre Laurent', 'Isabelle Simon',
            'Michel Francois', 'Catherine Michel', 'Philippe Garcia'
        ];

        for ($i = 0; $i < 15; $i++) {
            $rank = $ranks->random();
            $package = $packages->random();
            $sponsorId = 'SAL' . strtoupper(substr(uniqid(), -6));
            
            // Déterminer le sponsor
            $sponsor = null;
            if ($i < 3) {
                $sponsor = $admin; // Filleuls directs de l'admin
            } elseif ($i < 7) {
                // Filleuls de niveau 2
                $firstGen = User::where('sponsor_id', $admin->sponsor_id)->where('id', '!=', $admin->id)->first();
                $sponsor = $firstGen ?? $admin;
            } else {
                $secondGen = User::whereNotNull('sponsor_id')->where('sponsor_id', '!=', $admin->sponsor_id)->first();
                $sponsor = $secondGen ?? $admin;
            }

            $user = User::create([
                'name' => $names[$i],
                'email' => 'demo' . ($i + 1) . '@salang.com',
                'phone' => '+225 07' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'sponsor_id' => $sponsorId,
                'rank_id' => $rank->id,
                'package_id' => $package->id,
                'pv_balance' => rand(100, 8000),
                'bv_balance' => rand(100, 8000),
                'commission_balance' => rand(10, 2000),
                'total_earnings' => rand(100, 15000),
                'total_sponsors' => rand(0, 6),
                'total_team' => rand(0, 25),
                'is_active' => true,
                'country' => "Côte d'Ivoire",
                'city' => $cities[rand(0, count($cities) - 1)],
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
            ]);

            try {
                $user->assignRole('user');
            } catch (\Exception $e) {}

            Wallet::create([
                'user_id' => $user->id,
                'balance' => rand(10, 2000),
                'pending_balance' => rand(5, 300),
                'total_withdrawn' => rand(0, 800),
                'total_deposited' => rand(10, 1500),
                'currency' => 'USD',
                'is_active' => true,
            ]);

            Genealogy::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor?->id,
                'parent_id' => $sponsor?->id,
                'level' => $sponsor ? ($sponsor->genealogy?->level ?? 0) + 1 : 0,
                'left_count' => rand(0, 3),
                'right_count' => rand(0, 3),
                'total_children' => rand(0, 6),
            ]);

            $createdUsers[] = $user;
            $userCount++;
        }

        // Créer des commissions
        $commissionTypes = ['direct', 'indirect', 'leadership', 'retail'];
        $commissionCount = 0;
        
        for ($i = 0; $i < 40; $i++) {
            if (count($createdUsers) < 2) break;
            
            $user = $createdUsers[array_rand($createdUsers)];
            $fromUser = $createdUsers[array_rand($createdUsers)];
            
            if ($user->id !== $fromUser->id) {
                $amount = rand(10, 300);
                $type = $commissionTypes[array_rand($commissionTypes)];
                
                Commission::create([
                    'user_id' => $user->id,
                    'from_user_id' => $fromUser->id,
                    'type' => $type,
                    'amount' => $amount,
                    'percentage' => $type === 'direct' ? 30 : ($type === 'indirect' ? 15 : ($type === 'leadership' ? 10 : 25)),
                    'description' => 'Commission ' . $type . ' de ' . $fromUser->name,
                    'order_id' => null,
                    'package_id' => $packages->random()->id,
                    'status' => rand(0, 1) ? 'paid' : 'pending',
                    'paid_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
                $commissionCount++;
            }
        }

        // Créer des transactions
        $transactionTypes = ['commission', 'withdrawal', 'deposit', 'purchase'];
        $transactionCount = 0;
        
        foreach ($createdUsers as $user) {
            $wallet = $user->wallet;
            if ($wallet) {
                for ($i = 0; $i < 5; $i++) {
                    $amount = rand(10, 500);
                    $type = $transactionTypes[array_rand($transactionTypes)];
                    $fee = $type === 'withdrawal' ? $amount * 0.025 : 0;
                    
                    Transaction::create([
                        'user_id' => $user->id,
                        'wallet_id' => $wallet->id,
                        'type' => $type,
                        'amount' => $amount,
                        'fee' => $fee,
                        'net_amount' => $type === 'withdrawal' ? $amount - $fee : $amount,
                        'balance_before' => rand(100, 2000),
                        'balance_after' => rand(100, 2500),
                        'status' => ['pending', 'completed', 'failed'][rand(0, 2)],
                        'description' => ucfirst($type) . ' transaction',
                        'completed_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,
                        'created_at' => Carbon::now()->subDays(rand(1, 15)),
                    ]);
                    $transactionCount++;
                }
            }
        }

        // Créer des retraits
        $withdrawalMethods = ['crypto', 'mobile_money', 'bank'];
        for ($i = 0; $i < 10; $i++) {
            if (count($createdUsers) < 1) break;
            
            $user = $createdUsers[array_rand($createdUsers)];
            $wallet = $user->wallet;
            
            if ($wallet && $wallet->balance > 50) {
                $amount = rand(50, min(500, $wallet->balance));
                $fee = $amount * 0.025;
                $method = $withdrawalMethods[array_rand($withdrawalMethods)];
                
                Withdrawal::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => $amount - $fee,
                    'method' => $method,
                    'payment_address' => $method === 'crypto' ? '0x' . strtoupper(substr(uniqid(), -40)) : null,
                    'phone_number' => $method === 'mobile_money' ? '+225 07' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT) : null,
                    'bank_details' => $method === 'bank' ? json_encode(['bank' => 'SGBCI', 'account' => '0123456789']) : null,
                    'status' => ['pending', 'processing', 'completed', 'failed'][rand(0, 3)],
                    'notes' => 'Retrait ' . $method,
                    'processed_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 5)) : null,
                    'completed_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 3)) : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 20)),
                ]);
            }
        }

        // Mettre à jour les statistiques de l'admin
        $admin->total_earnings = Commission::where('user_id', $admin->id)->where('status', 'paid')->sum('amount');
        $admin->total_sponsors = User::where('sponsor_id', $admin->sponsor_id)->count();
        $admin->total_team = User::whereIn('sponsor_id', User::where('sponsor_id', $admin->sponsor_id)->pluck('sponsor_id'))->count();
        $admin->save();

        $this->command->info('');
        $this->command->info('✅ Données de démonstration créées avec succès!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('👤 Utilisateurs créés     : ' . $userCount);
        $this->command->info('💰 Commissions créées    : ' . $commissionCount);
        $this->command->info('💳 Transactions créées   : ' . $transactionCount);
        $this->command->info('🏦 Retraits créés        : 10');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🔑 Connectez-vous avec:');
        $this->command->info('   Email: admin@salang.com');
        $this->command->info('   Password: password123');
        $this->command->info('');
        $this->command->info('📧 Ou avec un compte de test:');
        $this->command->info('   Email: demo1@salang.com');
        $this->command->info('   Password: password123');
    }
}
