<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Firebase Push Notification</title>
</head>

<body>

    <h1>Hello World</h1>

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
    </script>
    <script src="https://www.gstatic.com/firebasejs/10.4.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.4.0/firebase-messaging-compat.js"></script>

    <script>
        // Registrasi Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(registration => {
                    console.log("✅ Service Worker terdaftar:", registration);
                })
                .catch(err => {
                    console.log("❌ Service Worker gagal:", err);
                });
        }

        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        // Minta izin notifikasi
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                messaging.getToken().then(token => {
                    console.log("✅ Token FCM:", token);

                    fetch("{{ route('token.store') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                token: token,
                                topic: "general"
                            })
                        }).then(response => response.json())
                        .then(data => console.log("✅ Token berhasil dikirim ke backend:", data))
                        .catch(err => console.error("❌ Error mengirim token:", err));
                }).catch(err => {
                    console.log("❌ Gagal mendapatkan token:", err);
                });
            } else {
                console.log("❌ Izin notifikasi ditolak.");
            }
        });

        // Hilangkan new Notification(), cukup log saja
        messaging.onMessage(payload => {
            console.log("🔔 Notifikasi diterima (foreground):", payload);
            new Notification(payload.notification.title, {
                body: payload.notification.body,
                icon: "images/asa.png",
                vibrate: [200, 100, 200],
            });
        });
    </script>

</body>

</html>
