<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionTriggerController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Déclencher le calcul des commissions après achat de package
     */
    public function triggerPackageCommission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
        ]);

        $result = $this->commissionService->calculatePackageCommission(
            $request->user_id,
            $request->package_id
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Commissions calculées avec succès'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du calcul des commissions'
        ], 500);
    }

    /**
     * Recalculer toutes les commissions (admin)
     */
    public function recalculateAll(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Récupérer tous les packages achetés
        $users = User::whereNotNull('package_id')->get();
        $count = 0;

        foreach ($users as $user) {
            $package = Package::find($user->package_id);
            if ($package) {
                $result = $this->commissionService->calculatePackageCommission(
                    $user->id,
                    $package->id
                );
                if ($result) $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' utilisateurs recalculés'
        ]);
    }
}
