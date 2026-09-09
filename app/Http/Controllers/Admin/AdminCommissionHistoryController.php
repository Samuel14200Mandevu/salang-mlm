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
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->get();
        
        $totals['direct'] = $commissions->where('type', 'direct')->sum('amount');
        $totals['indirect'] = $commissions->where('type', 'indirect')->sum('amount');
        $totals['leadership'] = $commissions->where('type', 'leadership')->sum('amount');
        $totals['cash_pos'] = $commissions->where('type', 'cash_pos')->sum('amount');
        $totals['total'] = $commissions->sum('amount');
        
        $users = User::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.pv.commission-history', compact(
            'periods',
            'selectedPeriod',
            'userId',
            'commissions',
            'totals',
            'users'
        ));
    }

    /**
     * Recalculer les commissions à partir des PV historiques
     */
    public function recalculate(Request $request)
    {
        $period = $request->input('period');
        $userId = $request->input('user_id');
        
        set_time_limit(300);
        
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
     * Calculer les commissions - Version optimisée avec exclusion à TOUTES les générations
     * 
     * RÈGLES :
     * - Direct : PV personnel × Taux (Génération 0)
     * - Indirect : PV descendant × Différence de taux (Générations 1 à 7)
     *   -> Le descendant doit avoir un grade >= 3
     *   -> Le bénéficiaire doit avoir un grade > descendant
     * - Leadership : PV descendant × Taux leadership (Générations 1 à 7)
     * 
     * EXCLUSION : Si à N'IMPORTE QUELLE génération un descendant a un grade 
     * supérieur ou égal au bénéficiaire, toute sa branche est exclue.
     */
    private function calculateCommissionsForUserAndPeriodOptimized($user, $period, $userMonthlyPV, $allUsers)
    {
        $commissions = [];
        $requirements = $this->getMonthlyPVRequirements();
        $userRank = $user->rank_level ?? 1;
        $userRate = $this->getDirectRate($userRank);
        
        // ============================================================
        // 1. BONUS DIRECT - Génération 0
        // ============================================================
        
        $existingDirectBonus = CommissionHistory::where('user_id', $user->id)
            ->where('period', $period)
            ->where('type', 'direct')
            ->where('generation', 0)
            ->exists();
        
        if (!$existingDirectBonus) {
            $req = $requirements[$userRank] ?? ['personal' => 0, 'group' => 0];
            
            $pvOk = true;
            if (($user->monthly_pv ?? 0) < $req['personal']) {
                $pvOk = false;
            }
            if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) {
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
                        'description' => "Bonus Direct Mensuel - {$userRate}% sur PV total de {$userMonthlyPV} PV pour {$period}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // ============================================================
        // 2. Récupérer les descendants AVEC leurs ancêtres pour l'exclusion
        // ============================================================
        
        $descendantsData = $this->getAllDescendantsWithAncestors($user, 7);
        
        if (empty($descendantsData)) {
            return $commissions;
        }
        
        // Récupérer les IDs des descendants
        $descendantIds = array_column($descendantsData, 'descendant_id');
        
        // Récupérer les PV des descendants en une seule requête
        $descendantPVs = PVHistory::whereIn('user_id', $descendantIds)
            ->where('period', $period)
            ->select('user_id', DB::raw('SUM(amount) as total_pv'))
            ->groupBy('user_id')
            ->pluck('total_pv', 'user_id')
            ->toArray();
        
        // Vérifier les conditions de PV pour le bénéficiaire
        $req = $requirements[$userRank] ?? ['personal' => 0, 'group' => 0];
        $pvOk = true;
        if (($user->monthly_pv ?? 0) < $req['personal']) {
            $pvOk = false;
        }
        if ($req['group'] > 0 && ($user->team_pv ?? 0) < $req['group']) {
            $pvOk = false;
        }
        
        foreach ($descendantsData as $data) {
            $generation = $data['generation'];
            $descendantId = $data['descendant_id'];
            $descendantRank = $data['descendant_rank'];
            $isExcluded = $data['is_excluded'];
            
            // Si la branche est exclue, passer
            if ($isExcluded) {
                continue;
            }
            
            // Vérifier si le descendant a des PV
            $descendantPV = $descendantPVs[$descendantId] ?? 0;
            
            if ($descendantPV <= 0) {
                continue;
            }
            
            $descendantRate = $this->getDirectRate($descendantRank);
            
            // ============================================================
            // INDIRECT - Générations 1 à 7
            // RÈGLES : 
            // - Bénéficiaire grade >= 3
            // - Descendant grade >= 3
            // - Bénéficiaire grade > Descendant grade
            // ============================================================
            if ($pvOk && $userRank >= 3 && $descendantRank >= 3 && $userRank > $descendantRank) {
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
                            'description' => "Bonus Indirect Génération {$generation} ({$rateDifference}% sur {$descendantPV} PV pour {$period}) - {$descendant->name}",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            
            // ============================================================
            // LEADERSHIP - Générations 1 à 7 (TOUJOURS, même si grade supérieur)
            // ============================================================
            if ($pvOk && $userRank >= 5) {
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
                            'description' => "Leadership Bonus Génération {$generation} ({$leadershipRate}% sur {$descendantPV} PV pour {$period}) - {$descendant->name}",
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
     * Récupérer tous les descendants avec leurs ancêtres pour l'exclusion
     * 
     * RÈGLE D'EXCLUSION : 
     * Si à N'IMPORTE QUELLE génération un descendant a un grade supérieur ou égal
     * au bénéficiaire, toute sa branche est exclue des commissions indirectes.
     */
    private function getAllDescendantsWithAncestors($user, $maxGenerations = 7)
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
            
            // Récupérer les IDs du niveau actuel
            $currentIds = array_column($currentLevel, 'id');
            
            $children = User::whereIn('parrain_id', $currentIds)
                ->where('is_active', true)
                ->get();
            
            foreach ($children as $child) {
                if (in_array($child->id, $processedIds)) {
                    continue;
                }
                
                // Trouver le parent dans le niveau actuel
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
                
                // ============================================================
                // RÈGLE D'EXCLUSION : À TOUTES LES GÉNÉRATIONS
                // Si le descendant a un grade >= au bénéficiaire, la branche est exclue
                // ============================================================
                $isExcluded = $parent['is_excluded'];
                
                // Si le parent est déjà exclu, l'enfant l'est aussi
                if ($isExcluded) {
                    // déjà exclu
                }
                // Sinon, vérifier si ce descendant a un grade >= au bénéficiaire
                else if ($childRank >= $userRank) {
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
     */
    private function getMonthlyPVRequirements(): array
    {
        return [
            1 => ['personal' => 0, 'group' => 0],
            2 => ['personal' => 10, 'group' => 0],
            3 => ['personal' => 30, 'group' => 0],
            4 => ['personal' => 50, 'group' => 0],
            5 => ['personal' => 60, 'group' => 500],
            6 => ['personal' => 75, 'group' => 1000],
            7 => ['personal' => 100, 'group' => 1000],
            8 => ['personal' => 200, 'group' => 2000],
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
}