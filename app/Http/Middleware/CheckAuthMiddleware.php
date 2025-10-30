<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        if (!$authenticated || !$loginTime || now()->diffInHours($loginTime) >= 24) {
            $request->session()->forget(['env_authenticated', 'env_auth_time']);
            return response()->json(['message' => 'Session expired or unauthorized.'], 401);
        }

        return $next($request);
    }
}
