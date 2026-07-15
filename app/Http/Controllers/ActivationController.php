<?php
// app/Http/Controllers/ActivationController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Notifications\ActivationCodeNotification;
use App\Services\MLM\CommissionDistributor;
use App\Models\CommissionPeriod;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivationController extends Controller
{
    /**
     * Afficher la page d'activation
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est déjà actif.');
        }

        $packages = Package::where('is_active', true)->get();

        return view('auth.activate', compact('user', 'packages'));
    }

    /**
     * Activer avec un code (le code = package)
     */
    public function activateWithCode(Request $request)
    {
        $request->validate([
            'activation_code' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est déjà actif.');
        }

        if ($user->activation_code !== $request->activation_code) {
            return back()->with('error', 'Code d\'activation invalide.');
        }

        if ($user->activation_code_expires_at < now()) {
            return back()->with('error', 'Code d\'activation expiré. Veuillez contacter l\'administrateur.');
        }

        // ✅ Récupérer le package associé au code
        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        // ✅ Mettre à jour l'utilisateur
        $updateData = [
            'is_active' => true,
            'activated_at' => now(),
            'activation_method' => 'code',
            'activation_code' => null,
            'activation_code_expires_at' => null,
        ];

        // ✅ Si un package est associé, l'assigner
        if ($package) {
            $updateData['package_id'] = $package->id;
            $updateData['pv_balance'] = ($user->pv_balance ?? 0) + $package->pv_value;
            $updateData['bv_balance'] = ($user->bv_balance ?? 0) + $package->bv_value;
        }

        $user->update($updateData);

        // ✅ Calculer les commissions si package
        if ($package) {
            $this->calculateCommissionsForPackage($user, $package);
        }

        Log::info('User activated with code', [
            'user_id' => $user->id,
            'email' => $user->email,
            'package_id' => $package?->id,
            'package_name' => $package?->name,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Votre compte a été activé avec succès !');
    }

    /**
     * Activer avec un package
     */
    public function activateWithPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est déjà actif.');
        }

        $package = Package::find($request->package_id);

        $user->update([
            'is_active' => true,
            'activated_at' => now(),
            'activation_method' => 'package',
            'package_id' => $package->id,
            'pv_balance' => ($user->pv_balance ?? 0) + $package->pv_value,
            'bv_balance' => ($user->bv_balance ?? 0) + $package->bv_value,
            'activation_code' => null,
            'activation_code_expires_at' => null,
        ]);

        // ✅ Calculer les commissions
        $this->calculateCommissionsForPackage($user, $package);

        Log::info('User activated with package', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
        ]);

        return redirect()->route('dashboard')->with('success', 'Votre compte a été activé avec succès !');
    }

    /**
     * Activer via un lien direct (avec code dans l'URL)
     */
    public function activateWithLink($code)
    {
        $user = User::where('activation_code', $code)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Code d\'activation invalide.');
        }

        if ($user->is_active) {
            return redirect()->route('login')->with('info', 'Ce compte est déjà actif.');
        }

        if ($user->activation_code_expires_at < now()) {
            return redirect()->route('login')->with('error', 'Code d\'activation expiré. Veuillez contacter l\'administrateur.');
        }

        // ✅ Récupérer le package associé
        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        $updateData = [
            'is_active' => true,
            'activated_at' => now(),
            'activation_method' => 'link',
            'activation_code' => null,
            'activation_code_expires_at' => null,
        ];

        if ($package) {
            $updateData['package_id'] = $package->id;
            $updateData['pv_balance'] = ($user->pv_balance ?? 0) + $package->pv_value;
            $updateData['bv_balance'] = ($user->bv_balance ?? 0) + $package->bv_value;
        }

        $user->update($updateData);

        if ($package) {
            $this->calculateCommissionsForPackage($user, $package);
        }

        Log::info('User activated with link', [
            'user_id' => $user->id,
            'email' => $user->email,
            'package_id' => $package?->id,
        ]);

        return redirect()->route('login')->with('success', 'Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Renvoyer le code d'activation
     */
    public function resendCode(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est déjà actif.');
        }

        // Générer un nouveau code
        $newCode = 'ACT-' . strtoupper(substr(md5(uniqid() . time()), 0, 12));
        
        // ✅ Conserver le package associé
        $user->update([
            'activation_code' => $newCode,
            'activation_code_expires_at' => now()->addDays(7),
        ]);

        // ✅ Envoyer la notification avec le package
        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        try {
            $user->notify(new ActivationCodeNotification($newCode, $package));
        } catch (\Exception $e) {
            Log::error('Error resending activation code: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'envoi du code. Veuillez réessayer.');
        }

        return back()->with('success', 'Un nouveau code d\'activation a été envoyé à votre adresse email.');
    }

    /**
     * Calculer les commissions pour un package
     */
    private function calculateCommissionsForPackage($user, $package)
    {
        try {
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'PKG-' . strtoupper(uniqid()),
                'subtotal' => $package->price,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $package->price,
                'status' => 'completed',
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'name' => $package->name,
                'sku' => 'PKG-' . $package->slug,
                'quantity' => 1,
                'price' => $package->price,
                'total' => $package->price,
                'pv_value' => $package->pv_value,
                'bv_value' => $package->bv_value,
                'options' => json_encode([
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                ]),
            ]);

            $commissionDistributor = app(CommissionDistributor::class);
            $commissions = $commissionDistributor->distributeCommissions(
                $user,
                $package,
                $order->id,
                $period
            );

            $totalAmount = collect($commissions)->sum('amount');

            Log::info('Commissions calculées pour l\'activation', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'commissions_count' => count($commissions),
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des commissions', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}