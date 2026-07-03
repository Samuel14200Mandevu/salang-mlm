<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rank;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Genealogy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['rank', 'package'])->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    // ============================================================
    // AFFICHER LES DÉTAILS D'UN UTILISATEUR
    // ============================================================
    public function show($id)
    {
        $user = User::with(['rank', 'package', 'sponsor'])->findOrFail($id);
        
        // Compter les downlines (filleuls)
        $downlinesCount = Genealogy::where('sponsor_id', $id)->count();
        
        // Compter les commissions
        $commissionsCount = $user->commissions()->count();
        $totalCommissions = $user->commissions()->sum('amount');
        
        return view('admin.users.show', compact('user', 'downlinesCount', 'commissionsCount', 'totalCommissions'));
    }

    public function create()
    {
        $ranks = Rank::all();
        $packages = Package::all();
        return view('admin.users.create', compact('ranks', 'packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'rank_id' => 'nullable|exists:ranks,id',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'rank_id' => $request->rank_id,
            'package_id' => $request->package_id,
            'sponsor_id' => 'SAL' . strtoupper(substr(uniqid(), -6)),
            'is_active' => true,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        Genealogy::create([
            'user_id' => $user->id,
            'sponsor_id' => null,
            'parent_id' => null,
            'level' => 0,
        ]);

        return redirect()->route('admin.users')->with('success', 'Utilisateur cree avec succes.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $ranks = Rank::all();
        $packages = Package::all();
        return view('admin.users.edit', compact('user', 'ranks', 'packages'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'rank_id' => 'nullable|exists:ranks,id',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'rank_id' => $request->rank_id,
            'package_id' => $request->package_id,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis a jour.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Utilisateur supprime.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'active' : 'desactive';
        return redirect()->route('admin.users')->with('success', "Utilisateur {$status} avec succes.");
    }
}