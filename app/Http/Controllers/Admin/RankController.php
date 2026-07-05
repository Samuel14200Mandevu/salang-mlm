<?php
// app/Http/Controllers/Admin/RankController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\User;
use App\Models\RankHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RankController extends Controller
{
    /**
     * Liste des grades
     */
    public function index()
    {
        $ranks = Rank::orderBy('min_pv', 'asc')->get();

        // Statistiques
        $stats = [
            'total' => Rank::count(),
            'active' => Rank::where('is_active', true)->count(),
            'users_by_rank' => User::select('rank_id', DB::raw('count(*) as count'))
                ->groupBy('rank_id')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->rank_id => $item->count];
                }),
        ];

        return view('admin.ranks.index', compact('ranks', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.ranks.create');
    }

    /**
     * Créer un grade
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ranks',
            'min_pv' => 'required|integer|min:0',
            'min_bv' => 'nullable|integer|min:0',
            'min_sponsors' => 'nullable|integer|min:0',
            'min_team' => 'nullable|integer|min:0',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $rank = Rank::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'min_pv' => $request->min_pv,
            'min_bv' => $request->min_bv ?? 0,
            'min_sponsors' => $request->min_sponsors ?? 0,
            'min_team' => $request->min_team ?? 0,
            'bonus_percentage' => $request->bonus_percentage,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', "🏅 Grade '{$rank->name}' créé avec succès.");
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $rank = Rank::findOrFail($id);
        return view('admin.ranks.edit', compact('rank'));
    }

    /**
     * Mettre à jour un grade
     */
    public function update(Request $request, $id)
    {
        $rank = Rank::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ranks,slug,' . $id,
            'min_pv' => 'required|integer|min:0',
            'min_bv' => 'nullable|integer|min:0',
            'min_sponsors' => 'nullable|integer|min:0',
            'min_team' => 'nullable|integer|min:0',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $rank->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'min_pv' => $request->min_pv,
            'min_bv' => $request->min_bv ?? 0,
            'min_sponsors' => $request->min_sponsors ?? 0,
            'min_team' => $request->min_team ?? 0,
            'bonus_percentage' => $request->bonus_percentage,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', "🏅 Grade '{$rank->name}' mis à jour.");
    }

    /**
     * Supprimer un grade
     */
    public function destroy($id)
    {
        $rank = Rank::findOrFail($id);

        // Vérifier si des utilisateurs ont ce grade
        $users = User::where('rank_id', $id)->count();
        if ($users > 0) {
            return back()->with('error', "Impossible de supprimer ce grade. {$users} utilisateur(s) l'ont actuellement.");
        }

        $name = $rank->name;
        $rank->delete();

        return redirect()->route('admin.ranks')
            ->with('success', "🗑️ Grade '{$name}' supprimé.");
    }

    /**
     * Activer/Désactiver un grade
     */
    public function toggleStatus($id)
    {
        $rank = Rank::findOrFail($id);
        $rank->is_active = !$rank->is_active;
        $rank->save();

        $status = $rank->is_active ? 'activé' : 'désactivé';

        return redirect()->route('admin.ranks')
            ->with('success', "🏅 Grade '{$rank->name}' {$status}.");
    }

    /**
     * Réaffecter tous les utilisateurs - ✅ CORRIGÉ
     */
    public function reassignAll(Request $request)
    {
        $request->validate([
            'force' => 'boolean',
        ]);

        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {
            // ✅ Sauvegarder l'ancien grade avant modification
            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank;
            
            $newRank = Rank::where('min_pv', '<=', $user->pv_balance)
                ->where('is_active', true)
                ->orderBy('min_pv', 'desc')
                ->first();

            if ($newRank && $newRank->id != $oldRankId) {
                $user->rank = $newRank->name;
                $user->rank_id = $newRank->id;
                $user->save();

                // ✅ Utiliser les variables sauvegardées
                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance,
                    'bv_at_time' => $user->bv_balance,
                    'notes' => 'Réaffectation automatique par admin',
                ]);

                $updated++;
            }
        }

        return redirect()->route('admin.ranks')
            ->with('success', "🏅 {$updated} utilisateur(s) réaffectés avec succès.");
    }

    /**
     * Historique des promotions
     */
    public function history(Request $request)
    {
        $query = RankHistory::with(['user', 'oldRank', 'newRank']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.ranks.history', compact('history', 'users'));
    }
}