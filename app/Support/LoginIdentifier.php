<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Résout un ou plusieurs membres à partir d'un identifiant de connexion :
 * email, code membre (sponsor_id) ou téléphone.
 */
final class LoginIdentifier
{
    /**
     * @return Collection<int, User>
     */
    public static function findUsers(string $raw): Collection
    {
        $login = trim($raw);

        if ($login === '') {
            return collect();
        }

        if (str_contains($login, '@')) {
            $user = User::query()
                ->where('email', Str::lower($login))
                ->first();

            return $user ? collect([$user]) : collect();
        }

        $bySponsor = User::query()
            ->where('sponsor_id', $login)
            ->first();

        if ($bySponsor) {
            return collect([$bySponsor]);
        }

        return self::findUsersByPhone($login);
    }

    public static function findUser(string $raw): ?User
    {
        $users = self::findUsers($raw);

        return $users->count() === 1 ? $users->first() : null;
    }

    /**
     * @return Collection<int, User>
     */
    private static function findUsersByPhone(string $login): Collection
    {
        $byPhoneExact = User::query()
            ->where('phone', $login)
            ->get();

        $digits = self::digitsOnly($login);

        if (strlen($digits) < 8) {
            return $byPhoneExact->values();
        }

        $tail = substr($digits, -3);
        $candidates = User::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('phone', 'like', '%'.$tail.'%')
            ->limit(50)
            ->get();

        $matches = $candidates->filter(function (User $user) use ($digits) {
            $phoneDigits = self::digitsOnly((string) $user->phone);

            return $phoneDigits !== ''
                && ($phoneDigits === $digits
                    || str_ends_with($phoneDigits, $digits)
                    || str_ends_with($digits, $phoneDigits));
        });

        $exact = $matches->filter(
            fn (User $user) => self::digitsOnly((string) $user->phone) === $digits
        );

        $resolved = $exact->isNotEmpty() ? $exact : $matches;

        return $resolved
            ->merge($byPhoneExact)
            ->unique('id')
            ->values();
    }

    public static function digitsOnly(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function maskEmail(?string $email): string
    {
        $email = trim((string) $email);

        if ($email === '' || ! str_contains($email, '@')) {
            return '—';
        }

        [$local, $domain] = explode('@', $email, 2);
        $keep = min(2, strlen($local));
        $prefix = substr($local, 0, $keep);
        $stars = str_repeat('•', max(3, strlen($local) - $keep));

        return $prefix.$stars.'@'.$domain;
    }
}
