<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

/**
 * Passwords of the old DeepskyLog: md5(htmlentities($password)), 32 hex digits.
 *
 * users:rehash-md5-passwords wraps the remaining ones in bcrypt (bcrypt of the
 * MD5 hash), so no plain MD5 hash stays in the database; check() accepts both
 * forms. The login replaces either with a normal hash of the password.
 */
class LegacyPassword
{
    public static function isMd5(?string $hash): bool
    {
        return $hash !== null && preg_match('/^[0-9a-f]{32}$/i', $hash) === 1;
    }

    public static function md5(string $password): string
    {
        return md5(htmlentities($password));
    }

    /**
     * Whether $password matches a stored MD5 hash, plain or wrapped in bcrypt.
     */
    public static function check(?string $password, ?string $hash): bool
    {
        if ($password === null || $password === '' || $hash === null || $hash === '') {
            return false;
        }

        if (self::isMd5($hash)) {
            return hash_equals(strtolower($hash), self::md5($password));
        }

        return self::isWrapped($hash) && Hash::check(self::md5($password), $hash);
    }

    public static function wrap(string $md5): string
    {
        return Hash::make(strtolower($md5));
    }

    private static function isWrapped(string $hash): bool
    {
        return (password_get_info($hash)['algoName'] ?? null) === 'bcrypt';
    }
}
