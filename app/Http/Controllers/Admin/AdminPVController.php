<?php
// app/Http/Controllers/Admin/AdminPVController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Rank;
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

        // Si un ID utilisateur est passé directement
        if ($request->filled('user_id')) {
            $user = User::with(['package', 'rank'])->find($request->user_id);
        }
        // Si une recherche est effectuée
        elseif ($request->filled('search')) {
            $search = $request->search;
            $results = User::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('sponsor_id', 'like', "%{$search}%")
                ->get();
            
            // Si un seul résultat, on le sélectionne automatiquement
            if ($results->count() == 1) {
                $user = $results->first();
            } 
            // Sinon, on affiche la liste des résultats
            else {
                return view('admin.pv.search', compact('results', 'search'));
            }
        }

        // Si aucun utilisateur n'est trouvé, on redirige vers la page de recherche
        if (!$user) {
            return redirect()->route('admin.pv.search')
                ->with('error', 'Aucun utilisateur trouvé. Veuillez effectuer une recherche.');
        }

        return view('admin.pv.index', compact('user', 'packages'));
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
     * Mettre à jour les PV d'un utilisateur
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

            $user->pv_balance = $request->pv_balance;
            $user->monthly_pv = $request->monthly_pv;
            
            if ($request->has('team_pv')) {
                $user->team_pv = $request->team_pv;
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

            $user->save();

            // Mettre à jour le grade
            if (method_exists($user, 'calculateAndUpdateRank')) {
                $user->calculateAndUpdateRank();
            }

            DB::commit();

            Log::info('PV mis à jour', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'old_pv' => $oldPv,
                'new_pv' => $request->pv_balance,
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', "PV de {$user->name} mis à jour avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour PV: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Ajouter des PV mensuels
     */
    public function addMonthly(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            $oldPv = $user->pv_balance;
            $oldMonthlyPv = $user->monthly_pv;

            $user->pv_balance += $request->amount;
            $user->monthly_pv += $request->amount;
            $user->save();

            // Mettre à jour le grade
            if (method_exists($user, 'calculateAndUpdateRank')) {
                $user->calculateAndUpdateRank();
            }

            DB::commit();

            Log::info('PV mensuel ajouté', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', "{$request->amount} PV ajoutés à {$user->name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Recalculer le grade d'un utilisateur
     */
    public function recalculateRank(Request $request, $id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            if (method_exists($user, 'calculateAndUpdateRank')) {
                $user->calculateAndUpdateRank();
            }

            DB::commit();

            return redirect()->route('admin.pv.index', ['user_id' => $user->id])
                ->with('success', "Grade de {$user->name} recalculé avec succès. Nouveau grade: {$user->rank_name}");

        } catch (\Exception $e) {
            DB::rollBack();
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
}