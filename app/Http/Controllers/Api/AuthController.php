<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $configPassword = config('envauth.password');

        if (Hash::check($request->password, $configPassword)) {
            $request->session()->put('env_authenticated', true);
            $request->session()->put('env_auth_time', now());
            $request->session()->put('env_auth_hash', $configPassword); // Simpan hash untuk validasi

            return $this->sendResponse([
                'auth'          => true,
                'expires_in'    => '24 hours'
            ], 'Authenticated successfully.');
        }

        return $this->sendError('Invalid password.', 401);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['env_authenticated', 'env_auth_time', 'env_auth_hash']);

        return $this->sendResponse([
            'auth' => false
        ], 'Logged out successfully.');
    }

    public function status(Request $request)
    {
        $authenticated = $request->session()->get('env_authenticated', false);
        $loginTime = $request->session()->get('env_auth_time');

        // kalau sudah login tapi lewat 1 hari, hapus session otomatis
        if ($authenticated && $loginTime && now()->diffInHours($loginTime) >= 24) {
            $request->session()->forget(['env_authenticated', 'env_auth_time', 'env_auth_hash']);
            $authenticated = false;
        }

        return $this->sendResponse(
            [
                'auth'          => $authenticated,
                'login_at'      => $loginTime,
                'expires_in'    => '24 hours'
            ],
            $authenticated ? 'Session active.' : 'Session expired or not logged in.'
        );
    }
}
