<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

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
        ]);

        Package::create($request->all());

        return redirect()->route('admin.packages')->with('success', '📦 Package créé avec succès.');
    }

    public function edit($id)
    {
        $package = Package::findOrFail($id);
        return view('admin.packages.edit', compact('package'));
    }

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
        ]);

        $package->update($request->all());

        return redirect()->route('admin.packages')->with('success', '📦 Package mis à jour.');
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $package->delete();
        return redirect()->route('admin.packages')->with('success', '🗑️ Package supprimé.');
    }
}
