<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/css/all.min.css"
        integrity="sha512-gMjQeDaELJ0ryCI+FtItusU9MkAifCZcGq789FrzkiM49D8lbDhoaUaIX4ASU187wofMNlgBJ4ckbrXM9sE6Pg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap4.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap4.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.5.0/css/select.bootstrap4.css" />
    @stack('css')
    <title>{{ $title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
</head>


<body>
    @include('components.nav')
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/js/all.min.js"
        integrity="sha512-jAu66pqHWWQ564NS+m2Zxe13Yek98R7JWNjQLzW+PQ4i2jsMxBT1nGrQ0gFUIVJ4kPkEFe5gelBWNEDTBqmn/w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.js">
    </script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap4.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.print.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.0/js/responsive.bootstrap4.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/select/1.5.0/js/dataTables.select.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"
        integrity="sha512-42PE0rd+wZ2hNXftlM78BSehIGzezNeQuzihiBCvUEB3CVxHvsShF86wBWwQORNxNINlBPuq7rG4WWhNiTVHFg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/block-ui@2.70.1/jquery.blockUI.min.js"></script>
    @stack('js')

    @yield('content')

    <div class="modal fade" id="modal_env" tabindex="-1" aria-labelledby="modal_envLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_envLabel">ODOO SESSION</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <textarea class="form-control" id="odoo_env" placeholder="ODOO SESSION">{{ $setting->odoo_session ?? '' }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="btn_modal_env" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

</body>

@if (session()->has('message'))
    <script>
        alert("{{ session('message') }}")
    </script>
@endif
<script>
    function bloc() {
        $.blockUI({
            message: '<img src="{{ asset('images/loading.gif') }}" width="20px" height="20px" /> Just a moment...'
        });
    }

    $(document).ready(function() {
        $(document).ajaxStart(function() {
            $.blockUI({
                message: '<img src="{{ asset('images/loading.gif') }}" width="20px" height="20px" /> Just a moment...'
            });
        }).ajaxStop($.unblockUI);
        bsCustomFileInput.init()

        $('#btn_modal_env').click(function() {
            let env_value = $('#odoo_env').val()
            if (env_value == '') {
                alert('Cannot empty!')
                return
            }
            $.ajax({
                type: 'POST',
                url: "{{ route('api.setting.env') }}",
                data: {
                    env_value: env_value
                }
            }).done(function(result) {
                $('#modal_env').modal('hide')
                alert(result.message)
            }).fail(function(xhr) {
                let message = xhr.responseJSON.message || 'Error!'
                alert(message)
            })
        })
    })

    function multiCheck(tb_var) {
        tb_var.on("change", ".chk-parent", function() {
                var e = $(this).closest("table").find("td:first-child .child-chk"),
                    a = $(this).is(":checked");
                $(e).each(function() {
                    a ? ($(this).prop("checked", !0), $(this).closest("tr").addClass("active")) : ($(this)
                        .prop("checked", !1), $(this).closest("tr").removeClass("active"))
                })
            }),
            tb_var.on("change", "tbody tr .new-control", function() {
                $(this).parents("tr").toggleClass("active")
            })
    }
</script>

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


</html>
