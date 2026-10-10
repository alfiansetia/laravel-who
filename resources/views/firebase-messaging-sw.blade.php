importScripts('https://www.gstatic.com/firebasejs/10.4.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.4.0/firebase-messaging-compat.js');

// Inisialisasi Firebase di Service Worker
firebase.initializeApp({
    apiKey: "{{ config('services.firebase.api_key') }}",
    authDomain: "{{ config('services.firebase.auth_domain') }}",
    projectId: "{{ config('services.firebase.project_id') }}",
    storageBucket: "{{ config('services.firebase.storage_bucket') }}",
    messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
    appId: "{{ config('services.firebase.app_id') }}",
});

const messaging = firebase.messaging();

// Tangani pesan saat aplikasi di background
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const notificationTitle = payload.data.title || "Notifikasi Baru";
    const notificationOptions = {
        body: payload.data.body || "Anda memiliki pesan baru",
        icon: payload.data.icon || "/images/asa.png",
        data: {}
    };

    // URL hanya diteruskan kalau server mengirimnya (so_id valid).
    // Tanpa URL, notif jadi non-klik: klik tidak membuka tab baru.
    if (payload.data.url) {
        notificationOptions.data.url = payload.data.url;
    }

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Tangani klik pada notifikasi
self.addEventListener("notificationclick", (event) => {
    console.log('[firebase-messaging-sw.js] Notification click Received.');
    
    event.notification.close();

    // Ambil URL dari event.notification.data yang kita set di atas.
    // Notif tanpa URL (so_id tidak valid): jangan buka tab baru,
    // cukup fokuskan tab aplikasi yang sudah terbuka, kalau ada.
    const urlToOpen = event.notification.data && event.notification.data.url 
                        ? event.notification.data.url 
                        : null;

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((windowClients) => {
            if (!urlToOpen) {
                for (let i = 0; i < windowClients.length; i++) {
                    if ('focus' in windowClients[i]) {
                        return windowClients[i].focus();
                    }
                }
                return;
            }
            // Cek jika ada tab yang sudah membuka URL tersebut, maka fokuskan
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // Jika tidak ada tab yang cocok, buka tab baru
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Force immediate activation of the new service worker version
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});
