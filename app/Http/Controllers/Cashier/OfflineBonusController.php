<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Services\MLM\OfflinePeriodBonusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineBonusController extends Controller
{
    use GeneratesPdfSafely;

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
        $search = trim((string) $request->input('search', ''));

        $periodRecord = $selectedPeriod
            ? CommissionPeriod::query()->where('period', $selectedPeriod)->first()
            : null;

        $summary = null;
        $membersWithPv = collect();

        if ($selectedPeriod) {
            $commissions = Commission::query()
                ->where('period', $selectedPeriod)
                ->whereIn('type', ['direct', 'indirect', 'leadership']);

            $pvRows = PVHistory::query()
                ->where('period', $selectedPeriod)
                ->selectRaw('user_id, SUM(amount) as pv_total')
                ->groupBy('user_id')
                ->orderByDesc('pv_total')
                ->get();

            $users = User::query()
                ->with('parrain:id,sponsor_id,name')
                ->whereIn('id', $pvRows->pluck('user_id'))
                ->get(['id', 'name', 'sponsor_id', 'rank_level', 'parrain_id'])
                ->keyBy('id');

            $membersWithPv = $pvRows->map(function ($row) use ($users) {
                return [
                    'user' => $users->get($row->user_id),
                    'pv' => (float) $row->pv_total,
                ];
            })->values();

            $summary = [
                'pv_rows' => PVHistory::where('period', $selectedPeriod)->count(),
                'pv_total' => (float) PVHistory::where('period', $selectedPeriod)->sum('amount'),
                'commission_count' => (clone $commissions)->count(),
                'commission_total' => (float) (clone $commissions)->sum('amount'),
                'pending_total' => (float) (clone $commissions)->whereIn('status', ['pending', 'approved'])->sum('amount'),
                'paid_total' => (float) (clone $commissions)->where('status', 'paid')->sum('amount'),
                'members' => $membersWithPv->count(),
            ];

            if ($search !== '') {
                $needle = mb_strtolower($search);
                $membersWithPv = $membersWithPv->filter(function ($row) use ($needle) {
                    $user = $row['user'] ?? null;
                    if (! $user) {
                        return false;
                    }

                    return str_contains(mb_strtolower((string) $user->name), $needle)
                        || str_contains(mb_strtolower((string) $user->sponsor_id), $needle)
                        || str_contains(mb_strtolower((string) ($user->parrain?->name ?? '')), $needle)
                        || str_contains(mb_strtolower((string) ($user->parrain?->sponsor_id ?? '')), $needle);
                })->values();
            }
        }

        return view('cashier.offline-bonus.calculate', compact(
            'periodsWithPv',
            'selectedPeriod',
            'periodRecord',
            'summary',
            'membersWithPv',
            'search'
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
     * Rapport global imprimable (même modèle que admin PV) — ouvre l’interface d’impression.
     */
    public function exportGlobalPdf(Request $request)
    {
        $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $period = (string) $request->input('period');

        try {
            $commissions = Commission::query()
                ->with([
                    'user:id,name,sponsor_id,parrain_id,rank_level,pv_balance',
                    'user.parrain:id,sponsor_id',
                ])
                ->where('period', $period)
                ->whereIn('type', ['direct', 'indirect', 'leadership', 'cash_pos'])
                ->select(['id', 'user_id', 'type', 'amount', 'period'])
                ->orderBy('user_id')
                ->orderBy('type')
                ->get();

            if ($commissions->isEmpty()) {
                return back()->with('error', 'Aucune commission trouvée pour la période '.$period.'.');
            }

            $totals = [
                'direct' => (float) $commissions->where('type', 'direct')->sum('amount'),
                'indirect' => (float) $commissions->where('type', 'indirect')->sum('amount'),
                'leadership' => (float) $commissions->where('type', 'leadership')->sum('amount'),
                'cash_pos' => (float) $commissions->where('type', 'cash_pos')->sum('amount'),
                'total' => (float) $commissions->sum('amount'),
            ];

            $commissionsCount = $commissions->count();

            $typeStats = [];
            foreach (['direct', 'indirect', 'leadership', 'cash_pos'] as $type) {
                $typeItems = $commissions->where('type', $type);
                $count = $typeItems->count();
                $total = (float) $typeItems->sum('amount');
                $typeStats[$type] = [
                    'count' => $count,
                    'total' => $total,
                    'percentage' => $totals['total'] > 0 ? ($total / $totals['total']) * 100 : 0,
                    'average' => $count > 0 ? $total / $count : 0,
                ];
            }

            $userIds = $commissions->pluck('user_id')->unique()->values()->all();
            $pvByUser = PVHistory::whereIn('user_id', $userIds)
                ->where('period', $period)
                ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                ->groupBy('user_id')
                ->pluck('total_pv', 'user_id')
                ->toArray();

            $beneficiaires = $commissions->groupBy('user_id')->map(function ($items, $userId) use ($pvByUser) {
                $user = $items->first()->user;
                $parrain = $user?->parrain;

                return [
                    'name' => $user?->name ?? 'N/A',
                    'id' => $userId,
                    'code' => $user?->sponsor_id ?? 'N/A',
                    'parrain_code' => $parrain?->sponsor_id ?? 'N/A',
                    'rank_level' => $user?->rank_level ?? 'N/A',
                    'pv' => (float) ($pvByUser[$userId] ?? 0),
                    'direct' => (float) $items->where('type', 'direct')->sum('amount'),
                    'indirect' => (float) $items->where('type', 'indirect')->sum('amount'),
                    'leadership' => (float) $items->where('type', 'leadership')->sum('amount'),
                    'cash_pos' => (float) $items->where('type', 'cash_pos')->sum('amount'),
                    'total' => (float) $items->sum('amount'),
                ];
            })->sortByDesc('total');

            unset($commissions);

            return response()->view('admin.pv.commission-global-pdf', [
                'period' => $period,
                'commissionsCount' => $commissionsCount,
                'totals' => $totals,
                'typeStats' => $typeStats,
                'beneficiaires' => $beneficiaires,
                'logoBase64' => $this->loadPdfLogoBase64(),
                'date_generated' => now()->format('d/m/Y H:i'),
                'autoPrint' => true,
                'printBackUrl' => route('cashier.offline-bonus.pay', ['period' => $period]),
            ]);
        } catch (\Throwable $e) {
            Log::error('cashier.offline-bonus.exportGlobalPdf', [
                'period' => $period,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lors de la génération du rapport. Réessayez.');
        }
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
        $totals = ['pending' => 0.0, 'paid' => 0.0, 'in_wallet' => 0.0, 'count' => 0];
        $stats = ['all' => 0, 'pending' => 0, 'paid' => 0];

        if ($selectedPeriod) {
            $rows = Commission::query()
                ->with(['user:id,name,sponsor_id,parrain_id', 'user.parrain:id,name,sponsor_id'])
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

            $memberIds = $byMember->pluck('user.id')->filter()->values();

            $inWallet = 0.0;
            $alreadyWithdrawn = 0.0;
            if ($memberIds->isNotEmpty()) {
                // Solde encore disponible dans les portefeuilles de ces membres.
                $inWallet = (float) Wallet::query()
                    ->whereIn('user_id', $memberIds)
                    ->sum('balance');

                // Déjà payé = retraits déjà effectués (complétés) par ces membres sur le mois.
                $periodStart = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
                $periodEnd = $periodStart->copy()->endOfMonth();
                $alreadyWithdrawn = (float) Withdrawal::query()
                    ->whereIn('user_id', $memberIds)
                    ->where('status', 'completed')
                    ->where(function ($q) use ($periodStart, $periodEnd) {
                        $q->whereBetween('completed_at', [$periodStart, $periodEnd])
                            ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                                $q2->whereNull('completed_at')
                                    ->whereBetween('processed_at', [$periodStart, $periodEnd]);
                            });
                    })
                    ->sum('amount');
            }

            $totals = [
                'pending' => (float) $byMember->sum('pending_amount'),
                'paid' => $alreadyWithdrawn,
                'in_wallet' => $inWallet,
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
                        || str_contains(mb_strtolower((string) $user->sponsor_id), $needle)
                        || str_contains(mb_strtolower((string) ($user->parrain?->name ?? '')), $needle)
                        || str_contains(mb_strtolower((string) ($user->parrain?->sponsor_id ?? '')), $needle);
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
     * Détail bonus d'un membre pour une période (actions de paiement).
     */
    public function payShow(Request $request, int $userId)
    {
        $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $period = $request->period;
        $member = User::query()
            ->with('parrain:id,name,sponsor_id')
            ->findOrFail($userId);

        $commissions = Commission::query()
            ->with('fromUser:id,name,sponsor_id')
            ->where('user_id', $member->id)
            ->where('period', $period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->orderByRaw("FIELD(type, 'direct', 'indirect', 'leadership')")
            ->orderBy('generation')
            ->orderBy('id')
            ->get();

        if ($commissions->isEmpty()) {
            return redirect()
                ->route('cashier.offline-bonus.pay', ['period' => $period])
                ->with('info', 'Aucun bonus pour ce membre sur cette période.');
        }

        $pending = $commissions->whereIn('status', ['pending', 'approved']);
        $paid = $commissions->where('status', 'paid');

        $summary = [
            'pending_amount' => (float) $pending->sum('amount'),
            'paid_amount' => (float) $paid->sum('amount'),
            'pending_count' => $pending->count(),
            'paid_count' => $paid->count(),
            'total_amount' => (float) $commissions->sum('amount'),
            'line_count' => $commissions->count(),
        ];

        return view('cashier.offline-bonus.pay-show', compact(
            'member',
            'period',
            'commissions',
            'summary'
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
            return redirect()
                ->route('cashier.offline-bonus.pay.show', ['userId' => $member->id, 'period' => $period])
                ->with('info', 'Aucune commission à payer pour ce membre.');
        }

        return $this->payCommissionCollection(
            $commissions,
            $period,
            $member->name,
            route('cashier.offline-bonus.pay.show', ['userId' => $member->id, 'period' => $period])
        );
    }

    protected function payCommissionCollection(
        $commissions,
        string $period,
        ?string $memberName = null,
        ?string $redirectUrl = null
    ) {
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

            $fail = $redirectUrl ? redirect($redirectUrl) : back();

            return $fail->with('error', 'Erreur paiement : '.$e->getMessage());
        }

        $who = $memberName ? " pour {$memberName}" : '';
        $ok = $redirectUrl ? redirect($redirectUrl) : back();

        return $ok->with(
            'success',
            "{$paid} commission(s) créditées{$who} · $".number_format($total, 2).
            ' — portefeuilles mis à jour / créés. Les montants sont dans l’historique.'
        );
    }
}
