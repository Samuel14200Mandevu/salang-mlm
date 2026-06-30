<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Genealogy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
{
    $user = Auth::user();
    
    // ✅ CORRECT - Utilise paginate()
    $downlines = User::where('sponsor_id', $user->sponsor_id)
        ->with(['package', 'genealogy'])
        ->paginate(20);
    
    return view('team.index', compact('downlines'));
}
}
