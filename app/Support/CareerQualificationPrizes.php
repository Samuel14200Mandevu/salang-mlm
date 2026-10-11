<?php

namespace App\Support;

use App\Models\User;

class CareerQualificationPrizes
{
    /** @return array<int, array<string, mixed>> */
    public static function levels(): array
    {
        return config('career_qualification_prizes.levels', []);
    }

    /** @return array<string, array{label: string, short: string}> */
    public static function walletBonusTypes(): array
    {
        return config('career_qualification_prizes.wallet_bonus_types', []);
    }

    /**
     * Niveaux carrière pour l’UI portefeuille (grade, récompense, image, débloqué).
     *
     * @return list<array{level: int, rank_name: string, reward: string, image_url: ?string, unlocked: bool}>
     */
    public static function forWallet(User $user): array
    {
        $currentLevel = max(0, (int) ($user->rank_level ?? $user->rank?->level ?? 0));
        $items = [];

        foreach (self::levels() as $level => $data) {
            $level = (int) $level;
            $imagePath = $data['image'] ?? null;
            $imageUrl = null;
            if ($imagePath) {
                $absolute = public_path($imagePath);
                $imageUrl = asset($imagePath);
                if (is_file($absolute)) {
                    $imageUrl .= '?v='.filemtime($absolute);
                }
            }

            $items[] = [
                'level' => $level,
                'rank_name' => MlmRank::label($level),
                'reward' => $data['reward'] ?? '',
                'image_url' => $imageUrl,
                'unlocked' => $currentLevel >= $level,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{level: int, prize: string}>
     */
    public static function earnedPrizesList(User $user): array
    {
        $currentLevel = max(0, (int) ($user->rank_level ?? $user->rank?->level ?? 0));
        $prizes = [];

        foreach (self::levels() as $level => $data) {
            $level = (int) $level;
            if ($currentLevel >= $level) {
                $prizes[] = [
                    'level' => $level,
                    'prize' => $data['reward'] ?? '',
                ];
            }
        }

        return $prizes;
    }
}
