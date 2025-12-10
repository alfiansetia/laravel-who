<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticated = $request->session()->get('env_authenticated', false);
        $loginTime = $request->session()->get('env_auth_time');
        $sessionHash = $request->session()->get('env_auth_hash');

        // Validasi 1: Cek apakah session ada
        if (!$authenticated || !$loginTime || !$sessionHash) {
            $this->clearSession($request);
            return response()->json(['message' => 'Session expired or unauthorized.'], 401);
        }

        // Validasi 2: Cek apakah hash masih cocok dengan ENV
        $configPassword = config('envauth.password');
        if ($sessionHash !== $configPassword) {
            Log::warning('CheckAuthMiddleware: Hash mismatch, possible session manipulation');
            $this->clearSession($request);
            return response()->json(['message' => 'Invalid session.'], 401);
        }

        // Validasi 3: Cek apakah session sudah expired (24 jam)
        if (now()->diffInHours($loginTime) >= 24) {
            Log::info('CheckAuthMiddleware: Session expired after 24 hours');
            $this->clearSession($request);
            return response()->json(['message' => 'Session expired.'], 401);
        }

        return $next($request);
    }

    /**
     * Clear authentication session
     */
    private function clearSession(Request $request): void
    {
        $request->session()->forget(['env_authenticated', 'env_auth_time', 'env_auth_hash']);
    }
}
