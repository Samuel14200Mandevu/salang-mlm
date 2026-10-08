<?php
// app/Http/Controllers/Admin/AdminCommissionHistoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PVHistory;
use App\Models\CommissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminCommissionHistoryController extends Controller
{
    /**
     * Nombre maximum de générations pour le bonus INDIRECT.
     */
    const MAX_GENERATIONS_INDIRECT = 8;

    /**
     * Nombre maximum de générations pour le bonus LEADERSHIP.
     */
    const MAX_GENERATIONS_LEADERSHIP = 12;

    /**
     * Nombre maximum absolu de générations parcourues dans l'arbre.
     * Doit être égal au maximum entre INDIRECT et LEADERSHIP.
     */
    const MAX_TREE_GENERATIONS = 12;

    /**
     * Afficher l'historique des commissions
     */
    public function index(Request $request)
    {
        $periods = CommissionHistory::select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->get();

        $selectedPeriod = $request->input('period');
        $userId = $request->input('user_id');
        $memberQ = trim((string) $request->input('member_q', ''));

        $commissions = collect();
        $totals = [
            'direct' => 0,
            'indirect' => 0,
            'leadership' => 0,
            'cash_pos' => 0,
            'total' => 0,
        ];

        $query = CommissionHistory::with(['user', 'fromUser']);

        if ($selectedPeriod) {
            $query->where('period', $selectedPeriod);
        }

        if ($memberQ !== '') {
            $like = '%' . $memberQ . '%';
            $query->whereHas('user', function ($userQuery) use ($like) {
                $userQuery->where('name', 'like', $like)
                    ->orWhere('sponsor_id', 'like', $like);
            });
        } elseif ($userId) {
            $query->where('user_id', $userId);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $totals['direct'] = $commissions->where('type', 'direct')->sum('amount');
        $totals['indirect'] = $commissions->where('type', 'indirect')->sum('amount');
        $totals['leadership'] = $commissions->where('type', 'leadership')->sum('amount');
        $totals['cash_pos'] = $commissions->where('type', 'cash_pos')->sum('amount');
        $totals['total'] = $commissions->sum('amount');

        $prefillMemberQ = $memberQ;
        if ($prefillMemberQ === '' && $userId) {
            $filteredUser = User::find($userId);
            if ($filteredUser) {
                $prefillMemberQ = $filteredUser->sponsor_id ?: $filteredUser->name;
            }
        }

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.pv.commission-history', compact(
            'periods',
            'selectedPeriod',
            'userId',
            'memberQ',
            'prefillMemberQ',
            'commissions',
            'totals',
            'users',
        ));
    }

    /**
     * Recalculer les commissions à partir des PV historiques
     * ⚠️ OPÉRATION UNIQUE - À n'utiliser qu'en cas d'urgence
     */
    public function recalculate(Request $request)
    {
        $period = $request->input('period');
        $userId = $request->input('user_id');

        set_time_limit(1800);
        ini_set('memory_limit', '2048M');

        $query = PVHistory::with(['user']);

        if ($period) {
            $query->where('period', $period);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $pvHistories = $query->orderBy('period', 'desc')->get();

        if ($pvHistories->isEmpty()) {
            return back()->with('warning', 'Aucun PV historique trouvé.');
        }

        $totalProcessed = 0;
        $totalCommissions = 0;
        $totalCreated = 0;

        DB::beginTransaction();

        try {
            $deleteQuery = CommissionHistory::query();
            if ($period) {
                $deleteQuery->where('period', $period);
            }
            if ($userId) {
                $deleteQuery->where('user_id', $userId);
            }
            $deleteQuery->delete();

            $allUsers = User::where('is_active', true)->get()->keyBy('id');

            $pvByUserAndPeriod = [];
            foreach ($pvHistories as $pvHistory) {
                $key = $pvHistory->user_id . '_' . $pvHistory->period;
                if (!isset($pvByUserAndPeriod[$key])) {
                    $pvByUserAndPeriod[$key] = [
                        'user' => $allUsers->get($pvHistory->user_id),
                        'period' => $pvHistory->period,
                        'total_pv' => 0,
                    ];
                }
                $pvByUserAndPeriod[$key]['total_pv'] += $pvHistory->amount;
            }

            $pvByUserAndPeriod = array_filter($pvByUserAndPeriod, function($data) {
                return $data['user'] !== null;
            });

            $chunks = array_chunk($pvByUserAndPeriod, 10);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $data) {
                    $user = $data['user'];
                    $periodValue = $data['period'];
                    $totalPv = $data['total_pv'];

                    if (!$user) {
                        continue;
                    }

                    $totalProcessed++;

                    $commissions = $this->calculateCommissionsForUserAndPeriodOptimized(
                        $user,
                        $periodValue,
                        $totalPv,
                        $allUsers
                    );

                    foreach ($commissions as $commission) {
                        CommissionHistory::create($commission);
                        $totalCreated++;
                        $totalCommissions += $commission['amount'];
                    }
                }
            }

            DB::commit();

            $message = "Recalcul terminé !\n";
            $message .= "PV traités: {$totalProcessed}\n";
            $message .= "Commissions créées: {$totalCreated}\n";
            $message .= "Montant total: $" . number_format($totalCommissions, 2);

            if ($period) {
                $message .= "\nPériode: {$period}";
            }
            if ($userId) {
                $user = User::find($userId);
                $message .= "\nUtilisateur: {$user->name}";
            }

            return redirect()->route('admin.pv.commission-history.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur recalcul historique: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Calculer les commissions pour un user et une période
     *
     *  Bonus direct avec from_user_id = user_id et generation = 0
     *  Utilise $userMonthlyPV (calculé depuis pv_history)
     *  Suppression du check $existingDirectBonus
     *  Règles alignées avec CommissionDistributor
     *
     *  INDIRECT   : générations 1 à 8
     *  LEADERSHIP : générations 1 à 12
     */
    private function calculateCommissionsForUserAndPeriodOptimized($user, $period, $userMonthlyPV, $allUsers)
    {
        $commissions = [];
        $requirements = $this->getMonthlyPVRequirements();
        $userRank = $user->rank_level ?? 1;
        $userRate = $this->getDirectRate($userRank);

        // ════════════════════════════════════════════════════════════
        // 1. BONUS DIRECT - Génération 0
        // ════════════════════════════════════════════════════════════

        $req = $requirements[$userRank] ?? ['personal' => 0, 'group' => 0];

        $pvOk = true;

        if ($userMonthlyPV < $req['personal']) {
            $pvOk = false;
        }

        if ($pvOk && $req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) {
            $pvOk = false;
        }

        if ($pvOk && $userRank >= 3 && $userRate > 0 && $userMonthlyPV > 0) {
            $directAmount = $userMonthlyPV * ($userRate / 100);

            if ($directAmount > 0) {
                $commissions[] = [
                    'user_id' => $user->id,
                    'from_user_id' => $user->id,
                    'period' => $period,
                    'type' => 'direct',
                    'amount' => $directAmount,
                    'percentage' => $userRate,
                    'pv_used' => $userMonthlyPV,
                    'generation' => 0,
                    'status' => 'pending',
                    'description' => "Bonus Direct Mensuel - {$userRate}% sur PV mensuel de {$userMonthlyPV} PV pour {$period}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // ════════════════════════════════════════════════════════════
        // 2. INDIRECT (1-8) + LEADERSHIP (1-12)
        // On parcourt jusqu'à MAX_TREE_GENERATIONS (12) pour couvrir
        // les deux cas, puis on filtre par type.
        // ════════════════════════════════════════════════════════════

        $descendantsData = $this->getAllDescendantsWithAncestors($user, self::MAX_TREE_GENERATIONS);

        if (empty($descendantsData)) {
            return $commissions;
        }

        $descendantIds = array_column($descendantsData, 'descendant_id');

        $descendantPVs = PVHistory::whereIn('user_id', $descendantIds)
            ->where('period', $period)
            ->select('user_id', DB::raw('SUM(amount) as total_pv'))
            ->groupBy('user_id')
            ->pluck('total_pv', 'user_id')
            ->toArray();

        $pvOkForIndirect = true;
        if ($userMonthlyPV < $req['personal']) {
            $pvOkForIndirect = false;
        }
        if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) {
            $pvOkForIndirect = false;
        }

        foreach ($descendantsData as $data) {
            $generation = $data['generation'];
            $descendantId = $data['descendant_id'];
            $descendantRank = $data['descendant_rank'];
            $isExcluded = $data['is_excluded'];

            if ($isExcluded) {
                continue;
            }

            $descendantPV = $descendantPVs[$descendantId] ?? 0;

            if ($descendantPV <= 0) {
                continue;
            }

            $descendantRate = $this->getDirectRate($descendantRank);

            // ─────────────────────────────────────────────
            // INDIRECT — uniquement jusqu'à 8 générations
            // ─────────────────────────────────────────────
            if (
                $generation <= self::MAX_GENERATIONS_INDIRECT
                && $pvOkForIndirect
                && $userRank >= 3
                && $descendantRank >= 3
                && $userRank > $descendantRank
            ) {
                $rateDifference = max(0, $userRate - $descendantRate);

                if ($rateDifference > 0) {
                    $indirectAmount = $descendantPV * ($rateDifference / 100);

                    if ($indirectAmount > 0) {
                        $descendant = $allUsers->get($descendantId);
                        $commissions[] = [
                            'user_id' => $user->id,
                            'from_user_id' => $descendantId,
                            'period' => $period,
                            'type' => 'indirect',
                            'amount' => $indirectAmount,
                            'percentage' => $rateDifference,
                            'pv_used' => $descendantPV,
                            'generation' => $generation,
                            'status' => 'pending',
                            'description' => "Bonus Indirect Génération {$generation} ({$rateDifference}% sur {$descendantPV} PV pour {$period}) - " . ($descendant?->name ?? 'N/A'),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // ─────────────────────────────────────────────
            // LEADERSHIP — jusqu'à 12 générations
            // ─────────────────────────────────────────────
            if (
                $generation <= self::MAX_GENERATIONS_LEADERSHIP
                && $pvOkForIndirect
                && $userRank >= 5
            ) {
                $leadershipRate = $this->getLeadershipRate($userRank);

                if ($leadershipRate > 0) {
                    $leadershipAmount = $descendantPV * ($leadershipRate / 100);

                    if ($leadershipAmount > 0) {
                        $descendant = $allUsers->get($descendantId);
                        $commissions[] = [
                            'user_id' => $user->id,
                            'from_user_id' => $descendantId,
                            'period' => $period,
                            'type' => 'leadership',
                            'amount' => $leadershipAmount,
                            'percentage' => $leadershipRate,
                            'pv_used' => $descendantPV,
                            'generation' => $generation,
                            'status' => 'pending',
                            'description' => "Leadership Bonus Génération {$generation} ({$leadershipRate}% sur {$descendantPV} PV pour {$period}) - " . ($descendant?->name ?? 'N/A'),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        return $commissions;
    }

    /**
     * Récupérer tous les descendants avec leurs ancêtres pour l'exclusion.
     *
     * @param User $user
     * @param int $maxGenerations Nombre maximum de générations (12 par défaut)
     */
    private function getAllDescendantsWithAncestors($user, $maxGenerations = 12)
    {
        $descendants = [];
        $currentGeneration = 1;
        $userRank = $user->rank_level ?? 1;

        $currentLevel = [
            [
                'id' => $user->id,
                'rank' => $userRank,
                'is_excluded' => false
            ]
        ];
        $processedIds = [$user->id];

        while ($currentGeneration <= $maxGenerations && !empty($currentLevel)) {
            $nextLevel = [];

            $currentIds = array_column($currentLevel, 'id');

            $children = User::whereIn('parrain_id', $currentIds)
                ->where('is_active', true)
                ->get();

            foreach ($children as $child) {
                if (in_array($child->id, $processedIds)) {
                    continue;
                }

                $parent = null;
                foreach ($currentLevel as $p) {
                    if ($p['id'] == $child->parrain_id) {
                        $parent = $p;
                        break;
                    }
                }

                if (!$parent) {
                    continue;
                }

                $childRank = $child->rank_level ?? 1;

                $isExcluded = $parent['is_excluded'];

                if (!$isExcluded && $childRank >= $userRank) {
                    $isExcluded = true;
                }

                $descendants[] = [
                    'generation' => $currentGeneration,
                    'descendant_id' => $child->id,
                    'descendant_rank' => $childRank,
                    'is_excluded' => $isExcluded,
                ];

                $nextLevel[] = [
                    'id' => $child->id,
                    'rank' => $childRank,
                    'is_excluded' => $isExcluded,
                ];

                $processedIds[] = $child->id;
            }

            $currentLevel = $nextLevel;
            $currentGeneration++;
        }

        return $descendants;
    }

    /**
     * Taux de commission directe en fonction du grade
     */
    private function getDirectRate($rankLevel)
    {
        $rates = [
            1 => 0,
            2 => 0,
            3 => 22,
            4 => 26,
            5 => 30,
            6 => 34,
            7 => 40,
            8 => 43,
            9 => 45,
        ];
        return $rates[$rankLevel] ?? 0;
    }

    /**
     * Taux de leadership en fonction du grade
     */
    private function getLeadershipRate($rankLevel)
    {
        $rates = [
            5 => 0.5,
            6 => 1.1,
            7 => 1.8,
            8 => 2.6,
            9 => 3.5,
        ];
        return $rates[$rankLevel] ?? 0;
    }

    /**
     * Conditions de PV mensuel pour toucher les commissions
     * ALIGNÉ avec CommissionDistributor
     */
    private function getMonthlyPVRequirements(): array
    {
        return [
            1 => ['personal' => 0, 'group' => 0],
            2 => ['personal' => 10, 'group' => 0],
            3 => ['personal' => 20, 'group' => 0],
            4 => ['personal' => 25, 'group' => 0],
            5 => ['personal' => 30, 'group' => 500],
            6 => ['personal' => 50, 'group' => 1000],
            7 => ['personal' => 100, 'group' => 2000],
            8 => ['personal' => 180, 'group' => 3000],
            9 => ['personal' => 300, 'group' => 5000],
        ];
    }


    /**
     * Voir les détails par période
     */
    public function details($period)
    {
        $commissions = CommissionHistory::with(['user', 'fromUser'])
            ->where('period', $period)
            ->orderBy('created_at', 'desc')
            ->get();

        $totals = [
            'direct' => $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => $commissions->where('type', 'leadership')->sum('amount'),
            'cash_pos' => $commissions->where('type', 'cash_pos')->sum('amount'),
            'total' => $commissions->sum('amount'),
        ];

        return view('admin.pv.commission-details', compact('period', 'commissions', 'totals'));
    }

    /**
     * Exporter le rapport en PDF pour une période
     */
    public function exportPDF(Request $request, $period)
    {
        $userId = $request->input('user_id');

        $query = CommissionHistory::with(['user', 'fromUser'])
            ->where('period', $period);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        if ($commissions->isEmpty()) {
            $pdf = Pdf::loadHTML('<h1 style="text-align:center; font-family:Arial;">Aucune commission trouvée pour la période ' . $period . '</h1>');
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("rapport_commissions_{$period}.pdf");
        }

        $totals = [
            'direct' => 0,
            'indirect' => 0,
            'leadership' => 0,
            'cash_pos' => 0,
            'total' => 0,
        ];

        foreach ($commissions as $commission) {
            if (isset($totals[$commission->type])) {
                $totals[$commission->type] += $commission->amount;
            }
            $totals['total'] += $commission->amount;
        }

        $user = null;
        $periodPV = 0;
        if ($userId) {
            $user = User::find($userId);
            $periodPV = PVHistory::where('user_id', $userId)
                ->where('period', $period)
                ->sum('amount');
        }

        $beneficiaires = $commissions->groupBy('user_id')->map(function($items, $userId) {
            $user = $items->first()->user;
            return [
                'name' => $user?->name ?? 'N/A',
                'id' => $userId,
                'total' => $items->sum('amount'),
                'details' => $items->map(function($item) {
                    return [
                        'type' => $item->type,
                        'from_user' => $item->fromUser?->name ?? 'N/A',
                        'amount' => $item->amount,
                        'percentage' => $item->percentage,
                        'pv_used' => $item->pv_used,
                        'generation' => $item->generation,
                        'description' => $item->description,
                    ];
                })
            ];
        });

        $logoBase64 = '';
        $logoPath = public_path('images/salang_logo.png');
        if (file_exists($logoPath) && filesize($logoPath) < 100000) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $data = [
            'period' => $period,
            'commissions' => $commissions,
            'totals' => $totals,
            'beneficiaires' => $beneficiaires,
            'user' => $user,
            'userId' => $userId,
            'periodPV' => $periodPV,
            'logoBase64' => $logoBase64,
            'date_generated' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('admin.pv.commission-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'Times New Roman',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->stream("rapport_commissions_{$period}.pdf");
    }

    /**
     * Exporter le rapport GLOBAL des commissions en PDF
     */
    public function exportGlobalPDF(Request $request, $period = 'all')
    {
        $query = CommissionHistory::with(['user', 'fromUser']);

        if ($period !== 'all') {
            $query->where('period', $period);
        }

        $commissions = $query->orderBy('user_id')
            ->orderBy('type')
            ->get();

        if ($commissions->isEmpty()) {
            $pdf = Pdf::loadHTML('<h1 style="text-align:center; font-family:Arial;">Aucune commission trouvée pour la période ' . $period . '</h1>');
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("rapport_global_commissions_{$period}.pdf");
        }

        $totals = [
            'direct' => $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => $commissions->where('type', 'leadership')->sum('amount'),
            'cash_pos' => $commissions->where('type', 'cash_pos')->sum('amount'),
            'total' => $commissions->sum('amount'),
        ];

        $userIds = $commissions->pluck('user_id')->unique()->toArray();

        $pvByUser = [];
        if ($period !== 'all') {
            $pvByUser = PVHistory::whereIn('user_id', $userIds)
                ->where('period', $period)
                ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                ->groupBy('user_id')
                ->pluck('total_pv', 'user_id')
                ->toArray();
        }

        $beneficiaires = $commissions->groupBy('user_id')->map(function($items, $userId) use ($pvByUser, $period) {
            $user = $items->first()->user;
            $parrain = $user?->parrain;

            if ($period !== 'all') {
                $userPV = $pvByUser[$userId] ?? 0;
            } else {
                $userPV = $user?->pv_balance ?? 0;
            }

            return [
                'name' => $user?->name ?? 'N/A',
                'id' => $userId,
                'code' => $user?->sponsor_id ?? 'N/A',
                'parrain_code' => $parrain?->sponsor_id ?? 'N/A',
                'rank_level' => $user?->rank_level ?? 'N/A',
                'pv' => $userPV,
                'total' => $items->sum('amount'),
                'details' => $items->map(function($item) {
                    return [
                        'type' => $item->type,
                        'from_user' => $item->fromUser?->name ?? 'N/A',
                        'amount' => $item->amount,
                        'percentage' => $item->percentage,
                        'pv_used' => $item->pv_used,
                        'generation' => $item->generation,
                        'description' => $item->description,
                    ];
                })->toArray(),
            ];
        });

        $beneficiaires = $beneficiaires->sortByDesc('total');

        $logoBase64 = '';
        $logoPath = public_path('images/salang_logo.png');
        if (file_exists($logoPath) && filesize($logoPath) < 100000) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $data = [
            'period' => $period,
            'commissions' => $commissions,
            'totals' => $totals,
            'beneficiaires' => $beneficiaires,
            'logoBase64' => $logoBase64,
            'date_generated' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('admin.pv.commission-global-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'Times New Roman',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->stream("rapport_global_commissions_{$period}.pdf");
    }
}