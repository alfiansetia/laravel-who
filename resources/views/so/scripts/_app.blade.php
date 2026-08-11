<script>
    const API_URL = '{{ route('api.so.index') }}';
    const DETAIL_URL = '{{ route('api.so.index') }}';
    const PRINT_URL = '{{ url('so') }}';
    let currentPage = 1;
    let currentPerPage = 10;
    let currentSearch = '';
    let currentNoteSearch = '';
    let currentFilter = '';
    let searchTimeout = null;
    let tableProduct = null;
    let currentDetailId = null;

    $(document).ready(function() {
        loadData();

        // Search input with debounce
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            const val = $(this).val().trim();
            searchTimeout = setTimeout(function() {
                currentSearch = val;
                currentPage = 1;
                loadData();
            }, 400);
        });

        $('#noteSearchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            const val = $(this).val().trim();
            searchTimeout = setTimeout(function() {
                currentNoteSearch = val;
                currentPage = 1;
                loadData();
            }, 400);
        });

        // Filter change
        $('#filterSelect').on('change', function() {
            currentFilter = $(this).val();
            currentPage = 1;
            loadData();
        });

        // Per page change
        $('#perPageSelect').on('change', function() {
            currentPerPage = parseInt($(this).val());
            currentPage = 1;
            loadData();
        });

        // Init DataTable for modal detail (client-side)
        tableProduct = $('#tableProduct').DataTable({
            processing: true,
            serverSide: false,
            dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            oLanguage: {
                "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
            },
            lengthMenu: [
                [10, 50, 100, 500],
                ['10 rows', '50 rows', '100 rows', '500 rows']
            ],
            pageLength: 10,
            columns: [{
                    data: "default_code",
                },
                {
                    data: "name",
                },
                {
                    data: "product_id",
                    render: function(data) {
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "unit_price1",
                    className: 'text-right',
                },
                {
                    data: "product_uom_qty",
                    className: 'text-center',
                },
                {
                    data: "qty_delivered",
                    className: 'text-center',
                },
            ],
            buttons: [{
                    extend: "colvis",
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Column Visible'
                    },
                    className: 'btn btn-sm btn-primary'
                },
                {
                    extend: "collection",
                    text: '<i class="fas fa-download mr-1"></i>Export',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Export Data'
                    },
                    className: 'btn btn-sm btn-primary',
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
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }, {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }, {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                },
            ],
        });

        // Print button in modal
        $('#btnPrint').on('click', function() {
            if (currentDetailId) {
                window.open(`${PRINT_URL}/${currentDetailId}/print`, '_blank');
            }
        });
    });

    // ── Data Loading (Main Table) ────────────────────────

    function loadData() {
        const $tbody = $('#tableBody');
        $tbody.html(
            `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
        );

        $.ajax({
            url: API_URL,
            type: 'GET',
            data: {
                page: currentPage,
                per_page: currentPerPage,
                search: currentSearch,
                filter: currentFilter,
                note_search: currentNoteSearch || '',
            },
            success: function(res) {
                renderTable(res.data);
                renderPagination(res.page, res.total_pages, res.total);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat data SO!';
                $tbody.html(
                    `<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(msg)}</td></tr>`
                );
            }
        });
    }

    // ── Main Table Rendering ─────────────────────────────

    function renderTable(data) {
        const $tbody = $('#tableBody');

        if (!data || data.length === 0) {
            $tbody.html(
                `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
            );
            return;
        }

        let html = '';
        data.forEach((row) => {
            const name = row.name || '-';
            const sistem = row.sistem ? row.sistem.trim().toUpperCase() : '';
            const showBadge = sistem && sistem !== '-';
            const nameHtml = escapeHtml(name) + (showBadge ?
                ` <span class="badge-sistem">${escapeHtml(sistem)}</span>` : '');
            const date = formatDateDisplay(row.date_order);
            const customer = Array.isArray(row.partner_id) ? escapeHtml(row.partner_id[1].substring(0, 30)) :
                '-';
            const notes = row.note_to_wh ? (row.note_to_wh.length > 40 ? escapeHtml(row.note_to_wh.substring(0,
                40)) + '...' : escapeHtml(row.note_to_wh)) : '-';
            const delivery = row.delivery_count || 0;
            const isPrint = (row.note_to_wh || '').includes('PRINT OK');

            html += `<tr data-id="${row.id}" data-note="${escapeHtml(row.note_to_wh || '')}">
                <td class="font-weight-bold">${nameHtml}</td>
                <td>${date}</td>
                <td>${customer}</td>
                <td>${notes}</td>
                <td class="text-center">${delivery}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success btn-print-so" data-id="${row.id}" title="Print SO">
                            <i class="fas fa-print"></i>
                        </button>
                        <button ${isPrint ? 'disabled' : ''} type="button" class="btn btn-sm btn-warning btn-mark-as-print" data-id="${row.id}" title="Mark As Print">
                            <i class="fas fa-check"></i>
                        </button>
                        <button ${isPrint ? '' : 'disabled'} type="button" class="btn btn-sm btn-danger btn-mark-as-unprint" data-id="${row.id}" title="Mark As Unprint">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        $tbody.html(html);

        // Row click → load detail (but not if clicking action buttons)
        $('#tableBody tr').on('click', function(e) {
            if ($(e.target).closest('button').length > 0) return;
            const id = $(this).data('id');
            if (id) loadDetail(id);
        });

        // Print button
        $('#tableBody').off('click', '.btn-print-so').on('click', '.btn-print-so', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const soId = $(this).data('id');
            window.open(`${PRINT_URL}/${soId}/print`, '_blank');
        });

        // Mark as print
        $('#tableBody').off('click', '.btn-mark-as-print').on('click', '.btn-mark-as-print', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const soId = $(this).data('id');
            const note = $(this).closest('tr').data('note') || '';
            confirmation('Mark as print?', function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: `${DETAIL_URL}/${soId}/mark-as-print`,
                        type: 'POST',
                        data: {
                            note: note
                        },
                        success: function(res) {
                            loadData();
                            show_message(res.message, 'success');
                        },
                        error: function(xhr) {
                            show_message(xhr.responseJSON?.message || 'Error!', 'error');
                        }
                    });
                }
            });
        });

        // Mark as unprint
        $('#tableBody').off('click', '.btn-mark-as-unprint').on('click', '.btn-mark-as-unprint', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const soId = $(this).data('id');
            const note = $(this).closest('tr').data('note') || '';
            confirmation('Mark as unprint?', function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: `${DETAIL_URL}/${soId}/mark-as-unprint`,
                        type: 'POST',
                        data: {
                            note: note
                        },
                        success: function(res) {
                            loadData();
                            show_message(res.message, 'success');
                        },
                        error: function(xhr) {
                            show_message(xhr.responseJSON?.message || 'Error!', 'error');
                        }
                    });
                }
            });
        });
    }

    // ── Detail Loading (into DataTable) ──────────────────

    function loadDetail(id) {
        tableProduct.clear().draw();
        $('#modalNote').text('');
        $('#modalTitle').html(`<i class="fas fa-info-circle mr-2"></i>Detail SO #${id}`);
        currentDetailId = id;

        $.ajax({
            url: `${DETAIL_URL}/${id}`,
            type: 'GET',
            success: function(res) {
                const d = res.data || res;
                $('#modalTitle').html(
                    `<i class="fas fa-info-circle mr-2"></i>Item SO: ${escapeHtml(d.name || id)}`
                );
                $('#modalNote').text(d.note_to_wh || d.note || '-');

                const lines = d.order_line_detail || [];
                tableProduct.clear().rows.add(lines).draw();
                $('#modalDetail').modal('show');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat detail SO!';
                show_message(msg, 'error');
            }
        });
    }

    // ── Main Table Pagination ────────────────────────────

    function renderPagination(page, totalPages, total) {
        const $pag = $('#pagination');
        const start = (page - 1) * currentPerPage + 1;
        const end = Math.min(page * currentPerPage, total);

        $('#pageInfo').text(total > 0 ? `Menampilkan ${start}–${end} dari ${total.toLocaleString('id-ID')} data` :
            'Tidak ada data');

        if (totalPages <= 1) {
            $pag.html('');
            return;
        }

        let html = '';

        html +=
            `<button class="page-btn" ${page <= 1 ? 'disabled' : ''} data-page="${page - 1}"><i class="fas fa-chevron-left"></i></button>`;

        const pages = getPaginationPages(page, totalPages);
        pages.forEach(p => {
            if (p === '...') {
                html += `<span class="page-btn" style="border:none;cursor:default;">…</span>`;
            } else {
                html += `<button class="page-btn ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
            }
        });

        html +=
            `<button class="page-btn" ${page >= totalPages ? 'disabled' : ''} data-page="${page + 1}"><i class="fas fa-chevron-right"></i></button>`;

        $pag.html(html);

        $pag.off('click', '.page-btn').on('click', '.page-btn', function() {
            if ($(this).is(':disabled') || $(this).css('cursor') === 'default') return;
            currentPage = parseInt($(this).data('page'));
            loadData();
            $('html, body').animate({
                scrollTop: $('#tableSO').offset().top - 80
            }, 200);
        });
    }

    function getPaginationPages(current, total) {
        if (total <= 7) {
            return Array.from({
                length: total
            }, (_, i) => i + 1);
        }

        const pages = [];
        pages.push(1);

        if (current > 3) pages.push('...');

        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        if (current < total - 2) pages.push('...');

        pages.push(total);
        return pages;
    }

    // ── Utility Functions ────────────────────────────────

    function formatDateDisplay(data) {
        if (!data) return '-';
        let date = new Date(data.replace(' ', 'T'));
        date.setHours(date.getHours() + 7);
        let d = ("0" + date.getDate()).slice(-2);
        let m = ("0" + (date.getMonth() + 1)).slice(-2);
        let y = date.getFullYear();
        let h = ("0" + date.getHours()).slice(-2);
        let min = ("0" + date.getMinutes()).slice(-2);
        let s = ("0" + date.getSeconds()).slice(-2);
        return `${d}/${m}/${y} ${h}:${min}:${s}`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
</script>
