<?php
// app/Http/Controllers/CommissionController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Package;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    protected MonthlyCommissionService $commissionService;

    public function __construct(MonthlyCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'order', 'period']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = $this->getUserStats($user->id);

        $byType = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                    'global' => 'Global',
                    'consumer' => 'Consumer',
                ];
                $colors = [
                    'direct' => 'primary',
                    'indirect' => 'warning',
                    'leadership' => 'danger',
                    'retail' => 'success',
                    'global' => 'info',
                    'consumer' => 'teal',
                ];
                return [
                    $item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => $colors[$item->type] ?? 'secondary',
                    ]
                ];
            });

        $monthly = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $periods = CommissionPeriod::orderBy('period', 'desc')->pluck('period');
        $types = Commission::where('user_id', $user->id)->distinct()->pluck('type');

        return view('commissions.index', compact(
            'commissions',
            'stats',
            'byType',
            'monthly',
            'periods',
            'types'
        ));
    }

    public function stats()
    {
        $user = Auth::user();
        $stats = $this->getUserStats($user->id);

        $byType = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                    'global' => 'Global',
                    'consumer' => 'Consumer',
                ];
                return [
                    $item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => [
                            'direct' => 'primary',
                            'indirect' => 'warning',
                            'leadership' => 'danger',
                            'retail' => 'success',
                            'global' => 'info',
                            'consumer' => 'teal',
                        ][$item->type] ?? 'secondary',
                    ]
                ];
            });

        $monthly = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $recent = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topSponsors = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('from_user_id', DB::raw('SUM(amount) as total'))
            ->with('fromUser')
            ->groupBy('from_user_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->fromUser?->name ?? 'Unknown',
                    'total' => (float) $item->total,
                ];
            });

        return view('commissions.stats', compact(
            'stats',
            'byType',
            'monthly',
            'recent',
            'topSponsors'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();

        $commission = Commission::with(['user', 'fromUser', 'package', 'order', 'period'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $similar = Commission::where('user_id', $user->id)
            ->where('type', $commission->type)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('commissions.show', compact('commission', 'similar'));
    }

    public function getLevelCommissions()
    {
        $user = Auth::user();

        $levels = [];
        $total = 0;

        $level1 = Commission::where('user_id', $user->id)
            ->where('type', 'direct')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[1] = [
            'label' => 'Direct (Level 1)',
            'amount' => (float) $level1,
            'percentage' => $this->getCommissionRate($user, 1),
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'direct')
                ->where('status', 'paid')
                ->count(),
            'color' => 'primary',
        ];
        $total += $level1;

        $level2 = Commission::where('user_id', $user->id)
            ->where('type', 'indirect')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[2] = [
            'label' => 'Indirect (Level 2)',
            'amount' => (float) $level2,
            'percentage' => $this->getCommissionRate($user, 2),
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'indirect')
                ->where('status', 'paid')
                ->count(),
            'color' => 'warning',
        ];
        $total += $level2;

        $level3 = Commission::where('user_id', $user->id)
            ->where('type', 'leadership')
            ->where('status', 'paid')
            ->sum('amount');
        $levels[3] = [
            'label' => 'Leadership (Level 3+)',
            'amount' => (float) $level3,
            'percentage' => $this->getCommissionRate($user, 3),
            'count' => Commission::where('user_id', $user->id)
                ->where('type', 'leadership')
                ->where('status', 'paid')
                ->count(),
            'color' => 'danger',
        ];
        $total += $level3;

        $level4 = Commission::where('user_id', $user->id)
            ->where('type', 'global')
            ->where('status', 'paid')
            ->sum('amount');
        if ($level4 > 0) {
            $levels[4] = [
                'label' => 'Global Bonus',
                'amount' => (float) $level4,
                'percentage' => $this->getCommissionRate($user, 4),
                'count' => Commission::where('user_id', $user->id)
                    ->where('type', 'global')
                    ->where('status', 'paid')
                    ->count(),
                'color' => 'info',
            ];
            $total += $level4;
        }

        return view('commissions.levels', compact('levels', 'total'));
    }

    private function getCommissionRate($user, $level)
    {
        $rates = [
            1 => 30,
            2 => 15,
            3 => 10,
            4 => 5,
        ];

        $rankLevel = $user->rank_level ?? 1;

        if ($rankLevel >= 5 && $level == 1) {
            $rates[1] = 30 + ($rankLevel - 5) * 2;
        }

        return min($rates[$level] ?? 0, 45);
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'period'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('period'), function ($query) use ($request) {
                return $query->where('period', $request->period);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'commissions_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Type', 'Amount', 'Rate (%)', 'Status',
                'Period', 'Description', 'From', 'Package', 'Date'
            ]);

            foreach ($commissions as $commission) {
                fputcsv($file, [
                    $commission->id,
                    ucfirst($commission->type),
                    number_format($commission->amount, 2),
                    $commission->percentage,
                    ucfirst($commission->status),
                    $commission->period ?? 'N/A',
                    $commission->description ?? '',
                    $commission->fromUser?->name ?? 'N/A',
                    $commission->package?->name ?? 'N/A',
                    $commission->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function apiIndex(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'period'])
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('period'), function ($query) use ($request) {
                return $query->where('period', $request->period);
            })
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 50))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $commissions,
            'total' => Commission::where('user_id', $user->id)->count(),
        ]);
    }

    public function apiStats()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'stats' => $this->getUserStats($user->id),
        ]);
    }

    private function getUserStats($userId)
    {
        $stats = Commission::where('user_id', $userId)
            ->select(
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
                DB::raw('COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count')
            )
            ->first();

        $byType = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                    'global' => 'Global',
                    'consumer' => 'Consumer',
                ];
                return [
                    $item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                        'label' => $labels[$item->type] ?? ucfirst($item->type),
                        'color' => [
                            'direct' => 'primary',
                            'indirect' => 'warning',
                            'leadership' => 'danger',
                            'retail' => 'success',
                            'global' => 'info',
                            'consumer' => 'teal',
                        ][$item->type] ?? 'secondary',
                    ]
                ];
            });

        $monthly = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values()
            ->toArray();

        $recent = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'total' => (float) ($stats->total ?? 0),
            'pending' => (float) ($stats->pending ?? 0),
            'paid' => (float) ($stats->paid ?? 0),
            'total_count' => (int) ($stats->total_count ?? 0),
            'pending_count' => (int) ($stats->pending_count ?? 0),
            'paid_count' => (int) ($stats->paid_count ?? 0),
            'by_type' => $byType,
            'monthly' => $monthly,
            'recent' => $recent,
        ];
    }
}