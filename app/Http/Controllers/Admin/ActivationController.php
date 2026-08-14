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
use App\Services\MLM\AdvancedRankCalculator;
use App\Jobs\UpdateTeamPV;
use App\Jobs\UpdateRanks;
use App\Jobs\CalculatePVBV;
use App\Notifications\ActivationCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ActivationController extends Controller
{
    protected CommissionDistributor $commissionDistributor;
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(
        CommissionDistributor $commissionDistributor,
        AdvancedRankCalculator $rankCalculator
    ) {
        $this->commissionDistributor = $commissionDistributor;
        $this->rankCalculator = $rankCalculator;
    }

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

    public function show($userId)
    {
        $user = User::with(['rank', 'package', 'wallet', 'activationPackage'])
            ->findOrFail($userId);

        $currentPV = $user->pv_balance ?? 0;
        $currentBV = $user->bv_balance ?? 0;
        
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

    public function generateCodeWithPackage(Request $request, $userId)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = User::findOrFail($userId);
        $package = Package::findOrFail($request->package_id);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est deja actif.');
        }

        $code = 'ACT-' . strtoupper(substr(md5(uniqid() . time() . rand()), 0, 12));

        $user->update([
            'activation_code' => $code,
            'activation_code_expires_at' => now()->addDays(7),
            'activation_package_id' => $package->id,
        ]);

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
            ->with('success', "Code d'activation genere pour {$user->name} avec le package {$package->name}");
    }

    public function activateManually($userId)
    {
        try {
            $user = User::findOrFail($userId);

            if ($user->is_active) {
                return back()->with('error', 'Ce compte est deja actif.');
            }

            if (!$user->activation_package_id) {
                return back()->with('error', 'Cet utilisateur n\'a pas de package d\'activation associe. Veuillez d\'abord generer un code d\'activation.');
            }

            $package = Package::find($user->activation_package_id);
            if (!$package) {
                return back()->with('error', 'Le package d\'activation n\'existe plus.');
            }

            DB::beginTransaction();

            // =============================================
            // AJOUT: Créditer le parrain en PV (10%)
            // =============================================
            $sponsorPV = 0;
            $sponsorName = null;
            
            if ($user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor && $sponsor->is_active) {
                    $sponsorPV = $package->pv_value * 0.10;
                    $sponsorBV = $package->bv_value * 0.10;
                    
                    $sponsor->pv_balance += $sponsorPV;
                    $sponsor->monthly_pv += $sponsorPV;
                    $sponsor->bv_balance += $sponsorBV;
                    $sponsor->monthly_bv += $sponsorBV;
                    $sponsor->saveQuietly();
                    $sponsorName = $sponsor->name;
                    
                    Log::info('Parrain credite en PV lors de l activation manuelle', [
                        'parrain_id' => $sponsor->id,
                        'parrain_name' => $sponsor->name,
                        'pv_credited' => $sponsorPV,
                        'filleul_id' => $user->id,
                        'filleul_name' => $user->name,
                        'package' => $package->name,
                    ]);
                }
            }

            // Créditer le nouveau membre
            $user->addPV($package->pv_value, 'activation', $package->id);
            
            $user->package_id = $package->id;
            $user->activation_method = 'admin_manual';
            $user->activation_code = null;
            $user->activation_code_expires_at = null;
            $user->activation_package_id = $package->id;
            
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

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            // Distribuer les commissions financières
            $this->distributeCommissionsForActivation($user, $package);

            DB::commit();

            // =============================================
            // AJOUT: Mettre à jour le grade du parrain
            // =============================================
            if ($user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor) {
                    $this->rankCalculator->recalculateUserRank($sponsor, 'Activation filleul');
                }
            }

            // Jobs en arrière-plan
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new CalculatePVBV($user->id))->onQueue('high');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            // Message de succès
            $user->refresh();
            $rankName = $user->rank ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;
            
            $message = "Compte de {$user->name} active avec succes !";
            $message .= "\nPackage: {$package->name}";
            $message .= "\nPV attribues: {$package->pv_value} PV et {$package->bv_value} BV";
            
            if ($sponsorPV > 0 && $sponsorName) {
                $message .= "\n\nLe parrain {$sponsorName} a recu " . number_format($sponsorPV, 1) . " PV (10%)";
            }
            
            $message .= "\nGrade: {$rankName} (Niv. {$rankLevel})";
            $message .= "\nLes recalculs complets sont en cours en arriere-plan.";

            return redirect()->route('admin.activations.show', $user->id)
                ->with('success', $message);

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
                return redirect()->back()->with('error', 'Ce compte est deja actif.');
            }

            DB::beginTransaction();

            // =============================================
            // AJOUT: Créditer le parrain en PV (10%)
            // =============================================
            $sponsorPV = 0;
            $sponsorName = null;
            
            if ($user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor && $sponsor->is_active) {
                    $sponsorPV = $package->pv_value * 0.10;
                    $sponsorBV = $package->bv_value * 0.10;
                    
                    $sponsor->pv_balance += $sponsorPV;
                    $sponsor->monthly_pv += $sponsorPV;
                    $sponsor->bv_balance += $sponsorBV;
                    $sponsor->monthly_bv += $sponsorBV;
                    $sponsor->saveQuietly();
                    $sponsorName = $sponsor->name;
                    
                    Log::info('Parrain credite en PV lors de l activation avec package', [
                        'parrain_id' => $sponsor->id,
                        'parrain_name' => $sponsor->name,
                        'pv_credited' => $sponsorPV,
                        'filleul_id' => $user->id,
                        'filleul_name' => $user->name,
                        'package' => $package->name,
                    ]);
                }
            }

            // Créditer le nouveau membre
            $user->addPV($package->pv_value, 'activation_with_package', $package->id);
            
            $user->activation_package_id = $package->id;
            $user->activation_code = null;
            $user->activation_code_expires_at = null;
            $user->package_id = $package->id;
            $user->activation_method = 'admin_manual_with_package';
            
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

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            $this->distributeCommissionsForActivation($user, $package);

            DB::commit();

            // =============================================
            // AJOUT: Mettre à jour le grade du parrain
            // =============================================
            if ($user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor) {
                    $this->rankCalculator->recalculateUserRank($sponsor, 'Activation filleul');
                }
            }

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new CalculatePVBV($user->id))->onQueue('high');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            $user->refresh();
            $rankName = $user->rank ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;

            $message = "Compte de {$user->name} active avec le package {$package->name} !";
            $message .= "\n{$package->pv_value} PV et {$package->bv_value} BV attribues";
            
            if ($sponsorPV > 0 && $sponsorName) {
                $message .= "\n\nLe parrain {$sponsorName} a recu " . number_format($sponsorPV, 1) . " PV (10%)";
            }
            
            $message .= "\nGrade: {$rankName} (Niv. {$rankLevel})";
            $message .= "\nLes recalculs complets sont en cours en arriere-plan.";

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

    private function distributeCommissionsForActivation($user, $package): void
    {
        try {
            if (!$user->parrain_id) {
                Log::info('Aucun parrain pour l\'activation, pas de commission', [
                    'user_id' => $user->id,
                ]);
                return;
            }

            $sponsor = User::find($user->parrain_id);
            if (!$sponsor || !$sponsor->is_active) {
                Log::info('Le parrain n\'est pas actif, pas de commission', [
                    'user_id' => $user->id,
                    'parrain_id' => $user->parrain_id,
                ]);
                return;
            }

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
                'source' => 'mlm',
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

            $commissions = $this->commissionDistributor->distributeCommissions(
                $user,
                $package,
                $order->id,
                $period
            );

            foreach ($commissions as $commission) {
                if ($commission->type === 'sponsor') {
                    $this->creditSponsorWallet($commission);
                }
            }

            Log::info('Commissions distribuees pour activation', [
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

            Log::info('Wallet du parrain credite', [
                'sponsor_id' => $commission->user_id,
                'amount' => $commission->amount,
                'commission_id' => $commission->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du credit du wallet du parrain', [
                'sponsor_id' => $commission->user_id,
                'commission_id' => $commission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendCode($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est deja actif.');
        }

        if (!$user->activation_code) {
            return back()->with('error', 'Aucun code d\'activation genere pour cet utilisateur.');
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

        return back()->with('success', "Code d'activation renvoye a {$user->email}");
    }

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