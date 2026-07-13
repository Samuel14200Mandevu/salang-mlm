<?php
// app/Http/Controllers/Admin/PackageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    private function getDefaultBenefits(): array
    {
        return [
            'Starter' => ['Commission up to 30%', 'Shop access', 'Unlimited referrals'],
            'Silver' => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus'],
            'Bronze' => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus', 'Training included'],
            'Gold' => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus', 'Training included', 'Priority support'],
            'Emerald' => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus', 'Training included', 'Priority support', 'Exclusive events'],
        ];
    }

    public function index()
    {
        $packages = Package::orderBy('price', 'asc')->get();

        $stats = [
            'total' => $packages->count(),
            'active' => $packages->where('is_active', true)->count(),
            'total_price' => $packages->sum('price'),
            'total_pv' => $packages->sum('pv_value'),
            'total_bv' => $packages->sum('bv_value'),
        ];

        return view('admin.packages.index', compact('packages', 'stats'));
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
            'benefits' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $defaultBenefits = $this->getDefaultBenefits();
        $benefits = $request->benefits ?? ($defaultBenefits[$request->name] ?? ['Commission up to 30%']);

        $package = Package::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'pv_value' => $request->pv_value,
            'bv_value' => $request->bv_value,
            'commission_rate' => $request->commission_rate,
            'description' => $request->description,
            'benefits' => $benefits,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        Log::info('Package created', [
            'package_id' => $package->id,
            'name' => $package->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$package->name}' created successfully.");
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
            'benefits' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $package->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'pv_value' => $request->pv_value,
            'bv_value' => $request->bv_value,
            'commission_rate' => $request->commission_rate,
            'description' => $request->description,
            'benefits' => $request->benefits ?? [],
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        Log::info('Package updated', [
            'package_id' => $package->id,
            'name' => $package->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$package->name}' updated.");
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        $usersCount = $package->users()->count();
        if ($usersCount > 0) {
            return back()->with('error', "Cannot delete. {$usersCount} user(s) are using this package.");
        }

        $name = $package->name;
        $package->delete();

        Log::info('Package deleted', [
            'package_id' => $id,
            'name' => $name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$name}' deleted.");
    }

    public function toggleStatus($id)
    {
        $package = Package::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();

        $status = $package->is_active ? 'activated' : 'deactivated';

        Log::info('Package ' . $status, [
            'package_id' => $package->id,
            'name' => $package->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$package->name}' {$status}.");
    }

    public function duplicate($id)
    {
        $package = Package::findOrFail($id);

        $newPackage = $package->replicate();
        $newPackage->name = $package->name . ' (Copy)';
        $newPackage->slug = $package->slug . '-copy-' . time();
        $newPackage->is_active = false;
        $newPackage->save();

        Log::info('Package duplicated', [
            'original_id' => $package->id,
            'new_id' => $newPackage->id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.packages')
            ->with('success', "Package '{$newPackage->name}' duplicated successfully.");
    }
}