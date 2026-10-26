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

    /**
     * Couleurs d’accent extraites des insignes PNG (public/images/grades).
     * Priorité au gemme / métal du badge, pas aux pastels dashboard génériques.
     *
     * @return array<int, string>
     */
    public static function insigniaAccents(): array
    {
        return [
            0 => '#64748b', // Membre — neutre
            1 => '#8f5c3a', // Distributeur — bronze
            2 => '#355376', // Qualification — acier bleu
            3 => '#9a6b1f', // Cumul Directeur — or ambré
            4 => '#86262d', // Directeur — rubis
            5 => '#305428', // Manager Senior — vert forêt
            6 => '#3d5f5a', // Directeur Envolée — teal
            7 => '#1e3485', // Saphire Manager — saphir
            8 => '#2b5f9b', // Diamant Bleu
            9 => '#8a6e52', // Perle Diamant — champagne
        ];
    }

    /** Couleur d’accent grade (insigne PNG, repli config/dashboard-levels.php) */
    public static function dashboardAccent(?int $level): string
    {
        $level = max(0, min(9, (int) ($level ?? 1)));
        $fromInsignia = self::insigniaAccents()[$level] ?? null;
        if ($fromInsignia) {
            return $fromInsignia;
        }

        $config = config('dashboard-levels.'.$level) ?? config('dashboard-levels.1');

        return $config['gradient'] ?? '#1e5dad';
    }

    /**
     * Fichier image du grade (public/images/grades), sans fond.
     *
     * @return array<int, string>
     */
    public static function levelImageFiles(): array
    {
        return [
            1 => 'distributeur.png',
            2 => 'qualification.png',
            3 => 'cumul directeur.png',
            4 => 'directeur.png',
            5 => 'manager senior.png',
            6 => "directeur de l'envole.png",
            7 => 'saphire manager.png',
            8 => 'diamant bleu.png',
            9 => 'diamant perle.png',
        ];
    }

    /** URL publique de l’insigne grade, ou null si absent / niveau 0. */
    public static function imageUrl(?int $level): ?string
    {
        $level = (int) ($level ?? 0);
        $file = self::levelImageFiles()[$level] ?? null;
        if (! $file) {
            return null;
        }

        $relative = 'images/grades/'.$file;
        if (! is_file(public_path($relative))) {
            return null;
        }

        return asset($relative);
    }
}
