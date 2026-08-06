@extends('template', ['title' => 'Data QC Lot'])
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 0.35rem 0.5rem;
            white-space: nowrap;
        }

        .table-modern tbody td {
            padding: 0.3rem 0.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.8rem;
        }

        .table-modern tbody tr {
            transition: background 0.15s;
            cursor: pointer;
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
        }

        .table-modern tbody tr.bg-duplicate,
        .table-modern tbody tr.bg-duplicate td {
            background-color: #fff3cd !important;
        }

        .pagination-modern {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .pagination-modern .page-btn {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            padding: 0 8px;
        }

        .pagination-modern .page-btn:hover:not(:disabled):not(.active) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #6366f1;
        }

        .pagination-modern .page-btn.active {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: #fff;
            border-color: transparent;
        }

        .pagination-modern .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .per-page-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card card-sm">
            <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px;">
                {{-- Left: Search + Refresh --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
                    <div class="input-group input-group-sm" style="max-width: 400px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" id="searchInput" class="form-control form-control-sm"
                            placeholder="Cari product, lot, qc by...">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Refresh"
                        onclick="loadData()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                {{-- Right: Action Buttons --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <button type="button" class="btn btn-info btn-sm" onclick="$('#modal_add').modal('show')">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='{{ route('qc_lots.import') }}'">
                        <i class="fas fa-file-import mr-1"></i> Import
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteBatch()">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern" id="tableQcLot" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="chkAll" class="new-control-input">
                                </th>
                                <th>PRODUCT</th>
                                <th>LOT / ED</th>
                                <th>DATE</th>
                                <th>QC BY</th>
                                <th>QC NOTE</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="text-muted small">Tampilkan</span>
                    <select id="perPageSelect" class="per-page-select">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                    <span class="text-muted small">data</span>
                </div>
                <small class="text-muted" id="pageInfo"></small>
                <div class="pagination-modern" id="pagination"></div>
            </div>
        </div>
    </div>

    @include('qc_lot.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.qc_lots.index') }}";
        let currentPage = 1;
        let currentPerPage = 10;
        let currentSearch = '';
        let searchTimeout = null;

        // Store current page data for row click → edit modal
        let currentData = [];

        $(document).ready(function() {
            // Init Select2 and Daterangepicker for add modal
            $('#qc_date').daterangepicker({
                singleDatePicker: true,
                autoApply: true,
                locale: { format: 'YYYY-MM-DD' }
            });
            $('#product_id').select2({ theme: 'bootstrap4' });

            // Init Select2 and Daterangepicker for edit modal
            $('#edit_qc_date').daterangepicker({
                singleDatePicker: true,
                autoApply: true,
                locale: { format: 'YYYY-MM-DD' }
            });
            $('#edit_product_id').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal_edit')
            });

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

            // Select all checkbox
            $('#chkAll').on('change', function() {
                const checked = $(this).is(':checked');
                $('input[name="id[]"]').prop('checked', checked);
            });

            // Row Click → Edit Modal
            $(document).on('click', '#tableBody tr', function(e) {
                if ($(e.target).is('input[type="checkbox"]')) return;
                const id = $(this).data('id');
                if (!id) return;
                const rowData = currentData.find(r => r.id == id);
                if (!rowData) return;
                $('#edit_id').val(rowData.id);
                $('#edit_product_id').val(rowData.product_id).trigger('change');
                $('#edit_lot_number').val(rowData.lot_number);
                $('#edit_lot_expiry').val(rowData.lot_expiry);
                $('#edit_qc_date').data('daterangepicker').setStartDate(moment(rowData.qc_date).format('YYYY-MM-DD'));
                $('#edit_qc_date').data('daterangepicker').setEndDate(moment(rowData.qc_date).format('YYYY-MM-DD'));
                $('#edit_qc_by').val(rowData.qc_by);
                $('#edit_qc_note').val(rowData.qc_note);
                $('#modal_edit').modal('show');
            });

            // Add form submit
            $('#modal_add').on('shown.bs.modal', function() {
                $('#lot_number').focus();
            });

            $('#form_add').submit(function(e) {
                e.preventDefault();
                let data = $(this).serialize();
                $.ajax({
                    url: URL_INDEX_API,
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: data,
                    success: function(res) {
                        $('#modal_add').modal('hide');
                        $('#lot_number').val('');
                        $('#product_id').val('').trigger('change');
                        $('#lot_expiry').val('');
                        $('#qc_date').val('');
                        $('#qc_by').val('');
                        $('#qc_note').val('');
                        show_message(res.message, 'success');
                        loadData();
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Error!');
                    }
                });
            });

            // Edit form submit
            $('#modal_edit').on('shown.bs.modal', function() {
                $('#edit_lot_number').focus();
            });

            $('#form_edit').submit(function(e) {
                e.preventDefault();
                let data = $(this).serialize();
                let id = $('#edit_id').val();
                $.ajax({
                    url: `${URL_INDEX_API}/${id}`,
                    type: "PUT",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: data,
                    success: function(res) {
                        $('#modal_edit').modal('hide');
                        show_message(res.message, 'success');
                        loadData();
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Error!');
                    }
                });
            });
        });

        // ── Data Loading ─────────────────────────────────────

        function loadData() {
            const $tbody = $('#tableBody');
            $tbody.html(
                `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
            );

            $.ajax({
                url: URL_INDEX_API,
                type: 'GET',
                data: {
                    page: currentPage,
                    per_page: currentPerPage,
                    search: currentSearch,
                },
                success: function(res) {
                    currentData = res.data || [];
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                },
                error: function() {
                    currentData = [];
                    $tbody.html(
                        `<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        // ── Table Rendering ──────────────────────────────────

        function renderTable(data) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                return;
            }

            // Detect duplicate lot numbers on current page
            const lotCounts = {};
            data.forEach(row => {
                if (row.lot_number) {
                    lotCounts[row.lot_number] = (lotCounts[row.lot_number] || 0) + 1;
                }
            });

            let html = '';
            data.forEach(row => {
                const productCode = escapeHtml(row.product?.code || '');
                const lotDisplay = escapeHtml(row.lot_number) + (row.lot_expiry ? '/' + escapeHtml(row.lot_expiry) : '');
                const dateDisplay = row.qc_date ? moment(row.qc_date).format('DD-MM-YYYY') : '';
                const isDuplicate = row.lot_number && lotCounts[row.lot_number] > 1;

                html += `<tr data-id="${row.id}" class="${isDuplicate ? 'bg-duplicate' : ''}">
                    <td class="text-center">
                        <input type="checkbox" name="id[]" value="${row.id}" class="new-control-input child-chk">
                    </td>
                    <td>${productCode}</td>
                    <td>${lotDisplay}</td>
                    <td>${escapeHtml(dateDisplay)}</td>
                    <td>${escapeHtml(row.qc_by)}</td>
                    <td>${escapeHtml(row.qc_note)}</td>
                </tr>`;
            });

            $tbody.html(html);
            $('#chkAll').prop('checked', false);
        }

        // ── Delete Batch ─────────────────────────────────────

        function deleteBatch() {
            const selectedIds = $('input[name="id[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                show_message("No Selected Data!");
                return;
            }

            confirmation('Delete Selected (' + selectedIds.length + ')?', function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: URL_INDEX_API,
                        type: "DELETE",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: { ids: selectedIds },
                        success: function(res) {
                            loadData();
                            show_message(res.message || 'Deleted!', 'success');
                        },
                        error: function(xhr) {
                            show_message(xhr.responseJSON?.message || 'Error!');
                        }
                    });
                }
            });
        }

        // ── Pagination ───────────────────────────────────────

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
                    scrollTop: $('#tableQcLot').offset().top - 80
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

        // ── Utility ──────────────────────────────────────────

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>
@endpush
