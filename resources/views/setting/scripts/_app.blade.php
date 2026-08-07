<script>
    const URL_INDEX_API = "{{ route('api.settings.index') }}";
    const currentToken = localStorage.getItem('fcm_token');

    $(document).ready(function() {
        getData();
        getResource();

        // ── Helper Functions ─────────────────────────────

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        function showToast(message, type) {
            let toast = document.querySelector('.toast-copy');
            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'toast-copy';
                document.body.appendChild(toast);
            }
            const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
            toast.innerHTML = `<i class="fas ${icon} mr-2"></i>${message}`;
            toast.classList.add('show');
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        window.showToast = showToast;

        // ── Odoo Session ─────────────────────────────────

        function getData() {
            $.ajax({
                url: URL_INDEX_API,
                type: 'GET',
                success: function(res) {
                    $('#odoo_env').val(res.data.session_id);
                    $('#odoo_session_username').val(res.data.username);
                    $('#odoo_session_name').val(`${res.data.name} (${res.data.uid})`);
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Error!', 'error');
                }
            });
        }

        $('#btn_refresh').click(function() {
            getData();
        });

        $('#form').submit(function(e) {
            e.preventDefault();
            let odoo_env = $('#odoo_env').val();
            $.ajax({
                url: URL_INDEX_API,
                type: 'POST',
                data: { env_value: odoo_env },
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Error!', 'error');
                }
            });
        });

        $('#btn_fix').click(function() {
            $.ajax({
                url: URL_INDEX_API,
                type: 'PUT',
                success: function(res) {
                    getData();
                    showToast(res.message, 'success');
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Error!', 'error');
                }
            });
        });

        $('#btn_notif').click(function() {
            $.ajax({
                url: URL_INDEX_API,
                type: 'DELETE',
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Error!', 'error');
                }
            });
        });

        $('#btn_cek_odoo').click(function() {
            $.ajax({
                url: "{{ route('api.settings.cek_odoo') }}",
                type: "GET",
                success: function(res) {
                    let formatted = JSON.stringify(res, null, 2);
                    $('#jsonResponse').text(formatted);
                    $('#modal_cek').modal('show');
                },
                error: function(xhr) {
                    let formatted = JSON.stringify(xhr.responseJSON || {}, null, 2);
                    $('#jsonResponse').text(formatted);
                    $('#modal_cek').modal('show');
                    showToast(xhr.responseJSON?.message || 'Error!', 'error');
                }
            });
        });

        $('#btn_logout').click(function() {
            confirmation('Are you sure want to logout?', function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: "{{ route('auth.logout') }}",
                        type: "POST",
                        success: function() {
                            window.location.href = "{{ route('home') }}";
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || 'Error!', 'error');
                        }
                    });
                }
            });
        });

        // ── Resource ─────────────────────────────────────

        $('#resource_refresh').click(function() {
            getResource();
        });

        function getResource() {
            $.ajax({
                url: "{{ route('api.resources.index') }}",
                type: 'GET',
                beforeSend: function() {
                    $('#resource-body').html(
                        '<p class="text-muted mb-0"><i class="fas fa-spinner fa-spin mr-1"></i>Loading data...</p>'
                    );
                },
                success: function(res) {
                    const data = res.data;
                    let html = `
                        <div class="resource-item">
                            <div>
                                <p class="resource-label mb-0">Products</p>
                                <span class="resource-value">${data.products.value.toLocaleString()} bytes</span>
                            </div>
                            <span class="resource-badge primary">${data.products.parse}</span>
                        </div>
                        <div class="resource-item">
                            <div>
                                <p class="resource-label mb-0">Logs</p>
                                <span class="resource-value">${data.logs.value.toLocaleString()} bytes</span>
                            </div>
                            <span class="resource-badge secondary">${data.logs.parse}</span>
                        </div>
                    `;
                    $('#log').val(res.data.logs.content);
                    $('#resource-body').html(html);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal mengambil data resource';
                    $('#resource-body').html(`<p class="text-danger mb-0"><i class="fas fa-exclamation-triangle mr-1"></i>${message}</p>`);
                }
            });
        }

        // ── Logs ─────────────────────────────────────────

        $('#btn_clear_log').click(function() {
            confirmation('Clear Log?', function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: "{{ route('api.resources.destroy_log') }}",
                        type: "DELETE",
                        success: function(res) {
                            getResource();
                            showToast(res.message, 'success');
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || 'Error!', 'error');
                        }
                    });
                }
            });
        });

        // ── Devices DataTable (Server-Side) ──────────────

        var table = $('#table').DataTable({
            rowId: 'id',
            serverSide: true,
            processing: true,
            ajax: {
                url: "{{ route('api.tokens.index') }}",
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Gagal memuat data device!';
                    showToast(message, 'error');
                },
            },
            dom: "<'d-none'B>t<'setting-footer d-flex justify-content-between align-items-center py-2 px-3'lip>",
            paging: true,
            lengthChange: true,
            oLanguage: {
                "sSearchPlaceholder": "Cari device...",
                "sLengthMenu": "Tampilkan _MENU_ data",
                "sInfo": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "sInfoEmpty": "Tidak ada data",
                "sInfoFiltered": "(disaring dari _MAX_ total data)",
                "sZeroRecords": "Data tidak ditemukan",
                "sEmptyTable": "Tidak ada data tersedia",
                "processing": '<i class="fas fa-spinner fa-spin mr-1"></i>Memuat data...',
            },
            lengthMenu: [
                [10, 25, 50, 100],
                ['10', '25', '50', '100']
            ],
            order: [
                [0, 'asc']
            ],
            pageLength: 10,
            columns: [{
                data: "platform",
                className: 'text-center',
                render: function(data) {
                    if (!data) return '<span class="text-muted">-</span>';
                    return `<span class="device-badge platform">${escapeHtml(data)}</span>`;
                }
            }, {
                data: "user_agent",
                className: 'text-left',
                render: function(data) {
                    if (!data) return '<span class="text-muted">-</span>';
                    let text = data.length > 40 ? data.substr(0, 40) + '...' : data;
                    return `<span title="${escapeHtml(data)}">${escapeHtml(text)}</span>`;
                }
            }, {
                data: "ip",
                className: 'text-left',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            }, {
                data: "token",
                className: 'text-left',
                render: function(data) {
                    if (!data) return '<span class="text-muted">-</span>';
                    let shortToken = data.length > 30 ? data.substr(0, 30) + '...' : data;
                    let badge = '';
                    if (currentToken && data.trim() === currentToken.trim()) {
                        badge = '<span class="device-badge current mr-1">Device Ini</span>';
                    }
                    return `${badge}<span title="${escapeHtml(data)}">${escapeHtml(shortToken)}</span>`;
                }
            }, {
                data: "last_status",
                className: 'text-left',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-muted">-</span>';
                    return `<span title="${escapeHtml(row.last_status_at || '')}">${escapeHtml(data)}</span>`;
                }
            }, {
                data: "id",
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<button type="button" class="btn btn-action btn-outline-primary btn-detail" data-id="${row.id}" title="Detail"><i class="fas fa-info-circle"></i></button>`;
                }
            }],
            buttons: [{
                extend: 'copy',
                exportOptions: { columns: ':visible' }
            }, {
                extend: 'csv',
                exportOptions: { columns: ':visible' }
            }, {
                extend: 'excel',
                exportOptions: { columns: ':visible' }
            }],
        });

        // ── Refresh Devices ──────────────────────────────

        $('#refresh_devices').click(function() {
            table.ajax.reload();
        });

        // ── Device Detail ────────────────────────────────

        $('#table tbody').on('click', '.btn-detail', function(e) {
            e.stopPropagation();
            var data = table.row($(this).parents('tr')).data();
            $('#detail_platform').val(data.platform || '');
            $('#detail_ip').val(data.ip || '');
            $('#detail_user_agent').val(data.user_agent || '');
            $('#detail_token').val(data.token || '');
            $('#detail_last_status').val(data.last_status || '');
            $('#detail_last_status_at').val(data.last_status_at || '');
            if (currentToken && data.token && data.token.trim() === currentToken.trim()) {
                $('#token_status').show();
            } else {
                $('#token_status').hide();
            }
            $('#modal_detail').modal('show');
        });
    });
</script>
