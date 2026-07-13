<?php
// app/Http/Controllers/Admin/RankController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\User;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RankController extends Controller
{
    protected $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function index(Request $request)
    {
        $ranks = Rank::orderBy('level', 'asc')->get();

        $stats = [
            'total' => $ranks->count(),
            'active' => $ranks->where('is_active', true)->count(),
            'inactive' => $ranks->where('is_active', false)->count(),
            'users_by_rank' => User::select('rank_id', DB::raw('count(*) as count'))
                ->groupBy('rank_id')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->rank_id => $item->count];
                }),
            'total_users' => User::count(),
            'users_with_rank' => User::whereNotNull('rank_id')->count(),
            'highest_rank' => Rank::where('is_active', true)->orderBy('level', 'desc')->first(),
        ];

        $usersByRank = [];
        foreach ($ranks as $rank) {
            $usersByRank[$rank->id] = $stats['users_by_rank'][$rank->id] ?? 0;
        }

        return view('admin.ranks.index', compact('ranks', 'stats', 'usersByRank'));
    }

    public function create()
    {
        return view('admin.ranks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ranks',
            'level' => 'required|integer|min:1|unique:ranks,level',
            'min_pv' => 'required|integer|min:0',
            'min_bv' => 'nullable|integer|min:0',
            'monthly_pv_required' => 'nullable|integer|min:0',
            'team_pv_required' => 'nullable|integer|min:0',
            'min_sponsors' => 'nullable|integer|min:0',
            'min_team' => 'nullable|integer|min:0',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'conditions' => 'nullable|array',
            'commission_types' => 'nullable|array',
        ]);

        $rank = Rank::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'level' => $request->level,
            'min_pv' => $request->min_pv,
            'min_bv' => $request->min_bv ?? 0,
            'monthly_pv_required' => $request->monthly_pv_required ?? 0,
            'team_pv_required' => $request->team_pv_required ?? 0,
            'min_sponsors' => $request->min_sponsors ?? 0,
            'min_team' => $request->min_team ?? 0,
            'bonus_percentage' => $request->bonus_percentage,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
            'conditions' => $request->conditions ?? [],
            'commission_types' => $request->commission_types ?? [],
        ]);

        Log::info('Rank created', [
            'rank_id' => $rank->id,
            'name' => $rank->name,
            'level' => $rank->level,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', "Rank '{$rank->name}' (Level {$rank->level}) created.");
    }

    public function edit($id)
    {
        $rank = Rank::findOrFail($id);
        return view('admin.ranks.edit', compact('rank'));
    }

    public function update(Request $request, $id)
    {
        $rank = Rank::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ranks,slug,' . $id,
            'level' => 'required|integer|min:1|unique:ranks,level,' . $id,
            'min_pv' => 'required|integer|min:0',
            'min_bv' => 'nullable|integer|min:0',
            'monthly_pv_required' => 'nullable|integer|min:0',
            'team_pv_required' => 'nullable|integer|min:0',
            'min_sponsors' => 'nullable|integer|min:0',
            'min_team' => 'nullable|integer|min:0',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'conditions' => 'nullable|array',
            'commission_types' => 'nullable|array',
        ]);

        $rank->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'level' => $request->level,
            'min_pv' => $request->min_pv,
            'min_bv' => $request->min_bv ?? 0,
            'monthly_pv_required' => $request->monthly_pv_required ?? 0,
            'team_pv_required' => $request->team_pv_required ?? 0,
            'min_sponsors' => $request->min_sponsors ?? 0,
            'min_team' => $request->min_team ?? 0,
            'bonus_percentage' => $request->bonus_percentage,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
            'conditions' => $request->conditions ?? [],
            'commission_types' => $request->commission_types ?? [],
        ]);

        Log::info('Rank updated', [
            'rank_id' => $rank->id,
            'name' => $rank->name,
            'level' => $rank->level,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', "Rank '{$rank->name}' updated.");
    }

    public function destroy($id)
    {
        $rank = Rank::findOrFail($id);

        $users = User::where('rank_id', $id)->count();
        if ($users > 0) {
            return back()->with('error', "Cannot delete this rank. {$users} user(s) currently have it.");
        }

        $history = RankHistory::where('old_rank_id', $id)->orWhere('new_rank_id', $id)->count();
        if ($history > 0) {
            return back()->with('error', "Cannot delete this rank. {$history} history entries use it.");
        }

        $name = $rank->name;
        $level = $rank->level;
        $rank->delete();

        Log::info('Rank deleted', [
            'rank_id' => $id,
            'name' => $name,
            'level' => $level,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', "Rank '{$name}' (Level {$level}) deleted.");
    }

    public function toggleStatus($id)
    {
        $rank = Rank::findOrFail($id);
        $rank->is_active = !$rank->is_active;
        $rank->save();

        $status = $rank->is_active ? 'activated' : 'deactivated';

        Log::info('Rank ' . $status, [
            'rank_id' => $rank->id,
            'name' => $rank->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', "Rank '{$rank->name}' {$status}.");
    }

    public function reassignAll(Request $request)
    {
        $request->validate([
            'force' => 'boolean',
        ]);

        $updated = 0;
        $errors = [];

        User::chunk(100, function ($users) use (&$updated, &$errors) {
            foreach ($users as $user) {
                try {
                    $newRank = $this->rankCalculator->calculateAdvancedRank($user);

                    if ($newRank && $newRank->id != $user->rank_id) {
                        $oldRankId = $user->rank_id;
                        $oldRankName = $user->rank_name;

                        $user->rank_id = $newRank->id;
                        $user->rank = $newRank->name;
                        $user->last_rank_update = now();
                        $user->save();

                        RankHistory::create([
                            'user_id' => $user->id,
                            'old_rank_id' => $oldRankId,
                            'new_rank_id' => $newRank->id,
                            'old_rank_name' => $oldRankName,
                            'new_rank_name' => $newRank->name,
                            'pv_at_time' => $user->pv_balance,
                            'bv_at_time' => $user->bv_balance,
                            'notes' => 'Automatic reassignment by admin with MLM rules',
                        ]);

                        $updated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "ID {$user->id}: " . $e->getMessage();
                }
            }
        });

        $message = "{$updated} user(s) reassigned successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " and " . (count($errors) - 5) . " more errors.";
            }
        }

        Log::info('Rank reassign all', [
            'updated' => $updated,
            'errors' => count($errors),
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.ranks')
            ->with('success', $message);
    }

    public function reassignUser($id)
    {
        $user = User::findOrFail($id);

        // ✅ S'assurer que l'utilisateur a un objet Rank
        if (is_string($user->rank) || is_null($user->rank)) {
            if ($user->rank_id) {
                $rank = Rank::find($user->rank_id);
                if ($rank) {
                    $user->setRelation('rank', $rank);
                }
            }
        }

        $newRank = $this->rankCalculator->calculateAdvancedRank($user);

        if ($newRank && $newRank->id != $user->rank_id) {
            $oldRank = $user->rank_name;

            $user->rank_id = $newRank->id;
            $user->rank = $newRank->name;
            $user->last_rank_update = now();
            $user->save();

            RankHistory::create([
                'user_id' => $user->id,
                'old_rank_id' => $user->getOriginal('rank_id'),
                'new_rank_id' => $newRank->id,
                'old_rank_name' => $oldRank,
                'new_rank_name' => $newRank->name,
                'pv_at_time' => $user->pv_balance,
                'notes' => 'Manual reassignment by admin',
            ]);

            Log::info('User rank reassigned', [
                'user_id' => $user->id,
                'old_rank' => $oldRank,
                'new_rank' => $newRank->name,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.users.show', $id)
                ->with('success', "Rank of {$user->name} updated to: {$newRank->name}");
        }

        return redirect()->route('admin.users.show', $id)
            ->with('info', "No change for {$user->name}.");
    }

    public function history(Request $request)
    {
        $query = RankHistory::with(['user', 'oldRank', 'newRank']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        $stats = [
            'total' => RankHistory::count(),
            'today' => RankHistory::whereDate('created_at', today())->count(),
            'this_month' => RankHistory::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'most_promoted' => RankHistory::select('new_rank_name', DB::raw('count(*) as count'))
                ->where('new_rank_name', '!=', 'Distributor')
                ->groupBy('new_rank_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'by_rank' => RankHistory::select('new_rank_id', DB::raw('count(*) as count'))
                ->groupBy('new_rank_id')
                ->with('newRank')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->newRank->name ?? 'Unknown' => $item->count];
                }),
        ];

        return view('admin.ranks.history', compact('history', 'users', 'stats'));
    }

    public function exportHistory(Request $request)
    {
        $query = RankHistory::with(['user', 'oldRank', 'newRank']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $history = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rank_history_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($history) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'User', 'Old Rank', 'New Rank',
                'PV', 'BV', 'Type', 'Date'
            ]);

            foreach ($history as $h) {
                $oldLevel = $h->oldRank ? $h->oldRank->level : 0;
                $newLevel = $h->newRank ? $h->newRank->level : 0;
                $type = $newLevel > $oldLevel ? 'Promotion' : ($newLevel < $oldLevel ? 'Demotion' : 'Update');

                fputcsv($file, [
                    $h->id,
                    $h->user->name ?? 'N/A',
                    $h->old_rank_name ?? 'N/A',
                    $h->new_rank_name ?? 'N/A',
                    $h->pv_at_time ?? 0,
                    $h->bv_at_time ?? 0,
                    $type,
                    $h->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}