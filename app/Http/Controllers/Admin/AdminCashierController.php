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

class AdminCashierController extends Controller
{
    /** Rôles staff gérés dans « Employés ». */
    public const STAFF_ROLES = ['admin', 'it_manager', 'cashier'];

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste des employés (admin, responsable IT, caissier).
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->with('roles')
            ->role(self::STAFF_ROLES);

        $roleFilter = $request->string('role')->toString();
        if (in_array($roleFilter, self::STAFF_ROLES, true)) {
            $query->role($roleFilter);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::role(self::STAFF_ROLES)->count(),
            'admins' => User::role('admin')->count(),
            'it_managers' => User::role('it_manager')->count(),
            'cashiers' => User::role('cashier')->count(),
            'active' => User::role(self::STAFF_ROLES)->where('is_active', true)->count(),
            'inactive' => User::role(self::STAFF_ROLES)->where('is_active', false)->count(),
        ];

        // Compat vues existantes ($cashiers)
        $cashiers = $employees;

        return view('admin.cashiers.index', compact('employees', 'cashiers', 'stats', 'roleFilter'));
    }

    /**
     * Voir un employé.
     */
    public function show($id)
    {
        $cashier = User::role(self::STAFF_ROLES)->with('roles')->findOrFail($id);
        $employee = $cashier;

        return view('admin.cashiers.show', compact('cashier', 'employee'));
    }

    /**
     * Formulaire de création (redirige vers la création d'utilisateur staff).
     */
    public function create()
    {
        return redirect()->route('admin.users.create', ['role' => 'cashier']);
    }

    /**
     * Créer un caissier (SANS code de parrain, SANS MLM).
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
                'sponsor_id' => null,
                'parrain_id' => null,
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

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            $user->assignRole('cashier');

            Log::info('Nouvel employé caissier créé', [
                'admin_id' => auth()->id(),
                'cashier_id' => $user->id,
            ]);

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Employé (caissier) créé avec succès !');
        } catch (\Exception $e) {
            Log::error('Erreur création employé caissier', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        return redirect()->route('admin.users.edit', $id);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.users.update', $id)->withInput();
    }

    public function destroy($id)
    {
        try {
            $cashier = User::role(self::STAFF_ROLES)->findOrFail($id);

            if ((int) $cashier->id === (int) auth()->id()) {
                return redirect()->back()
                    ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            }

            $cashierName = $cashier->name;

            if ($cashier->wallet) {
                $cashier->wallet->delete();
            }

            $cashier->delete();

            Log::info('Employé supprimé', [
                'admin_id' => auth()->id(),
                'employee_id' => $id,
                'employee_name' => $cashierName,
            ]);

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Employé supprimé avec succès !');
        } catch (\Exception $e) {
            Log::error('Erreur suppression employé', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $cashier = User::role(self::STAFF_ROLES)->findOrFail($id);

            if ((int) $cashier->id === (int) auth()->id() && $cashier->is_active) {
                return redirect()->back()
                    ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            }

            $cashier->is_active = ! $cashier->is_active;
            $cashier->save();

            $status = $cashier->is_active ? 'activé' : 'désactivé';

            Log::info('Statut employé modifié', [
                'admin_id' => auth()->id(),
                'employee_id' => $cashier->id,
                'new_status' => $status,
            ]);

            return redirect()->route('admin.cashiers.index')
                ->with('success', "Employé {$status} avec succès !");
        } catch (\Exception $e) {
            Log::error('Erreur changement statut employé', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors du changement de statut: ' . $e->getMessage());
        }
    }
}
