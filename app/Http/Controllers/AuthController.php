<?php

namespace App\Http\Controllers;

use App\Services\EnvAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function verify(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $configPassword = config('envauth.password');

        if (empty($configPassword)) {
            Log::error('Auth verify failed: ADMIN_PASS is not set.');

            if ($request->wantsJson()) {
                return $this->sendError('Server password is not configured.', 500);
            }

            return back()->with('error', 'Server password is not configured.');
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

            // Kembalikan URL tujuan yang disimpan middleware saat redirect.
            $intended = $this->sanitizedIntended($request);
            session()->forget('url.intended');

            if ($request->wantsJson()) {
                return $this->sendResponse([
                    'auth' => true,
                    'expires_in' => '24 hours',
                    'intended' => $intended,
                ], 'Authenticated successfully.');
            }

            return redirect($intended ?? route('home'))->with('success', 'Authenticated successfully.');
        }

        if ($request->wantsJson()) {
            return $this->sendError('Invalid password.', 401);
        }

        return back()->with('error', 'Invalid password.');
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        EnvAuth::clear();

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'auth' => false,
            ], 'Logged out successfully.');
        }

        return back()->with('success', 'Logged out successfully.');
    }

    public function status(Request $request): JsonResponse|RedirectResponse
    {
        // Satu logika dengan middleware (termasuk cek hash & expiry 24 jam).
        $authenticated = EnvAuth::check();

        if ($request->wantsJson()) {
            return $this->sendResponse(
                [
                    'auth' => $authenticated,
                    'login_at' => session()->get('env_auth_time'),
                    'expires_in' => '24 hours',
                ],
                $authenticated ? 'Session active.' : 'Session expired or not logged in.'
            );
        }

        return back();
    }

    /**
     * Ambil URL tujuan dari session dalam bentuk path lokal saja.
     * Middleware menyimpan fullUrl() absolut, jadi terima host yang sama
     * dengan host request dan tolak URL luar (open redirect).
     */
    private function sanitizedIntended(Request $request): ?string
    {
        $intended = session()->get('url.intended');
        if (! is_string($intended) || $intended === '') {
            return null;
        }

        $parts = parse_url($intended);
        if (! is_array($parts)) {
            return null;
        }

        $host = $parts['host'] ?? null;
        if (is_string($host) && $host !== '' && strtolower($host) !== strtolower($request->getHost())) {
            return null;
        }

        $scheme = $parts['scheme'] ?? null;
        if (is_string($scheme) && $scheme !== '' && ! in_array(strtolower($scheme), ['http', 'https'], true)) {
            return null;
        }

        $path = $parts['path'] ?? null;
        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        $query = $parts['query'] ?? null;

        return $path.((is_string($query) && $query !== '') ? '?'.$query : '');
    }
}
