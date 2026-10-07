<?php

namespace App\Http\Controllers;

use App\Models\PVHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyPvController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = PVHistory::query()
            ->where('user_id', $user->id);

        if ($request->filled('period')) {
            $query->where('period', $request->string('period')->toString());
        }

        $entries = $query
            ->orderByDesc('period')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $periods = PVHistory::query()
            ->where('user_id', $user->id)
            ->whereNotNull('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        $totals = [
            'personal' => (float) ($user->pv_balance ?? 0),
            'team' => (float) ($user->team_pv ?? 0),
            'monthly' => (float) ($user->monthly_pv ?? 0),
            'history_sum' => (float) PVHistory::where('user_id', $user->id)->sum('amount'),
        ];

        return view('my-pv.index', compact('entries', 'periods', 'totals', 'user'));
    }
}
