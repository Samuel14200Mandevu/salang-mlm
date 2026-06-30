<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::with('user')
            ->orderBy('balance', 'desc')
            ->paginate(20);
            
        $totalBalance = Wallet::sum('balance');
        $totalWallets = Wallet::count();
        
        return view('admin.wallets.index', compact('wallets', 'totalBalance', 'totalWallets'));
    }
}
