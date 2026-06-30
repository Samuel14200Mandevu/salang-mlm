<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        $balance = $wallet ? $wallet->balance : 0;
        $pendingBalance = $wallet ? $wallet->pending_balance : 0;
        $totalWithdrawn = $wallet ? $wallet->total_withdrawn : 0;
        $totalDeposited = $wallet ? $wallet->total_deposited : 0;
        
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('wallet.index', compact(
            'balance', 
            'pendingBalance', 
            'totalWithdrawn', 
            'totalDeposited',
            'transactions'
        ));
    }
}
