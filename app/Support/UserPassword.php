<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class UserPassword
{
    public static function usesLegacyMd5(User $user): bool
    {
        return self::isMd5((string) $user->getAuthPassword());
    }

    public static function verify(User $user, string $plain): bool
    {
        $stored = (string) $user->getAuthPassword();

        if ($stored === '') {
            return false;
        }

        if (self::isBcrypt($stored)) {
            try {
                return Hash::check($plain, $stored);
            } catch (\RuntimeException) {
                return false;
            }
        }

        if (self::isMd5($stored)) {
            return hash_equals(strtolower($stored), md5($plain));
        }

        return false;
    }

    public static function rehashToBcryptIfNeeded(User $user, string $plain): void
    {
        $stored = (string) $user->getAuthPassword();

        if ($stored === '' || self::isBcrypt($stored)) {
            return;
        }

        $user->password = $plain;
        $user->saveQuietly();
    }

    public static function isBcrypt(string $hash): bool
    {
        return preg_match('/^\$2[ayb]\$.{56}$/', $hash) === 1;
    }

    public static function isMd5(string $hash): bool
    {
        return preg_match('/^[a-f0-9]{32}$/i', $hash) === 1;
    }
}
