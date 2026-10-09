<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/asa.png') }}">
    <meta name="theme-color" content="#e3f2fd" />
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="WHO" />
    <title inertia>{{ config('app.name', 'ASA WHO') }}</title>
    <script src="https://www.gstatic.com/firebasejs/10.4.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.4.0/firebase-messaging-compat.js"></script>
    <script>
        // Naikkan setiap mengubah resources/views/sw.blade.php agar klien update.
        const SW_VERSION = '2';
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(`/sw.js?v=${SW_VERSION}`).then(
                    () => console.log('Service worker PWA terdaftar'),
                    (error) => console.error(`Service worker PWA gagal: ${error}`),
                );
            });
        }
        // Tangkap install prompt untuk tombol Install di AppLayout (Vue).
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.__pwaInstallPrompt = e;
            window.dispatchEvent(new CustomEvent('pwa:installable'));
        });
        window.addEventListener('appinstalled', () => {
            window.__pwaInstallPrompt = null;
            window.dispatchEvent(new CustomEvent('pwa:installed'));
        });
    </script>
    @routes
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @inertiaHead
</head>
<body class="bg-background text-foreground antialiased">
    @inertia
</body>
</html>
