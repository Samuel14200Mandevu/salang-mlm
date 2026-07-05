<?php
// app/Services/NetworkService.php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class NetworkService
{
    /**
     * Calculer les statistiques du réseau pour un utilisateur
     */
    public function getNetworkStats($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $sponsorId = $user->id; // ✅ CORRECTION : le sponsor c'est l'utilisateur lui-même

        return [
            'total' => User::where('sponsor_id', $sponsorId)->count(),
            'level_1' => User::where('sponsor_id', $sponsorId)->count(),
            'level_2' => $this->countLevel($sponsorId, 2),
            'level_3' => $this->countLevel($sponsorId, 3),
            'active' => User::where('sponsor_id', $sponsorId)
                ->where('is_active', true)
                ->count(),
            'inactive' => User::where('sponsor_id', $sponsorId)
                ->where('is_active', false)
                ->count(),
        ];
    }

    /**
     * Compter les utilisateurs à un niveau donné
     */
    private function countLevel($sponsorId, $level)
    {
        if ($level <= 0 || !$sponsorId) {
            return 0;
        }

        $currentIds = [$sponsorId];
        
        for ($i = 0; $i < $level; $i++) {
            $currentIds = User::whereIn('sponsor_id', $currentIds)->pluck('id')->toArray();
            if (empty($currentIds)) {
                return 0;
            }
        }

        return User::whereIn('sponsor_id', $currentIds)->count();
    }

    /**
     * Obtenir l'arbre généalogique complet
     */
    public function getTree($userId, $maxLevel = 5)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return $this->buildTreeRecursive($user, 0, $maxLevel);
    }

    /**
     * Construire l'arbre récursivement
     */
    private function buildTreeRecursive($user, $level, $maxLevel)
    {
        if ($level > $maxLevel) {
            return null;
        }

        $children = User::where('sponsor_id', $user->id)->get(); // ✅ CORRECTION
        $tree = [
            'user' => $user,
            'level' => $level,
            'children' => [],
        ];

        foreach ($children as $child) {
            $tree['children'][] = $this->buildTreeRecursive($child, $level + 1, $maxLevel);
        }

        return $tree;
    }

    /**
     * Obtenir les downlines par niveau
     */
    public function getDownlinesByLevel($userId, $level)
    {
        $user = User::find($userId);
        if (!$user || $level <= 0) {
            return collect();
        }

        $currentIds = [$user->id]; // ✅ CORRECTION
        
        for ($i = 0; $i < $level; $i++) {
            $currentIds = User::whereIn('sponsor_id', $currentIds)->pluck('id')->toArray();
            if (empty($currentIds)) {
                return collect();
            }
        }

        return User::whereIn('sponsor_id', $currentIds)->get();
    }

    /**
     * Obtenir les commissions du réseau
     */
    public function getNetworkCommissions($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return collect();
        }

        return \App\Models\Commission::where('user_id', $userId)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}