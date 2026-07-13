<?php
// app/Http/Controllers/NetworkController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Genealogy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $parrain = User::find($user->parrain_id);
        $filleuls = User::where('parrain_id', $user->id)->get();
        $tree = $this->buildTree($user);

        $stats = [
            'total' => User::where('parrain_id', $user->id)->count(),
            'level_1' => User::where('parrain_id', $user->id)->count(),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'active' => User::where('parrain_id', $user->id)
                ->where('is_active', true)
                ->count(),
        ];

        $recentDownlines = User::where('parrain_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['package', 'rank'])
            ->get();

        return view('network.index', compact(
            'user',
            'parrain',
            'filleuls',
            'tree',
            'stats',
            'recentDownlines'
        ));
    }

    private function buildTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel || !$user) {
            return null;
        }

        $downlines = User::where('parrain_id', $user->id)->get();
        $children = [];

        foreach ($downlines as $child) {
            $children[] = [
                'user' => $child,
                'children' => $this->buildTree($child, $level + 1, $maxLevel),
            ];
        }

        return [
            'user' => $user,
            'level' => $level,
            'children' => $children,
        ];
    }

    private function countLevel2($user)
    {
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();

        if (empty($level1Ids)) {
            return 0;
        }

        return User::whereIn('parrain_id', $level1Ids)->count();
    }

    private function countLevel3($user)
    {
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();

        if (empty($level1Ids)) {
            return 0;
        }

        $level2Ids = User::whereIn('parrain_id', $level1Ids)->pluck('id')->toArray();

        if (empty($level2Ids)) {
            return 0;
        }

        return User::whereIn('parrain_id', $level2Ids)->count();
    }

    public function treeData()
    {
        $user = Auth::user();
        $tree = $this->buildTree($user, 0, 5);

        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    public function downlines(Request $request)
    {
        $user = Auth::user();

        $query = User::where('parrain_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%");
            });
        }

        $downlines = $query->with(['package', 'rank'])
            ->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $downlines
            ]);
        }

        return view('network.downlines', compact('downlines'));
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $results = User::where('parrain_id', $user->id)
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('sponsor_id', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'email', 'sponsor_id', 'avatar', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function apiStats()
    {
        $user = Auth::user();

        $stats = [
            'total' => User::where('parrain_id', $user->id)->count(),
            'level_1' => User::where('parrain_id', $user->id)->count(),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'active' => User::where('parrain_id', $user->id)
                ->where('is_active', true)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}