<?php
// app/Http/Controllers/Admin/UserController.php

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

    public function show($id)
    {
        $user = User::with(['rank', 'package', 'sponsor'])->findOrFail($id);
        
        $downlinesCount = Genealogy::where('sponsor_id', $id)->count();
        $commissionsCount = $user->commissions()->count();
        $totalCommissions = $user->commissions()->sum('amount');
        
        return view('admin.users.show', compact('user', 'downlinesCount', 'commissionsCount', 'totalCommissions'));
    }

    public function create()
    {
        $ranks = Rank::all();
        $packages = Package::all();
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('admin.users.create', compact('ranks', 'packages', 'users'));
    }

    /**
     * Créer un utilisateur - ✅ CORRIGÉ
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'rank_id' => 'nullable|exists:ranks,id',
            'package_id' => 'nullable|exists:packages,id',
            'sponsor_id' => 'nullable|exists:users,id', // ✅ ID utilisateur valide
        ]);

        // ✅ Générer un code de parrain unique (pour le champ sponsor_id qui est la colonne)
        $sponsorCode = 'SAL' . strtoupper(substr(uniqid(), -6));
        while (User::where('sponsor_id', $sponsorCode)->exists()) {
            $sponsorCode = 'SAL' . strtoupper(substr(uniqid(), -6));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'rank_id' => $request->rank_id,
            'package_id' => $request->package_id,
            'sponsor_id' => $sponsorCode, // ✅ Code de parrain unique
            'is_active' => true,
        ]);

        // Créer le portefeuille
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        // Créer la généalogie
        Genealogy::create([
            'user_id' => $user->id,
            'sponsor_id' => $request->sponsor_id, // ✅ ID du sponsor (clé étrangère)
            'parent_id' => $request->sponsor_id,
            'level' => 0,
        ]);

        // Mettre à jour le compteur du sponsor
        if ($request->sponsor_id) {
            $sponsor = User::find($request->sponsor_id);
            if ($sponsor) {
                $sponsor->increment('total_sponsors');
                $sponsor->increment('total_team');
            }
        }

        return redirect()->route('admin.users')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $ranks = Rank::all();
        $packages = Package::all();
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'ranks', 'packages', 'users'));
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
            'sponsor_id' => 'nullable|exists:users,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'rank_id' => $request->rank_id,
            'package_id' => $request->package_id,
            'is_active' => $request->has('is_active'),
        ];

        // Ne pas toucher au sponsor_id sauf si spécifié
        if ($request->has('sponsor_id')) {
            $data['sponsor_id'] = $request->sponsor_id;
        }

        $user->update($data);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activé' : 'désactivé';
        return redirect()->route('admin.users')->with('success', "Utilisateur {$status} avec succès.");
    }
}