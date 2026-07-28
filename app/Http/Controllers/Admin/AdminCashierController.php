<?php
// app/Http/Controllers/Admin/AdminCashierController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AdminCashierController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste des caissiers
     */
    public function index()
    {
        $cashiers = User::role('cashier')->paginate(20);
        return view('admin.cashiers.index', compact('cashiers'));
    }

    /**
     * Voir un caissier
     */
    public function show($id)
    {
        $cashier = User::role('cashier')->findOrFail($id);
        return view('admin.cashiers.show', compact('cashier'));
    }

    /**
     * Formulaire de création (redirige vers la création d'utilisateur)
     */
    public function create()
    {
        return redirect()->route('admin.users.create', ['role' => 'cashier']);
    }

    /**
     * Créer un caissier (SANS code de parrain, SANS MLM)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? 'N/A',
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'is_active' => $request->is_active ?? true,
                //  Pas de code de parrain
                'sponsor_id' => null,
                'parrain_id' => null,
                //  Pas de grade MLM
                'rank_id' => null,
                'rank' => null,
                'rank_level' => null,
                'package_id' => null,
                'pv_balance' => 0,
                'bv_balance' => 0,
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
                'total_team' => 0,
                'total_sponsors' => 0,
                'qualified_branches' => 0,
                'direct_sponsors_count' => 0,
                'commission_balance' => 0,
                'total_earnings' => 0,
                'kyc_status' => 'not_submitted',
            ]);

            //  Créer le wallet
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            //  Assigner le rôle cashier
            $user->assignRole('cashier');

            Log::info('Nouveau caissier créé (sans code de parrain, sans MLM)', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'cashier_id' => $user->id,
                'cashier_name' => $user->name,
                'cashier_email' => $user->email,
            ]);

            return redirect()->route('admin.cashiers.index')
                ->with('success', ' Caissier créé avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur création caissier', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', ' Erreur lors de la création: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Formulaire d'édition (redirige vers l'édition d'utilisateur)
     */
    public function edit($id)
    {
        return redirect()->route('admin.users.edit', $id);
    }

    /**
     * Mettre à jour un caissier (redirige vers la mise à jour d'utilisateur)
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('admin.users.update', $id)->withInput();
    }

    /**
     * Supprimer un caissier
     */
    public function destroy($id)
    {
        try {
            $cashier = User::role('cashier')->findOrFail($id);
            $cashierName = $cashier->name;

            // Supprimer le wallet
            if ($cashier->wallet) {
                $cashier->wallet->delete();
            }

            $cashier->delete();

            Log::info('Caissier supprimé', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'cashier_id' => $id,
                'cashier_name' => $cashierName,
            ]);

            return redirect()->route('admin.cashiers.index')
                ->with('success', ' Caissier supprimé avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur suppression caissier', [
                'cashier_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', ' Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Activer/Désactiver un caissier
     */
    public function toggleStatus($id)
    {
        try {
            $cashier = User::role('cashier')->findOrFail($id);
            $cashier->is_active = !$cashier->is_active;
            $cashier->save();

            $status = $cashier->is_active ? 'activé' : 'désactivé';

            Log::info('Statut du caissier modifié', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'cashier_id' => $cashier->id,
                'cashier_name' => $cashier->name,
                'new_status' => $status,
            ]);

            return redirect()->route('admin.cashiers.index')
                ->with('success', " Caissier {$status} avec succès !");

        } catch (\Exception $e) {
            Log::error('Erreur changement statut caissier', [
                'cashier_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', ' Erreur lors du changement de statut: ' . $e->getMessage());
        }
    }
}