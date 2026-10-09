<?php

namespace App\Http\Middleware;

use App\Services\EnvAuth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! EnvAuth::check()) {
            Log::info('CheckAuthMiddleware: unauthenticated or expired session');

            // Jika request dari API, return JSON
            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Session expired or unauthorized.'], 401);
            }

            // Ingat halaman tujuan agar habis login bisa kembali otomatis
            // (mis. dibuka lagi /settings, bukan nyangkut di /home).
            if ($request->isMethodCacheable()) {
                session()->put('url.intended', $request->fullUrl());
            }

            // Jika request dari web, redirect ke home dengan pesan
            return redirect()->route('home')->with('error', 'Anda harus login terlebih dahulu untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
