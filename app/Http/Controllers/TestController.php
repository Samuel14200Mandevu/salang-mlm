<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Wallet;

class TestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $data = [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rank' => $user->rank,
                'rank_id' => $user->rank_id,
                'pv_balance' => $user->pv_balance,
                'sponsor_id' => $user->sponsor_id,
            ] : 'Non connecté',
            
            'commissions' => $user ? Commission::where('user_id', $user->id)->count() : 0,
            'commissions_total' => $user ? Commission::where('user_id', $user->id)->sum('amount') : 0,
            'transactions' => $user ? Transaction::where('user_id', $user->id)->count() : 0,
            'wallet' => $user ? Wallet::where('user_id', $user->id)->first() : null,
        ];
        
        return response()->json($data);
    }
}
