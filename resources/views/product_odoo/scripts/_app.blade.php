<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const API_URL = '{{ route('api.product_odoo.index') }}';
    const DETAIL_URL = '{{ route('api.product_odoo.index') }}';
    let currentPage = 1;
    let currentPerPage = 10;
    let currentSearch = '';
    let searchTimeout = null;
    let tableProduct = null;
    let tableProductLot = null;
    let tableOnHand = null;
    let currentRowData = null;

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
            oLanguage: {
                "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
            },
            lengthMenu: [
                [10, 50, 100, 500, 1000],
                ['10 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
            ],
            pageLength: 50,
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
                    data: "name",
                },
                {
                    data: "akl_id",
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
                {
                    data: "quantity_done",
                    className: 'text-center',
                    render: function(data, type, row) {
                        return row.product_uom_qty - data;
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

        // ── DataTable: On Hand (client-side) ──
        tableOnHand = $('#table_onhand').DataTable({
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
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "location_id",
                    render: function(data) {
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "lot_id",
                    render: function(data) {
                        return Array.isArray(data) ? data[1] : (data || '');
                    }
                },
                {
                    data: "itds_expired",
                    render: function(data) {
                        if (data && data !== false) return moment(data).format('YYYY.MM.DD');
                        return '';
                    }
                },
                {
                    data: "quantity",
                    className: 'text-center',
                    render: function(data, type) {
                        if (type === 'display') return hrg(data);
                        return data;
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

        // On-hand button click
        $('#tableBody').on('click', '.btn-onhand', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const row = $(this).closest('tr');
            const rowData = row.data('row');
            if (!rowData) return;
            loadOnHand(rowData);
        });

        // Move button click
        $('#tableBody').on('click', '.btn-move', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const row = $(this).closest('tr');
            const rowData = row.data('row');
            if (!rowData) return;
            loadMove(rowData);
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
            },
            success: function(res) {
                renderTable(res.data);
                renderPagination(res.page, res.total_pages, res.total);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat data Product Odoo!';
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
            const id = row.id;
            const defaultCode = escapeHtml(row.default_code || '-');
            const name = escapeHtml(row.name || '-');
            const akl = Array.isArray(row.akl_id) ? escapeHtml(row.akl_id[1]) : escapeHtml(row.akl_id || '-');
            const aklTo = row.x_studio_valid_to_akl ? escapeHtml(row.x_studio_valid_to_akl) : '-';
            const qtyAvailable = hrg(row.qty_available || 0);

            html += `<tr data-id="${id}" data-row-index="${data.indexOf(row)}">
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-action btn-primary btn-onhand" title="On Hand">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-action btn-warning btn-move" title="Move">
                            <i class="fas fa-arrows-alt-v"></i>
                        </button>
                    </div>
                </td>
                <td>${defaultCode}</td>
                <td>${name}</td>
                <td>${akl}</td>
                <td class="text-center">${aklTo}</td>
                <td class="text-center">${qtyAvailable}</td>
            </tr>`;
        });

        $tbody.html(html);

        // Store row data for button clicks
        data.forEach((row, index) => {
            $tbody.find(`tr:eq(${index})`).data('row', row);
        });

        // Row click → load detail (but not if clicking action buttons)
        $('#tableBody tr').on('click', function(e) {
            if ($(e.target).closest('button').length > 0) return;
            const id = $(this).data('id');
            if (id) loadDetail(id);
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
                scrollTop: $('#tableProductOdoo').offset().top - 80
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
        $.ajax({
            url: `${DETAIL_URL}/${id}`,
            type: 'GET',
            success: function(res) {
                const d = res.data;

                // Modal title & info
                $('#modal_productLabel').html(`List Item RI No : ${d.name || id}`);
                $('#modal_note').text(d.note_to_wh || '-');
                $('#modal_origin').text(d.origin || '-');

                // Products
                const products = d.move_without_package_detail || [];
                // Lots
                let lots = d.move_line_ids_without_package_detail || [];

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
                const msg = xhr.responseJSON?.message || 'Gagal memuat detail Product!';
                show_message(msg, 'error');
            }
        });
    }

    // ── Load On Hand ─────────────────────────────────────

    function loadOnHand(rowData) {
        const id = rowData.id;
        let variant = 0;
        if (Array.isArray(rowData.product_variant_id)) {
            variant = rowData.product_variant_id[0];
        }

        $.ajax({
            url: `${DETAIL_URL}/${id}/${variant}/on-hand`,
            type: 'GET',
            success: function(response) {
                tableOnHand.clear().rows.add(response.data || []).draw();

                // Calculate summary per location
                let summaryMap = {};
                if (Array.isArray(response.data)) {
                    response.data.forEach(item => {
                        let loc = Array.isArray(item.location_id) ? item.location_id[1] : item
                            .location_id;
                        let qty = parseFloat(item.quantity) || 0;
                        summaryMap[loc] = (summaryMap[loc] || 0) + qty;
                    });
                }

                let summaryHtml = '';
                let hasData = false;
                Object.entries(summaryMap).forEach(([loc, qty]) => {
                    summaryHtml +=
                        `<div><i class="fas fa-arrow-right mr-2 text-primary"></i><b>${escapeHtml(loc)}</b>: <span class="badge badge-info">${hrg(qty)}</span></div>`;
                    hasData = true;
                });

                $('#modal_onhandLabel').html(
                    `<i class="fas fa-boxes mr-2"></i>On Hand: ${escapeHtml(rowData.name || '')}`);
                $('#modal_summary').html(hasData ? summaryHtml : '-');
                $('#modal_onhand').modal('show');
            },
            error: function(xhr) {
                show_message(xhr.responseJSON?.message || 'Gagal memuat data On Hand!', 'error');
            }
        });
    }

    // ── Load Move ────────────────────────────────────────

    function loadMove(rowData) {
        const id = rowData.id;
        let variant = 0;
        if (Array.isArray(rowData.product_variant_id)) {
            variant = rowData.product_variant_id[0];
        }

        $.ajax({
            url: `${DETAIL_URL}/${id}/${variant}/move`,
            type: 'GET',
            success: function(response) {
                // TODO: handle move response if needed
            },
            error: function(xhr) {
                show_message(xhr.responseJSON?.message || 'Gagal memuat data Move!', 'error');
            }
        });
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

    function hrg(x) {
        return parseInt(x).toLocaleString('en-US');
    }
</script>
