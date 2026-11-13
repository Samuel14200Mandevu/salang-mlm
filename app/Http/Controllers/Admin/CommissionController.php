<?php
// app/Http/Controllers/Admin/CommissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Models\Commission;
use App\Models\User;
use App\Models\PVHistory;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Notifications\CommissionPaidNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommissionController extends Controller
{
    use GeneratesPdfSafely;

    protected $monthlyCommissionService;

    public function __construct(MonthlyCommissionService $monthlyCommissionService)
    {
        $this->monthlyCommissionService = $monthlyCommissionService;
    }

    /**
     * Types affichés dans le tableau admin (vue par membre).
     *
     * @return list<string>
     */
    protected function coreCommissionTypes(): array
    {
        return ['direct', 'indirect', 'leadership', 'cash_pos'];
    }

    /**
     * Applique les filtres communs (recherche, type, statut, période, dates, bandes).
     */
    protected function applyCommissionFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('sponsor_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period') && $request->period !== 'all') {
            $query->where('period', $request->period);
        }

        if ($request->filled('band')) {
            $band = $request->string('band')->lower()->toString();
            $query->whereHas('period', function ($q) use ($band) {
                match ($band) {
                    'a', 'historical' => $q->where('is_historical', true),
                    'b', 'payable' => $q->where('is_historical', false)->where('is_hidden', false),
                    'c', 'hidden' => $q->where('is_hidden', true),
                    default => $q,
                };
            });
        }

        if ($request->boolean('exclude_hidden')) {
            $query->whereHas('period', fn ($q) => $q->where('is_hidden', false));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Liste des commissions agrégées par membre
     */
    public function index(Request $request)
    {
        $coreTypes = $this->coreCommissionTypes();

        $periods = CommissionPeriod::orderBy('period', 'desc')->pluck('period');
        if ($periods->isEmpty()) {
            $periods = Commission::query()
                ->whereIn('type', $coreTypes)
                ->whereNotNull('period')
                ->where('period', '!=', '')
                ->distinct()
                ->orderByDesc('period')
                ->pluck('period');
        }

        // Mois sélectionné par défaut = période la plus récente (URL explicite)
        if (! $request->filled('period') && $periods->isNotEmpty()) {
            return redirect()->route(
                'admin.commissions',
                array_merge($request->query(), ['period' => $periods->first()])
            );
        }

        $selectedPeriod = ($request->filled('period') && $request->period !== 'all')
            ? (string) $request->period
            : null;

        $query = Commission::query()->whereIn('type', $coreTypes);
        $this->applyCommissionFilters($query, $request);

        $members = (clone $query)
            ->select('user_id')
            ->selectRaw("SUM(CASE WHEN type = 'direct' THEN amount ELSE 0 END) as direct")
            ->selectRaw("SUM(CASE WHEN type = 'indirect' THEN amount ELSE 0 END) as indirect")
            ->selectRaw("SUM(CASE WHEN type = 'leadership' THEN amount ELSE 0 END) as leadership")
            ->selectRaw("SUM(CASE WHEN type = 'cash_pos' THEN amount ELSE 0 END) as cash_pos")
            ->selectRaw("SUM(CASE WHEN status IN ('pending', 'approved') THEN amount ELSE 0 END) as pending_total")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as wallet_total")
            ->selectRaw('SUM(amount) as total')
            ->groupBy('user_id')
            ->orderByDesc(DB::raw('SUM(amount)'))
            ->paginate(20)
            ->withQueryString();

        $userIds = $members->getCollection()->pluck('user_id')->filter()->values()->all();

        $users = User::query()
            ->with(['parrain:id,name,sponsor_id'])
            ->whereIn('id', $userIds)
            ->select(['id', 'name', 'sponsor_id', 'parrain_id', 'rank_level', 'rank'])
            ->get()
            ->keyBy('id');

        // PV = uniquement le mois sélectionné (pv_history)
        $pvByUser = [];
        if ($selectedPeriod && $userIds !== []) {
            $pvByUser = PVHistory::query()
                ->whereIn('user_id', $userIds)
                ->where('period', $selectedPeriod)
                ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                ->groupBy('user_id')
                ->pluck('total_pv', 'user_id')
                ->toArray();
        }

        // Payé = retraits complétés (paiement réel), même logique que le KPI
        $paidByUser = [];
        if ($userIds !== []) {
            $paidQuery = Withdrawal::query()
                ->where('status', 'completed')
                ->whereIn('user_id', $userIds);

            if ($selectedPeriod) {
                try {
                    $periodStart = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
                    $periodEnd = $periodStart->copy()->endOfMonth();
                    $paidQuery->where(function ($q) use ($periodStart, $periodEnd) {
                        $q->whereBetween('completed_at', [$periodStart, $periodEnd])
                            ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                                $q2->whereNull('completed_at')
                                    ->whereBetween('processed_at', [$periodStart, $periodEnd]);
                            });
                    });
                } catch (\Throwable) {
                    // période invalide → pas de filtre date
                }
            }

            $paidByUser = $paidQuery
                ->select('user_id', DB::raw('SUM(amount) as total_paid'))
                ->groupBy('user_id')
                ->pluck('total_paid', 'user_id')
                ->toArray();
        }

        $members->setCollection(
            $members->getCollection()->map(function ($row) use ($users, $pvByUser, $paidByUser, $selectedPeriod) {
                $user = $users->get($row->user_id);

                $pendingTotal = (float) ($row->pending_total ?? 0);
                $walletTotal = (float) ($row->wallet_total ?? 0);
                $total = (float) $row->total;
                $paid = (float) ($paidByUser[$row->user_id] ?? 0);
                $reste = max(0, $total - $paid);
                // Couleur Bonus Total : en attente si reste du pending, sinon portefeuille
                $bonusTone = $pendingTotal > 0 ? 'pending' : ($walletTotal > 0 ? 'wallet' : 'neutral');

                return (object) [
                    'user_id' => (int) $row->user_id,
                    'user' => $user,
                    'code' => $user?->sponsor_id ?: '—',
                    'name' => $user?->name ?? 'N/A',
                    'grade' => $user?->rank_level ?? '—',
                    'parrain_code' => $user?->parrain?->sponsor_id
                        ?: ($user?->parrainSponsorCode() ?: '—'),
                    'parrain_name' => $user?->parrain?->name ?: '—',
                    'pv' => $selectedPeriod
                        ? (float) ($pvByUser[$row->user_id] ?? 0)
                        : null,
                    'direct' => (float) $row->direct,
                    'indirect' => (float) $row->indirect,
                    'leadership' => (float) $row->leadership,
                    'cash_pos' => (float) $row->cash_pos,
                    'pending_total' => $pendingTotal,
                    'wallet_total' => $walletTotal,
                    'bonus_tone' => $bonusTone,
                    'total' => $total,
                    'paid' => $paid,
                    'reste' => $reste,
                ];
            })
        );

        // Alias pour la pagination / sous-titre existants
        $commissions = $members;

        $statsBase = Commission::query()->whereIn('type', $coreTypes);
        if ($selectedPeriod) {
            $statsBase->where('period', $selectedPeriod);
        }

        $withdrawnQuery = Withdrawal::query()->where('status', 'completed');
        if ($selectedPeriod) {
            try {
                $periodStart = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
                $periodEnd = $periodStart->copy()->endOfMonth();
                $withdrawnQuery->where(function ($q) use ($periodStart, $periodEnd) {
                    $q->whereBetween('completed_at', [$periodStart, $periodEnd])
                        ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                            $q2->whereNull('completed_at')
                                ->whereBetween('processed_at', [$periodStart, $periodEnd]);
                        });
                });
            } catch (\Throwable) {
                // période invalide → pas de filtre date
            }
        }

        $stats = [
            // Crédité au wallet (status paid)
            'total_wallet' => (clone $statsBase)->where('status', 'paid')->sum('amount'),
            // Retraits complétés (paiement réel)
            'total_paid_out' => (float) $withdrawnQuery->sum('amount'),
            'total_pending' => (clone $statsBase)->where('status', 'pending')->sum('amount'),
            'total_cancelled' => (clone $statsBase)->where('status', 'cancelled')->sum('amount'),
            'total_count' => (clone $statsBase)->count(),
            'member_count' => $members->total(),
            // Compat anciennes clés
            'total_paid' => (clone $statsBase)->where('status', 'paid')->sum('amount'),
        ];

        $types = collect($coreTypes);

        $periodBandCounts = [
            'historical' => CommissionPeriod::where('is_historical', true)->count(),
            'payable' => CommissionPeriod::where('is_historical', false)->where('is_hidden', false)->count(),
            'hidden' => CommissionPeriod::where('is_hidden', true)->count(),
        ];

        return view('admin.commissions.index', compact(
            'commissions',
            'members',
            'stats',
            'types',
            'periods',
            'periodBandCounts',
            'selectedPeriod'
        ));
    }

    /**
     * Détail de toutes les commissions d'un membre (filtres index conservés).
     */
    public function memberShow(Request $request, int $user)
    {
        $coreTypes = $this->coreCommissionTypes();
        $member = User::query()
            ->with(['parrain:id,name,sponsor_id'])
            ->findOrFail($user);

        $query = Commission::query()
            ->with(['fromUser:id,name,sponsor_id,rank_level', 'period'])
            ->where('user_id', $member->id)
            ->whereIn('type', $coreTypes);

        $this->applyCommissionFilters($query, $request);

        $commissions = $query
            ->orderByRaw("CASE type WHEN 'direct' THEN 1 WHEN 'indirect' THEN 2 WHEN 'leadership' THEN 3 WHEN 'cash_pos' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->get();

        if ($commissions->isEmpty()) {
            return redirect()
                ->route('admin.commissions', $request->only(['search', 'type', 'status', 'period', 'band']))
                ->with('info', 'Aucune commission pour ce membre avec ces filtres.');
        }

        // PV du mois sélectionné ; si absent mais une seule période dans les lignes, l’utiliser
        $selectedPeriod = $request->filled('period')
            ? (string) $request->period
            : (
                $commissions->pluck('period')->filter()->unique()->count() === 1
                    ? (string) $commissions->pluck('period')->filter()->first()
                    : null
            );

        $pv = $selectedPeriod
            ? (float) PVHistory::query()
                ->where('user_id', $member->id)
                ->where('period', $selectedPeriod)
                ->sum('amount')
            : null;

        $summary = [
            'direct' => (float) $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => (float) $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => (float) $commissions->where('type', 'leadership')->sum('amount'),
            'cash_pos' => (float) $commissions->where('type', 'cash_pos')->sum('amount'),
            'total' => (float) $commissions->sum('amount'),
            'line_count' => $commissions->count(),
            'pv' => $pv,
            'pv_period' => $selectedPeriod,
            'grade' => $member->rank_level ?? '—',
            'pending_amount' => (float) $commissions->whereIn('status', ['pending', 'approved'])->sum('amount'),
            'paid_amount' => (float) $commissions->where('status', 'paid')->sum('amount'),
        ];

        $backQuery = $request->only(['search', 'type', 'status', 'period', 'band', 'page']);

        return view('admin.commissions.member-show', compact(
            'member',
            'commissions',
            'summary',
            'backQuery'
        ));
    }

    /**
     * PDF détaillé des commissions d’un membre (même gabarit que PV commission-pdf).
     */
    public function exportMemberPDF(Request $request, int $user)
    {
        try {
            $coreTypes = $this->coreCommissionTypes();
            $bonusOrder = $coreTypes;
            $member = User::query()
                ->with(['package', 'parrain:id,name'])
                ->findOrFail($user);

            $period = ($request->filled('period') && $request->period !== 'all')
                ? (string) $request->period
                : 'all';

            $query = Commission::query()
                ->with(['fromUser:id,name,sponsor_id'])
                ->where('user_id', $member->id)
                ->whereIn('type', $coreTypes);

            $this->applyCommissionFilters($query, $request);

            $commissions = $query
                ->orderByRaw("CASE type WHEN 'direct' THEN 1 WHEN 'indirect' THEN 2 WHEN 'leadership' THEN 3 WHEN 'cash_pos' THEN 4 ELSE 5 END")
                ->orderBy('id')
                ->get();

            if ($commissions->isEmpty()) {
                return back()->with('error', 'Aucune commission à exporter pour ce membre.');
            }

            if ($period === 'all') {
                $uniquePeriods = $commissions->pluck('period')->filter()->unique();
                if ($uniquePeriods->count() === 1) {
                    $period = (string) $uniquePeriods->first();
                }
            }

            $totals = [
                'direct' => (float) $commissions->where('type', 'direct')->sum('amount'),
                'indirect' => (float) $commissions->where('type', 'indirect')->sum('amount'),
                'leadership' => (float) $commissions->where('type', 'leadership')->sum('amount'),
                'cash_pos' => (float) $commissions->where('type', 'cash_pos')->sum('amount'),
                'total' => (float) $commissions->sum('amount'),
            ];

            $periodPV = ($period !== 'all')
                ? (float) PVHistory::query()
                    ->where('user_id', $member->id)
                    ->where('period', $period)
                    ->sum('amount')
                : 0;

            $detailsByType = [];
            $totalsByType = [];
            foreach ($bonusOrder as $type) {
                $detailsByType[$type] = [];
                $totalsByType[$type] = 0.0;
            }

            foreach ($commissions as $item) {
                $type = $item->type;
                if (! isset($detailsByType[$type])) {
                    continue;
                }
                $amount = (float) $item->amount;
                $detailsByType[$type][] = [
                    'type' => $type,
                    'from_user_code' => $item->fromUser?->sponsor_id ?? '—',
                    'from_user' => $item->fromUser?->name ?? 'N/A',
                    'amount' => $amount,
                    'percentage' => $item->percentage,
                    'pv_used' => $item->pv_used,
                    'generation' => $item->generation,
                    'description' => Str::limit((string) ($item->description ?? ''), 45),
                ];
                $totalsByType[$type] += $amount;
            }

            $beneficiaires = [[
                'name' => $member->name,
                'id' => $member->id,
                'total' => $totals['total'],
                'details_by_type' => $detailsByType,
                'totals_by_type' => $totalsByType,
            ]];

            $code = $member->sponsor_id ?: $member->id;
            $periodSlug = $period === 'all' ? 'toutes' : $period;

            return $this->downloadPdfView(
                'admin.pv.commission-pdf',
                [
                    'period' => $period,
                    'commissionsCount' => $commissions->count(),
                    'totals' => $totals,
                    'beneficiaires' => $beneficiaires,
                    'user' => $member,
                    'userId' => $member->id,
                    'periodPV' => $periodPV,
                    'logoBase64' => $this->loadPdfLogoBase64(),
                    'date_generated' => now()->format('d/m/Y H:i'),
                    'bonusOrder' => $bonusOrder,
                    'bonusLabels' => [
                        'direct' => 'Direct Bonus',
                        'indirect' => 'Indirect Bonus',
                        'leadership' => 'Leadership Bonus',
                        'cash_pos' => 'Cash POS Bonus',
                    ],
                ],
                "rapport_commissions_{$code}_{$periodSlug}.pdf",
                'a4',
                'portrait',
                300,
                '1024M'
            );
        } catch (\Throwable $e) {
            $this->logPdfFailure('admin.commissions.exportMemberPDF', $e, ['user' => $user]);

            return back()->with('error', 'Erreur lors de la génération du PDF membre.');
        }
    }

    /**
     * Ancienne fiche unitaire → redirige vers le détail membre.
     */
    public function show($id)
    {
        $commission = Commission::query()
            ->select(['id', 'user_id', 'period'])
            ->findOrFail($id);

        return redirect()->route(
            'admin.commissions.member',
            array_filter([
                'user' => $commission->user_id,
                'period' => $commission->period,
            ])
        );
    }

    /**
     * Approuver une commission
     */
    public function approve($id)
    {
        $commission = Commission::findOrFail($id);

        if ($commission->status !== 'pending') {
            return redirect()->route('admin.commissions')
                ->with('error', 'Cette commission ne peut pas être approuvée.');
        }

        DB::beginTransaction();

        try {
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
                    'description' => "Commission {$commission->type} approuvée #{$commission->id}",
                    'metadata' => json_encode([
                        'commission_id' => $commission->id,
                        'admin_id' => auth()->id(),
                    ]),
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Commission approuvée', [
                'commission_id' => $commission->id,
                'user_id' => $commission->user_id,
                'amount' => $commission->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.commissions')
                ->with('success', "Commission #{$id} approuvée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur approbation commission', [
                'commission_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une commission
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $commission = Commission::findOrFail($id);

        if ($commission->status !== 'pending') {
            return redirect()->route('admin.commissions')
                ->with('error', 'Cette commission ne peut pas être rejetée.');
        }

        $commission->status = 'cancelled';
        $commission->notes = $request->reason ?? 'Rejeté par l\'administrateur';
        $commission->save();

        Log::info('Commission rejetée', [
            'commission_id' => $commission->id,
            'user_id' => $commission->user_id,
            'amount' => $commission->amount,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.commissions')
            ->with('success', "Commission #{$id} rejetée.");
    }

    /**
     * Exporter les commissions en CSV
     */
    public function export(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'period']);

        // Exclure les types non souhaités
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
        $query->whereNotIn('type', $excludedTypes);

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
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="commissions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Utilisateur', 'Email', 'De', 'Type', 'Montant',
                'Période', 'Description', 'Statut', 'Payé le', 'Créé le'
            ]);

            $statusLabels = [
                'paid' => 'Payé',
                'pending' => 'En attente',
                'cancelled' => 'Annulé',
            ];

            $typeLabels = [
                'direct' => 'Direct',
                'indirect' => 'Indirect',
                'leadership' => 'Leadership',
                'retail' => 'Retail',
                'global' => 'Global',
                'binary' => 'Binaire',
            ];

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->user->name ?? 'N/A',
                    $c->user->email ?? 'N/A',
                    $c->fromUser->name ?? 'N/A',
                    $typeLabels[$c->type] ?? $c->type,
                    number_format($c->amount, 2),
                    $c->period ?? 'N/A',
                    $c->description ?? 'N/A',
                    $statusLabels[$c->status] ?? $c->status,
                    $c->paid_at ? $c->paid_at->format('d/m/Y H:i') : 'En attente',
                    $c->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter les commissions en PDF (rapport individuel avec filtres)
     */
    public function exportPDF(Request $request)
    {
        try {
            $this->preparePdfExport(300, '1024M');

        $query = Commission::with(['user:id,name,email,sponsor_id', 'fromUser:id,name']);

        // Exclure les types non souhaités
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];
        $query->whereNotIn('type', $excludedTypes);

        // Appliquer les filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

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

        $commissions = $query->orderBy('created_at', 'desc')->get();

        if ($commissions->isEmpty()) {
            return $this->downloadPdfHtml(
                '<h1 style="text-align:center; font-family:DejaVu Sans; margin-top:50px;">Aucune commission trouvée</h1>',
                'commissions_vide.pdf'
            );
        }

        $totals = [
            'direct' => $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => $commissions->where('type', 'leadership')->sum('amount'),
            'retail' => $commissions->where('type', 'retail')->sum('amount'),
            'cash_pos' => $commissions->where('type', 'cash_pos')->sum('amount'),
            'sponsor' => $commissions->where('type', 'sponsor')->sum('amount'),
            'total' => $commissions->sum('amount'),
        ];

        $stats = [
            'total_paid' => $commissions->where('status', 'paid')->sum('amount'),
            'total_pending' => $commissions->where('status', 'pending')->sum('amount'),
            'total_cancelled' => $commissions->where('status', 'cancelled')->sum('amount'),
            'total_count' => $commissions->count(),
        ];

        $periods = $commissions->groupBy('period')->map(function ($items, $period) {
            return [
                'period' => $period,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'items' => $items->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'user' => $item->user?->name ?? 'N/A',
                        'from_user' => $item->fromUser?->name ?? 'Système',
                        'amount' => $item->amount,
                        'percentage' => $item->percentage,
                        'status' => $item->status,
                        'description' => Str::limit((string) ($item->description ?? ''), 45),
                    ];
                }),
            ];
        });

        $data = [
            'commissions' => $commissions,
            'totals' => $totals,
            'stats' => $stats,
            'periods' => $periods,
            'logoBase64' => $this->loadPdfLogoBase64(),
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
                'status' => $request->status,
                'period' => $request->period,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
            'date_generated' => now()->format('d/m/Y H:i'),
        ];

        return $this->downloadPdfView(
            'admin.commissions.commission-pdf',
            $data,
            'rapport_commissions_'.date('Y-m-d').'.pdf',
            'a4',
            'portrait',
            300,
            '1024M'
        );
        } catch (\Throwable $e) {
            $this->logPdfFailure('admin.commissions.exportPDF', $e);

            return back()->with('error', 'Erreur lors de la génération du PDF. Réessayez avec plus de filtres.');
        }
    }

    /**
     * Rapport GLOBAL des commissions — même structure / marges que
     * resources/views/admin/pv/commission-global-pdf.blade.php
     */
    public function exportGlobalPDF(Request $request)
    {
        try {
            $coreTypes = $this->coreCommissionTypes();
            $period = $request->filled('period') ? (string) $request->period : 'all';
            if ($period === '') {
                $period = 'all';
            }

            $query = Commission::query()
                ->with([
                    'user:id,name,sponsor_id,parrain_id,rank_level,pv_balance',
                    'user.parrain:id,sponsor_id',
                ])
                ->whereIn('type', $coreTypes);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('sponsor_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($period !== 'all') {
                $query->where('period', $period);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $commissions = $query
                ->select(['id', 'user_id', 'type', 'amount', 'period'])
                ->orderBy('user_id')
                ->orderBy('type')
                ->get();

            if ($commissions->isEmpty()) {
                return back()->with('error', 'Aucune commission trouvée pour ce rapport.');
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
            foreach ($coreTypes as $type) {
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
            $pvByUser = [];
            if ($period !== 'all' && $userIds !== []) {
                $pvByUser = PVHistory::query()
                    ->whereIn('user_id', $userIds)
                    ->where('period', $period)
                    ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                    ->groupBy('user_id')
                    ->pluck('total_pv', 'user_id')
                    ->toArray();
            }

            $beneficiaires = $commissions->groupBy('user_id')->map(function ($items, $userId) use ($pvByUser, $period) {
                $user = $items->first()->user;
                $parrain = $user?->parrain;

                return [
                    'name' => $user?->name ?? 'N/A',
                    'id' => $userId,
                    'code' => $user?->sponsor_id ?? 'N/A',
                    'parrain_code' => $parrain?->sponsor_id ?? 'N/A',
                    'rank_level' => $user?->rank_level ?? 'N/A',
                    'pv' => $period !== 'all'
                        ? (float) ($pvByUser[$userId] ?? 0)
                        : (float) ($user?->pv_balance ?? 0),
                    'direct' => (float) $items->where('type', 'direct')->sum('amount'),
                    'indirect' => (float) $items->where('type', 'indirect')->sum('amount'),
                    'leadership' => (float) $items->where('type', 'leadership')->sum('amount'),
                    'cash_pos' => (float) $items->where('type', 'cash_pos')->sum('amount'),
                    'total' => (float) $items->sum('amount'),
                ];
            })->sortByDesc('total');

            unset($commissions);

            $backQuery = array_filter(
                $request->only(['search', 'type', 'status', 'period', 'band']),
                fn ($v) => $v !== null && $v !== ''
            );

            return response()->view('admin.pv.commission-global-pdf', [
                'period' => $period,
                'commissionsCount' => $commissionsCount,
                'totals' => $totals,
                'typeStats' => $typeStats,
                'beneficiaires' => $beneficiaires,
                'logoBase64' => $this->loadPdfLogoBase64(),
                'date_generated' => now()->format('d/m/Y H:i'),
                'autoPrint' => true,
                'printBackUrl' => route('admin.commissions', $backQuery),
            ]);
        } catch (\Throwable $e) {
            $this->logPdfFailure('admin.commissions.exportGlobalPDF', $e);

            return back()->with('error', 'Erreur lors de la génération du rapport global. Réessayez avec plus de filtres.');
        }
    }

    /**
     * Statistiques en JSON pour l'API
     */
    public function stats(Request $request)
    {
        $excludedTypes = ['purchase', 'new_client', 'pos_transaction'];

        $stats = [
            'total_pending' => Commission::where('status', 'pending')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'total_cancelled' => Commission::where('status', 'cancelled')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'today' => Commission::whereDate('created_at', today())->whereNotIn('type', $excludedTypes)->sum('amount'),
            'this_month' => Commission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereNotIn('type', $excludedTypes)
                ->sum('amount'),
            'total_count' => Commission::whereNotIn('type', $excludedTypes)->count(),
            'pending_count' => Commission::where('status', 'pending')->whereNotIn('type', $excludedTypes)->count(),
            'paid_count' => Commission::where('status', 'paid')->whereNotIn('type', $excludedTypes)->count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }
}