<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * Acheter un abonnement
     */
    public function buy(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $user = Auth::user();
        $subscription = Package::findOrFail($request->package_id);

        // Vérifier si l'utilisateur a déjà ce package
        if ($user->package_id == $subscription->id) {
            return back()->with('error', 'Vous avez déjà cet abonnement.');
        }

        // Vérifier si l'utilisateur a un package supérieur
        if ($user->package_id && $user->package_id > $subscription->id) {
            return back()->with('error', 'Vous ne pouvez pas acheter un abonnement inférieur à votre abonnement actuel.');
        }

        try {
            DB::beginTransaction();

            // Récupérer ou créer le wallet
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

            // SIMULATION DE PAIEMENT (À remplacer par un vrai système)
            $paymentSuccess = true;

            if ($paymentSuccess) {
                // Mettre à jour le package de l'utilisateur
                $user->package_id = $subscription->id;
                $user->pv_balance = ($user->pv_balance ?? 0) + $subscription->pv_value;
                $user->bv_balance = ($user->bv_balance ?? 0) + $subscription->bv_value;
                $user->save();

                DB::commit();

                return redirect()->route('subscriptions.index')->with('success', 'Abonnement acheté avec succès !');
            }

            DB::rollBack();
            return back()->with('error', 'Le paiement a échoué.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de l\'achat: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à niveau l'abonnement
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $user = Auth::user();
        $subscription = Package::findOrFail($request->package_id);

        // Vérifier que l'utilisateur a un package
        if (!$user->package_id) {
            return back()->with('error', 'Vous devez d\'abord acheter un abonnement.');
        }

        // Vérifier que le package est supérieur
        if ($user->package_id >= $subscription->id) {
            return back()->with('error', 'Vous ne pouvez pas passer à un abonnement inférieur ou égal.');
        }

        try {
            DB::beginTransaction();

            // Calculer la différence de prix
            $currentPackage = Package::find($user->package_id);
            $upgradePrice = $subscription->price - ($currentPackage ? $currentPackage->price : 0);

            if ($upgradePrice <= 0) {
                return back()->with('error', 'Le prix de mise à niveau est invalide.');
            }

            // SIMULATION DE PAIEMENT
            $paymentSuccess = true;

            if ($paymentSuccess) {
                // Mettre à jour le package
                $user->package_id = $subscription->id;
                $user->pv_balance = ($user->pv_balance ?? 0) + $subscription->pv_value - ($currentPackage ? $currentPackage->pv_value : 0);
                $user->bv_balance = ($user->bv_balance ?? 0) + $subscription->bv_value - ($currentPackage ? $currentPackage->bv_value : 0);
                $user->save();

                DB::commit();

                return redirect()->route('subscriptions.index')->with('success', 'Abonnement mis à niveau avec succès !');
            }

            DB::rollBack();
            return back()->with('error', 'Le paiement a échoué.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la mise à niveau: ' . $e->getMessage());
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
                'description' => 'Abonnement idéal pour débuter',
                'is_active' => true,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'price' => 85,
                'pv_value' => 50,
                'bv_value' => 30,
                'commission_rate' => 30,
                'description' => 'Abonnement argent pour les ambassadeurs',
                'is_active' => true,
            ],
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'price' => 350,
                'pv_value' => 200,
                'bv_value' => 150,
                'commission_rate' => 30,
                'description' => 'Abonnement bronze pour les leaders',
                'is_active' => true,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'price' => 1450,
                'pv_value' => 1000,
                'bv_value' => 800,
                'commission_rate' => 30,
                'description' => 'Abonnement gold pour les elites',
                'is_active' => true,
            ],
            [
                'name' => 'Emerald',
                'slug' => 'emerald',
                'price' => 4850,
                'pv_value' => 3800,
                'bv_value' => 3000,
                'commission_rate' => 30,
                'description' => 'Abonnement emerald pour les légendes',
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