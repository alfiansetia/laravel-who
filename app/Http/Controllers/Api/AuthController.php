<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnvAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $configPassword = config('envauth.password');

        if (empty($configPassword)) {
            Log::error('Auth verify failed: ADMIN_PASS is not set.');

            return $this->sendError('Server password is not configured.', 500);
        }

        $authenticated = false;

        try {
            // ADMIN_PASS normalnya berisi hash bcrypt (diawali $2y$).
            // Kalau di server terisi plaintext / algoritma lain (argon),
            // Hash::check akan melempar RuntimeException ("does not use
            // the Bcrypt algorithm") — tangani agar jadi 401, bukan 500.
            if (Hash::isHashed($configPassword)) {
                $authenticated = Hash::check($request->password, $configPassword);
            } else {
                Log::warning('ADMIN_PASS is not a hashed value. Falling back to plain comparison.');
                $authenticated = hash_equals((string) $configPassword, (string) $request->password);
            }
        } catch (\RuntimeException $e) {
            Log::warning('Auth Hash::check failed, fallback to password_verify: '.$e->getMessage());
            // password_verify native mendukung semua algoritma (bcrypt/argon),
            // tanpa validasi driver seperti BcryptHasher.
            $authenticated = password_verify((string) $request->password, (string) $configPassword);
        }

        if ($authenticated) {
            EnvAuth::login();
            return $this->sendResponse([
                'auth'          => true,
                'expires_in'    => '24 hours'
            ], 'Authenticated successfully.');
        }

        return $this->sendError('Invalid password.', 401);
    }

    public function logout(Request $request)
    {
        EnvAuth::clear();

        return $this->sendResponse([
            'auth' => false
        ], 'Logged out successfully.');
    }

    public function status(Request $request)
    {
        // Satu logika dengan middleware (termasuk cek hash & expiry 24 jam).
        $authenticated = EnvAuth::check();

        return $this->sendResponse(
            [
                'auth'          => $authenticated,
                'login_at'      => session()->get('env_auth_time'),
                'expires_in'    => '24 hours'
            ],
            $authenticated ? 'Session active.' : 'Session expired or not logged in.'
        );
    }
}
