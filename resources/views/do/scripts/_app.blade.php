<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const API_URL = '{{ route('api.do.index') }}';
    const DETAIL_URL = '{{ route('api.do.index') }}';
    const PRINT_URL = '{{ url('do') }}';
    let currentPage = 1;
    let currentPerPage = 10;
    let currentSearch = '';
    let searchTimeout = null;
    let tableProduct = null;
    let tableProductLot = null;
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

        // Per page change
        $('#perPageSelect').on('change', function() {
            currentPerPage = parseInt($(this).val());
            currentPage = 1;
            loadData();
        });

        // ── DataTable: Product (client-side) ──
        tableProduct = $('#table_product').DataTable({
            processing: true,
            serverSide: false,
            dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            paging: false,
            scrollCollapse: true,
            scrollY: '400px',
            columns: [{
                    data: "product_id",
                    render: function(data) {
                        if (Array.isArray(data)) return getCode(data[1]);
                        return data;
                    }
                },
                {
                    data: "product_id",
                    render: function(data) {
                        if (Array.isArray(data)) return getDesc(data[1]);
                        return data;
                    }
                },
                {
                    data: "akl_id",
                    className: 'text-center',
                    render: function(data) {
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "product_uom_qty",
                    className: 'text-center',
                },
                {
                    data: "quantity_done",
                    className: 'text-center',
                },
            ],
            buttons: [{
                    extend: "colvis",
                    className: 'btn btn-sm btn-primary'
                },
                {
                    extend: "collection",
                    text: '<i class="fas fa-download mr-1"></i>Export',
                    className: 'btn btn-sm btn-primary',
                    buttons: [{
                            extend: 'copy',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                    ],
                },
            ],
        });

        // ── DataTable: Product Lot (client-side) ──
        tableProductLot = $('#table_product_lot').DataTable({
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
                [10, 50, 100, 500, 1000],
                ['10 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
            ],
            pageLength: 10,
            paging: true,
            columns: [{
                    data: "product_id",
                    render: function(data) {
                        let text = Array.isArray(data) ? data[1] : data;
                        return getCode(text);
                    }
                },
                {
                    data: "product_id",
                    render: function(data) {
                        let text = Array.isArray(data) ? data[1] : data;
                        return getDesc(text);
                    }
                },
                {
                    data: "product_id",
                    render: function(data) {
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "akl_id",
                    render: function(data) {
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "lot_id",
                    render: function(data, type, row) {
                        return Array.isArray(data) ? data[1] : (row.lot_name || data || '');
                    }
                },
                {
                    data: "qty_done",
                    className: 'text-center',
                },
                {
                    data: "expired_date",
                    className: 'text-center',
                    render: function(data) {
                        if (!data) return '';
                        return moment(data).format('YYYY.MM.DD');
                    }
                },
            ],
            buttons: [{
                    extend: "colvis",
                    className: 'btn btn-sm btn-primary'
                },
                {
                    extend: "collection",
                    text: '<i class="fas fa-download mr-1"></i>Export',
                    className: 'btn btn-sm btn-primary',
                    buttons: [{
                            extend: 'copy',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                    ],
                },
            ],
        });

        // Select2 filter for product code in lot tab
        $('#filter_product_code').select2({
            placeholder: 'Select Product Code',
            allowClear: true,
            theme: 'bootstrap4',
            dropdownParent: $('#modal_product')
        }).on('change', function() {
            var val = $(this).val();
            tableProductLot
                .column(0)
                .search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false)
                .draw();
        });

        $('#btn_reset_filter').on('click', function() {
            $('#filter_product_code').val('').trigger('change.select2');
            tableProductLot.search('').columns().search('').draw();
        });

        // Print buttons in modal
        $('#btn_print').on('click', function() {
            if (currentDetailId) {
                window.open(`${PRINT_URL}/${currentDetailId}/print`, '_blank');
            }
        });

        $('#btn_print_lot').on('click', function() {
            if (currentDetailId) {
                window.open(`${PRINT_URL}/${currentDetailId}/print?with_lot=true`, '_blank');
            }
        });
    });

    // ── Data Loading (Main Table) ────────────────────────

    function loadData() {
        const $tbody = $('#tableBody');
        $tbody.html(
            `<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
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
                const msg = xhr.responseJSON?.message || 'Gagal memuat data DO!';
                $tbody.html(
                    `<tr><td colspan="7" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(msg)}</td></tr>`
                );
            }
        });
    }

    // ── Main Table Rendering ─────────────────────────────

    function renderTable(data) {
        const $tbody = $('#tableBody');

        if (!data || data.length === 0) {
            $tbody.html(
                `<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
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
            const fdate = formatDateDisplay(row.force_date);
            const partner = Array.isArray(row.partner_id) ? escapeHtml(row.partner_id[1].substring(0, 30)) :
            '-';
            const origin = escapeHtml(row.origin || '-');
            const state = escapeHtml(row.state || '-');
            const notes = row.note_to_wh ? (row.note_to_wh.length > 40 ? escapeHtml(row.note_to_wh.substring(0,
                40)) + '...' : escapeHtml(row.note_to_wh)) : '-';

            html += `<tr data-id="${row.id}">
                <td class="font-weight-bold">${nameHtml}</td>
                <td>${fdate}</td>
                <td>${partner}</td>
                <td class="text-center">${origin}</td>
                <td class="text-center">${state}</td>
                <td>${notes}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-action btn-success btn-print-do" data-id="${row.id}" title="Print DO">
                            <i class="fas fa-print"></i>
                        </button>
                        <button type="button" class="btn btn-action btn-warning btn-print-lot" data-id="${row.id}" title="Print Lot">
                            <i class="fas fa-print"></i>
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

        // Action button: Print DO
        $('#tableBody').on('click', '.btn-print-do', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const doId = $(this).data('id');
            window.open(`${PRINT_URL}/${doId}/print`, '_blank');
        });

        // Action button: Print Lot
        $('#tableBody').on('click', '.btn-print-lot', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const doId = $(this).data('id');
            window.open(`${PRINT_URL}/${doId}/print?with_lot=true`, '_blank');
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
                scrollTop: $('#tableDO').offset().top - 80
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
        for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
            pages.push(i);
        }
        if (current < total - 2) pages.push('...');
        pages.push(total);
        return pages;
    }

    // ── Load Detail (Modal) ──────────────────────────────

    function loadDetail(id) {
        currentDetailId = id;

        $.ajax({
            url: `${DETAIL_URL}/${id}`,
            type: 'GET',
            success: function(res) {
                const d = res.data;

                // Modal title & info
                $('#modal_productLabel').html(`<i class="fas fa-list mr-2"></i>Item DO: ${d.name || id}`);
                $('#modal_status').html(
                    `<span class="badge badge-warning">${escapeHtml(d.state || '-')}</span>`
                );
                $('#modal_note').text(d.note_to_wh || '-');

                // Products
                const products = d.move_ids_detail || [];
                // Lots
                let lots = d.move_line_detail || [];

                // Map akl_id from products to lots
                let aklMap = {};
                products.forEach(p => {
                    let pid = Array.isArray(p.product_id) ? p.product_id[0] : p.product_id;
                    aklMap[pid] = p.akl_id;
                });
                lots.forEach(lot => {
                    let pid = Array.isArray(lot.product_id) ? lot.product_id[0] : lot.product_id;
                    lot.akl_id = aklMap[pid];
                });

                // Fill DataTables
                tableProduct.clear().rows.add(products).draw();
                tableProductLot.clear().search('').columns().search('').rows.add(lots).draw();

                // Populate Select2 filter
                let filterSelect = $('#filter_product_code');
                filterSelect.empty().append(
                    '<option value=""></option><option value="">All Products</option>');
                let codes = [];
                lots.forEach(function(item) {
                    let text = Array.isArray(item.product_id) ? item.product_id[1] : item
                    .product_id;
                    let code = getCode(text);
                    if (code && !codes.includes(code)) codes.push(code);
                });
                codes.sort().forEach(function(code) {
                    filterSelect.append(new Option(code, code));
                });
                filterSelect.val('').trigger('change.select2');

                // Show modal, activate first tab
                $('#modal_product').modal('show');
                $('#product-tab').tab('show');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat detail DO!';
                show_message(msg, 'error');
            }
        });
    }

    // ── Utility Functions ────────────────────────────────

    function formatDateDisplay(dateStr) {
        if (!dateStr) return '-';
        let date = new Date(dateStr.replace(' ', 'T'));
        date.setHours(date.getHours() + 7);
        let d = ("0" + date.getDate()).slice(-2);
        let m = ("0" + (date.getMonth() + 1)).slice(-2);
        let y = date.getFullYear();
        let h = ("0" + date.getHours()).slice(-2);
        let min = ("0" + date.getMinutes()).slice(-2);
        let s = ("0" + date.getSeconds()).slice(-2);
        return `${d}/${m}/${y} ${h}:${min}:${s}`;
    }

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
