<?php
// app/Http/Controllers/UserPackageController.php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPackageController extends Controller
{
    /**
     * Afficher la liste des abonnements
     */
    public function index()
    {
        // Récupérer tous les packages actifs
        $subscriptions = Package::where('is_active', true)->get();
        
        // Si aucun package n'existe dans la base, créer des données par défaut
        if ($subscriptions->isEmpty()) {
            $this->createDefaultPackages();
            $subscriptions = Package::where('is_active', true)->get();
        }
        
        $user = Auth::user();
        
        return view('subscriptions.index', compact('subscriptions', 'user'));
    }

    /**
     * Acheter un abonnement - PAIEMENT RÉEL
     */
    public function buy(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $user = Auth::user();
        $package = Package::findOrFail($request->package_id);

        // Vérifier si l'utilisateur a déjà ce package
        if ($user->package_id == $package->id) {
            return back()->with('error', 'Vous avez déjà ce package.');
        }

        // Vérifier si l'utilisateur a un package supérieur
        if ($user->package_id && $user->package_id > $package->id) {
            return back()->with('error', 'Vous ne pouvez pas acheter un package inférieur à votre package actuel.');
        }

        DB::beginTransaction();

        try {
            // 1. Récupérer ou créer le wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 0,
                    'pending_balance' => 0,
                    'total_withdrawn' => 0,
                    'total_deposited' => 0,
                    'currency' => 'USD',
                    'is_active' => true
                ]
            );

            // 2. Vérifier le solde
            if ($wallet->balance < $package->price) {
                return back()->with('error', 'Solde insuffisant. Vous avez $' . number_format($wallet->balance, 2) . ' et le package coûte $' . number_format($package->price, 2) . '.');
            }

            // 3. DÉBITER LE PORTEFEUILLE (PAIEMENT RÉEL)
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $package->price;
            $wallet->save();

            // 4. CRÉER LA TRANSACTION
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'purchase',
                'amount' => -$package->price,
                'fee' => 0,
                'net_amount' => -$package->price,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => 'Achat du package ' . $package->name,
                'metadata' => json_encode(['package_id' => $package->id]),
                'completed_at' => now(),
            ]);

            // 5. METTRE À JOUR L'UTILISATEUR
            $user->package_id = $package->id;
            $user->pv_balance = ($user->pv_balance ?? 0) + $package->pv_value;
            $user->bv_balance = ($user->bv_balance ?? 0) + $package->bv_value;
            $user->save();

            // 6. CALCULER LES COMMISSIONS
            $commissionService = new CommissionService();
            $commissionService->calculatePackageCommission($user->id, $package->id);

            DB::commit();

            $message = "Package '{$package->name}' acheté avec succès !";
            if ($package->pv_value > 0) {
                $message .= " Vous avez gagné {$package->pv_value} PV.";
            }

            return redirect()->route('subscriptions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur achat package: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur lors de l\'achat: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à niveau l'abonnement - PAIEMENT RÉEL
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $user = Auth::user();
        $newPackage = Package::findOrFail($request->package_id);

        // Vérifier que l'utilisateur a un package
        if (!$user->package_id) {
            return back()->with('error', 'Vous devez d\'abord acheter un package.');
        }

        // Vérifier que le nouveau package est supérieur
        if ($user->package_id >= $newPackage->id) {
            return back()->with('error', 'Vous ne pouvez pas passer à un package inférieur ou égal.');
        }

        DB::beginTransaction();

        try {
            // Récupérer le package actuel
            $currentPackage = Package::find($user->package_id);
            $upgradePrice = $newPackage->price - ($currentPackage ? $currentPackage->price : 0);

            if ($upgradePrice <= 0) {
                return back()->with('error', 'Le prix de mise à niveau est invalide.');
            }

            // Récupérer le wallet
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                return back()->with('error', 'Portefeuille introuvable.');
            }

            if ($wallet->balance < $upgradePrice) {
                return back()->with('error', 'Solde insuffisant pour la mise à niveau.');
            }

            // DÉBITER LE PORTEFEUILLE (PAIEMENT RÉEL)
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $upgradePrice;
            $wallet->save();

            // CRÉER LA TRANSACTION
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'upgrade',
                'amount' => -$upgradePrice,
                'fee' => 0,
                'net_amount' => -$upgradePrice,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => "Upgrade vers {$newPackage->name}",
                'metadata' => json_encode([
                    'old_package' => $currentPackage?->name,
                    'new_package' => $newPackage->name,
                ]),
                'completed_at' => now(),
            ]);

            // METTRE À JOUR L'UTILISATEUR
            $user->package_id = $newPackage->id;
            $user->pv_balance = ($user->pv_balance ?? 0) + $newPackage->pv_value - ($currentPackage ? $currentPackage->pv_value : 0);
            $user->bv_balance = ($user->bv_balance ?? 0) + $newPackage->bv_value - ($currentPackage ? $currentPackage->bv_value : 0);
            $user->save();

            // CALCULER LES COMMISSIONS
            $commissionService = new CommissionService();
            $commissionService->calculatePackageCommission($user->id, $newPackage->id);

            DB::commit();

            return redirect()->route('subscriptions.index')
                ->with('success', "Package mis à niveau vers '{$newPackage->name}' avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur upgrade package: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'new_package_id' => $newPackage->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur lors de la mise à niveau: ' . $e->getMessage());
        }
    }

    /**
     * Créer des abonnements par défaut
     */
    private function createDefaultPackages()
    {
        $packages = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price' => 30,
                'pv_value' => 0,
                'bv_value' => 0,
                'commission_rate' => 30,
                'description' => 'Package idéal pour débuter',
                'is_active' => true,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'price' => 85,
                'pv_value' => 50,
                'bv_value' => 30,
                'commission_rate' => 30,
                'description' => 'Package argent pour les ambassadeurs',
                'is_active' => true,
            ],
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'price' => 350,
                'pv_value' => 200,
                'bv_value' => 150,
                'commission_rate' => 30,
                'description' => 'Package bronze pour les leaders',
                'is_active' => true,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'price' => 1450,
                'pv_value' => 1000,
                'bv_value' => 800,
                'commission_rate' => 30,
                'description' => 'Package gold pour les élites',
                'is_active' => true,
            ],
            [
                'name' => 'Emerald',
                'slug' => 'emerald',
                'price' => 4850,
                'pv_value' => 3800,
                'bv_value' => 3000,
                'commission_rate' => 30,
                'description' => 'Package emerald pour les légendes',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $data) {
            Package::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}