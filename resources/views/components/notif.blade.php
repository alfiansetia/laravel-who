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

    // Naikkan manual setiap mengubah file firebase-messaging-sw.
    // JANGAN pakai timestamp: URL baru tiap load bikin browser download
    // ulang & reinstall service worker di setiap halaman.
    const FCM_SW_VERSION = '1';
    const FCM_TOKEN_KEY = 'fcm_token';
    const FCM_ASKED_KEY = 'fcm_perm_asked';

    function test_notif() {
        new Notification('✅ Dah Masuk niii. 😁👍', {
            body: 'Ini Tes Notif dari saye...',
            icon: "images/asa.png",
            vibrate: [200, 100, 200],
        });
    }

    function handleForegroundMessage(payload) {
        console.log("🔔 Notifikasi diterima (foreground):", payload);
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
    function syncFcmToken(messaging, force = false) {
        messaging.getToken().then(token => {
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
                .then(data => console.log("✅ Token berhasil dikirim ke backend:", data))
                .catch(err => console.error("❌ Error mengirim token:", err));
        }).catch(err => {
            console.log("❌ Gagal mendapatkan token:", err);
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
            navigator.serviceWorker.register('/firebase-messaging-sw.js?v=' + FCM_SW_VERSION)
                .then(registration => {
                    console.log("✅ Service Worker terdaftar");
                    firebase.initializeApp(firebaseConfig);
                    const messaging = firebase.messaging();
                    messaging.onMessage(handleForegroundMessage);

                    if (Notification.permission === 'granted') {
                        syncFcmToken(messaging);
                    } else if (Notification.permission === 'default' && !localStorage.getItem(FCM_ASKED_KEY)) {
                        localStorage.setItem(FCM_ASKED_KEY, '1');
                        Notification.requestPermission().then(permission => {
                            if (permission === 'granted') {
                                syncFcmToken(messaging);
                            }
                        });
                    }
                })
                .catch(err => {
                    console.log("❌ Service Worker gagal:", err);
                });
        } catch (err) {
            console.log("❌ Notifikasi belum siap:", err.message);
        }
    })();
</script>
