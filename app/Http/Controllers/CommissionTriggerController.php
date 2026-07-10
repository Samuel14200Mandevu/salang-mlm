<?php
// app/Http/Controllers/CommissionTriggerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\Commission;
use App\Services\CommissionService;
use App\Services\RankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionTriggerController extends Controller
{
    /**
     * Déclencher manuellement les commissions pour un utilisateur - DONNÉES RÉELLES
     */
    public function triggerPackageCommission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
        ]);
        
        // ✅ Vérifier que l'utilisateur connecté est admin ou le propriétaire
        if (!Auth::user()->isAdmin() && Auth::id() != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }
        
        try {
            $commissionService = new CommissionService();
            $result = $commissionService->calculatePackageCommission(
                $request->user_id,
                $request->package_id
            );
            
            if ($result) {
                // ✅ Récupérer les commissions créées
                $commissions = Commission::where('user_id', $request->user_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Commissions calculées avec succès',
                    'data' => $commissions
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des commissions'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erreur trigger commissions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recalculer toutes les commissions - ADMIN
     */
    public function recalculateAll(Request $request)
    {
        // ✅ Vérifier que l'utilisateur est admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }
        
        $request->validate([
            'package_id' => 'nullable|exists:packages,id',
        ]);
        
        try {
            $commissionService = new CommissionService();
            $rankService = new RankService();
            
            // Récupérer tous les utilisateurs
            $users = User::whereNotNull('package_id');
            
            if ($request->filled('package_id')) {
                $users->where('package_id', $request->package_id);
            }
            
            $users = $users->get();
            $processed = 0;
            $errors = 0;
            
            foreach ($users as $user) {
                try {
                    $result = $commissionService->calculatePackageCommission(
                        $user->id,
                        $user->package_id
                    );
                    
                    if ($result) {
                        $processed++;
                    } else {
                        $errors++;
                    }
                    
                    // Mettre à jour le rank
                    $rankService->updateRank($user->id);
                    
                } catch (\Exception $e) {
                    Log::error('Erreur recalcul pour user ' . $user->id . ': ' . $e->getMessage());
                    $errors++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Recalcul terminé',
                'processed' => $processed,
                'errors' => $errors,
                'total' => $users->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur recalcul all: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Déclencher les commissions de retail - DONNÉES RÉELLES
     */
    public function triggerRetailCommission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'order_id' => 'nullable|exists:orders,id',
        ]);
        
        try {
            $commissionService = new CommissionService();
            $result = $commissionService->calculateRetailProfit(
                $request->user_id,
                $request->amount,
                null,
                $request->order_id
            );
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commission retail calculée avec succès',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erreur trigger retail: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}