{{-- Modal cek & tes notifikasi. Dibuka dari ikon lonceng di navbar. --}}
<div class="modal fade" id="notifModal" tabindex="-1" role="dialog" aria-labelledby="notifModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notifModalLabel">
                    <i class="fas fa-bell mr-1 text-primary"></i>Notifikasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-key mr-2 text-muted"></i>Izin notifikasi</span>
                        <span id="notif_status_permission" class="badge badge-secondary">...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-mobile-alt mr-2 text-muted"></i>Dukungan browser</span>
                        <span id="notif_status_support" class="badge badge-secondary">...</span>
                    </li>
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fingerprint mr-2 text-muted"></i>Token perangkat ini</span>
                            <span id="notif_status_token" class="badge badge-secondary">...</span>
                        </div>
                        <small id="notif_status_token_value" class="form-text text-muted text-truncate d-block" style="max-width: 100%;"></small>
                    </li>
                </ul>

                <div id="notif_test_result"></div>

                <div class="d-flex flex-wrap" style="gap: 6px;">
                    <button type="button" id="btn_notif_enable" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-bell mr-1"></i>Aktifkan / Daftar Ulang
                    </button>
                    <button type="button" id="btn_notif_local" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-comment-dots mr-1"></i>Tes Lokal
                    </button>
                    <button type="button" id="btn_notif_fcm" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-paper-plane mr-1"></i>Tes FCM ke Perangkat Ini
                    </button>
                    @if (\App\Services\EnvAuth::check())
                        <button type="button" id="btn_notif_broadcast" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-bullhorn mr-1"></i>Tes ke Semua Perangkat
                        </button>
                    @endif
                </div>
                <small class="form-text text-muted mt-2">
                    Tes Lokal tampil langsung tanpa server. Tes FCM mengirim push sungguhan lewat server ke perangkat ini.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function notifModalSetBadge(el, text, color) {
        el.textContent = text;
        el.className = 'badge badge-' + color;
    }

    function notifModalResult(html) {
        $('#notif_test_result').html(html);
    }

    function notifModalRefreshStatus() {
        const badgePermission = document.getElementById('notif_status_permission');
        const badgeSupport = document.getElementById('notif_status_support');
        const badgeToken = document.getElementById('notif_status_token');
        const tokenValue = document.getElementById('notif_status_token_value');

        if (!('Notification' in window)) {
            notifModalSetBadge(badgePermission, 'Tidak didukung', 'danger');
            notifModalSetBadge(badgeSupport, 'Tidak', 'danger');
            notifModalSetBadge(badgeToken, 'Belum ada', 'secondary');
            tokenValue.textContent = '';
            return;
        }

        const permission = Notification.permission;
        if (permission === 'granted') {
            notifModalSetBadge(badgePermission, 'Aktif', 'success');
        } else if (permission === 'denied') {
            notifModalSetBadge(badgePermission, 'Ditolak', 'danger');
        } else {
            notifModalSetBadge(badgePermission, 'Belum diminta', 'warning');
        }

        let supported = false;
        try {
            supported = firebase.messaging.isSupported();
        } catch (e) {
            supported = false;
        }
        notifModalSetBadge(badgeSupport, supported ? 'Ya' : 'Tidak', supported ? 'success' : 'danger');

        const token = localStorage.getItem('fcm_token');
        if (token) {
            notifModalSetBadge(badgeToken, 'Terdaftar', 'success');
            tokenValue.textContent = token.substr(0, 40) + (token.length > 40 ? '...' : '');
            tokenValue.title = token;
        } else {
            notifModalSetBadge(badgeToken, 'Belum ada', 'secondary');
            tokenValue.textContent = 'Klik "Aktifkan / Daftar Ulang" untuk mendaftarkan perangkat ini.';
        }
    }

    $(document).ready(function() {
        $('#notifModal').on('show.bs.modal', function() {
            notifModalResult('');
            notifModalRefreshStatus();
        });

        $('#btn_notif_enable').click(function() {
            if (!('Notification' in window)) {
                notifModalResult('<div class="alert alert-danger py-2">Browser tidak mendukung notifikasi.</div>');
                return;
            }
            if (typeof window.refreshFcmToken !== 'function') {
                notifModalResult('<div class="alert alert-danger py-2">Modul notifikasi belum siap, coba lagi sebentar.</div>');
                return;
            }
            window.refreshFcmToken();
            notifModalResult('<div class="alert alert-info py-2">Meminta izin / mendaftarkan ulang... status diperbarui otomatis.</div>');
            setTimeout(notifModalRefreshStatus, 1500);
            setTimeout(notifModalRefreshStatus, 4000);
        });

        $('#btn_notif_local').click(function() {
            if (!('Notification' in window)) {
                notifModalResult('<div class="alert alert-danger py-2">Browser tidak mendukung notifikasi.</div>');
                return;
            }
            if (Notification.permission !== 'granted') {
                notifModalResult('<div class="alert alert-warning py-2">Izin belum diberikan. Klik "Aktifkan / Daftar Ulang" dulu.</div>');
                return;
            }
            try {
                test_notif();
                notifModalResult('<div class="alert alert-success py-2">Notif lokal ditampilkan. Tidak muncul? Cek izin notifikasi di browser/OS.</div>');
            } catch (e) {
                notifModalResult('<div class="alert alert-danger py-2">Gagal: ' + e.message + '</div>');
            }
        });

        $('#btn_notif_fcm').click(function() {
            const token = localStorage.getItem('fcm_token');
            if (!token) {
                notifModalResult('<div class="alert alert-warning py-2">Perangkat belum terdaftar. Klik "Aktifkan / Daftar Ulang" dulu.</div>');
                return;
            }
            const btn = $(this);
            btn.prop('disabled', true);
            notifModalResult('<div class="alert alert-info py-2"><i class="fas fa-spinner fa-spin mr-1"></i>Mengirim test via server...</div>');
            fetch("{{ route('api.tokens.test') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token })
                })
                .then(response => response.json().then(data => ({ status: response.status, data: data })))
                .then(({ status, data }) => {
                    if (status >= 200 && status < 300) {
                        notifModalResult('<div class="alert alert-success py-2">' + (data.message || 'Terkirim!') + ' Tunggu beberapa detik, notif akan masuk.</div>');
                    } else {
                        notifModalResult('<div class="alert alert-danger py-2">Gagal: ' + (data.message || 'unknown') + '</div>');
                    }
                })
                .catch(err => {
                    notifModalResult('<div class="alert alert-danger py-2">Gagal: ' + err.message + '</div>');
                })
                .finally(() => btn.prop('disabled', false));
        });

        @if (\App\Services\EnvAuth::check())
            $('#btn_notif_broadcast').click(function() {
                const btn = $(this);
                btn.prop('disabled', true);
                notifModalResult('<div class="alert alert-info py-2"><i class="fas fa-spinner fa-spin mr-1"></i>Mengirim test ke semua perangkat...</div>');
                $.ajax({
                    url: "{{ route('api.settings.test_notif') }}",
                    type: 'DELETE',
                    success: function(res) {
                        notifModalResult('<div class="alert alert-success py-2">' + (res.message || 'Terkirim ke semua perangkat!') + '</div>');
                    },
                    error: function(xhr) {
                        notifModalResult('<div class="alert alert-danger py-2">Gagal: ' + (xhr.responseJSON?.message || 'Error!') + '</div>');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
        @endif
    });
</script>
