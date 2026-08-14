<?php
// app/Http/Controllers/UserPackageController.php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\CommissionDistributor;
use App\Services\MLM\AdvancedRankCalculator;
use App\Jobs\UpdateTeamPV;
use App\Jobs\UpdateRanks;
use App\Jobs\CalculatePVBV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserPackageController extends Controller
{
    protected MonthlyCommissionService $commissionService;
    protected CommissionDistributor $commissionDistributor;
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(
        MonthlyCommissionService $commissionService,
        CommissionDistributor $commissionDistributor,
        AdvancedRankCalculator $rankCalculator
    ) {
        $this->commissionService = $commissionService;
        $this->commissionDistributor = $commissionDistributor;
        $this->rankCalculator = $rankCalculator;
    }

    public function index()
    {
        $subscriptions = Package::where('is_active', true)->get();

        if ($subscriptions->isEmpty()) {
            $this->createDefaultPackages();
            $subscriptions = Package::where('is_active', true)->get();
        }

        $user = Auth::user();

        $totalPV = ($user->pv_balance ?? 0) + ($user->package?->pv_value ?? 0);
        $totalBV = ($user->bv_balance ?? 0) + ($user->package?->bv_value ?? 0);

        return view('subscriptions.index', compact('subscriptions', 'user', 'totalPV', 'totalBV'));
    }

    public function buy(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $user = Auth::user();
        $package = Package::findOrFail($request->package_id);

        if ($user->package_id == $package->id) {
            return back()->with('error', 'You already have this package.');
        }

        if ($user->package_id && $user->package_id > $package->id) {
            return back()->with('error', 'You cannot downgrade to a lower package.');
        }

        DB::beginTransaction();

        try {
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

            if ($wallet->balance < $package->price) {
                return back()->with('error', 'Insufficient balance. You have $' . number_format($wallet->balance, 2) . ' and the package costs $' . number_format($package->price, 2) . '.');
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance -= $package->price;
            $wallet->save();

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
                'description' => 'Purchase of package ' . $package->name,
                'metadata' => json_encode(['package_id' => $package->id]),
                'completed_at' => now(),
            ]);

            $user->package_id = $package->id;
            $user->pv_balance = ($user->pv_balance ?? 0) + $package->pv_value;
            $user->bv_balance = ($user->bv_balance ?? 0) + $package->bv_value;
            $user->monthly_pv = ($user->monthly_pv ?? 0) + $package->pv_value;
            $user->monthly_bv = ($user->monthly_bv ?? 0) + $package->bv_value;
            
            $wasInactive = false;
            if (!$user->is_active) {
                $wasInactive = true;
                $user->is_active = true;
                $user->activated_at = now();
                $user->activation_method = 'package';
                $user->activation_code = null;
                $user->activation_code_expires_at = null;
            }
            
            $user->save();

            $this->calculateCommissionsForPackage($user, $package);

            DB::commit();

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
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $rankName = $user->rank_name ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;

            $message = "Package '{$package->name}' purchased successfully!\n";
            $message .= "Grade actuel: {$rankName} (Niv. {$rankLevel})\n";
            $message .= "PV Total: " . number_format($user->pv_balance, 1, ',', ' ') . "\n";
            $message .= "Cumul PV: " . number_format(($user->pv_balance ?? 0) + ($user->team_pv ?? 0), 1, ',', ' ') . "\n";
            
            if ($package->pv_value > 0) {
                $message .= "You earned {$package->pv_value} PV and {$package->bv_value} BV.\n";
            }
            if ($wasInactive) {
                $message .= "Your account has been activated!\n";
            }
            $message .= "Les recalculs complets sont en cours en arriere-plan.";

            Log::info('Package purchase completed with jobs', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'was_inactive' => $wasInactive,
                'rank' => $rankName,
                'rank_level' => $rankLevel,
            ]);

            return redirect()->route('subscriptions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error purchasing package: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error purchasing package: ' . $e->getMessage());
        }
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $user = Auth::user();
        $newPackage = Package::findOrFail($request->package_id);

        if (!$user->package_id) {
            return back()->with('error', 'You must first purchase a package.');
        }

        if ($user->package_id >= $newPackage->id) {
            return back()->with('error', 'You cannot upgrade to a lower or equal package.');
        }

        DB::beginTransaction();

        try {
            $currentPackage = Package::find($user->package_id);
            $upgradePrice = $newPackage->price - ($currentPackage ? $currentPackage->price : 0);

            if ($upgradePrice <= 0) {
                return back()->with('error', 'Invalid upgrade price.');
            }

            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                return back()->with('error', 'Wallet not found.');
            }

            if ($wallet->balance < $upgradePrice) {
                return back()->with('error', 'Insufficient balance for upgrade.');
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance -= $upgradePrice;
            $wallet->save();

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
                'description' => "Upgrade to {$newPackage->name}",
                'metadata' => json_encode([
                    'old_package' => $currentPackage?->name,
                    'new_package' => $newPackage->name,
                ]),
                'completed_at' => now(),
            ]);

            $pvDiff = $newPackage->pv_value - ($currentPackage ? $currentPackage->pv_value : 0);
            $bvDiff = $newPackage->bv_value - ($currentPackage ? $currentPackage->bv_value : 0);

            $user->package_id = $newPackage->id;
            $user->pv_balance = ($user->pv_balance ?? 0) + $pvDiff;
            $user->bv_balance = ($user->bv_balance ?? 0) + $bvDiff;
            $user->monthly_pv = ($user->monthly_pv ?? 0) + $pvDiff;
            $user->monthly_bv = ($user->monthly_bv ?? 0) + $bvDiff;
            
            $wasInactive = false;
            if (!$user->is_active) {
                $wasInactive = true;
                $user->is_active = true;
                $user->activated_at = now();
                $user->activation_method = 'upgrade';
                $user->activation_code = null;
                $user->activation_code_expires_at = null;
            }
            
            $user->save();

            $this->calculateCommissionsForPackage($user, $newPackage);

            DB::commit();

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
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $rankName = $user->rank_name ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;

            $message = "Package upgraded to '{$newPackage->name}' successfully!\n";
            $message .= "Grade actuel: {$rankName} (Niv. {$rankLevel})\n";
            $message .= "PV Total: " . number_format($user->pv_balance, 1, ',', ' ') . "\n";
            $message .= "Cumul PV: " . number_format(($user->pv_balance ?? 0) + ($user->team_pv ?? 0), 1, ',', ' ') . "\n";
            $message .= "PV earned: {$pvDiff}\n";
            
            if ($wasInactive) {
                $message .= "Your account has been activated!\n";
            }
            $message .= "Les recalculs complets sont en cours en arriere-plan.";

            Log::info('Package upgrade completed with jobs', [
                'user_id' => $user->id,
                'old_package_id' => $currentPackage?->id,
                'new_package_id' => $newPackage->id,
                'was_inactive' => $wasInactive,
                'rank' => $rankName,
                'rank_level' => $rankLevel,
            ]);

            return redirect()->route('subscriptions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error upgrading package: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'new_package_id' => $newPackage->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error upgrading package: ' . $e->getMessage());
        }
    }

    private function calculateCommissionsForPackage(User $buyer, Package $package): void
    {
        try {
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            $order = Order::create([
                'user_id' => $buyer->id,
                'order_number' => 'PKG-' . strtoupper(uniqid()),
                'subtotal' => $package->price,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $package->price,
                'status' => 'completed',
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'name' => $package->name,
                'sku' => 'PKG-' . $package->slug,
                'quantity' => 1,
                'price' => $package->price,
                'total' => $package->price,
                'pv_value' => $package->pv_value,
                'bv_value' => $package->bv_value,
                'options' => json_encode([
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                ]),
            ]);

            $commissions = $this->commissionDistributor->distributeCommissions(
                $buyer,
                $package,
                $order->id,
                $period
            );

            $totalAmount = collect($commissions)->sum('amount');

            Log::info('Commissions calculees pour l\'achat de package', [
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'commissions_count' => count($commissions),
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des commissions pour le package', [
                'buyer_id' => $buyer->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

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
                'description' => 'Ideal package to start',
                'is_active' => true,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'price' => 85,
                'pv_value' => 50,
                'bv_value' => 30,
                'commission_rate' => 30,
                'description' => 'Silver package for ambassadors',
                'is_active' => true,
            ],
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'price' => 350,
                'pv_value' => 200,
                'bv_value' => 150,
                'commission_rate' => 30,
                'description' => 'Bronze package for leaders',
                'is_active' => true,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'price' => 1450,
                'pv_value' => 1000,
                'bv_value' => 800,
                'commission_rate' => 30,
                'description' => 'Gold package for elites',
                'is_active' => true,
            ],
            [
                'name' => 'Emerald',
                'slug' => 'emerald',
                'price' => 4850,
                'pv_value' => 3800,
                'bv_value' => 3000,
                'commission_rate' => 30,
                'description' => 'Emerald package for legends',
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