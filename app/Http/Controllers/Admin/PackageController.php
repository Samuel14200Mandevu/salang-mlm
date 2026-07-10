<?php
// app/Http/Controllers/Admin/PackageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Liste des packages
     */
    public function index()
    {
        $packages = Package::orderBy('price', 'asc')->get();
        
        $stats = [
            'total' => $packages->count(),
            'active' => $packages->where('is_active', true)->count(),
            'total_price' => $packages->sum('price'),
            'total_pv' => $packages->sum('pv_value'),
        ];
        
        return view('admin.packages.index', compact('packages', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    /**
     * Créer un package
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:packages',
            'price' => 'required|numeric|min:0',
            'pv_value' => 'required|integer|min:0',
            'bv_value' => 'required|integer|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'benefits' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $package = Package::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'pv_value' => $request->pv_value,
            'bv_value' => $request->bv_value,
            'commission_rate' => $request->commission_rate,
            'description' => $request->description,
            'benefits' => $request->benefits ?? [],
            'is_active' => $request->has('is_active') ? 1 : 0, // CORRIGÉ
        ]);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$package->name}' créé avec succès.");
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $package = Package::findOrFail($id);
        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Mettre à jour un package
     */
    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:packages,slug,' . $id,
            'price' => 'required|numeric|min:0',
            'pv_value' => 'required|integer|min:0',
            'bv_value' => 'required|integer|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'benefits' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // CORRIGÉ : Gérer le statut correctement
        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'pv_value' => $request->pv_value,
            'bv_value' => $request->bv_value,
            'commission_rate' => $request->commission_rate,
            'description' => $request->description,
            'benefits' => $request->benefits ?? [],
            'is_active' => $request->has('is_active') ? 1 : 0, // CORRIGÉ
        ];

        $package->update($data);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$package->name}' mis à jour.");
    }

    /**
     * Supprimer un package
     */
    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        
        // Vérifier si des utilisateurs utilisent ce package
        $usersCount = $package->users()->count();
        if ($usersCount > 0) {
            return back()->with('error', "Impossible de supprimer. {$usersCount} utilisateur(s) utilisent ce package.");
        }
        
        $name = $package->name;
        $package->delete();
        
        return redirect()->route('admin.packages')
            ->with('success', "Package '{$name}' supprimé.");
    }

    /**
     * Activer/Désactiver un package
     */
    public function toggleStatus($id)
    {
        $package = Package::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();
        
        $status = $package->is_active ? 'activé' : 'désactivé';
        return redirect()->route('admin.packages')
            ->with('success', "Package '{$package->name}' {$status}.");
    }

    /**
     * Dupliquer un package
     */
    public function duplicate($id)
    {
        $package = Package::findOrFail($id);
        
        $newPackage = $package->replicate();
        $newPackage->name = $package->name . ' (Copie)';
        $newPackage->slug = $package->slug . '-copy-' . time();
        $newPackage->is_active = false;
        $newPackage->save();
        
        return redirect()->route('admin.packages')
            ->with('success', "Package '{$newPackage->name}' dupliqué avec succès.");
    }
}