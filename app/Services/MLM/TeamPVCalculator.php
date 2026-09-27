<?php

namespace App\Services\MLM;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ══════════════════════════════════════════════════════════════════════
 * SERVICE UNIQUE DE CALCUL DU TEAM_PV / TEAM_BV / TOTAL_TEAM
 * ══════════════════════════════════════════════════════════════════════
 *
 * RÈGLE MÉTIER :
 *   team_pv    = pv_balance + SUM(descendants.pv_balance)
 *   team_bv    = bv_balance + SUM(descendants.bv_balance)
 *   total_team = nombre de descendants actifs (hors soi-même)
 *
 * IMPORTANT : team_pv INCLUT le pv_balance personnel.
 * Conséquence : getCumulPV() = team_pv (ne PAS additionner pv_balance).
 *
 * ✅ SÉCURITÉ ANTI-CYCLE :
 *   Toutes les requêtes récursives utilisent FIND_IN_SET() pour détecter
 *   les cycles et éviter les boucles infinies.
 *
 * Utilisé PARTOUT. Ne JAMAIS dupliquer cette logique ailleurs.
 * ══════════════════════════════════════════════════════════════════════
 */
class TeamPVCalculator
{
    /**
     * Profondeur maximale de récursion (sécurité).
     */
    protected int $maxDepth = 100;

