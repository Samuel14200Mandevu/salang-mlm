<?php

namespace App\Services;

use App\Models\User;
use App\Models\Genealogy;
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

        $sponsorId = $user->sponsor_id;

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
        if ($level <= 0) {
            return 0;
        }

        $ids = User::where('sponsor_id', $sponsorId)->pluck('sponsor_id');
        
        for ($i = 1; $i < $level; $i++) {
            $ids = User::whereIn('sponsor_id', $ids)->pluck('sponsor_id');
        }

        return User::whereIn('sponsor_id', $ids)->count();
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

        $children = User::where('sponsor_id', $user->sponsor_id)->get();
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

        $sponsorId = $user->sponsor_id;
        $ids = [$sponsorId];

        for ($i = 0; $i < $level; $i++) {
            $ids = User::whereIn('sponsor_id', $ids)->pluck('sponsor_id')->toArray();
        }

        return User::whereIn('sponsor_id', $ids)->get();
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