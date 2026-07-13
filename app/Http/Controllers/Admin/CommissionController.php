<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    protected $monthlyCommissionService;

    public function __construct(MonthlyCommissionService $monthlyCommissionService)
    {
        $this->monthlyCommissionService = $monthlyCommissionService;
    }

    public function index(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'order', 'package', 'period']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->sum('amount'),
            'total_count' => Commission::count(),
            'pending_count' => Commission::where('status', 'pending')->count(),
            'paid_count' => Commission::where('status', 'paid')->count(),
            'by_type' => Commission::select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->where('status', 'paid')
                ->groupBy('type')
                ->get()
                ->mapWithKeys(function($item) {
                    $labels = [
                        'direct' => 'Direct Bonuses',
                        'indirect' => 'Indirect Bonuses',
                        'leadership' => 'Leadership Bonuses',
                        'retail' => 'Retail Bonuses',
                        'global' => 'Global Bonuses',
                    ];
                    return [
                        $item->type => [
                            'label' => $labels[$item->type] ?? ucfirst($item->type),
                            'total' => (float) $item->total,
                            'count' => $item->count,
                        ]
                    ];
                }),
            'monthly' => Commission::where('status', 'paid')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $types = Commission::distinct()->pluck('type');
        $periods = CommissionPeriod::orderBy('period', 'desc')->pluck('period');

        return view('admin.commissions.index', compact(
            'commissions',
            'stats',
            'users',
            'types',
            'periods'
        ));
    }

    public function show($id)
    {
        $commission = Commission::with(['user', 'fromUser', 'order', 'package', 'period'])
            ->findOrFail($id);

        $parrain = User::find($commission->user->parrain_id ?? null);

        $similarCommissions = Commission::where('user_id', $commission->user_id)
            ->where('type', $commission->type)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $networkCommissions = Commission::where('user_id', $commission->user_id)
            ->where('status', 'paid')
            ->sum('amount');

        return view('admin.commissions.show', compact(
            'commission',
            'parrain',
            'similarCommissions',
            'networkCommissions'
        ));
    }

    public function approve($id)
    {
        $commission = Commission::findOrFail($id);

        if ($commission->status !== 'pending') {
            return back()->with('error', 'This commission cannot be approved.');
        }

        if ($commission->commission_period_id) {
            $period = CommissionPeriod::find($commission->commission_period_id);
            if ($period && $period->status === 'closed') {
                return back()->with('error', 'Cannot approve commission for a closed period.');
            }
        }

        DB::beginTransaction();

        try {
            $commission->status = 'paid';
            $commission->paid_at = now();
            $commission->save();

            $wallet = Wallet::where('user_id', $commission->user_id)->firstOrFail();

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
                'description' => "Commission {$commission->type} approved #{$commission->id}",
                'metadata' => json_encode([
                    'commission_id' => $commission->id,
                    'admin_id' => auth()->id(),
                    'period' => $commission->period,
                ]),
                'completed_at' => now(),
            ]);

            DB::commit();

            try {
                $commission->user->notify(new CommissionPaidNotification(
                    $commission->amount,
                    $commission->type,
                    $commission->id
                ));
            } catch (\Exception $e) {
                Log::error('Error sending commission notification', [
                    'commission_id' => $commission->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Commission approved', [
                'commission_id' => $commission->id,
                'user_id' => $commission->user_id,
                'amount' => $commission->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.commissions')
                ->with('success', "Commission #{$id} approved successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving commission', [
                'commission_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $commission = Commission::findOrFail($id);

        if ($commission->status !== 'pending') {
            return back()->with('error', 'This commission cannot be rejected.');
        }

        $commission->status = 'cancelled';
        $commission->notes = $request->reason ?? 'Rejected by admin';
        $commission->save();

        Log::info('Commission rejected', [
            'commission_id' => $commission->id,
            'user_id' => $commission->user_id,
            'amount' => $commission->amount,
            'admin_id' => auth()->id(),
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.commissions')
            ->with('success', "Commission #{$id} rejected.");
    }

    public function batchApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:commissions,id',
        ]);

        $count = 0;
        $errors = [];
        $totalAmount = 0;

        DB::beginTransaction();

        try {
            foreach ($request->ids as $id) {
                $commission = Commission::find($id);
                if (!$commission || $commission->status !== 'pending') {
                    $errors[] = "ID {$id}: Already processed or invalid";
                    continue;
                }

                if ($commission->commission_period_id) {
                    $period = CommissionPeriod::find($commission->commission_period_id);
                    if ($period && $period->status === 'closed') {
                        $errors[] = "ID {$id}: Period is closed";
                        continue;
                    }
                }

                $commission->status = 'paid';
                $commission->paid_at = now();
                $commission->save();

                $wallet = Wallet::where('user_id', $commission->user_id)->first();
                if ($wallet) {
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
                        'description' => "Commission {$commission->type} approved #{$commission->id}",
                        'metadata' => json_encode([
                            'commission_id' => $commission->id,
                            'admin_id' => auth()->id(),
                            'batch' => true,
                        ]),
                        'completed_at' => now(),
                    ]);

                    $totalAmount += $commission->amount;
                }

                $count++;
            }

            DB::commit();

            Log::info('Commissions batch approved', [
                'count' => $count,
                'total_amount' => $totalAmount,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} commissions approved for a total of {$totalAmount} USD",
                'count' => $count,
                'total_amount' => $totalAmount,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in batch approve', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'period']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="commissions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'User', 'Email', 'From', 'Type', 'Amount', 'Percentage',
                'Period', 'Description', 'Status', 'Paid At', 'Created At'
            ]);

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->user->name ?? 'N/A',
                    $c->user->email ?? 'N/A',
                    $c->fromUser->name ?? 'N/A',
                    $c->type,
                    number_format($c->amount, 2),
                    $c->percentage . '%',
                    $c->period ?? 'N/A',
                    $c->description ?? 'N/A',
                    $c->status,
                    $c->paid_at ? $c->paid_at->format('Y-m-d H:i') : 'Pending',
                    $c->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stats(Request $request)
    {
        $period = $request->input('period', date('Y-m'));

        $stats = [
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->sum('amount'),
            'by_type' => Commission::select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->where('status', 'paid')
                ->groupBy('type')
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type,
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => ucfirst($item->type),
                    ];
                }),
            'today' => Commission::whereDate('created_at', today())->sum('amount'),
            'this_month' => Commission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_count' => Commission::count(),
            'pending_count' => Commission::where('status', 'pending')->count(),
            'paid_count' => Commission::where('status', 'paid')->count(),
        ];

        $topUsers = Commission::where('status', 'paid')
            ->select('user_id', DB::raw('SUM(amount) as total'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $item->user->name ?? 'N/A',
                    'user_email' => $item->user->email ?? 'N/A',
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'top_users' => $topUsers,
        ]);
    }

    public function viewNetwork($userId)
    {
        $user = User::with(['rank', 'package'])->findOrFail($userId);

        $parrain = User::find($user->parrain_id);

        $filleuls = User::where('parrain_id', $user->id)->with(['rank', 'package'])->get();

        $networkCommissions = Commission::whereIn('user_id', $filleuls->pluck('id'))
            ->where('status', 'paid')
            ->sum('amount');

        $networkStats = [
            'total_filleuls' => $filleuls->count(),
            'active_filleuls' => $filleuls->where('is_active', true)->count(),
            'total_commissions' => $networkCommissions,
            'total_pv' => $user->pv_balance ?? 0,
            'total_bv' => $user->bv_balance ?? 0,
            'team_pv' => $user->team_pv ?? 0,
            'team_bv' => $user->team_bv ?? 0,
        ];

        return view('admin.commissions.network', compact(
            'user',
            'parrain',
            'filleuls',
            'networkCommissions',
            'networkStats'
        ));
    }

    public function recalculatePeriod(Request $request, $period)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        try {
            $commissionPeriod = CommissionPeriod::where('period', $period)->first();

            if (!$commissionPeriod) {
                return redirect()->back()->with('error', "Period {$period} not found.");
            }

            Commission::where('commission_period_id', $commissionPeriod->id)->delete();

            $result = $this->monthlyCommissionService->calculateMonthlyCommissions($commissionPeriod->id);

            if ($result) {
                return redirect()->back()->with('success', "Period {$period} recalculated successfully.");
            }

            return redirect()->back()->with('error', "Error recalculating period {$period}.");

        } catch (\Exception $e) {
            Log::error('Error recalculate period', [
                'period' => $period,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}