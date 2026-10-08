import api from '@/lib/axios';

const TOKEN_KEY = 'fcm_token';
const ASKED_KEY = 'fcm_perm_asked';
const SW_VERSION = '1';

let initialized = false;

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
    navigator.serviceWorker
        .register(`/firebase-messaging-sw.js?v=${SW_VERSION}`)
        .then((registration) => {
            if (fb.apps.length === 0) {
                fb.initializeApp(config);
            }
            const messaging = fb.messaging();
            messaging.onMessage(handleForegroundMessage);
            void registration;
            if (Notification.permission === 'granted') {
                syncFcmToken(messaging);
            } else if (Notification.permission === 'default' && !localStorage.getItem(ASKED_KEY)) {
                localStorage.setItem(ASKED_KEY, '1');
                Notification.requestPermission().then((permission) => {
                    if (permission === 'granted') {
                        syncFcmToken(messaging);
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
        syncFcmToken(messaging, true);
        return true;
    }
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            syncFcmToken(messaging, true);
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

function syncFcmToken(messaging, force = false) {
    messaging
        .getToken()
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
