<?php
// app/Http/Controllers/Admin/ActivationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Commission;
use App\Notifications\ActivationCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivationController extends Controller
{
    /**
     * Interface d'activation pour l'admin
     */
    public function index(Request $request)
    {
        $query = User::where('is_active', false)
            ->with(['package', 'rank', 'activationPackage']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%");
            });
        }

        $inactiveUsers = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_inactive' => User::where('is_active', false)->count(),
            'with_code' => User::where('is_active', false)
                ->whereNotNull('activation_code')
                ->count(),
            'without_code' => User::where('is_active', false)
                ->whereNull('activation_code')
                ->count(),
        ];

        $packages = Package::where('is_active', true)->get();

        return view('admin.activations.index', compact('inactiveUsers', 'stats', 'packages'));
    }

    /**
     * Voir les détails d'un utilisateur pour activation
     */
    public function show($userId)
    {
        $user = User::with(['rank', 'package', 'wallet', 'activationPackage'])
            ->findOrFail($userId);

        $totalCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $paidCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;

        $totalEarnings = $totalCommissions + $paidCommissions;

        $packages = Package::where('is_active', true)->get();

        $commissions = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'period'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.activations.show', compact(
            'user',
            'totalCommissions',
            'paidCommissions',
            'totalEarnings',
            'packages',
            'commissions'
        ));
    }

    /**
     * Générer un code d'activation avec un package
     */
    public function generateCodeWithPackage(Request $request, $userId)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = User::findOrFail($userId);
        $package = Package::findOrFail($request->package_id);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est déjà actif.');
        }

        // Générer le code d'activation
        $code = 'ACT-' . strtoupper(substr(md5(uniqid() . time() . rand()), 0, 12));

        // Associer le package au code
        $user->update([
            'activation_code' => $code,
            'activation_code_expires_at' => now()->addDays(7),
            'activation_package_id' => $package->id,
        ]);

        // Envoyer le code par email avec le package
        try {
            $user->notify(new ActivationCodeNotification($code, $package));
        } catch (\Exception $e) {
            Log::error('Error sending activation code: ' . $e->getMessage());
        }

        Log::info('Activation code generated with package', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.activations.show', $user->id)
            ->with('success', "Code d'activation généré pour {$user->name} avec le package {$package->name}");
    }

    /**
     * Activer manuellement un utilisateur (admin)
     */
    public function activateManually($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est déjà actif.');
        }

        $user->update([
            'is_active' => true,
            'activated_at' => now(),
            'activation_method' => 'admin_manual',
            'activation_code' => null,
            'activation_code_expires_at' => null,
        ]);

        Log::info('User activated manually by admin', [
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
        ]);

        return back()->with('success', "Compte de {$user->name} activé avec succès.");
    }

    /**
     * Renvoyer le code d'activation
     */
    public function sendCode($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->is_active) {
            return back()->with('error', 'Ce compte est déjà actif.');
        }

        if (!$user->activation_code) {
            return back()->with('error', 'Aucun code d\'activation généré pour cet utilisateur.');
        }

        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        try {
            $user->notify(new ActivationCodeNotification($user->activation_code, $package));
        } catch (\Exception $e) {
            Log::error('Error resending activation code: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'envoi du code.');
        }

        return back()->with('success', "Code d'activation renvoyé à {$user->email}");
    }
}