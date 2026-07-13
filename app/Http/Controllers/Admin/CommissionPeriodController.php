<?php
// app/Http/Controllers/Admin/CommissionPeriodController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionPeriod;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommissionPeriodController extends Controller
{
    protected $monthlyCommissionService;

    public function __construct(MonthlyCommissionService $monthlyCommissionService)
    {
        $this->monthlyCommissionService = $monthlyCommissionService;
    }

    public function index(Request $request)
    {
        $query = CommissionPeriod::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('period', 'like', $request->year . '%');
        }

        $periods = $query->orderBy('period', 'desc')->paginate(20);

        $stats = [
            'total_periods' => CommissionPeriod::count(),
            'pending' => CommissionPeriod::where('status', 'pending')->count(),
            'calculating' => CommissionPeriod::where('status', 'calculating')->count(),
            'calculated' => CommissionPeriod::where('status', 'calculated')->count(),
            'paying' => CommissionPeriod::where('status', 'paying')->count(),
            'paid' => CommissionPeriod::where('status', 'paid')->count(),
            'closed' => CommissionPeriod::where('status', 'closed')->count(),
            'total_commissions' => CommissionPeriod::sum('total_commissions'),
            'total_paid' => CommissionPeriod::sum('total_paid'),
        ];

        $years = CommissionPeriod::select(DB::raw('DISTINCT SUBSTRING(period, 1, 4) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('admin.commissions.periods', compact('periods', 'stats', 'years'));
    }

    public function show($id)
    {
        $period = CommissionPeriod::with(['payments.user', 'commissions' => function($query) {
            $query->with(['user', 'fromUser'])->orderBy('amount', 'desc');
        }])->findOrFail($id);

        $stats = [
            'total_commissions' => $period->total_commissions,
            'total_paid' => $period->total_paid,
            'total_pending' => Commission::where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->sum('amount'),
            'users_with_commissions' => Commission::where('commission_period_id', $period->id)
                ->distinct('user_id')->count('user_id'),
            'users_paid' => CommissionPayment::where('commission_period_id', $period->id)
                ->where('status', 'paid')
                ->count(),
            'users_pending' => CommissionPayment::where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->count(),
            'by_type' => Commission::where('commission_period_id', $period->id)
                ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->type => [
                        'total' => (float) $item->total,
                        'count' => $item->count,
                    ]];
                }),
            'top_earners' => CommissionPayment::where('commission_period_id', $period->id)
                ->with('user')
                ->orderBy('net_amount', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'user_name' => $item->user->name ?? 'N/A',
                        'user_email' => $item->user->email ?? 'N/A',
                        'total' => (float) $item->total_amount,
                        'net' => (float) $item->net_amount,
                    ];
                }),
        ];

        return view('admin.commissions.period-detail', compact('period', 'stats'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2024',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $periodString = $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $existing = CommissionPeriod::where('period', $periodString)->first();

        if ($existing) {
            return back()->with('error', "Period {$periodString} already exists.");
        }

        try {
            $period = $this->monthlyCommissionService->createMonthlyPeriod(
                $request->year,
                $request->month
            );

            return redirect()->route('admin.commissions.periods')
                ->with('success', "Period {$period->period} created successfully.");

        } catch (\Exception $e) {
            Log::error('Error creating period', [
                'year' => $request->year,
                'month' => $request->month,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function process(Request $request, $id)
    {
        $period = CommissionPeriod::findOrFail($id);

        if ($period->status === 'closed') {
            return back()->with('error', 'This period is closed.');
        }

        $action = $request->input('action');
        $isDryRun = $request->input('dry_run', false);

        try {
            switch ($action) {
                case 'calculate_pv':
                    $result = $this->monthlyCommissionService->calculateMonthlyPVBV($period->id);
                    $message = 'PV/BV calculated successfully';
                    break;

                case 'calculate_ranks':
                    $result = $this->monthlyCommissionService->calculateMonthlyRanks($period->id);
                    $message = 'Ranks calculated successfully';
                    break;

                case 'calculate_commissions':
                    $result = $this->monthlyCommissionService->calculateMonthlyCommissions($period->id);
                    $message = 'Commissions calculated successfully';
                    break;

                case 'generate_payments':
                    if (!$isDryRun) {
                        $result = $this->monthlyCommissionService->generatePayments($period->id);
                        $message = 'Payments generated successfully';
                    } else {
                        $result = true;
                        $message = 'Simulation: payments not generated';
                    }
                    break;

                case 'process_all':
                    if (!$isDryRun) {
                        $result = $this->processAllSteps($period->id);
                        $message = 'Full processing completed successfully';
                    } else {
                        $result = true;
                        $message = 'Simulation: full processing not executed';
                    }
                    break;

                default:
                    return back()->with('error', 'Action not recognized');
            }

            if ($result) {
                return back()->with('success', $message);
            }

            return back()->with('error', 'Error during processing.');

        } catch (\Exception $e) {
            Log::error('Error processing period', [
                'period_id' => $id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function processAllSteps($periodId)
    {
        if (!$this->monthlyCommissionService->calculateMonthlyPVBV($periodId)) {
            return false;
        }

        if (!$this->monthlyCommissionService->calculateMonthlyRanks($periodId)) {
            return false;
        }

        if (!$this->monthlyCommissionService->calculateMonthlyCommissions($periodId)) {
            return false;
        }

        if (!$this->monthlyCommissionService->generatePayments($periodId)) {
            return false;
        }

        return true;
    }

    public function close($id)
    {
        $period = CommissionPeriod::findOrFail($id);

        if ($period->status === 'closed') {
            return back()->with('error', 'This period is already closed.');
        }

        if ($period->status !== 'paid') {
            return back()->with('error', 'Period must be paid before closing.');
        }

        $period->status = 'closed';
        $period->notes = ($period->notes ?? '') . "\nClosed on " . now();
        $period->save();

        Log::info('Period closed', [
            'period_id' => $period->id,
            'period' => $period->period,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.commissions.periods')
            ->with('success', "Period {$period->period} closed successfully.");
    }

    public function destroy($id)
    {
        $period = CommissionPeriod::findOrFail($id);

        if ($period->status === 'closed') {
            return back()->with('error', 'A closed period cannot be deleted.');
        }

        if ($period->status === 'calculating' || $period->status === 'paying') {
            return back()->with('error', 'Cannot delete a period being processed.');
        }

        if ($period->payments()->where('status', 'paid')->count() > 0) {
            return back()->with('error', 'Payments have already been made for this period.');
        }

        DB::beginTransaction();

        try {
            Commission::where('commission_period_id', $period->id)->delete();
            CommissionPayment::where('commission_period_id', $period->id)->delete();
            $period->delete();

            DB::commit();

            Log::info('Period deleted', [
                'period_id' => $id,
                'period' => $period->period,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.commissions.periods')
                ->with('success', "Period {$period->period} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting period', [
                'period_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function export($id)
    {
        $period = CommissionPeriod::findOrFail($id);

        $commissions = Commission::where('commission_period_id', $period->id)
            ->with(['user', 'fromUser'])
            ->orderBy('amount', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="period_' . $period->period . '_commissions.csv"',
        ];

        $callback = function() use ($commissions, $period) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Period', 'Commission ID', 'User', 'Email', 'From',
                'Type', 'Amount', 'Percentage', 'Status', 'Date'
            ]);

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $period->period,
                    $c->id,
                    $c->user->name ?? 'N/A',
                    $c->user->email ?? 'N/A',
                    $c->fromUser->name ?? 'N/A',
                    $c->type,
                    number_format($c->amount, 2),
                    $c->percentage . '%',
                    $c->status,
                    $c->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}