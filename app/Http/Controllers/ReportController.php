<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Commission;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalEarnings = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        $transactionsCount = Transaction::where('user_id', $user->id)->count();
        $ordersCount = Order::where('user_id', $user->id)->count();

        $wallet = $user->wallet;
        $balance = $wallet ? (float) $wallet->balance : 0.0;

        $commissionsCount = Commission::where('user_id', $user->id)->count();

        return view('report.index', compact(
            'totalEarnings',
            'totalWithdrawn',
            'transactionsCount',
            'ordersCount',
            'balance',
            'commissionsCount'
        ));
    }

    public function earnings()
    {
        return redirect()->route('commissions.index');
    }

    public function network()
    {
        return redirect()->route('network.index');
    }

    public function export(Request $request)
    {
        return redirect()->route('wallet.export', $request->query());
    }
}