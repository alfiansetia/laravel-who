// Service Worker gabungan: offline cache + Firebase background push.
// Naikkan SW_VERSION setiap mengubah file ini agar klien update.
// Diregistrasi sebagai /sw.js?v=SW_VERSION dari template Blade, app Blade (SPA),
// dan lib/fcm.js. SATU-SATUNYA SW scope root — jangan daftarkan SW lain.
const SW_VERSION = '3';
const CACHE_NAME = `who-offline-v${SW_VERSION}`;
const PRECACHE = ['/offline.html', '/manifest.json', '/icons/icon-192.png'];

// --- Lifecycle: aktif segera agar versi baru langsung mengendalikan halaman.
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))))
            .then(() => clients.claim()),
    );
});

// --- Offline: hanya GET. API dan non-GET selalu network-only.
self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') {
        return;
    }
    const url = new URL(request.url);
    if (url.pathname.startsWith('/api/')) {
        return;
    }
    // Navigasi: network-first, jatuh ke offline.html saat offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(() => caches.match(request).then((hit) => hit || caches.match('/offline.html'))),
        );
        return;
    }
    // Aset statis: cache-first, fallback network.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname.startsWith('/images/')) {
        event.respondWith(
            caches.match(request).then(
                (hit) => hit || fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    return response;
                }),
            ),
        );
    }
});

// --- Firebase Cloud Messaging (background push) ---
importScripts('https://www.gstatic.com/firebasejs/10.4.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.4.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "{{ config('services.firebase.api_key') }}",
    authDomain: "{{ config('services.firebase.auth_domain') }}",
    projectId: "{{ config('services.firebase.project_id') }}",
    storageBucket: "{{ config('services.firebase.storage_bucket') }}",
    messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
    appId: "{{ config('services.firebase.app_id') }}",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const data = payload.data ?? {};
    const notificationTitle = data.title || 'Notifikasi Baru';
    const notificationOptions = {
        body: data.body || 'Anda memiliki pesan baru',
        icon: data.icon || '/icons/icon-192.png',
        data: {},
    };

    // URL hanya diteruskan kalau server mengirimnya (so_id valid).
    // Tanpa URL, notif jadi non-klik: klik tidak membuka tab baru.
    if (data.url) {
        notificationOptions.data.url = data.url;
    }

    self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : null;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Notif tanpa URL (so_id tidak valid): jangan buka tab baru.
            // Cukup fokuskan tab aplikasi yang sudah terbuka, kalau ada.
            if (!urlToOpen) {
                for (let i = 0; i < windowClients.length; i++) {
                    if ('focus' in windowClients[i]) {
                        return windowClients[i].focus();
                    }
                }
                return;
            }
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        }),
    );
});
