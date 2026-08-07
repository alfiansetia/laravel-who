<script>
    const API_URL = '{{ route('api.po.index') }}';
    const DETAIL_URL = '{{ route('api.po.index') }}';
    let currentPage = 1;
    let currentPerPage = 10;
    let currentSearch = '';
    let searchTimeout = null;
    let tableProduct = null;

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

        // Per page change
        $('#perPageSelect').on('change', function() {
            currentPerPage = parseInt($(this).val());
            currentPage = 1;
            loadData();
        });

        // Init DataTable for modal detail (client-side, not server-side)
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
                    data: "code",
                },
                {
                    data: "desc",
                },
                {
                    data: "origin",
                },
                {
                    data: "akl",
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: "qty",
                    className: 'text-center',
                },
                {
                    data: "qty_received",
                    className: 'text-center',
                },
                {
                    data: "qty_sisa",
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
    });

    // ── Data Loading (Main Table) ────────────────────────

    function loadData() {
        const $tbody = $('#tableBody');
        $tbody.html(
            `<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
        );

        $.ajax({
            url: API_URL,
            type: 'GET',
            data: {
                page: currentPage,
                per_page: currentPerPage,
                search: currentSearch,
            },
            success: function(res) {
                renderTable(res.data);
                renderPagination(res.page, res.total_pages, res.total);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat data PO!';
                $tbody.html(
                    `<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(msg)}</td></tr>`
                );
            }
        });
    }

    // ── Main Table Rendering ─────────────────────────────

    function renderTable(data) {
        const $tbody = $('#tableBody');

        if (!data || data.length === 0) {
            $tbody.html(
                `<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
            );
            return;
        }

        let html = '';
        data.forEach((row, index) => {
            const name = row.name || '-';
            const vendor = Array.isArray(row.partner_id) ? row.partner_id[1] : (row.partner_id || '-');
            const user = Array.isArray(row.user_id) ? row.user_id[1] : (row.user_id || '-');
            const notes = row.notes ? (row.notes.length > 40 ? row.notes.substring(0, 40) + '...' : row.notes) :
                '-';
            const picking = row.picking_count || 0;

            html += `<tr data-id="${row.id}">
                <td class="font-weight-bold">${escapeHtml(name)}</td>
                <td>${escapeHtml(vendor)}</td>
                <td>${escapeHtml(user)}</td>
                <td>${escapeHtml(notes)}</td>
                <td class="text-center">${picking}</td>
            </tr>`;
        });

        $tbody.html(html);

        // Row click → load detail
        $('#tableBody tr').on('click', function() {
            const id = $(this).data('id');
            if (id) loadDetail(id);
        });
    }

    // ── Detail Loading (into DataTable) ──────────────────

    function loadDetail(id) {
        // Show loading state
        tableProduct.clear().draw();
        $('#modalNote').text('');
        $('#modalTitle').html(`<i class="fas fa-info-circle mr-2"></i>Detail PO #${id}`);

        $.ajax({
            url: `${DETAIL_URL}/${id}`,
            type: 'GET',
            success: function(res) {
                const d = res.data || res;
                $('#modalTitle').html(
                    `<i class="fas fa-info-circle mr-2"></i>List Item PO No : ${escapeHtml(d.name || id)}`
                );
                $('#modalNote').text(d.notes || '-');

                const lines = d.order_line_detail || [];
                const rows = lines.map(line => {
                    const productText = Array.isArray(line.product_id) ? line.product_id[1] : (
                        line.product_id || '');
                    return {
                        code: getCode(productText),
                        desc: getDesc(productText),
                        origin: Array.isArray(line.product_id) ? line.product_id[1] : (line
                            .product_id || ''),
                        akl: Array.isArray(line.akl) ? line.akl[1] : (line.akl || ''),
                        qty: line.product_qty || 0,
                        qty_received: line.qty_received || 0,
                        qty_sisa: (line.product_qty || 0) - (line.qty_received || 0),
                    };
                });

                tableProduct.clear().rows.add(rows).draw();
                $('#modalDetail').modal('show');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat detail PO!';
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
                scrollTop: $('#tablePO').offset().top - 80
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

    function getCode(str) {
        if (!str) return '';
        let match = str.match(/\[(.*?)\]/);
        return match ? match[1] : '';
    }

    function getDesc(str) {
        if (!str) return '';
        let match = str.match(/\]\s*(.*)/);
        return match ? match[1] : str;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
</script>
