<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index()
    {
        $commissions = Commission::with(['user', 'fromUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $totalCommissions = Commission::where('status', 'paid')->sum('amount');
        $pendingCommissions = Commission::where('status', 'pending')->sum('amount');
        
        return view('admin.commissions.index', compact('commissions', 'totalCommissions', 'pendingCommissions'));
    }
}
