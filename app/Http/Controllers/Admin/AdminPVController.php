<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Rank;
use App\Models\PVHistory;
use App\Models\Transaction;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use App\Jobs\UpdateRanks;
use App\Jobs\UpdateTeamPV;
use App\Jobs\CalculatePVBV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminPVController extends Controller
{
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function index(Request $request)
    {
        $user = null;
        $packages = Package::where('is_active', true)->orderBy('price')->get();
        $pvHistory = null;

        if ($request->filled('user_id')) {
            $user = User::with(['package', 'rank'])->find($request->user_id);
            if ($user) {
                $pvHistory = PVHistory::where('user_id', $user->id)
                    ->orderBy('date', 'desc')
                    ->get();
            }
        }
        elseif ($request->filled('search')) {
            $search = $request->search;
            $results = User::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('sponsor_id', 'like', "%{$search}%")
                ->get();
            
            if ($results->count() == 1) {
                $user = $results->first();
                $pvHistory = PVHistory::where('user_id', $user->id)
                    ->orderBy('date', 'desc')
                    ->get();
            } else {
                return view('admin.pv.search', compact('results', 'search'));
            }
        }

        if (!$user) {
            return redirect()->route('admin.pv.search')
                ->with('error', 'Aucun utilisateur trouve. Veuillez effectuer une recherche.');
        }

        return view('admin.pv.index', compact('user', 'packages', 'pvHistory'));
    }

    public function search(Request $request)
    {
        $results = null;
        $search = $request->get('search');

        if ($search) {
            $results = User::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('sponsor_id', 'like', "%{$search}%")
                ->paginate(20);
        }

        return view('admin.pv.search', compact('results', 'search'));
    }

    public function show($id)
    {
        $user = User::with(['package', 'rank'])->findOrFail($id);
        $packages = Package::where('is_active', true)->orderBy('price')->get();
        $pvHistory = PVHistory::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.pv.index', compact('user', 'packages', 'pvHistory'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pv_balance' => 'required|numeric|min:0',
            'monthly_pv' => 'required|numeric|min:0',
            'team_pv' => 'nullable|numeric|min:0',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            $oldPv = $user->pv_balance;
            $oldPackageId = $user->package_id;
            $oldRankName = $user->rank ?? 'Distributeur';

            $user->pv_balance = (float) $request->pv_balance;
            $user->monthly_pv = (float) $request->monthly_pv;
            
            if ($request->has('team_pv')) {
                $user->team_pv = (float) $request->team_pv;
            }

            $packageChanged = false;
            if ($request->has('package_id') && $request->package_id != $oldPackageId) {
                $packageChanged = true;
                $user->package_id = $request->package_id;
                
                if ($request->package_id) {
                    $package = Package::find($request->package_id);
                    if ($package) {
                        $user->bv_balance = $package->bv_value;
                    }
                } else {
                    $user->bv_balance = 0;
                }
            }

            $user->saveQuietly();

            // Mettre à jour le team_pv du parrain et de tous ses ancêtres
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain && $parrain->is_active) {
                    $this->updateParrainAndAncestorsTeamPV($parrain);
                }
            }

            DB::commit();

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('low');
            dispatch(new UpdateRanks($user->id))->onQueue('low');
            dispatch(new CalculatePVBV($user->id))->onQueue('low');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            $user->refresh();
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $newRankName = $user->rank ?? 'Distributeur';
            $newRankLevel = $user->rank_level ?? 1;
            $newPackageName = $user->package?->name ?? 'Aucun';

            $message = "PV de {$user->name} mis a jour avec succes.\n";
            $message .= "Package: {$newPackageName}\n";
            $message .= "Grade actuel: {$newRankName} (Niv. {$newRankLevel})\n";
            $message .= "PV Total: " . number_format($user->pv_balance, 1, ',', ' ') . "\n";
            
            if ($packageChanged) {
                $message .= "Package change !\n";
            }
            
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $message .= "\nLe parrain {$parrain->name} a recu les PV dans son equipe.";
                    $message .= "\nteam_pv du parrain: " . number_format($parrain->team_pv ?? 0, 1, ',', ' ');
                }
            }
            
            $message .= "\nLes recalculs complets sont en cours en arriere-plan.";

            Log::info('PV mis a jour avec jobs', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'old_pv' => $oldPv,
                'new_pv' => $request->pv_balance,
                'old_package_id' => $oldPackageId,
                'new_package_id' => $user->package_id,
                'old_rank' => $oldRankName,
                'new_rank' => $newRankName,
                'package_changed' => $packageChanged,
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise a jour PV: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function reset(Request $request, $id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            $rank = Rank::where('name', 'Distributeur')->first();
            
            if (!$rank) {
                $rank = Rank::find(1);
                if (!$rank) {
                    throw new \Exception('Aucun rang trouve dans la base de donnees !');
                }
            }

            $oldValues = [
                'pv_balance' => $user->pv_balance,
                'monthly_pv' => $user->monthly_pv,
                'team_pv' => $user->team_pv,
                'bv_balance' => $user->bv_balance,
                'team_bv' => $user->team_bv,
                'rank_id' => $user->rank_id,
                'rank' => $user->rank,
                'rank_level' => $user->rank_level,
                'package_id' => $user->package_id,
            ];

            $user->pv_balance = 0;
            $user->monthly_pv = 0;
            $user->team_pv = 0;
            $user->bv_balance = 0;
            $user->monthly_bv = 0;
            $user->team_bv = 0;
            $user->total_team = 0;
            $user->commission_balance = 0;
            $user->total_earnings = 0;
            $user->total_sponsors = 0;
            $user->qualified_branches = 0;
            $user->direct_sponsors_count = 0;
            
            $user->rank_id = $rank->id;
            $user->rank = $rank->name;
            $user->rank_level = $rank->level ?? 1;
            $user->package_id = null;
            $user->rank_update_queued = 0;
            $user->last_rank_update = now();
            $user->saveQuietly();

            // Mettre à jour le team_pv du parrain et de tous ses ancêtres
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain && $parrain->is_active) {
                    $this->updateParrainAndAncestorsTeamPV($parrain);
                }
            }

            PVHistory::where('user_id', $user->id)->delete();

            DB::commit();

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new CalculatePVBV($user->id))->onQueue('high');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");
            Cache::forget("rank_calculation_{$user->id}");

            Log::info('PV et Rang reinitialises', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'admin_id' => auth()->id(),
                'old_values' => $oldValues,
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', "{$user->name} a ete reinitialise avec succes ! Grade: Distributeur (Niv. 1) Package: Aucun. Les recalculs complets sont en cours en arriere-plan.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur reinitialisation: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function addMonthly(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $amount = (float) $request->amount;

        DB::beginTransaction();
        try {
            $oldPv = $user->pv_balance;

            $user->pv_balance += $amount;
            $user->monthly_pv += $amount;
            $user->bv_balance += $amount;
            $user->monthly_bv += $amount;
            $user->saveQuietly();

            // Mettre à jour le team_pv du parrain et de tous ses ancêtres
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain && $parrain->is_active) {
                    $this->updateParrainAndAncestorsTeamPV($parrain);
                }
            }

            DB::commit();

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('low');
            dispatch(new UpdateRanks($user->id))->onQueue('low');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            $user->refresh();
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $newRankName = $user->rank ?? 'Distributeur';
            $newRankLevel = $user->rank_level ?? 1;

            Log::info('PV mensuel ajoute avec jobs', [
                'user_id' => $user->id,
                'amount' => $amount,
                'admin_id' => auth()->id(),
            ]);

            $message = "{$amount} PV ajoutes a {$user->name}.\n";
            $message .= "Grade actuel: {$newRankName} (Niv. {$newRankLevel})\n";
            $message .= "PV Total: " . number_format($user->pv_balance, 1, ',', ' ') . "\n";
            
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $message .= "\nLe parrain {$parrain->name} a recu {$amount} PV dans son equipe.";
                    $message .= "\nteam_pv du parrain: " . number_format($parrain->team_pv ?? 0, 1, ',', ' ');
                }
            }
            
            $message .= "\nLes recalculs complets sont en cours en arriere-plan.";

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout PV mensuel: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function addHistorical(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'date' => 'required|date|before_or_equal:today',
            'period' => 'required|date_format:Y-m',
            'type' => 'required|in:personal,team,monthly',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $amount = (float) $request->amount;

        DB::beginTransaction();
        try {
            PVHistory::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'date' => $request->date,
                'period' => $request->period,
                'type' => $request->type,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            if ($request->type === 'personal' || $request->type === 'monthly') {
                $user->pv_balance += $amount;
                $user->monthly_pv += $amount;
                $user->bv_balance += $amount;
                $user->monthly_bv += $amount;
            }

            if ($request->type === 'team') {
                $user->team_pv += $amount;
                $user->team_bv += $amount;
            }

            $user->saveQuietly();

            // Mettre à jour le team_pv du parrain et de tous ses ancêtres
            if ($user->parrain_id && ($request->type === 'personal' || $request->type === 'monthly')) {
                $parrain = User::find($user->parrain_id);
                if ($parrain && $parrain->is_active) {
                    $this->updateParrainAndAncestorsTeamPV($parrain);
                }
            }

            DB::commit();

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('low');
            dispatch(new UpdateRanks($user->id))->onQueue('low');
            dispatch(new CalculatePVBV($user->id))->onQueue('low');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            $user->refresh();
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $newRankName = $user->rank ?? 'Distributeur';
            $newRankLevel = $user->rank_level ?? 1;

            $message = "{$amount} PV ajoutes historiquement pour {$user->name} (periode: {$request->period}).\n";
            $message .= "Grade actuel: {$newRankName} (Niv. {$newRankLevel})\n";
            
            if ($user->parrain_id && ($request->type === 'personal' || $request->type === 'monthly')) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $message .= "\nLe parrain {$parrain->name} a recu {$amount} PV dans son equipe.";
                    $message .= "\nteam_pv du parrain: " . number_format($parrain->team_pv ?? 0, 1, ',', ' ');
                }
            }
            
            $message .= "\nLes recalculs complets sont en cours en arriere-plan.";

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout historique PV: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function deleteHistory($historyId)
    {
        $history = PVHistory::findOrFail($historyId);
        $userId = $history->user_id;

        DB::beginTransaction();
        try {
            $user = User::find($userId);
            $amount = $history->amount;
            
            if ($history->type === 'personal' || $history->type === 'monthly') {
                $user->pv_balance -= $amount;
                $user->monthly_pv -= $amount;
                $user->bv_balance -= $amount;
                $user->monthly_bv -= $amount;
            }
            if ($history->type === 'team') {
                $user->team_pv -= $amount;
                $user->team_bv -= $amount;
            }
            $user->saveQuietly();

            // Mettre à jour le team_pv du parrain et de tous ses ancêtres
            if ($user->parrain_id && ($history->type === 'personal' || $history->type === 'monthly')) {
                $parrain = User::find($user->parrain_id);
                if ($parrain && $parrain->is_active) {
                    $this->updateParrainAndAncestorsTeamPV($parrain);
                }
            }

            $history->delete();

            DB::commit();

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('low');
            dispatch(new UpdateRanks($user->id))->onQueue('low');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Historique supprime avec succes',
                'user' => [
                    'pv_balance' => $user->pv_balance,
                    'monthly_pv' => $user->monthly_pv,
                    'team_pv' => $user->team_pv,
                    'rank' => $user->rank ?? 'Distributeur',
                    'rank_level' => $user->rank_level ?? 1,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression historique PV: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recalculateRank(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            $message = "Recalcul du grade de {$user->name} en cours en arriere-plan.\n";
            $message .= "Le resultat sera visible dans quelques instants.";

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur recalcul grade: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = User::with(['package', 'rank']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('pv_balance', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pv_report_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Nom', 'Email', 'Code Sponsor', 'Grade', 'Package',
                'PV Total', 'BV', 'PV Mensuel', 'PV Equipe', 'Cumul PV', 'Statut'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->sponsor_id ?? '',
                    $user->rank ?? 'Distributeur',
                    $user->package?->name ?? 'Aucun',
                    $user->pv_balance ?? 0,
                    $user->bv_balance ?? 0,
                    $user->monthly_pv ?? 0,
                    $user->team_pv ?? 0,
                    ($user->pv_balance ?? 0) + ($user->team_pv ?? 0),
                    $user->is_active ? 'Actif' : 'Inactif',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getRankInfo($id)
    {
        $user = User::findOrFail($id);
        
        $progress = $this->rankCalculator->getProgress($user);
        
        return response()->json([
            'current_rank' => $user->rank ?? 'Distributeur',
            'rank_level' => $user->rank_level ?? 1,
            'pv_balance' => $user->pv_balance ?? 0,
            'team_pv' => $user->team_pv ?? 0,
            'cumul_pv' => ($user->pv_balance ?? 0) + ($user->team_pv ?? 0),
            'next_rank' => $progress['next_rank'] ?? null,
            'next_level' => $progress['next_level'] ?? null,
            'progress_percentage' => $progress['progress_percentage'] ?? 0,
            'pv_needed' => $progress['pv_needed'] ?? 0,
            'progress_pv' => $progress['progress_pv'] ?? 0,
            'total_pv_needed' => $progress['total_pv_needed'] ?? 0,
        ]);
    }

    public function stats()
    {
        $stats = [
            'total_users' => User::where('is_active', 1)->count(),
            'total_pv' => User::where('is_active', 1)->sum('pv_balance'),
            'total_team_pv' => User::where('is_active', 1)->sum('team_pv'),
            'total_monthly_pv' => User::where('is_active', 1)->sum('monthly_pv'),
            'users_with_pv' => User::where('is_active', 1)->where('pv_balance', '>', 0)->count(),
            'users_with_team' => User::where('is_active', 1)->where('team_pv', '>', 0)->count(),
            'rank_distribution' => User::where('is_active', 1)
                ->select('rank', DB::raw('count(*) as count'))
                ->groupBy('rank')
                ->get()
                ->toArray(),
        ];

        return view('admin.pv.stats', compact('stats'));
    }

    public function history($userId = null)
    {
        $query = PVHistory::with(['user', 'creator']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $history = $query->orderBy('date', 'desc')
            ->paginate(50);

        return view('admin.pv.history', compact('history', 'userId'));
    }

    // ============================================================
    // METHODES DE RECALCUL DU TEAM_PV AVEC TOUS LES DESCENDANTS ET ANCETRES
    // ============================================================

    /**
     * Met à jour le team_pv du parrain et de TOUS ses ancêtres
     */
    private function updateParrainAndAncestorsTeamPV(User $parrain): void
    {
        try {
            // Récupérer les IDs de tous les ancêtres en 1 requête SQL
            $ancestorIds = DB::select("
                WITH RECURSIVE ancestors AS (
                    SELECT id, parrain_id, 1 as level
                    FROM users 
                    WHERE id = ?
                    
                    UNION ALL
                    
                    SELECT u.id, u.parrain_id, a.level + 1
                    FROM users u
                    INNER JOIN ancestors a ON u.id = a.parrain_id
                    WHERE u.is_active = true
                )
                SELECT id, level FROM ancestors ORDER BY level DESC
            ", [$parrain->id]);

            if (empty($ancestorIds)) {
                return;
            }

            $ids = array_column($ancestorIds, 'id');
            
            // Mettre à jour le team_pv pour TOUS les ancêtres en 1 requête
            DB::statement("
                UPDATE users u
                SET team_pv = (
                    SELECT COALESCE(SUM(pv_balance + monthly_pv + team_pv), 0)
                    FROM users
                    WHERE parrain_id = u.id
                    AND is_active = true
                ),
                team_bv = (
                    SELECT COALESCE(SUM(bv_balance + monthly_bv + team_bv), 0)
                    FROM users
                    WHERE parrain_id = u.id
                    AND is_active = true
                ),
                total_team = (
                    SELECT COUNT(*)
                    FROM users
                    WHERE parrain_id = u.id
                    AND is_active = true
                )
                WHERE u.id IN (" . implode(',', $ids) . ")
            ");

            // Recalculer les grades
            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user) {
                    $this->rankCalculator->recalculateUserRank($user, 'Mise à jour team_pv');
                }
            }

            Log::info('team_pv mis à jour pour tous les ancêtres (SQL CTE)', [
                'parrain_id' => $parrain->id,
                'ancestors_updated' => count($ids),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour team_pv du parrain et ancetres', [
                'parrain_id' => $parrain->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Met à jour le team_pv d'un seul parrain
     */
    private function updateParrainTeamPV(User $parrain): void
    {
        try {
            $teamData = $this->calculateTeamPVRecursive($parrain);

            $parrain->team_pv = $teamData['pv'];
            $parrain->team_bv = $teamData['bv'];
            $parrain->total_team = $teamData['total'];
            $parrain->saveQuietly();

            $this->rankCalculator->recalculateUserRank($parrain, 'Mise a jour team_pv');

        } catch (\Exception $e) {
            Log::error('Erreur mise a jour team_pv du parrain', [
                'parrain_id' => $parrain->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calcul récursif du team_pv avec TOUS les descendants
     */
    private function calculateTeamPVRecursive(User $user): array
    {
        $totalPV = $user->pv_balance ?? 0;
        $totalBV = $user->bv_balance ?? 0;
        $totalCount = 0;

        $filleuls = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($filleuls as $filleul) {
            $childData = $this->calculateTeamPVRecursive($filleul);
            $totalPV += $childData['pv'];
            $totalBV += $childData['bv'];
            $totalCount += 1 + $childData['total'];
        }

        return [
            'pv' => $totalPV,
            'bv' => $totalBV,
            'total' => $totalCount,
        ];
    }
}