<?php
// app/Http/Controllers/CommissionDashboardController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Package;
use App\Services\MLM\UserCommissionDisplayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionDashboardController extends Controller
{
    public function __construct(
        protected UserCommissionDisplayService $commissionDisplay
    ) {}

    public function index()
    {
        $user = Auth::user();

        $commissionBands = $this->commissionDisplay->summarize($user->id);
        $stats = $this->getGlobalStats($user->id, $commissionBands);
        $byType = $this->getCommissionsByType($user->id);
        $monthly = $this->getMonthlyCommissions($user->id);

        $visible = fn ($q) => $q->where('user_id', $user->id)->visibleToMember();

        $recent = Commission::query()
            ->tap($visible)
            ->with(['fromUser', 'package', 'period'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topReferrals = Commission::query()
            ->tap($visible)
            ->where('status', 'paid')
            ->select('from_user_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('from_user_id')
            ->with('fromUser')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $pending = Commission::query()
            ->where('user_id', $user->id)
            ->pendingPayableViaSystem()
            ->with(['fromUser', 'package', 'period'])
            ->orderBy('created_at', 'desc')
            ->get();

        $byPackage = Commission::query()
            ->tap($visible)
            ->where('status', 'paid')
            ->whereNotNull('package_id')
            ->select('package_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('package_id')
            ->with('package')
            ->orderBy('total', 'desc')
            ->get();

        $bestMonth = Commission::query()
            ->tap($visible)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('total', 'desc')
            ->first();

        $avgCommission = Commission::query()
            ->tap($visible)
            ->where('status', 'paid')
            ->avg('amount');

        $daily = Commission::query()
            ->tap($visible)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return view('commissions.dashboard', compact(
            'stats',
            'byType',
            'monthly',
            'recent',
            'topReferrals',
            'pending',
            'byPackage',
            'bestMonth',
            'avgCommission',
            'daily',
            'user',
            'commissionBands'
        ));
    }

    /**
     * @param  array<string, float|int>  $commissionBands
     */
    private function getGlobalStats(int $userId, array $commissionBands): array
    {
        $stats = Commission::query()
            ->where('user_id', $userId)
            ->visibleToMember()
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount'),
                DB::raw('COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count'),
                DB::raw('SUM(CASE WHEN type = "direct" THEN amount ELSE 0 END) as direct_total'),
                DB::raw('SUM(CASE WHEN type = "indirect" THEN amount ELSE 0 END) as indirect_total'),
                DB::raw('SUM(CASE WHEN type = "leadership" THEN amount ELSE 0 END) as leadership_total'),
                DB::raw('SUM(CASE WHEN type = "retail" THEN amount ELSE 0 END) as retail_total')
            )
            ->first();

        $total = (float) ($stats->total_amount ?? 0);
        $paidAmount = (float) ($commissionBands['paid_total_visible'] ?? 0);
        $pendingAmount = (float) ($commissionBands['pending_payable'] ?? 0);

        return [
            'total_count' => (int) ($stats->total_count ?? 0),
            'total_amount' => $total,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'paid_count' => (int) ($commissionBands['paid_visible_count'] ?? 0),
            'pending_count' => (int) ($commissionBands['pending_payable_count'] ?? 0),
            'paid_offline_historical' => (float) ($commissionBands['paid_offline_historical'] ?? 0),
            'paid_system' => (float) ($commissionBands['paid_system'] ?? 0),
            'direct_total' => $stats->direct_total ?? 0,
            'indirect_total' => $stats->indirect_total ?? 0,
            'leadership_total' => $stats->leadership_total ?? 0,
            'retail_total' => $stats->retail_total ?? 0,
            'direct_percent' => $total > 0 ? round(($stats->direct_total ?? 0) / $total * 100, 1) : 0,
            'indirect_percent' => $total > 0 ? round(($stats->indirect_total ?? 0) / $total * 100, 1) : 0,
            'leadership_percent' => $total > 0 ? round(($stats->leadership_total ?? 0) / $total * 100, 1) : 0,
            'retail_percent' => $total > 0 ? round(($stats->retail_total ?? 0) / $total * 100, 1) : 0,
        ];
    }

    private function getCommissionsByType($userId)
    {
        $types = [
            'direct' => ['label' => 'Direct', 'color' => 'primary'],
            'indirect' => ['label' => 'Indirect', 'color' => 'warning'],
            'leadership' => ['label' => 'Leadership', 'color' => 'danger'],
            'retail' => ['label' => 'Retail', 'color' => 'success'],
            'global' => ['label' => 'Global', 'color' => 'info'],
        ];

        $data = Commission::where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $result = [];
        foreach ($types as $key => $info) {
            $result[$key] = [
                'label' => $info['label'],
                'color' => $info['color'],
                'total' => $data->has($key) ? $data[$key]->total : 0,
                'count' => $data->has($key) ? $data[$key]->count : 0,
            ];
        }

        return $result;
    }

    private function getMonthlyCommissions($userId)
    {
        $data = Commission::where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            $existing = $data->firstWhere('month', $month);

            $months[] = [
                'month' => $month,
                'label' => $monthLabel,
                'total' => $existing ? $existing->total : 0,
                'count' => $existing ? $existing->count : 0,
            ];
        }

        return $months;
    }
}