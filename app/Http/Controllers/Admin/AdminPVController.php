<?php
// app/Http/Controllers/Admin/AdminPVController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Rank;
use App\Models\PVHistory;
use App\Models\Transaction;
use App\Jobs\UpdateRanks;
use App\Jobs\UpdateTeamPV;
use App\Jobs\CalculatePVBV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPVController extends Controller
{
    /**
     * Afficher la gestion des PV pour un utilisateur spécifique
     */
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
                ->with('error', 'Aucun utilisateur trouvé. Veuillez effectuer une recherche.');
        }

        return view('admin.pv.index', compact('user', 'packages', 'pvHistory'));
    }

    /**
     * Page de recherche d'utilisateur
     */
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

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show($id)
    {
        $user = User::with(['package', 'rank'])->findOrFail($id);
        $packages = Package::where('is_active', true)->orderBy('price')->get();
        $pvHistory = PVHistory::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.pv.index', compact('user', 'packages', 'pvHistory'));
    }

    /**
     * Mettre à jour les PV d'un utilisateur (OPTIMISÉ)
     */
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
            $oldMonthlyPv = $user->monthly_pv;
            $oldTeamPv = $user->team_pv;
            $oldPackageId = $user->package_id;

            $user->pv_balance = (float) $request->pv_balance;
            $user->monthly_pv = (float) $request->monthly_pv;
            
            if ($request->has('team_pv')) {
                $user->team_pv = (float) $request->team_pv;
            }

            if ($request->package_id) {
                $user->package_id = $request->package_id;
                $package = Package::find($request->package_id);
                if ($package) {
                    $user->bv_balance = $package->bv_value;
                }
            } else {
                $user->package_id = null;
            }

            $user->saveQuietly();

            // Utiliser vos jobs existants
            $user->updateTeamPVOptimized();
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new CalculatePVBV($user->id))->onQueue('high');

            DB::commit();

            Log::info('PV mis à jour', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'old_pv' => $oldPv,
                'new_pv' => $request->pv_balance,
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', " PV de {$user->name} mis à jour avec succès. Les recalculs sont en cours en arrière-plan.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour PV: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * RÉINITIALISATION COMPLÈTE - PV à 0 et Rang Distributeur
     */
    public function reset(Request $request, $id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. Trouver le rang "Distributeur"
            $rank = Rank::where('name', 'Distributeur')->first();
            
            if (!$rank) {
                // Si le rang n'existe pas, prendre le premier rang (id = 1)
                $rank = Rank::find(1);
                if (!$rank) {
                    throw new \Exception('Aucun rang trouvé dans la base de données !');
                }
            }

            // 2. Sauvegarder les anciennes valeurs pour le log
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

            // 3. Réinitialiser TOUS les PV à 0
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
            
            // 4. Réinitialiser le rang à "Distributeur"
            $user->rank_id = $rank->id;
            $user->rank = $rank->name;
            $user->rank_level = $rank->level ?? 1;
            
            // 5. Réinitialiser le package
            $user->package_id = null;
            
            // 6. Marquer comme non mis à jour
            $user->rank_update_queued = 0;
            $user->last_rank_update = now();
            
            // 7. Sauvegarder sans déclencher d'événements
            $user->saveQuietly();

            // 8. Supprimer l'historique des PV de cet utilisateur
            PVHistory::where('user_id', $user->id)->delete();

            // 9. Dispatcher les jobs pour recalculer les ancêtres
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            
            // 10. Mettre à jour les ancêtres
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }

            DB::commit();

            Log::info('PV et Rang réinitialisés', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'admin_id' => auth()->id(),
                'old_values' => $oldValues,
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', " {$user->name} a été réinitialisé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur réinitialisation: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Ajouter des PV mensuels (OPTIMISÉ)
     */
    public function addMonthly(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            $oldPv = $user->pv_balance;
            $oldMonthlyPv = $user->monthly_pv;

            $user->pv_balance += (float) $request->amount;
            $user->monthly_pv += (float) $request->amount;
            $user->saveQuietly();

            $user->updateTeamPVOptimized();
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');

            DB::commit();

            Log::info('PV mensuel ajouté', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', " {$request->amount} PV ajoutés à {$user->name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJOUTER DES PV HISTORIQUES (OPTIMISÉ)
     */
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

        DB::beginTransaction();
        try {
            $history = PVHistory::create([
                'user_id' => $user->id,
                'amount' => (float) $request->amount,
                'date' => $request->date,
                'period' => $request->period,
                'type' => $request->type,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            if ($request->type === 'personal' || $request->type === 'monthly') {
                $user->pv_balance += (float) $request->amount;
                $user->monthly_pv += (float) $request->amount;
            }

            if ($request->type === 'team') {
                $user->team_pv += (float) $request->amount;
            }

            $user->saveQuietly();

            $user->updateTeamPVOptimized();
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new CalculatePVBV($user->id))->onQueue('high');

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'pv_historical',
                'amount' => (float) $request->amount,
                'description' => "Ajout historique de {$request->amount} PV pour la période {$request->period}",
                'source_type' => 'admin',
                'source_id' => auth()->id(),
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', " {$request->amount} PV ajoutés historiquement pour {$user->name} (période: {$request->period})");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout historique PV: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * SUPPRIMER UN HISTORIQUE PV (OPTIMISÉ)
     */
    public function deleteHistory($historyId)
    {
        $history = PVHistory::findOrFail($historyId);
        $userId = $history->user_id;

        DB::beginTransaction();
        try {
            $user = User::find($userId);
            
            if ($history->type === 'personal' || $history->type === 'monthly') {
                $user->pv_balance -= $history->amount;
                $user->monthly_pv -= $history->amount;
            }
            if ($history->type === 'team') {
                $user->team_pv -= $history->amount;
            }
            $user->saveQuietly();

            $user->updateTeamPVOptimized();
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');

            $history->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Historique supprimé avec succès',
                'user' => [
                    'pv_balance' => $user->pv_balance,
                    'monthly_pv' => $user->monthly_pv,
                    'team_pv' => $user->team_pv,
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

    /**
     * Recalculer le grade d'un utilisateur (dispatch un job)
     */
    public function recalculateRank(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            
            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', " Recalcul du grade de {$user->name} en cours. Le résultat sera visible dans quelques secondes.");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Exporter les données PV
     */
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
                'PV Total', 'BV', 'PV Mensuel', 'PV Équipe', 'Cumul PV', 'Statut'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->sponsor_id ?? '',
                    $user->rank_name ?? 'Distributeur',
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

    /**
     * Récupérer les informations de grade
     */
    public function getRankInfo($id)
    {
        $user = User::findOrFail($id);
        
        return response()->json([
            'current_rank' => $user->rank_name ?? 'Distributeur',
            'pv_balance' => $user->pv_balance ?? 0,
            'team_pv' => $user->team_pv ?? 0,
            'cumul_pv' => ($user->pv_balance ?? 0) + ($user->team_pv ?? 0),
            'next_rank' => null,
            'progress_percentage' => 0,
            'pv_needed' => 0,
        ]);
    }

    /**
     * Statistiques PV
     */
    public function stats()
    {
        $stats = [
            'total_users' => User::where('is_active', 1)->count(),
            'total_pv' => User::where('is_active', 1)->sum('pv_balance'),
            'total_team_pv' => User::where('is_active', 1)->sum('team_pv'),
            'total_monthly_pv' => User::where('is_active', 1)->sum('monthly_pv'),
            'users_with_pv' => User::where('is_active', 1)->where('pv_balance', '>', 0)->count(),
            'users_with_team' => User::where('is_active', 1)->where('team_pv', '>', 0)->count(),
        ];

        return view('admin.pv.stats', compact('stats'));
    }

    /**
     * Historique des PV
     */
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
}