<?php
// app/Http/Controllers/CommissionTriggerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommissionTriggerController extends Controller
{
    protected MonthlyCommissionService $monthlyCommissionService;
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(
        MonthlyCommissionService $monthlyCommissionService,
        AdvancedRankCalculator $rankCalculator
    ) {
        $this->monthlyCommissionService = $monthlyCommissionService;
        $this->rankCalculator = $rankCalculator;
    }

    /**
     * Trigger package commission for a user
     */
    public function triggerPackageCommission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if (!Auth::user()->isAdmin() && Auth::id() != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $user = User::find($request->user_id);
            $package = Package::find($request->package_id);

            if (!$user || !$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'User or package not found'
                ], 404);
            }

            // Add PV/BV from package
            $user->pv_balance += $package->pv_value;
            $user->bv_balance += $package->bv_value;
            $user->save();

            // Log the action
            Log::info('Package commission triggered', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'pv_added' => $package->pv_value,
                'bv_added' => $package->bv_value,
                'admin_id' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Package commission triggered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'rank' => $user->rank_name,
                        'pv_balance' => $user->pv_balance,
                        'bv_balance' => $user->bv_balance,
                    ],
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'pv_value' => $package->pv_value,
                        'bv_value' => $package->bv_value,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error triggering package commission: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate all commissions for a period
     */
    public function recalculateAll(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'period' => 'nullable|date_format:Y-m',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $period = $request->input('period', date('Y-m'));

            if ($request->filled('user_id')) {
                return $this->recalculateUser($request->user_id, $period);
            }

            $commissionPeriod = CommissionPeriod::where('period', $period)->first();

            if (!$commissionPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => "Period {$period} not found. Please create the period first."
                ], 404);
            }

            // Delete existing commissions for this period
            $deleted = Commission::where('commission_period_id', $commissionPeriod->id)->delete();

            Log::info('Recalculating all commissions', [
                'period' => $period,
                'deleted_commissions' => $deleted,
                'admin_id' => Auth::id(),
            ]);

            // Recalculate commissions
            $result = $this->monthlyCommissionService->calculateMonthlyCommissions($commissionPeriod->id);

            if ($result) {
                $stats = [
                    'total_commissions' => Commission::where('commission_period_id', $commissionPeriod->id)->sum('amount'),
                    'total_direct' => Commission::where('commission_period_id', $commissionPeriod->id)
                        ->where('type', 'direct')->sum('amount'),
                    'total_indirect' => Commission::where('commission_period_id', $commissionPeriod->id)
                        ->where('type', 'indirect')->sum('amount'),
                    'total_leadership' => Commission::where('commission_period_id', $commissionPeriod->id)
                        ->where('type', 'leadership')->sum('amount'),
                    'total_consumer' => Commission::where('commission_period_id', $commissionPeriod->id)
                        ->where('type', 'consumer')->sum('amount'),
                    'total_global' => Commission::where('commission_period_id', $commissionPeriod->id)
                        ->where('type', 'global')->sum('amount'),
                ];

                return response()->json([
                    'success' => true,
                    'message' => "Recalculation completed for period {$period}",
                    'data' => [
                        'period' => $period,
                        'deleted_commissions' => $deleted,
                        'stats' => $stats,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error during recalculation'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error recalculating all: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate commissions for a specific user
     */
    private function recalculateUser($userId, $period)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        try {
            $commissionPeriod = CommissionPeriod::where('period', $period)->first();
            if (!$commissionPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => "Period {$period} not found"
                ], 404);
            }

            // Delete existing commissions for this user and period
            $deleted = Commission::where('commission_period_id', $commissionPeriod->id)
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('from_user_id', $user->id);
                })
                ->delete();

            Log::info('Recalculating user commissions', [
                'user_id' => $user->id,
                'period' => $period,
                'deleted_commissions' => $deleted,
                'admin_id' => Auth::id(),
            ]);

            // Calculate commissions for the user
            $commissions = $this->monthlyCommissionService->calculateUserCommissions($user, $commissionPeriod);

            // Create commission records
            $created = 0;
            foreach ($commissions as $commissionData) {
                Commission::create(array_merge(
                    $commissionData,
                    [
                        'commission_period_id' => $commissionPeriod->id,
                        'period' => $period,
                        'status' => 'pending',
                        'calculation_type' => 'manual',
                    ]
                ));
                $created++;
            }

            return response()->json([
                'success' => true,
                'message' => "Recalculation completed for {$user->name}",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'period' => $period,
                    'deleted_commissions' => $deleted,
                    'commissions_created' => $created,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error recalculating user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger retail commission for a user
     */
    public function triggerRetailCommission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if (!Auth::user()->isAdmin() && Auth::id() != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $user = User::find($request->user_id);
            $retailRate = config('commission.rates.retail', 25);
            $commission = $request->amount * ($retailRate / 100);

            Log::info('Retail commission triggered', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'commission' => $commission,
                'rate' => $retailRate,
                'admin_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Retail commission calculated',
                'data' => [
                    'user' => $user->name,
                    'amount' => $request->amount,
                    'commission' => $commission,
                    'rate' => $retailRate . '%',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error triggering retail commission: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force monthly processing for a period
     */
    public function forceMonthlyProcessing(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'year' => 'nullable|integer|min:2024|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'dry_run' => 'nullable|boolean',
        ]);

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m', strtotime('-1 month')));
        $isDryRun = $request->input('dry_run', false);

        try {
            $periodString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

            if ($isDryRun) {
                return response()->json([
                    'success' => true,
                    'message' => "Simulation mode - No changes made for {$periodString}",
                    'data' => [
                        'period' => $periodString,
                        'dry_run' => true,
                        'steps' => [
                            'create_period' => 'simulated',
                            'calculate_pv' => 'simulated',
                            'calculate_ranks' => 'simulated',
                            'calculate_commissions' => 'simulated',
                            'generate_payments' => 'simulated',
                        ]
                    ]
                ]);
            }

            // Check if period already exists
            $existing = CommissionPeriod::where('period', $periodString)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => "Period {$periodString} already exists. Please use a different period or clean it first."
                ], 400);
            }

            // Create period
            $period = $this->monthlyCommissionService->createMonthlyPeriod($year, $month);

            $results = [
                'period' => $period->period,
                'steps' => []
            ];

            // Step 1: Calculate PV/BV
            $result = $this->monthlyCommissionService->calculateMonthlyPVBV($period->id);
            $results['steps']['pv'] = $result ? 'completed' : 'failed';

            // Step 2: Calculate ranks
            $result = $this->monthlyCommissionService->calculateMonthlyRanks($period->id);
            $results['steps']['ranks'] = $result ? 'completed' : 'failed';

            // Step 3: Calculate commissions
            $result = $this->monthlyCommissionService->calculateMonthlyCommissions($period->id);
            $results['steps']['commissions'] = $result ? 'completed' : 'failed';

            // Step 4: Generate payments
            $result = $this->monthlyCommissionService->generatePayments($period->id);
            $results['steps']['payments'] = $result ? 'completed' : 'failed';

            Log::info('Monthly processing forced', [
                'period' => $periodString,
                'results' => $results,
                'admin_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Monthly processing completed for {$periodString}",
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Error forcing monthly processing: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly processing status
     */
    public function getMonthlyStatus(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $period = $request->input('period', date('Y-m'));

        try {
            $commissionPeriod = CommissionPeriod::where('period', $period)->first();

            if (!$commissionPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => "Period {$period} not found"
                ], 404);
            }

            $stats = [
                'period' => $commissionPeriod->period,
                'status' => $commissionPeriod->status,
                'status_label' => $commissionPeriod->status_label,
                'start_date' => $commissionPeriod->start_date,
                'end_date' => $commissionPeriod->end_date,
                'calculation_date' => $commissionPeriod->calculation_date,
                'payment_date' => $commissionPeriod->payment_date,
                'total_commissions' => $commissionPeriod->total_commissions,
                'total_paid' => $commissionPeriod->total_paid,
                'progress' => $commissionPeriod->progress,
                'commissions_count' => Commission::where('commission_period_id', $commissionPeriod->id)->count(),
                'users_with_commissions' => Commission::where('commission_period_id', $commissionPeriod->id)
                    ->distinct('user_id')->count('user_id'),
                'payments_count' => $commissionPeriod->payments()->count(),
                'payments_paid' => $commissionPeriod->payments()->where('status', 'paid')->count(),
                'payments_pending' => $commissionPeriod->payments()->where('status', 'pending')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting monthly status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean a period (delete all data)
     */
    public function cleanPeriod(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'period' => 'required|date_format:Y-m',
            'confirm' => 'required|boolean',
        ]);

        if (!$request->input('confirm')) {
            return response()->json([
                'success' => false,
                'message' => 'You must confirm the deletion'
            ], 400);
        }

        try {
            $period = $request->input('period');
            $commissionPeriod = CommissionPeriod::where('period', $period)->first();

            if (!$commissionPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => "Period {$period} not found"
                ], 404);
            }

            if ($commissionPeriod->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => "Period {$period} is closed, cannot clean"
                ], 400);
            }

            DB::beginTransaction();

            // Delete commissions
            $deletedCommissions = Commission::where('commission_period_id', $commissionPeriod->id)->delete();

            // Delete payments
            $deletedPayments = $commissionPeriod->payments()->delete();

            // Delete monthly ranks
            $deletedRanks = \App\Models\UserMonthlyRank::where('period', $period)->delete();

            // Reset period
            $commissionPeriod->status = 'pending';
            $commissionPeriod->total_commissions = 0;
            $commissionPeriod->total_paid = 0;
            $commissionPeriod->calculation_date = null;
            $commissionPeriod->payment_date = null;
            $commissionPeriod->notes = 'Cleaned manually on ' . now();
            $commissionPeriod->save();

            DB::commit();

            Log::info('Period cleaned', [
                'period' => $period,
                'deleted_commissions' => $deletedCommissions,
                'deleted_payments' => $deletedPayments,
                'deleted_ranks' => $deletedRanks,
                'admin_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Period {$period} cleaned successfully",
                'data' => [
                    'period' => $period,
                    'deleted_commissions' => $deletedCommissions,
                    'deleted_payments' => $deletedPayments,
                    'deleted_ranks' => $deletedRanks,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cleaning period: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all periods with their status
     */
    public function getPeriods(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $periods = CommissionPeriod::orderBy('period', 'desc')
                ->limit($request->input('limit', 12))
                ->get()
                ->map(function($period) {
                    return [
                        'period' => $period->period,
                        'status' => $period->status,
                        'status_label' => $period->status_label,
                        'start_date' => $period->start_date,
                        'end_date' => $period->end_date,
                        'total_commissions' => $period->total_commissions,
                        'total_paid' => $period->total_paid,
                        'progress' => $period->progress,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $periods
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting periods: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a specific period step
     */
    public function processPeriodStep(Request $request, $periodId)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'step' => 'required|in:pv,ranks,commissions,payments',
        ]);

        try {
            $period = CommissionPeriod::findOrFail($periodId);

            if ($period->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This period is closed'
                ], 400);
            }

            $step = $request->input('step');
            $result = false;

            switch ($step) {
                case 'pv':
                    $result = $this->monthlyCommissionService->calculateMonthlyPVBV($period->id);
                    break;
                case 'ranks':
                    $result = $this->monthlyCommissionService->calculateMonthlyRanks($period->id);
                    break;
                case 'commissions':
                    $result = $this->monthlyCommissionService->calculateMonthlyCommissions($period->id);
                    break;
                case 'payments':
                    $result = $this->monthlyCommissionService->generatePayments($period->id);
                    break;
            }

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Step '{$step}' completed successfully",
                    'data' => [
                        'period' => $period->period,
                        'step' => $step,
                        'status' => $period->fresh()->status,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Error processing step '{$step}'"
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error processing period step: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}