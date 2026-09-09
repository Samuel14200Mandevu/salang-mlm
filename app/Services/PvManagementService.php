<?php

// app/Services/PvManagementService.php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\PvAllocation;
use App\Models\UserPvBalance;
use App\Models\PvTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PvManagementService
{
    /**
     * Ajouter des PV à un membre suite à une vente
     * Les PV sont ajoutés UNIQUEMENT dans user_pv_balances
     * Ils seront synchronisés vers users UNIQUEMENT après distribution + approbation
     */
    public function addPvFromSale(User $user, Order $order, array $pvData): bool
    {
        try {
            DB::beginTransaction();

            $totalPv = $pvData['pv'] ?? 0;
            $totalBv = $pvData['bv'] ?? 0;

            if ($totalPv <= 0) {
                return true;
            }

            // ============================================================
            // 1. UNIQUEMENT user_pv_balances
            //    PAS de mise à jour de users ici !
            // ============================================================
            $balance = UserPvBalance::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'total_pv' => 0,
                    'allocated_pv' => 0,
                    'pending_pv' => 0,
                    'available_pv' => 0,
                    'total_bv' => 0,
                    'allocated_bv' => 0,
                    'pending_bv' => 0,
                    'available_bv' => 0,
                ]
            );

            $balance->total_pv += $totalPv;
            $balance->available_pv += $totalPv;  // Disponibles pour distribution
            
            if ($totalBv > 0) {
                $balance->total_bv += $totalBv;
                $balance->available_bv += $totalBv;
            }
            
            $balance->last_updated_at = now();
            $balance->save();

            // ============================================================
            // 2. Enregistrer la transaction
            // ============================================================
            PvTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'type' => 'credit',
                'source' => 'sale',
                'pv_amount' => $totalPv,
                'bv_amount' => $totalBv,
                'description' => "PV crédités suite à la vente #{$order->order_number} (en attente de distribution)",
                'metadata' => [
                    'order_number' => $order->order_number,
                    'order_total' => $order->total,
                    'buyer_id' => $order->user_id,
                    'buyer_name' => $order->user?->name,
                    'status' => 'available_for_distribution',
                ]
            ]);

            DB::commit();
            
            Log::info('PV ajoutés à user_pv_balances (en attente de distribution)', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'pv_added' => $totalPv,
                'available_pv' => $balance->available_pv,
            ]);
            
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout PV: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Distribuer les PV d'un membre vers son réseau
     */
    public function distributePv(User $distributor, array $distributions): array
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            DB::beginTransaction();

            $distributorBalance = UserPvBalance::where('user_id', $distributor->id)->first();
            
            if (!$distributorBalance) {
                throw new \Exception('Membre sans solde PV');
            }

            $totalPvToAllocate = array_sum(array_column($distributions, 'pv_amount'));
            $totalBvToAllocate = array_sum(array_column($distributions, 'bv_amount'));

            // Vérifier que le distributeur a assez de PV disponibles
            if ($distributorBalance->available_pv < $totalPvToAllocate) {
                throw new \Exception("PV disponibles insuffisants. Disponible: {$distributorBalance->available_pv}, Demandé: {$totalPvToAllocate}");
            }

            // Pour chaque distribution
            foreach ($distributions as $distribution) {
                $recipient = User::find($distribution['user_id']);
                
                if (!$recipient) {
                    $result['failed']++;
                    $result['errors'][] = "Destinataire introuvable: ID {$distribution['user_id']}";
                    continue;
                }

                $pvAmount = $distribution['pv_amount'] ?? 0;
                $bvAmount = $distribution['bv_amount'] ?? 0;

                if ($pvAmount <= 0) {
                    $result['failed']++;
                    $result['errors'][] = "PV invalide pour {$recipient->name}";
                    continue;
                }

                // Vérifier que le destinataire fait partie du réseau du distributeur
                if (!$this->isInNetwork($distributor, $recipient)) {
                    $result['failed']++;
                    $result['errors'][] = "{$recipient->name} ne fait pas partie de votre réseau";
                    continue;
                }

                // Créer l'allocation
                $allocation = PvAllocation::create([
                    'order_id' => null,
                    'user_id' => $recipient->id,
                    'distributor_id' => $distributor->id,
                    'pv_amount' => $pvAmount,
                    'bv_amount' => $bvAmount,
                    'distribution_type' => 'manual',
                    'notes' => $distribution['notes'] ?? null,
                    'status' => 'pending',
                    'allocated_at' => now(),
                ]);

                // Déduire des PV disponibles du distributeur
                $distributorBalance->available_pv -= $pvAmount;
                $distributorBalance->allocated_pv += $pvAmount;
                
                if ($bvAmount > 0) {
                    $distributorBalance->available_bv -= $bvAmount;
                    $distributorBalance->allocated_bv += $bvAmount;
                }
                $distributorBalance->save();

                // Ajouter les PV en attente chez le destinataire
                $recipientBalance = UserPvBalance::firstOrCreate(
                    ['user_id' => $recipient->id],
                    [
                        'total_pv' => 0,
                        'allocated_pv' => 0,
                        'pending_pv' => 0,
                        'available_pv' => 0,
                        'total_bv' => 0,
                        'allocated_bv' => 0,
                        'pending_bv' => 0,
                        'available_bv' => 0,
                    ]
                );
                $recipientBalance->total_pv += $pvAmount;
                $recipientBalance->pending_pv += $pvAmount;
                
                if ($bvAmount > 0) {
                    $recipientBalance->total_bv += $bvAmount;
                    $recipientBalance->pending_bv += $bvAmount;
                }
                $recipientBalance->save();

                // Créer une transaction pour le distributeur (débit)
                PvTransaction::create([
                    'user_id' => $distributor->id,
                    'pv_allocation_id' => $allocation->id,
                    'type' => 'debit',
                    'source' => 'distribution',
                    'pv_amount' => $pvAmount,
                    'bv_amount' => $bvAmount,
                    'description' => "PV distribués à {$recipient->name}",
                    'metadata' => [
                        'recipient_id' => $recipient->id,
                        'recipient_name' => $recipient->name,
                        'status' => 'pending_approval',
                    ]
                ]);

                // Créer une transaction pour le destinataire (crédit - en attente)
                PvTransaction::create([
                    'user_id' => $recipient->id,
                    'pv_allocation_id' => $allocation->id,
                    'type' => 'credit',
                    'source' => 'distribution',
                    'pv_amount' => $pvAmount,
                    'bv_amount' => $bvAmount,
                    'description' => "PV reçus de {$distributor->name} (en attente d'approbation)",
                    'metadata' => [
                        'distributor_id' => $distributor->id,
                        'distributor_name' => $distributor->name,
                        'status' => 'pending_approval',
                    ]
                ]);

                $result['success']++;
            }

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur distribution PV: ' . $e->getMessage());
            $result['failed']++;
            $result['errors'][] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Vérifier si un membre est dans le réseau d'un autre
     */
    private function isInNetwork(User $distributor, User $recipient): bool
    {
        // Vérifier si le destinataire est un parrainé direct ou indirect
        $current = $recipient;
        $depth = 0;
        $maxDepth = 10;

        while ($current->sponsor_id && $depth < $maxDepth) {
            if ($current->sponsor_id == $distributor->id) {
                return true;
            }
            $current = User::find($current->sponsor_id);
            $depth++;
        }

        return false;
    }

    /**
     * Approuver une allocation de PV
     * C'est ICI que les PV sont synchronisés vers users
     *  FILLEUL → pv_balance +, monthly_pv +
     *  PARRAIN → team_pv +
     *  ANCÊTRES → team_pv +
     */
    public function approveAllocation(PvAllocation $allocation, User $approver): bool
    {
        try {
            DB::beginTransaction();

            // ============================================================
            // 1. Mettre à jour le statut de l'allocation
            // ============================================================
            $allocation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $approver->id,
            ]);

            // ============================================================
            // 2. Récupérer le filleul et le parrain
            // ============================================================
            $filleul = User::find($allocation->user_id);
            $parrain = User::find($allocation->distributor_id);
            
            if (!$filleul || !$parrain) {
                throw new \Exception('Destinataire ou distributeur introuvable');
            }

            // ============================================================
            // 3. Mettre à jour user_pv_balances (filleul)
            // ============================================================
            $filleulBalance = UserPvBalance::where('user_id', $filleul->id)->first();
            if ($filleulBalance) {
                $filleulBalance->available_pv += $allocation->pv_amount;
                $filleulBalance->pending_pv -= $allocation->pv_amount;
                
                if ($allocation->bv_amount > 0) {
                    $filleulBalance->available_bv += $allocation->bv_amount;
                    $filleulBalance->pending_bv -= $allocation->bv_amount;
                }
                $filleulBalance->save();
            }

            // ============================================================
            // 4.  SYNCHRONISATION VERS users (FILLEUL)
            //    C'EST ICI QUE LES PV VONT DANS monthly_pv ET pv_balance
            // ============================================================
            $filleul->increment('pv_balance', $allocation->pv_amount);
            $filleul->increment('monthly_pv', $allocation->pv_amount);
            
            if ($allocation->bv_amount > 0) {
                $filleul->increment('bv_balance', $allocation->bv_amount);
                $filleul->increment('monthly_bv', $allocation->bv_amount);
            }

            Log::info(' SYNCHRONISATION FILLEUL → users', [
                'filleul_id' => $filleul->id,
                'filleul_name' => $filleul->name,
                'pv_balance' => $filleul->pv_balance,
                'monthly_pv' => $filleul->monthly_pv,
            ]);

            // ============================================================
            // 5.  SYNCHRONISATION VERS users (PARRAIN)
            //    C'EST ICI QUE LES PV VONT DANS team_pv
            // ============================================================
            $parrain->increment('team_pv', $allocation->pv_amount);
            
            if ($allocation->bv_amount > 0) {
                $parrain->increment('team_bv', $allocation->bv_amount);
            }

            Log::info(' SYNCHRONISATION PARRAIN → users', [
                'parrain_id' => $parrain->id,
                'parrain_name' => $parrain->name,
                'team_pv' => $parrain->team_pv,
            ]);

            // ============================================================
            // 6.  SYNCHRONISATION VERS users (ANCÊTRES)
            //    TOUS LES ANCÊTRES GAGNENT EN team_pv
            // ============================================================
            $current = $filleul->parrain;
            $depth = 0;
            $processed = [];

            while ($current && $depth < 9 && !in_array($current->id, $processed)) {
                $processed[] = $current->id;
                
                $current->increment('team_pv', $allocation->pv_amount);
                
                if ($allocation->bv_amount > 0) {
                    $current->increment('team_bv', $allocation->bv_amount);
                }
                
                Log::info(' SYNCHRONISATION ANCÊTRE → users', [
                    'ancestor_id' => $current->id,
                    'ancestor_name' => $current->name,
                    'team_pv' => $current->team_pv,
                    'depth' => $depth + 1,
                ]);
                
                if (method_exists($current, 'calculateAndUpdateRank')) {
                    $current->calculateAndUpdateRank();
                }
                
                $current = $current->parrain;
                $depth++;
            }

            // ============================================================
            // 7. RECALCULER LE GRADE DU FILLEUL
            // ============================================================
            if (method_exists($filleul, 'calculateAndUpdateRank')) {
                $filleul->calculateAndUpdateRank();
            }

            // ============================================================
            // 8. RECALCULER LE GRADE DU PARRAIN
            // ============================================================
            if (method_exists($parrain, 'calculateAndUpdateRank')) {
                $parrain->calculateAndUpdateRank();
            }

            // ============================================================
            // 9. Mettre à jour la transaction du filleul
            // ============================================================
            PvTransaction::where('pv_allocation_id', $allocation->id)
                ->where('user_id', $filleul->id)
                ->update([
                    'description' => "PV reçus de {$parrain->name} (approuvé) - ajoutés au monthly_pv",
                    'metadata' => [
                        'approved_at' => now(),
                        'approved_by' => $approver->id,
                        'approved_by_name' => $approver->name,
                        'added_to_monthly_pv' => true,
                    ]
                ]);

            // ============================================================
            // 10. Nettoyer le cache
            // ============================================================
            $this->clearUserCache($filleul);
            $this->clearUserCache($parrain);
            foreach ($processed as $id) {
                $this->clearUserCache(User::find($id));
            }

            DB::commit();
            
            Log::info(' Allocation approuvée et synchronisée', [
                'allocation_id' => $allocation->id,
                'pv_amount' => $allocation->pv_amount,
                'filleul_monthly_pv' => $filleul->monthly_pv,
                'parrain_team_pv' => $parrain->team_pv,
                'ancestors_updated' => count($processed),
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur approbation allocation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Traiter l'approbation d'une allocation
     */
    private function processAllocationApproval(PvAllocation $allocation): void
    {
        // Cette méthode peut être étendue pour gérer des cas spécifiques
        // Par exemple, mettre à jour les commissions, les bonus, etc.
        
        Log::info("Allocation PV approuvée", [
            'allocation_id' => $allocation->id,
            'recipient' => $allocation->user_id,
            'distributor' => $allocation->distributor_id,
            'pv_amount' => $allocation->pv_amount,
        ]);
    }
}