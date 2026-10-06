<?php

namespace App\Support;

class MlmRank
{
    /** @return array<int, string> */
    public static function levelNames(): array
    {
        return [
            0 => 'Membre',
            1 => 'Distributeur',
            2 => 'Qualification',
            3 => 'Cumul Directeur',
            4 => 'Directeur',
            5 => 'Manager Senior',
            6 => 'Directeur Envolée',
            7 => 'Saphire Manager',
            8 => 'Diamant Bleu',
            9 => 'Perle Diamant',
        ];
    }

    public static function label(?int $level): string
    {
        $level = (int) ($level ?? 0);
        $names = self::levelNames();

        return $names[$level] ?? ('Niveau ' . $level);
    }

    /** Classes badge-level-0 … badge-level-9 (couleurs dans dark-surfaces.css) */
    public static function badgeClass(?int $level): string
    {
        $level = max(0, min(9, (int) ($level ?? 0)));

        return 'badge-level badge-level-' . $level;
    }
}