    /**
     * Calcule les totaux pour un utilisateur (personnel + descendants).
     *
     * ✅ Anti-cycle : FIND_IN_SET(u.id, d.path) = 0
     */
    public function calculateForUser(User $user): array
    {
        try {
            $result = DB::select("
                WITH RECURSIVE descendants AS (
                    -- Point de départ : soi-même
                    SELECT 
                        id, 
                        pv_balance, 
                        bv_balance, 
                        0 as depth,
                        CAST(id AS CHAR(2000)) as path
                    FROM users
                    WHERE id = ?
                      AND is_active = true

                    UNION ALL

                    -- Descendants récursifs avec anti-cycle
                    SELECT 
                        u.id, 
                        u.pv_balance, 
                        u.bv_balance, 
                        d.depth + 1,
                        CONCAT(d.path, ',', u.id)
                    FROM users u
                    INNER JOIN descendants d ON u.parrain_id = d.id
                    WHERE u.is_active = true
                      AND d.depth < ?
                      AND FIND_IN_SET(u.id, d.path) = 0
                )
                SELECT
                    COALESCE(SUM(pv_balance), 0) as total_pv,
                    COALESCE(SUM(bv_balance), 0) as total_bv,
                    COUNT(*) as total_members
                FROM descendants
            ", [$user->id, $this->maxDepth]);

        } catch (\Exception $e) {
            Log::error('TeamPVCalculator::calculateForUser - Erreur SQL', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback : au moins retourner le pv_balance personnel
            return [
                'pv' => (float) ($user->pv_balance ?? 0),
                'bv' => (float) ($user->bv_balance ?? 0),
                'total' => 0,
            ];
        }

        if (empty($result)) {
            return [
                'pv' => (float) ($user->pv_balance ?? 0),
                'bv' => (float) ($user->bv_balance ?? 0),
                'total' => 0,
            ];
        }

        $row = $result[0];

        return [
            'pv' => round((float) ($row->total_pv ?? 0), 2),
            'bv' => round((float) ($row->total_bv ?? 0), 2),
            'total' => max(0, ((int) ($row->total_members ?? 1)) - 1),
        ];
    }

    /**
     * Met à jour le team_pv / team_bv / total_team d'un utilisateur en base.
     */
    public function updateUser(User $user): array
    {
        $data = $this->calculateForUser($user);

        $user->team_pv = $data['pv'];
        $user->team_bv = $data['bv'];
        $user->total_team = $data['total'];
        $user->saveQuietly();

        return $data;
    }

    /**
     * Retourne la liste ordonnée des IDs ancêtres (du plus proche au plus lointain).
     *
     * ✅ Anti-cycle : FIND_IN_SET(u.id, a.path) = 0
     */
    public function getAncestorIds(User $user, int $maxDepth = null): array
    {
        $maxDepth = $maxDepth ?? $this->maxDepth;

        try {
            $result = DB::select("
                WITH RECURSIVE ancestors AS (
                    SELECT 
                        id, 
                        parrain_id, 
                        1 as level,
                        CAST(id AS CHAR(2000)) as path
                    FROM users
                    WHERE id = ?

                    UNION ALL

                    SELECT 
                        u.id, 
                        u.parrain_id, 
                        a.level + 1,
                        CONCAT(a.path, ',', u.id)
                    FROM users u
                    INNER JOIN ancestors a ON u.id = a.parrain_id
                    WHERE u.is_active = true
                      AND a.level < ?
                      AND FIND_IN_SET(u.id, a.path) = 0
                )
                SELECT id, level
                FROM ancestors
                WHERE id != ?
                ORDER BY level ASC
            ", [$user->id, $maxDepth, $user->id]);

        } catch (\Exception $e) {
            Log::error('TeamPVCalculator::getAncestorIds - Erreur SQL', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }

        return array_map(fn($row) => (int) $row->id, $result);
    }

    /**
     * Met à jour récursivement tous les ancêtres d'un utilisateur.
     *
     * ✅ Anti-cycle intégré dans la sous-requête.
     */
    public function updateAncestors(User $user, int $maxDepth = null): int
    {
        $maxDepth = $maxDepth ?? $this->maxDepth;
        $ancestorIds = $this->getAncestorIds($user, $maxDepth);

        if (empty($ancestorIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ancestorIds), '?'));

        try {
            DB::statement("
                UPDATE users u
                SET
                    team_pv = (
                        SELECT COALESCE(SUM(d.pv_balance), 0)
                        FROM (
                            WITH RECURSIVE desc AS (
                                SELECT 
                                    id, 
                                    pv_balance, 
                                    1 as depth,
                                    CAST(id AS CHAR(2000)) as path
                                FROM users 
                                WHERE parrain_id = u.id 
                                  AND is_active = true
                                
                                UNION ALL
                                
                                SELECT 
                                    x.id, 
                                    x.pv_balance, 
                                    d.depth + 1,
                                    CONCAT(d.path, ',', x.id)
                                FROM users x
                                INNER JOIN desc d ON x.parrain_id = d.id
                                WHERE x.is_active = true
                                  AND d.depth < ?
                                  AND FIND_IN_SET(x.id, d.path) = 0
                            )
                            SELECT pv_balance FROM desc
                        ) d
                    ) + COALESCE(u.pv_balance, 0),
                    team_bv = (
                        SELECT COALESCE(SUM(d.bv_balance), 0)
                        FROM (
                            WITH RECURSIVE desc AS (
                                SELECT 
                                    id, 
                                    bv_balance, 
                                    1 as depth,
                                    CAST(id AS CHAR(2000)) as path
                                FROM users 
                                WHERE parrain_id = u.id 
                                  AND is_active = true
                                
                                UNION ALL
                                
                                SELECT 
                                    x.id, 
                                    x.bv_balance, 
                                    d.depth + 1,
                                    CONCAT(d.path, ',', x.id)
                                FROM users x
                                INNER JOIN desc d ON x.parrain_id = d.id
                                WHERE x.is_active = true
                                  AND d.depth < ?
                                  AND FIND_IN_SET(x.id, d.path) = 0
                            )
                            SELECT bv_balance FROM desc
                        ) d
                    ) + COALESCE(u.bv_balance, 0),
                    total_team = (
                        SELECT COUNT(*)
                        FROM (
                            WITH RECURSIVE desc AS (
                                SELECT 
                                    id, 
                                    1 as depth,
                                    CAST(id AS CHAR(2000)) as path
                                FROM users 
                                WHERE parrain_id = u.id 
                                  AND is_active = true
                                
                                UNION ALL
                                
                                SELECT 
                                    x.id, 
                                    d.depth + 1,
                                    CONCAT(d.path, ',', x.id)
                                FROM users x
                                INNER JOIN desc d ON x.parrain_id = d.id
                                WHERE x.is_active = true
                                  AND d.depth < ?
                                  AND FIND_IN_SET(x.id, d.path) = 0
                            )
                            SELECT id FROM desc
                        ) d
                    )
                WHERE u.id IN ({$placeholders})
            ", array_merge(
                [$maxDepth, $maxDepth, $maxDepth],
                $ancestorIds
            ));

        } catch (\Exception $e) {
            Log::error('TeamPVCalculator::updateAncestors - Erreur SQL', [
                'user_id' => $user->id,
                'ancestors_count' => count($ancestorIds),
                'error' => $e->getMessage(),
            ]);
            return 0;
        }

        return count($ancestorIds);
    }

    /**
     * Purge le cache lié au team_pv.
     */
    public function clearCache(User $user): void
    {
        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");
        Cache::forget("team_pv_{$user->id}");
    }

    /**
     * Diagnostic : détecte les cycles dans la hiérarchie.
     * Utile avant un import massif.
     */
    public function detectCycles(): array
    {
        try {
            $cycles = DB::select("
                SELECT 
                    u1.id as user_id,
                    u1.name as user_name,
                    u1.parrain_id,
                    u2.id as parrain_real_id,
                    u2.name as parrain_name
                FROM users u1
                INNER JOIN users u2 ON u1.parrain_id = u2.id
                WHERE u2.parrain_id = u1.id
                   OR u1.id = u1.parrain_id
                LIMIT 100
            ");

            return array_map(fn($row) => [
                'user_id' => $row->user_id,
                'user_name' => $row->user_name,
                'parrain_id' => $row->parrain_id,
                'parrain_name' => $row->parrain_name,
            ], $cycles);

        } catch (\Exception $e) {
            Log::error('TeamPVCalculator::detectCycles - Erreur', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Diagnostic : détecte les orphelins (parrain_id inexistant).
     */
    public function detectOrphans(): array
    {
        try {
            $orphans = DB::select("
                SELECT u.id, u.name, u.parrain_id, u.sponsor_id
                FROM users u
                LEFT JOIN users p ON u.parrain_id = p.id
                WHERE u.parrain_id IS NOT NULL 
                  AND p.id IS NULL
                LIMIT 100
            ");

            return array_map(fn($row) => [
                'user_id' => $row->id,
                'user_name' => $row->name,
                'parrain_id' => $row->parrain_id,
                'sponsor_id' => $row->sponsor_id,
            ], $orphans);

        } catch (\Exception $e) {
            Log::error('TeamPVCalculator::detectOrphans - Erreur', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}