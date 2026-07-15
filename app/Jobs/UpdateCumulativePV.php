<?php
// app/Jobs/UpdateCumulativePV.php

namespace App\Jobs;

use App\Models\User;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCumulativePV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        Log::info('Starting cumulative PV update', [
            'user_id' => $this->userId ?? 'all'
        ]);

        try {
            $query = User::where('is_active', true);

            if ($this->userId) {
                $query->where('id', $this->userId);
            }

            $updated = 0;
            $errors = [];

            // ✅ Réinitialiser les PV cumulés à 0 pour recalculer
            // Ne pas réinitialiser si on veut juste ajouter les nouveaux achats
            $query->chunk(100, function ($users) use (&$updated, &$errors) {
                foreach ($users as $user) {
                    try {
                        DB::beginTransaction();

                        // ✅ Calculer le PV total depuis toutes les commandes
                        $totalPV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('orders.user_id', $user->id)
                            ->where('orders.payment_status', 'completed')
                            ->sum('order_items.pv_value');

                        $totalBV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('orders.user_id', $user->id)
                            ->where('orders.payment_status', 'completed')
                            ->sum('order_items.bv_value');

                        // ✅ Mettre à jour les PV cumulés
                        $user->pv_balance = (int) $totalPV;
                        $user->bv_balance = (int) $totalBV;
                        $user->save();

                        // ✅ Mettre à jour les PV d'équipe pour tous les parrains
                        $this->updateTeamPV($user);

                        DB::commit();
                        $updated++;

                    } catch (\Exception $e) {
                        DB::rollBack();
                        $errors[] = "User {$user->id}: " . $e->getMessage();
                        Log::error('Error updating cumulative PV', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('Cumulative PV update completed', [
                'users_updated' => $updated,
                'errors' => count($errors)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating cumulative PV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Met à jour les PV d'équipe pour tous les parrains
     */
    private function updateTeamPV(User $user): void
    {
        // ✅ Récupérer tous les descendants
        $descendants = $this->getAllDescendants($user);
        
        // ✅ Calculer le PV total de l'équipe
        $teamPV = 0;
        $teamBV = 0;
        
        foreach ($descendants as $descendant) {
            $teamPV += $descendant->pv_balance;
            $teamBV += $descendant->bv_balance;
        }

        // ✅ Mettre à jour l'utilisateur
        $user->team_pv = $teamPV;
        $user->team_bv = $teamBV;
        $user->total_team = count($descendants);
        $user->save();

        // ✅ Mettre à jour récursivement les ancêtres
        $current = $user->parrain;
        $level = 1;
        $maxLevel = 9;

        while ($current && $level <= $maxLevel) {
            // Recalculer l'équipe de l'ancêtre
            $descendantsOfAncestor = $this->getAllDescendants($current);
            $teamPVAncestor = 0;
            $teamBVAncestor = 0;
            
            foreach ($descendantsOfAncestor as $descendant) {
                $teamPVAncestor += $descendant->pv_balance;
                $teamBVAncestor += $descendant->bv_balance;
            }

            $current->team_pv = $teamPVAncestor;
            $current->team_bv = $teamBVAncestor;
            $current->total_team = count($descendantsOfAncestor);
            $current->save();

            $current = $current->parrain;
            $level++;
        }
    }

    /**
     * Récupère tous les descendants d'un utilisateur
     */
private function getAllDescendants(User $user): array
{
    $cacheKey = "descendants_{$user->id}";
    
    return Cache::remember($cacheKey, 3600, function () use ($user) {
        $descendants = [];
        $stack = [$user];
        $processed = [];
        
        while (!empty($stack)) {
            $current = array_pop($stack);
            
            if (in_array($current->id, $processed)) {
                continue;
            }
            
            $processed[] = $current->id;
            
            $children = User::where('parrain_id', $current->id)
                ->where('is_active', true)
                ->get();
            
            foreach ($children as $child) {
                $descendants[] = $child;
                $stack[] = $child;
            }
        }
        
        return $descendants;
    });
}
}