<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use App\Models\Commission;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::where('is_active', true)->get();
        $user = Auth::user();
        
        return view('packages.index', compact('packages', 'user'));
    }

    public function buy(Request $request)
    {
        $user = Auth::user();
        $package = Package::find($request->package_id);
        
        if (!$package) {
            return back()->with('error', 'Package introuvable.');
        }

        // Vérifier si l'utilisateur a déjà ce package
        if ($user->package_id == $package->id) {
            return back()->with('error', 'Vous avez déjà ce package.');
        }

        // Simulation de paiement (à remplacer par intégration réelle)
        $paymentSuccess = true;

        if ($paymentSuccess) {
            // Mettre à jour le package de l'utilisateur
            $user->package_id = $package->id;
            $user->pv_balance += $package->pv_value;
            $user->bv_balance += $package->bv_value;
            $user->save();

            // Créditer les commissions
            $commissionService = new CommissionService();
            $commissionService->calculatePackageCommission($user->id, $package->id);

            // Créer une transaction
            if ($user->wallet) {
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $user->wallet->id,
                    'type' => 'purchase',
                    'amount' => -$package->price,
                    'fee' => 0,
                    'net_amount' => -$package->price,
                    'balance_before' => $user->wallet->balance,
                    'balance_after' => $user->wallet->balance - $package->price,
                    'status' => 'completed',
                    'description' => 'Achat du package ' . $package->name,
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Package acheté avec succès !');
        }

        return back()->with('error', 'Le paiement a échoué.');
    }

    public function upgrade(Request $request)
    {
        $user = Auth::user();
        $package = Package::find($request->package_id);
        
        if (!$package) {
            return back()->with('error', 'Package introuvable.');
        }

        if ($user->package_id >= $package->id) {
            return back()->with('error', 'Vous avez déjà un package supérieur ou égal.');
        }

        // Calculer le prix de mise à niveau (différence de prix)
        $currentPackage = Package::find($user->package_id);
        $upgradePrice = $package->price - ($currentPackage ? $currentPackage->price : 0);

        // Simulation de paiement
        $paymentSuccess = true;

        if ($paymentSuccess) {
            $user->package_id = $package->id;
            $user->pv_balance += $package->pv_value - ($currentPackage ? $currentPackage->pv_value : 0);
            $user->bv_balance += $package->bv_value - ($currentPackage ? $currentPackage->bv_value : 0);
            $user->save();

            // Créditer les commissions sur la différence
            $commissionService = new CommissionService();
            $commissionService->calculatePackageCommission($user->id, $package->id);

            return redirect()->route('dashboard')->with('success', 'Package mis à jour avec succès !');
        }

        return back()->with('error', 'Le paiement a échoué.');
    }
}
