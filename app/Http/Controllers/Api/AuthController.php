<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnvAuth;
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
