<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RankController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ranks = Rank::all();
        
        // Progression
        $nextRank = Rank::where('min_pv', '>', $user->pv_balance)
            ->orderBy('min_pv', 'asc')
            ->first();
            
        $progress = $nextRank ? 
            min(100, ($user->pv_balance / $nextRank->min_pv) * 100) : 
            100;
            
        $pvNeeded = $nextRank ? 
            max(0, $nextRank->min_pv - $user->pv_balance) : 
            0;
        
        $currentPv = $user->pv_balance ?? 0;
        $nextPv = $nextRank ? $nextRank->min_pv : $user->pv_balance ?? 0;
        
        // Historique des grades
        $history = RankHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('rank.index', compact(
            'user',
            'ranks',
            'nextRank',
            'progress',
            'pvNeeded',
            'currentPv',
            'nextPv',
            'history'
        ));
    }

    public function history()
    {
        $user = Auth::user();
        
        $history = RankHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('rank.history', compact('history'));
    }
}