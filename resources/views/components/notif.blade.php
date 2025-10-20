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

    if (!("Notification" in window)) {
        console.log("Browser tidak mendukung notifikasi.");
        danger('❌ Browser tidak mendukung notifikasi. 😓, Ganti browser cuy!.')
    } else {
        if (Notification.permission === "granted") {
            success('✅ Izin Notifikasi sudah ok. 😁👍')
            console.log("permission ok");
        } else {
            reqPermission()
        }
    }

    function reqPermission() {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                success('✅ Izin Notifikasi sudah ok. 😁👍')
            } else {
                console.log("Izin notifikasi ditolak.");
                danger('❌ Izin notifikasi ditolak 😓, Izinin dong Woi!.')
            }
        });
    }



    function test_notif() {
        new Notification('✅ Dah Masuk niii. 😁👍', {
            body: 'Ini Tes Notif dari saye...',
            icon: "images/asa.png",
            vibrate: [200, 100, 200],
        });
    }
    // Registrasi Service Worker
    try {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(registration => {
                    console.log("✅ Service Worker terdaftar");
                    // console.log("✅ Service Worker terdaftar:", registration);
                    // success('✅ Notifikasi sudah siap. 😁👍')
                })
                .catch(err => {
                    console.log("❌ Service Worker gagal:", err);
                    danger('❌ Notifikasi belum siap 😓, Tolong Refresh halaman!.')
                });
        }
        if (firebase.messaging.isSupported()) {

            firebase.initializeApp(firebaseConfig);
            const messaging = firebase.messaging();

            // Minta izin notifikasi
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    messaging.getToken().then(token => {
                        console.log("✅ Token FCM:", token);
                        localStorage.setItem('fcm_token', token);
                        success('✅ Notifikasi sudah siap. 😁👍')
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
                        danger('❌ Gagal Atur Notifikasi 😓')
                    });
                } else {
                    console.log("❌ Izin notifikasi ditolak.");
                    danger('❌ Izin notifikasi ditolak 😓, Izinin dong Woi!.')
                }
            });

            messaging.onMessage(payload => {
                console.log("🔔 Notifikasi diterima (foreground):", payload);
                const {
                    title,
                    body,
                    icon
                } = payload.data;

                new Notification(title, {
                    body,
                    icon,
                    vibrate: [200, 100, 200],
                });
            });
            success('✅ Notifikasi sudah siap. 😁👍')
        } else {
            danger('❌ Browser gak support notif 😓')

        }

    } catch (err) {
        danger('❌ Notifikasi belum siap 😓, ' + err.message)
    }
</script>
