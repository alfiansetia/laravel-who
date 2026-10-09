import api from '@/lib/axios';

const TOKEN_KEY = 'fcm_token';
const ASKED_KEY = 'fcm_perm_asked';
// Samakan dengan SW_VERSION di app.blade.php + resources/views/sw.blade.php.
const SW_VERSION = '2';

let initialized = false;
let vapidKey = null;

function firebaseApp() {
    if (typeof window.firebase === 'undefined' || typeof window.firebase.messaging === 'undefined') {
        return null;
    }
    return window.firebase;
}

export function fcmToken() {
    return localStorage.getItem(TOKEN_KEY);
}

export function fcmSupported() {
    try {
        return 'Notification' in window && !!firebaseApp()?.messaging.isSupported();
    } catch {
        return false;
    }
}

export function initFcm(config) {
    if (initialized || !('Notification' in window) || !('serviceWorker' in navigator)) {
        return;
    }
    const fb = firebaseApp();
    if (!fb || !fcmSupported()) {
        return;
    }
    initialized = true;
    vapidKey = config.vapidKey || null;
    if (!vapidKey) {
        console.log('FCM: FIREBASE_VAPID_KEY belum diisi — token push tidak akan terbit.');
    }
    // Satu-satunya SW scope root (offline + FCM). Jangan daftarkan SW lain.
    navigator.serviceWorker
        .register(`/sw.js?v=${SW_VERSION}`)
        .then((registration) => {
            if (fb.apps.length === 0) {
                fb.initializeApp(config);
            }
            const messaging = fb.messaging();
            messaging.onMessage(handleForegroundMessage);
            if (Notification.permission === 'granted') {
                syncFcmToken(messaging, false, registration);
            } else if (Notification.permission === 'default' && !localStorage.getItem(ASKED_KEY)) {
                localStorage.setItem(ASKED_KEY, '1');
                Notification.requestPermission().then((permission) => {
                    if (permission === 'granted') {
                        syncFcmToken(messaging, false, registration);
                    }
                });
            }
        })
        .catch((err) => {
            console.log('Service Worker gagal:', err);
        });
}

export function refreshFcmToken() {
    if (!('Notification' in window) || !fcmSupported()) {
        return false;
    }
    const messaging = firebaseApp().messaging();
    if (Notification.permission === 'granted') {
        navigator.serviceWorker.ready.then((registration) => syncFcmToken(messaging, true, registration));
        return true;
    }
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            navigator.serviceWorker.ready.then((registration) => syncFcmToken(messaging, true, registration));
        }
    });
    return true;
}

export function testLocalNotif() {
    new Notification('Notifikasi lokal aktif', {
        body: 'Ini tes notifikasi dari aplikasi.',
        icon: '/images/asa.png',
        vibrate: [200, 100, 200],
    });
}

function syncFcmToken(messaging, force = false, registration = null) {
    // VAPID wajib untuk Web Push (dari shared props `firebase`, backend).
    const options = registration ? { serviceWorkerRegistration: registration } : {};
    if (vapidKey) {
        options.vapidKey = vapidKey;
    }
    messaging
        .getToken(options)
        .then((token) => {
            const cached = localStorage.getItem(TOKEN_KEY);
            if (!force && cached && cached === token) {
                return;
            }
            localStorage.setItem(TOKEN_KEY, token);
            api.post(route('api.tokens.store'), {
                token,
                topic: 'general',
                platform: navigator.platform || 'unknown',
            }).catch((err) => {
                console.log('Gagal mengirim token:', err);
            });
        })
        .catch((err) => {
            console.log('Gagal mendapatkan token:', err);
        });
}

function handleForegroundMessage(payload) {
    const { title, body, icon, url } = payload.data ?? {};
    const notification = new Notification(title, {
        body,
        icon,
        data: { url },
        vibrate: [200, 100, 200],
    });
    notification.onclick = function (event) {
        event.preventDefault();
        window.open(this.data.url, '_blank');
        notification.close();
    };
}
