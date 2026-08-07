<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const API_URL = '{{ route('api.lots.index') }}';
    let currentPage = 1;
    let currentPerPage = 10;
    let currentSearch = '';
    let currentProduct = '';
    let searchTimeout = null;
    let tableLotDetail = null;

    $(document).ready(function() {
        // Initialize Select2 for product filter
        $('#productFilter').select2({
            theme: 'bootstrap4',
            allowClear: true,
            placeholder: 'Semua Product',
        }).on('change', function() {
            currentProduct = $(this).val() || '';
            currentPage = 1;
            loadData();
        });

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

        // ── DataTable: Lot Detail (client-side) ──
        tableLotDetail = $('#table_lot').DataTable({
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
                    data: "location_id",
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
                    data: "expired_date",
                    className: 'text-center',
                    render: function(data) {
                        if (!data) return '';
                        return moment(data).format('YYYY.MM.DD');
                    }
                },
                {
                    data: "qty_done",
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

        // Copy buttons
        $('#btn_copy_lot').on('click', function() {
            copyToClipboard($('#detail_lot').val());
        });

        $('#btn_copy_sn').on('click', function() {
            copyToClipboard($('#detail_sn').val());
        });

        // Initial load
        loadData();
    });

    // ── Data Loading (Main Table) ────────────────────────

    function loadData() {
        const $tbody = $('#tableBody');
        $tbody.html(
            `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
        );

        const params = {
            page: currentPage,
            per_page: currentPerPage,
            search: currentSearch,
        };
        if (currentProduct) {
            params.product = currentProduct;
        }

        $.ajax({
            url: API_URL,
            type: 'GET',
            data: params,
            success: function(res) {
                renderTable(res.data);
                renderPagination(res.page, res.total_pages, res.total);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat data Lot!';
                $tbody.html(
                    `<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(msg)}</td></tr>`
                );
            }
        });
    }

    // ── Main Table Rendering ─────────────────────────────

    function renderTable(data) {
        const $tbody = $('#tableBody');

        if (!data || data.length === 0) {
            $tbody.html(
                `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
            );
            return;
        }

        let html = '';
        data.forEach((row) => {
            const id = row.id;
            const lotName = escapeHtml(row.name || '-');
            const qty = hrg(row.product_qty1 || 0);
            const productName = Array.isArray(row.product_id) ? escapeHtml(row.product_id[1]) : escapeHtml(row
                .product_id || '-');

            html += `<tr data-id="${id}">
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-action btn-info btn-trace" title="Trace">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </td>
                <td>${lotName}</td>
                <td class="text-center">${qty}</td>
                <td>${productName}</td>
            </tr>`;
        });

        $tbody.html(html);

        // Store row data for button clicks
        data.forEach((row, index) => {
            $tbody.find(`tr:eq(${index})`).data('row', row);
        });

        // Trace button click
        $('#tableBody').on('click', '.btn-trace', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const row = $(this).closest('tr');
            const id = row.data('id');
            if (id) loadTrace(id);
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
                scrollTop: $('#tableLot').offset().top - 80
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

    // ── Load Lot Detail (Modal) ──────────────────────────

    function loadLotDetail(id) {
        $.ajax({
            url: `${API_URL}/${id}`,
            type: 'GET',
            success: function(res) {
                const d = res.data;

                // Build lot and sn strings from move_line_detail
                let lotLines = [];
                let snLines = [];
                const lines = d.move_line_detail || [];

                if (Array.isArray(lines)) {
                    lines.forEach(function(line) {
                        let lotText = Array.isArray(line.lot_id) ? line.lot_id[1] : (line
                            .lot_name || line.lot_id || '');
                        if (lotText) {
                            lotLines.push(lotText);
                            if (!snLines.includes(lotText)) {
                                snLines.push(lotText);
                            }
                        }
                    });
                }

                $('#modal_lotLabel').html(
                    `<i class="fas fa-box mr-2"></i>Lot: ${escapeHtml(d.name || id)}`);
                $('#detail_lot').val(lotLines.join('\n'));
                $('#detail_sn').val(snLines.join('\n'));

                // Load detail into DataTable
                tableLotDetail.clear().rows.add(lines).draw();

                $('#modal_lot').modal('show');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat detail Lot!';
                show_message(msg, 'error');
            }
        });
    }

    // ── Load Trace (Modal) ───────────────────────────────

    function loadTrace(id) {
        $.ajax({
            url: `${API_URL}/${id}/trace`,
            type: 'GET',
            success: function(response) {
                let htmlContent = response.data?.html || 'Tidak ada data';
                $('#modal_trace_content').html(htmlContent);

                // Initialize DataTable if table exists in trace content
                if ($('#modal_trace_content table').length > 0) {
                    $('#modal_trace_content table').DataTable({
                        order: [
                            [2, 'desc']
                        ],
                    });
                }

                $('#modal_trace').modal('show');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memuat data Trace!';
                show_message(msg, 'error');
            }
        });
    }

    // ── Utility Functions ────────────────────────────────

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function hrg(x) {
        return parseInt(x).toLocaleString('en-US');
    }

    function copyToClipboard(text) {
        if (!text) {
            show_message('Tidak ada data untuk disalin', 'info');
            return;
        }
        navigator.clipboard.writeText(text).then(function() {
            show_message('Berhasil disalin ke clipboard', 'success');
        }).catch(function() {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            show_message('Berhasil disalin ke clipboard', 'success');
        });
    }
</script>
