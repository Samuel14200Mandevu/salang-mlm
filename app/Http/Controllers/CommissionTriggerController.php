<?php
// app/Http/Controllers/CommissionTriggerController.php

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
     * Déclencher le calcul des commissions après achat de package - DONNÉES RÉELLES
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
                'message' => 'Commissions calculées avec succès',
                'data' => $this->getCommissionDetails($request->user_id)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du calcul des commissions'
        ], 500);
    }

    /**
     * Recalculer toutes les commissions (admin) - DONNÉES RÉELLES
     */
    public function recalculateAll(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $users = User::whereNotNull('package_id')->get();
        $count = 0;
        $errors = [];

        foreach ($users as $user) {
            $package = Package::find($user->package_id);
            if ($package) {
                $result = $this->commissionService->calculatePackageCommission(
                    $user->id,
                    $package->id
                );
                if ($result) {
                    $count++;
                } else {
                    $errors[] = $user->id;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' utilisateurs recalculés',
            'errors' => $errors,
            'total_users' => $users->count()
        ]);
    }

    /**
     * ✅ NOUVEAU : Obtenir les détails des commissions d'un utilisateur - DONNÉES RÉELLES
     */
    private function getCommissionDetails($userId)
    {
        $user = User::find($userId);
        if (!$user) return null;

        $commissions = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'user' => $user->name,
            'total_commissions' => $commissions->sum('amount'),
            'count' => $commissions->count(),
            'commissions' => $commissions
        ];
    }

    /**
     * ✅ NOUVEAU : Voir les commissions d'un parrainage spécifique - DONNÉES RÉELLES
     */
    public function viewReferralCommissions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'referral_id' => 'required|exists:users,id',
        ]);

        $commissions = Commission::where('from_user_id', $request->referral_id)
            ->where('user_id', $request->user_id)
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $commissions,
            'total' => $commissions->sum('amount'),
            'count' => $commissions->count()
        ]);
    }
}