<script src="https://www.gstatic.com/firebasejs/10.4.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.4.0/firebase-messaging-compat.js"></script>

<script>
    const BASE_URL = "{{ url('/') }}"
    const firebaseConfig = {
        apiKey: "{{ config('services.firebase.api_key') }}",
        authDomain: "{{ config('services.firebase.auth_domain') }}",
        projectId: "{{ config('services.firebase.project_id') }}",
        storageBucket: "{{ config('services.firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
        appId: "{{ config('services.firebase.app_id') }}",
        measurementId: "{{ config('services.firebase.measurement_id') }}"
    };
    const FCM_VAPID_KEY = "{{ config('services.firebase.vapid_key') }}";

    // Naikkan manual setiap mengubah resources/views/sw.blade.php.
    // JANGAN pakai timestamp: URL baru tiap load bikin browser download
    // ulang & reinstall service worker di setiap halaman.
    const FCM_SW_VERSION = '2';
    const FCM_TOKEN_KEY = 'fcm_token';
    const FCM_ASKED_KEY = 'fcm_perm_asked';

    function test_notif() {
        new Notification('Notifikasi masuk.', {
            body: 'Ini tes notifikasi dari aplikasi.',
            icon: "images/asa.png",
            vibrate: [200, 100, 200],
        });
    }

    function handleForegroundMessage(payload) {
        console.log("Notifikasi diterima (foreground):", payload);
        const {
            title,
            body,
            icon,
            so_id,
            url
        } = payload.data;

        const notification = new Notification(title, {
            body,
            icon,
            data: {
                url: url
            },
            vibrate: [200, 100, 200],
        });

        notification.onclick = function(event) {
            event.preventDefault();
            window.open(this.data.url, '_blank');
            notification.close();
        };
    }

    // Kirim token ke backend HANYA kalau belum pernah / berubah.
    // Mencegah updateOrCreate ke DB di setiap page load.
    function syncFcmToken(messaging, force = false, registration = null) {
        // VAPID wajib untuk Web Push (isi FIREBASE_VAPID_KEY di .env).
        const options = registration ? {
            serviceWorkerRegistration: registration
        } : {};
        if (FCM_VAPID_KEY) {
            options.vapidKey = FCM_VAPID_KEY;
        }
        messaging.getToken(options).then(token => {
            const cached = localStorage.getItem(FCM_TOKEN_KEY);
            if (!force && cached && cached === token) {
                return; // sudah terdaftar, skip POST
            }
            localStorage.setItem(FCM_TOKEN_KEY, token);
            fetch("{{ route('api.tokens.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        token: token,
                        topic: "general",
                        platform: navigator.platform || 'unknown',
                    })
                }).then(response => response.json())
                .then(data => console.log("Token berhasil dikirim ke backend:", data))
                .catch(err => console.error("Error mengirim token:", err));
        }).catch(err => {
            console.log("Gagal mendapatkan token:", err);
        });
    }

    // Dipakai halaman setting untuk daftar ulang manual (mis. setelah user
    // mengizinkan notifikasi yang sebelumnya ditolak/diabaikan).
    window.refreshFcmToken = function() {
        if (!('Notification' in window) || !firebase.messaging.isSupported()) {
            return;
        }
        const messaging = firebase.messaging();
        if (Notification.permission === 'granted') {
            syncFcmToken(messaging, true);
            return;
        }
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                syncFcmToken(messaging, true);
            }
        });
    };

    // Init: permission diminta MAKSIMAL sekali per browser. Kalau user menolak
    // ('denied'), diam saja sampai user aktifkan manual via refreshFcmToken().
    (function initNotif() {
        try {
            if (!('Notification' in window)) {
                return;
            }
            if (!('serviceWorker' in navigator)) {
                return;
            }
            if (!firebase.messaging.isSupported()) {
                return;
            }
            // Satu-satunya SW scope root (offline + FCM). Jangan daftarkan SW lain.
            navigator.serviceWorker.register('/sw.js?v=' + FCM_SW_VERSION)
                .then(registration => {
                    console.log("Service Worker terdaftar");
                    firebase.initializeApp(firebaseConfig);
                    const messaging = firebase.messaging();
                    messaging.onMessage(handleForegroundMessage);

                    if (Notification.permission === 'granted') {
                        syncFcmToken(messaging, false, registration);
                    } else if (Notification.permission === 'default' && !localStorage.getItem(FCM_ASKED_KEY)) {
                        localStorage.setItem(FCM_ASKED_KEY, '1');
                        Notification.requestPermission().then(permission => {
                            if (permission === 'granted') {
                                syncFcmToken(messaging, false, registration);
                            }
                        });
                    }
                })
                .catch(err => {
                    console.log("Service Worker gagal:", err);
                });
        } catch (err) {
            console.log("Notifikasi belum siap:", err.message);
        }
    })();
</script>
