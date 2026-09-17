<?php

namespace App\Services;

/**
 * Single source of truth untuk status login "env auth" (password akses server).
 *
 * Dipakai di: CheckAuthMiddleware, Api\AuthController, dan Blade (nav).
 * Jangan duplikasi logika 24 jam / hash di tempat lain.
 */
class EnvAuth
{
    public const SESSION_KEYS = ['env_authenticated', 'env_auth_time', 'env_auth_hash'];

    public const EXPIRY_HOURS = 24;

    public static function check(): bool
    {
        if (! session()->get('env_authenticated', false)) {
            return false;
        }

        $loginTime = session()->get('env_auth_time');
        if (! $loginTime) {
            return false;
        }

        // Hash session harus cocok dengan password aktif (deteksi rotasi ENV).
        if (session()->get('env_auth_hash') !== config('envauth.password')) {
            static::clear();

            return false;
        }

        if (now()->diffInHours($loginTime) >= self::EXPIRY_HOURS) {
            static::clear();

            return false;
        }

        return true;
    }

    public static function login(): void
    {
        session()->put('env_authenticated', true);
        session()->put('env_auth_time', now());
        session()->put('env_auth_hash', config('envauth.password'));
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEYS);
    }
}
