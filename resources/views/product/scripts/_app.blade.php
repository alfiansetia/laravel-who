<script>
    $(document).ready(function() {
        const API_URL = "{{ route('api.products.index') }}";
        const API_SHOW_URL = "{{ route('api.products.show', ':id') }}";
        const API_MOVE_URL = "{{ route('api.products.move', ':id') }}";
        const SYNC_URL = "{{ route('api.products.sync') }}";
        let currentId = 0;

        // ── Main DataTable ───────────────────────────────
        const table = $('#table').DataTable({
            rowId: 'id',
            ajax: {
                url: API_URL,
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Gagal memuat data product!';
                    $('.dt-empty').text(message);
                    $('#table_processing').hide();
                    showToast(message, 'error');
                },
            },
            dom: "<'d-none'B>t<'product-footer d-flex justify-content-between align-items-center py-2 px-3'lip>",
            paging: true,
            lengthChange: true,
            oLanguage: {
                "sSearchPlaceholder": "Cari kode atau nama...",
                "sLengthMenu": "Tampilkan _MENU_ data",
                "sInfo": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "sInfoEmpty": "Tidak ada data",
                "sInfoFiltered": "(disaring dari _MAX_ total data)",
                "sZeroRecords": "Data tidak ditemukan",
                "sEmptyTable": "Tidak ada data tersedia",
            },
            lengthMenu: [
                [10, 25, 50, 100, -1],
                ['10', '25', '50', '100', 'Semua']
            ],
            order: [
                [1, 'asc']
            ],
            pageLength: 10,
            columns: [{
                data: 'id',
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-action btn-outline-info btn-info-product" data-id="${row.id}" title="Detail">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-outline-primary btn-move" data-id="${row.id}" title="Move">
                                <i class="fas fa-arrows-alt-v"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-outline-success btn-copy" data-code="${escapeHtml(row.code)}" data-name="${escapeHtml(row.name)}" title="Copy">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>`;
                }
            }, {
                data: "code",
                className: "text-left font-weight-bold",
            }, {
                data: "name",
                className: "text-left",
            }, {
                data: "akl",
                className: "text-left",
            }, {
                data: "akl_exp",
                className: "text-left",
                render: function(data, type, row) {
                    if (!data) return '-';
                    if (type === 'display') {
                        const d = new Date(data);
                        const now = new Date();
                        const diff = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
                        const cls = diff >= 0 ? 'badge-exp-valid' : 'badge-exp-expired';
                        return `<span class="badge ${cls}">${data}</span>`;
                    }
                    return data;
                }
            }, {
                data: "desc",
                className: "text-left",
                visible: false,
            }, ],
            createdRow: function(row, data) {
                $(row).attr('title', `${data.code} - ${data.name}`);
            },
            buttons: [{
                extend: 'copy',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'csv',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'excel',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'pdf',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'print',
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });

        // ── Search Input → DataTable Search ──────────────
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // ── Refresh Button ───────────────────────────────
        $('#btnRefresh').on('click', function() {
            table.ajax.reload();
        });

        // ── ColVis Dropdown ──────────────────────────────
        function buildColvisMenu() {
            const $menu = $('#colvisMenu');
            $menu.empty();
            table.columns().every(function(index) {
                const col = this;
                const header = $(col.header()).text().trim();
                if (!header) return;
                const visible = col.visible();
                $menu.append(`
                    <label class="dropdown-item mb-0" style="cursor:pointer;">
                        <input type="checkbox" class="colvis-toggle" data-col="${index}" ${visible ? 'checked' : ''}>
                        ${escapeHtml(header)}
                    </label>
                `);
            });
        }
        buildColvisMenu();

        $(document).on('change', '.colvis-toggle', function() {
            const colIdx = $(this).data('col');
            const isChecked = $(this).is(':checked');
            table.column(colIdx).visible(isChecked);
        });

        $('#btnColvisDropdown').on('show.bs.dropdown', function() {
            buildColvisMenu();
        });

        // ── Export Dropdown Handlers ─────────────────────
        $('#exportCopy').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-copy').trigger();
        });
        $('#exportCsv').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-csv').trigger();
        });
        $('#exportExcel').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-excel').trigger();
        });
        $('#exportPdf').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-pdf').trigger();
        });
        $('#exportPrint').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-print').trigger();
        });

        // ── Sync Button ──────────────────────────────────
        $('#btnSync').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Syncing...');
            $.post(SYNC_URL)
                .done(function(res) {
                    table.ajax.reload();
                    show_message(res.message || 'Sync berhasil!', 'success');
                })
                .fail(function(xhr) {
                    show_message(xhr.responseJSON?.message || 'Error!');
                })
                .always(function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-sync mr-1"></i>Sync');
                });
        });

        // ── Detail Button ────────────────────────────────
        $('#table tbody').on('click', '.btn-info-product', function(e) {
            e.stopPropagation();
            currentId = $(this).data('id');
            showDetail(currentId);
        });

        // ── Move Button ──────────────────────────────────
        $('#table tbody').on('click', '.btn-move', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            showMove(id);
        });

        // ── Copy Button ──────────────────────────────────
        $('#table tbody').on('click', '.btn-copy', function(e) {
            e.stopPropagation();
            const code = $(this).data('code');
            const name = $(this).data('name');
            copyToClipboard(`${code}\t${name}`);
        });

        // ── Print PL ─────────────────────────────────────
        $(document).on('click', '.btn-print-pl', function() {
            const id = $(this).data('id');
            window.open(`{{ url('packs') }}/${id}/print`, '_blank');
        });

        // ── Print Combined SOP + PL ──────────────────────
        $(document).on('click', '.btn-print-combined', function() {
            const id = $(this).data('id');
            window.open(`{{ url('packs') }}/${id}/print-combined`, '_blank');
        });

        // ── Print SOP ────────────────────────────────────
        $('#btn-print-sop').on('click', function() {
            const id = $(this).data('id');
            window.open(`{{ url('sops') }}/${id}/print`, '_blank');
        });

        // ── Print Collage ────────────────────────────────
        $('#btn-print-collage').on('click', function() {
            const productId = $(this).data('id');
            let url = "{{ route('product_images.collage', ':id') }}";
            url = url.replace(':id', productId);
            window.open(url, '_blank');
        });

        // ── Download ZIP ─────────────────────────────────
        $('#btn-download-zip').on('click', function() {
            const productId = $(this).data('id');
            let url = "{{ route('api.products.download_zip', ':id') }}";
            url = url.replace(':id', productId);
            window.location.href = url;
        });

        // ── Detail Modal ─────────────────────────────────

        function showDetail(id) {
            let url = API_SHOW_URL.replace(':id', id);
            $.get(url).done(function(res) {
                const d = res.data;
                $('#detail_product_name').html(d.name);
                $('#detail_product_code').html(`KODE: ${d.code || '-'}`);
                $('#detail_product_desc').html(d.desc || 'Tidak ada deskripsi.');
                $('#btn-download-zip').data('id', d.id);

                $('#table_pl tbody').empty();
                $('#table_target tbody').empty();
                $('#target_value').html('');
                $('#table_pl_container').empty();

                // Render packs
                if (d.packs && d.packs.length > 0) {
                    d.packs.forEach((pack, packIndex) => {
                        let rows = '';
                        pack.items.forEach((item, itemIndex) => {
                            rows += `
                                <tr>
                                    <td class="text-center">${itemIndex + 1}</td>
                                    <td>${item.item}</td>
                                    <td>${item.qty || ''}</td>
                                </tr>`;
                            if (item.items && item.items.length > 0) {
                                item.items.forEach((sub) => {
                                    rows += `
                                        <tr>
                                            <td class="text-center"></td>
                                            <td class="ps-4">↳ ${sub.item}</td>
                                            <td>${sub.qty || ''}</td>
                                        </tr>`;
                                });
                            }
                        });

                        $('#table_pl_container').append(`
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Pack ${packIndex + 1}: ${pack.name || '(Tanpa Nama)'}</strong>
                                    <button type="button" class="btn btn-xs btn-outline-info btn-print-pl" data-id="${pack.id}">
                                        <i class="fas fa-print"></i> Cetak PL
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-primary btn-print-combined ${d.sop ? '' : 'd-none'}" data-id="${pack.id}">
                                        <i class="fas fa-file-alt"></i> Cetak SOP + PL
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">No</th>
                                                <th>ITEM</th>
                                                <th>QTY</th>
                                            </tr>
                                        </thead>
                                        <tbody>${rows}</tbody>
                                    </table>
                                </div>
                            </div>`);
                    });
                }

                // Render SOP
                if (d.sop != null) {
                    $('#btn-print-sop').removeClass('d-none').data('id', d.sop.id);
                    $('#target_value').html(d.sop.target);
                    d.sop.items.forEach((item, index) => {
                        $('#table_target tbody').append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.item}</td>
                            </tr>
                        `);
                    });
                } else {
                    $('#btn-print-sop').addClass('d-none');
                }

                // Render images
                let imgHtml = '';
                if (d.images && d.images.length > 0) {
                    $('#btn-print-collage').removeClass('d-none').data('id', id);
                    d.images.forEach((img) => {
                        imgHtml += `
                            <a href="${img.url}"
                                data-lightbox="product-${id}"
                                data-title="Image [${d.code}] ${d.name} (${img.name})">
                                    <img src="${img.url}"
                                        class="img-thumbnail"
                                        style="width:100px;height:100px;object-fit:cover;">
                            </a>
                        `;
                    });
                } else {
                    $('#btn-print-collage').addClass('d-none');
                    imgHtml = '<p class="text-muted">Tidak ada gambar.</p>';
                }
                $('#detail_images').html(imgHtml);

                // Render PLTBB
                $('#pltbb_is_complete').html('Not Complete').addClass('badge-danger').removeClass('badge-success');
                if (d.pltbb != null) {
                    $('#pltbb_p').html(d.pltbb.p);
                    $('#pltbb_l').html(d.pltbb.l);
                    $('#pltbb_t').html(d.pltbb.t);
                    $('#pltbb_b').html(d.pltbb.b);
                    $('#pltbb_note').html(d.pltbb.note);
                    if (d.pltbb.is_complete) {
                        $('#pltbb_is_complete').html('Complete').addClass('badge-success').removeClass('badge-danger');
                    }
                } else {
                    $('#pltbb_p, #pltbb_l, #pltbb_t, #pltbb_b, #pltbb_note').html('-');
                }

                $('#modal_pl').modal('show');
            }).fail(function() {
                show_message('Data Tidak ada!');
            });
        }

        // ── Move Modal ───────────────────────────────────

        function showMove(id) {
            let url = API_MOVE_URL.replace(':id', id);
            $.get(url).done(function(res) {
                const data = res.data || [];
                let html = '';
                if (data.length === 0) {
                    html = `<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data move</td></tr>`;
                } else {
                    data.forEach(row => {
                        const fromLoc = row.location_id ? row.location_id[1] : '';
                        const destLoc = row.location_dest_id ? row.location_dest_id[1] : '';
                        const lotSn = row.lot_id ? row.lot_id[1] : '';
                        html += `
                            <tr>
                                <td>${escapeHtml(row.reference || '')}</td>
                                <td>${escapeHtml(fromLoc)}</td>
                                <td>${escapeHtml(destLoc)}</td>
                                <td>${escapeHtml(row.date || '')}</td>
                                <td>${escapeHtml(lotSn)}</td>
                                <td class="text-center">${row.qty_done || ''}</td>
                                <td>${escapeHtml(row.x_studio_no_so || '')}</td>
                                <td>${escapeHtml(row.x_studio_customer || '')}</td>
                            </tr>`;
                    });
                }
                $('#table_move tbody').html(html);
                $('#modal_move').modal('show');
            }).fail(function() {
                show_message('Gagal memuat data move!');
            });
        }

        // ── Utility Functions ────────────────────────────

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Berhasil disalin ke clipboard');
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('Berhasil disalin ke clipboard');
            });
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
    });
</script>
