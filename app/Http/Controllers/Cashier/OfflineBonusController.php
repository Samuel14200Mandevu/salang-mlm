<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MLM\OfflinePeriodBonusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineBonusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasRole('cashier') && ! auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers.');
            }

            return $next($request);
        });
    }

    /**
     * Page : calculer les bonus d'un mois hors système (PV déjà importés).
     */
    public function calculateIndex(Request $request)
    {
        $periodsWithPv = PVHistory::query()
            ->select('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        $selectedPeriod = $request->input('period', $periodsWithPv->first());

        $periodRecord = $selectedPeriod
            ? CommissionPeriod::query()->where('period', $selectedPeriod)->first()
            : null;

        $summary = null;
        if ($selectedPeriod) {
            $commissions = Commission::query()
                ->where('period', $selectedPeriod)
                ->whereIn('type', ['direct', 'indirect', 'leadership']);

            $summary = [
                'pv_rows' => PVHistory::where('period', $selectedPeriod)->count(),
                'pv_total' => (float) PVHistory::where('period', $selectedPeriod)->sum('amount'),
                'commission_count' => (clone $commissions)->count(),
                'commission_total' => (float) (clone $commissions)->sum('amount'),
                'pending_total' => (float) (clone $commissions)->whereIn('status', ['pending', 'approved'])->sum('amount'),
                'paid_total' => (float) (clone $commissions)->where('status', 'paid')->sum('amount'),
                'members' => (clone $commissions)->pluck('user_id')->unique()->count(),
            ];
        }

        return view('cashier.offline-bonus.calculate', compact(
            'periodsWithPv',
            'selectedPeriod',
            'periodRecord',
            'summary'
        ));
    }

    public function calculate(Request $request, OfflinePeriodBonusService $service)
    {
        $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        try {
            $result = $service->calculate($request->period);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('cashier.offline-bonus.pay', ['period' => $result['period']])
            ->with(
                'success',
                "Bonus calculés pour {$result['period']} : {$result['created']} lignes, ".
                '$'.number_format($result['total_amount'], 2).' · '.
                "{$result['members']} membre(s). Vous pouvez maintenant les envoyer aux portefeuilles."
            );
    }

    /**
     * Page : envoyer les bonus calculés vers les portefeuilles membres.
     */
    public function payIndex(Request $request)
    {
        $periods = Commission::query()
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->select('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        $selectedPeriod = $request->input('period', $periods->first());
        $status = $request->input('status', 'pending');
        $search = trim((string) $request->input('search', ''));

        $byMember = collect();
        $totals = ['pending' => 0.0, 'paid' => 0.0, 'count' => 0];
        $stats = ['all' => 0, 'pending' => 0, 'paid' => 0];

        if ($selectedPeriod) {
            $rows = Commission::query()
                ->with('user:id,name,sponsor_id')
                ->where('period', $selectedPeriod)
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->orderBy('user_id')
                ->get();

            $byMember = $rows->groupBy('user_id')->map(function ($group) {
                /** @var \Illuminate\Support\Collection $group */
                $user = $group->first()->user;
                $pending = $group->whereIn('status', ['pending', 'approved']);
                $paid = $group->where('status', 'paid');

                return [
                    'user' => $user,
                    'pending_amount' => (float) $pending->sum('amount'),
                    'paid_amount' => (float) $paid->sum('amount'),
                    'pending_count' => $pending->count(),
                    'paid_count' => $paid->count(),
                    'total_amount' => (float) $group->sum('amount'),
                ];
            })->sortByDesc('pending_amount')->values();

            $totals = [
                'pending' => (float) $byMember->sum('pending_amount'),
                'paid' => (float) $byMember->sum('paid_amount'),
                'count' => $byMember->count(),
            ];

            $stats = [
                'all' => $byMember->count(),
                'pending' => $byMember->where('pending_amount', '>', 0)->count(),
                'paid' => $byMember->filter(fn ($row) => ($row['pending_amount'] ?? 0) <= 0)->count(),
            ];

            if ($status === 'pending') {
                $byMember = $byMember->filter(fn ($row) => ($row['pending_amount'] ?? 0) > 0)->values();
            } elseif ($status === 'paid') {
                $byMember = $byMember->filter(fn ($row) => ($row['pending_amount'] ?? 0) <= 0)->values();
            }

            if ($search !== '') {
                $needle = mb_strtolower($search);
                $byMember = $byMember->filter(function ($row) use ($needle) {
                    $user = $row['user'] ?? null;
                    if (! $user) {
                        return false;
                    }

                    return str_contains(mb_strtolower((string) $user->name), $needle)
                        || str_contains(mb_strtolower((string) $user->sponsor_id), $needle);
                })->values();
            }
        }

        return view('cashier.offline-bonus.pay', compact(
            'periods',
            'selectedPeriod',
            'byMember',
            'totals',
            'stats',
            'status',
            'search'
        ));
    }

    /**
     * Payer toutes les commissions pending/approved d'une période → portefeuilles.
     */
    public function payPeriod(Request $request)
    {
        $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $period = $request->period;

        $commissions = Commission::query()
            ->where('period', $period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved'])
            ->where('amount', '>', 0)
            ->get();

        if ($commissions->isEmpty()) {
            return back()->with('info', "Aucune commission à payer pour {$period}.");
        }

        return $this->payCommissionCollection($commissions, $period);
    }

    /**
     * Payer les commissions pending d'un membre pour une période.
     */
    public function payMember(Request $request, int $userId)
    {
        $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $member = User::query()->findOrFail($userId);
        $period = $request->period;

        $commissions = Commission::query()
            ->where('user_id', $member->id)
            ->where('period', $period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved'])
            ->where('amount', '>', 0)
            ->get();

        if ($commissions->isEmpty()) {
            return back()->with('info', 'Aucune commission à payer pour ce membre.');
        }

        return $this->payCommissionCollection($commissions, $period, $member->name);
    }

    protected function payCommissionCollection($commissions, string $period, ?string $memberName = null)
    {
        $paid = 0;
        $total = 0.0;

        try {
            DB::beginTransaction();

            foreach ($commissions as $commission) {
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Wallet::creditCommissionToUser(
                    (int) $commission->user_id,
                    (float) $commission->amount,
                    'Bonus '.$period.' : '.($commission->description ?? 'Commission #'.$commission->id),
                    [
                        'commission_id' => $commission->id,
                        'period' => $period,
                        'paid_by_cashier' => auth()->id(),
                        'source' => 'offline_bonus_pay',
                    ]
                );

                $paid++;
                $total += (float) $commission->amount;
            }

            $periodRecord = CommissionPeriod::query()->where('period', $period)->first();
            if ($periodRecord) {
                $stillPending = Commission::query()
                    ->where('period', $period)
                    ->whereIn('type', ['direct', 'indirect', 'leadership'])
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                $periodRecord->total_paid = (float) Commission::query()
                    ->where('period', $period)
                    ->whereIn('type', ['direct', 'indirect', 'leadership'])
                    ->where('status', 'paid')
                    ->sum('amount');

                if (! $stillPending) {
                    $periodRecord->status = 'paid';
                    $periodRecord->payment_date = now();
                } else {
                    $periodRecord->status = 'paying';
                }
                $periodRecord->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Offline bonus pay failed', [
                'period' => $period,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur paiement : '.$e->getMessage());
        }

        $who = $memberName ? " pour {$memberName}" : '';

        return back()->with(
            'success',
            "{$paid} commission(s) créditées{$who} · $".number_format($total, 2).
            ' — portefeuilles mis à jour / créés. Les montants sont dans l’historique.'
        );
    }
}
