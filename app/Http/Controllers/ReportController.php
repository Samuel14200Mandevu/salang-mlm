<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Commission;
use App\Models\Withdrawal;
use App\Models\Package;
use App\Models\User;
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
        $packagesCount = Package::where('is_active', true)->count();

        return view('report.index', compact(
            'totalEarnings',
            'totalWithdrawn',
            'transactionsCount',
            'packagesCount'
        ));
    }
}