<?php

use App\Http\Middleware\CheckAuthMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Percayai header X-Forwarded-* dari TLS-terminating proxy agar
        // request()->secure() dan url()/route() menghasilkan https (anti mixed content).
        $middleware->trustProxies(at: '*');

        // Grup stateful untuk seluruh route api.php (butuh session cookie:
        // auth + controller ber-middleware env_auth). Tanpa CSRF agar
        // kompatibel dengan AJAX Blade lama yang tidak mengirim X-CSRF-TOKEN.
        // JANGAN append EncryptCookies/StartSession secara global: web group
        // bawaan Laravel sudah memilikinya, dobel = cookie terdekripsi 2x
        // (jadi null) dan session selalu baru tiap request web.
        $middleware->group('stateful', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);

        $middleware->alias([
            'env_auth' => CheckAuthMiddleware::class,
        ]);

        // Rate-limit default: lindungi api + auth dari brute-force/polling agresif.
        $middleware->throttleApi();

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Jangan bocorkan pesan internal ke klien di prod; log penuh di server.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && app()->isProduction()) {
                $status = $e instanceof HttpExceptionInterface
                    ? $e->getStatusCode()
                    : 500;

                if ($status === 500) {
                    Log::error('Unhandled exception', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile().':'.$e->getLine(),
                    ]);

                    return response()->json([
                        'data' => null,
                        'message' => 'Terjadi kesalahan. Silakan coba lagi.',
                    ], 500);
                }
            }

            return null;
        });
    })->create();
