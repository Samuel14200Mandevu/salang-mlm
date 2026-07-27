<?php
// app/Http/Controllers/Admin/ActivationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MLM\CommissionDistributor;
use App\Notifications\ActivationCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ActivationController extends Controller
{
    protected CommissionDistributor $commissionDistributor;

    public function __construct(CommissionDistributor $commissionDistributor)
    {
        $this->commissionDistributor = $commissionDistributor;
    }

    /**
     * Interface d'activation pour l'admin - Liste des utilisateurs inactifs
     */
    public function index(Request $request)
    {
        $query = User::where('is_active', false)
            ->with(['package', 'rank', 'activationPackage']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%");
            });
        }

        $inactiveUsers = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_inactive' => User::where('is_active', false)->count(),
            'with_code' => User::where('is_active', false)
                ->whereNotNull('activation_code')
                ->count(),
            'without_code' => User::where('is_active', false)
                ->whereNull('activation_code')
                ->count(),
        ];

        $packages = Package::where('is_active', true)->get();

        return view('admin.activations.index', compact('inactiveUsers', 'stats', 'packages'));
    }

    /**
     * Voir les détails d'un utilisateur pour activation
     */
    public function show($userId)
    {
        $user = User::with(['rank', 'package', 'wallet', 'activationPackage'])
            ->findOrFail($userId);

        // Récupérer les PV/BV actuels de l'utilisateur
        $currentPV = $user->pv_balance ?? 0;
        $currentBV = $user->bv_balance ?? 0;
        
        // Calculer les PV/BV qui seront ajoutés avec le package d'activation
        $packagePV = 0;
        $packageBV = 0;
        $packageName = 'Aucun';
        $packagePrice = 0;
        
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
            if ($package) {
                $packagePV = $package->pv_value ?? 0;
                $packageBV = $package->bv_value ?? 0;
                $packageName = $package->name;
                $packagePrice = $package->price;
            }
        }

        $totalCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $paidCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;

        $totalEarnings = $totalCommissions + $paidCommissions;

        $packages = Package::where('is_active', true)->get();

        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'period'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.activations.show', compact(
            'user',
            'totalCommissions',
            'paidCommissions',
            'totalEarnings',
            'packages',
            'commissions',
            'currentPV',
            'currentBV',
            'packagePV',
            'packageBV',
            'packageName',
            'packagePrice'
        ));
    }

    /**
     * Générer un code d'activation avec un package
     */
    public function generateCodeWithPackage(Request $request, $userId)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = User::findOrFail($userId);
        $package = Package::findOrFail($request->package_id);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est déjà actif.');
        }

        // Générer le code d'activation
        $code = 'ACT-' . strtoupper(substr(md5(uniqid() . time() . rand()), 0, 12));

        // Associer le package au code
        $user->update([
            'activation_code' => $code,
            'activation_code_expires_at' => now()->addDays(7),
            'activation_package_id' => $package->id,
        ]);

        // Envoyer le code par email avec le package
        try {
            $user->notify(new ActivationCodeNotification($code, $package));
        } catch (\Exception $e) {
            Log::error('Error sending activation code: ' . $e->getMessage());
        }

        Log::info('Activation code generated with package', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.activations.show', $user->id)
            ->with('success', "Code d'activation généré pour {$user->name} avec le package {$package->name}");
    }

    /**
     * Activer manuellement un utilisateur (admin) - Utilise le package d'activation existant
     * ✅ CORRIGÉ : Distribution automatique des commissions
     */
    public function activateManually($userId)
    {
        try {
            $user = User::findOrFail($userId);

            if ($user->is_active) {
                return back()->with('error', 'Ce compte est déjà actif.');
            }

            // Vérifier si l'utilisateur a un package d'activation
            if (!$user->activation_package_id) {
                return back()->with('error', 'Cet utilisateur n\'a pas de package d\'activation associé. Veuillez d\'abord générer un code d\'activation.');
            }

            // Récupérer le package
            $package = Package::find($user->activation_package_id);
            if (!$package) {
                return back()->with('error', 'Le package d\'activation n\'existe plus.');
            }

            DB::beginTransaction();

            // 1. Ajouter les PV et BV
            $user->addPV($package->pv_value, 'activation', $package->id);
            
            // 2. Associer le package
            $user->package_id = $package->id;
            $user->activation_method = 'admin_manual';
            $user->activation_code = null;
            $user->activation_code_expires_at = null;
            $user->activation_package_id = $package->id;
            
            // 3. Activer le compte
            $user->is_active = true;
            $user->activated_at = now();
            $user->save();

            Log::info('User activated manually by admin', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'pv_added' => $package->pv_value,
                'bv_added' => $package->bv_value,
                'admin_id' => auth()->id(),
            ]);

            // 4. Mettre à jour le rang
            if (method_exists($user, 'calculateAndUpdateRank')) {
                $user->calculateAndUpdateRank();
            }

            // 5. Créer le wallet si nécessaire
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            // ✅ 6. DISTRIBUER LES COMMISSIONS AUTOMATIQUEMENT
            $this->distributeCommissionsForActivation($user, $package);

            DB::commit();

            return redirect()->route('admin.activations.show', $user->id)
                ->with('success', "Compte de {$user->name} activé avec succès ! 
                    Package {$package->name} attribué avec {$package->pv_value} PV et {$package->bv_value} BV.
                    Le parrain a reçu sa commission de parrainage.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during manual activation', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de l\'activation : ' . $e->getMessage());
        }
    }

    /**
     * Activer un utilisateur avec un package spécifique (sélectionné dans la modale)
     * ✅ CORRIGÉ : Distribution automatique des commissions
     */
    public function activateWithPackage(Request $request, $userId)
    {
        try {
            Log::info('activateWithPackage called', [
                'user_id' => $userId,
                'package_id' => $request->package_id,
            ]);

            $validated = $request->validate([
                'package_id' => 'required|exists:packages,id',
            ]);

            $user = User::findOrFail($userId);
            $package = Package::findOrFail($validated['package_id']);

            if ($user->is_active) {
                return redirect()->back()->with('error', 'Ce compte est déjà actif.');
            }

            DB::beginTransaction();

            // 1. Ajouter les PV et BV
            $user->addPV($package->pv_value, 'activation_with_package', $package->id);
            
            // 2. Associer le package
            $user->activation_package_id = $package->id;
            $user->activation_code = null;
            $user->activation_code_expires_at = null;
            $user->package_id = $package->id;
            $user->activation_method = 'admin_manual_with_package';
            
            // 3. Activer le compte
            $user->is_active = true;
            $user->activated_at = now();
            $user->save();

            Log::info('User activated with package by admin', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'pv_added' => $package->pv_value,
                'bv_added' => $package->bv_value,
                'admin_id' => auth()->id(),
            ]);

            // 4. Mettre à jour le rang
            if (method_exists($user, 'calculateAndUpdateRank')) {
                $user->calculateAndUpdateRank();
            }

            // 5. Créer le wallet si nécessaire
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            // ✅ 6. DISTRIBUER LES COMMISSIONS AUTOMATIQUEMENT
            $this->distributeCommissionsForActivation($user, $package);

            DB::commit();

            $message = "Compte de {$user->name} activé avec le package {$package->name} ! " . 
                       "{$package->pv_value} PV et {$package->bv_value} BV attribués.
                       Le parrain a reçu sa commission de parrainage.";

            return redirect()->route('admin.users.show', $user->id)
                ->with('success', $message);

        } catch (ValidationException $e) {
            Log::error('Validation error in activateWithPackage', [
                'errors' => $e->errors(),
                'user_id' => $userId
            ]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in activateWithPackage', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Erreur lors de l\'activation : ' . $e->getMessage());
        }
    }

    /**
     * ✅ DISTRIBUER LES COMMISSIONS POUR UNE ACTIVATION
     */
    private function distributeCommissionsForActivation($user, $package): void
    {
        try {
            // Vérifier si l'utilisateur a un parrain
            if (!$user->parrain_id) {
                Log::info('Aucun parrain pour l\'activation, pas de commission', [
                    'user_id' => $user->id,
                ]);
                return;
            }

            // Vérifier que le parrain existe et est actif
            $sponsor = User::find($user->parrain_id);
            if (!$sponsor || !$sponsor->is_active) {
                Log::info('Le parrain n\'est pas actif, pas de commission', [
                    'user_id' => $user->id,
                    'parrain_id' => $user->parrain_id,
                ]);
                return;
            }

            // Récupérer ou créer la période de commission
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'active',
                    'total_commissions' => 0,
                    'total_paid' => 0,
                ]
            );

            // ✅ CORRECTION : source = 'mlm' au lieu de 'activation'
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ACT-' . strtoupper(uniqid()),
                'subtotal' => $package->price,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $package->price,
                'status' => 'completed',
                'payment_status' => 'completed',
                'payment_method' => 'activation',
                'source' => 'mlm', // ✅ CHANGÉ : 'activation' -> 'mlm'
                'paid_at' => now(),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'name' => $package->name . ' (Activation)',
                'quantity' => 1,
                'price' => $package->price,
                'total' => $package->price,
                'pv_value' => $package->pv_value,
                'bv_value' => $package->bv_value,
                'commission_rate' => $package->commission_rate ?? 0,
            ]);

            Log::info('Distribution des commissions pour activation', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'package' => $package->name,
                'parrain_id' => $user->parrain_id,
                'parrain_name' => $sponsor->name,
                'order_id' => $order->id,
                'period' => $period->period,
            ]);

            // ✅ Distribuer les commissions
            $commissions = $this->commissionDistributor->distributeCommissions(
                $user,
                $package,
                $order->id,
                $period
            );

            // ✅ Créditer le wallet du parrain pour les commissions sponsor
            foreach ($commissions as $commission) {
                if ($commission->type === 'sponsor') {
                    $this->creditSponsorWallet($commission);
                }
            }

            Log::info('Commissions distribuées pour activation', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'package' => $package->name,
                'parrain_id' => $user->parrain_id,
                'commissions_count' => count($commissions),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la distribution des commissions', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Créditer le wallet du parrain
     */
    private function creditSponsorWallet(Commission $commission): void
    {
        try {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $commission->user_id],
                ['balance' => 0, 'currency' => 'USD']
            );

            $balanceBefore = $wallet->balance;
            $wallet->balance += $commission->amount;
            $wallet->save();

            Transaction::create([
                'user_id' => $commission->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'commission',
                'amount' => $commission->amount,
                'fee' => 0,
                'net_amount' => $commission->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => $commission->description,
                'completed_at' => now(),
            ]);

            Log::info('Wallet du parrain crédité', [
                'sponsor_id' => $commission->user_id,
                'amount' => $commission->amount,
                'commission_id' => $commission->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du crédit du wallet du parrain', [
                'sponsor_id' => $commission->user_id,
                'commission_id' => $commission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Renvoyer le code d'activation par email
     */
    public function sendCode($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est déjà actif.');
        }

        if (!$user->activation_code) {
            return back()->with('error', 'Aucun code d\'activation généré pour cet utilisateur.');
        }

        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        try {
            $user->notify(new ActivationCodeNotification($user->activation_code, $package));
        } catch (\Exception $e) {
            Log::error('Error resending activation code: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'envoi du code.');
        }

        return back()->with('success', "Code d'activation renvoyé à {$user->email}");
    }

    /**
     * Vérifier les PV/BV de l'utilisateur (API)
     */
    public function checkUserBalance($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            $package = null;
            $packagePV = 0;
            $packageBV = 0;
            $packageName = 'Aucun';
            
            if ($user->activation_package_id) {
                $package = Package::find($user->activation_package_id);
                if ($package) {
                    $packagePV = $package->pv_value ?? 0;
                    $packageBV = $package->bv_value ?? 0;
                    $packageName = $package->name;
                }
            }

            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_active' => $user->is_active,
                'current_pv' => $user->pv_balance ?? 0,
                'current_bv' => $user->bv_balance ?? 0,
                'total_pv_earned' => $user->total_pv_earned ?? 0,
                'total_bv_earned' => $user->total_bv_earned ?? 0,
                'package_name' => $packageName,
                'package_pv' => $packagePV,
                'package_bv' => $packageBV,
                'package_price' => $package?->price ?? 0,
                'activation_code' => $user->activation_code,
                'activation_code_expires_at' => $user->activation_code_expires_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}