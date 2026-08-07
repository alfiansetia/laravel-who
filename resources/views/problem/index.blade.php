@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --success-soft: #ecfdf5;
            --success-text: #059669;
            --danger-soft: #fef2f2;
            --danger-text: #dc2626;
            --info-soft: #eff6ff;
            --info-text: #2563eb;
            --warning-soft: #fffbeb;
            --warning-text: #d97706;
            --secondary-soft: #f8fafc;
            --secondary-text: #64748b;
        }

        .filter-card {
            border-radius: 16px;
            border: none;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .filter-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* ── Table Modern ── */
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

        .btn-action {
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0;
            font-size: 0.7rem;
            transition: all 0.15s;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* ── Badges ── */
        .badge-soft {
            padding: 0.4em 0.8em;
            font-weight: 600;
            border-radius: 8px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }

        .badge-soft-success { background: var(--success-soft); color: var(--success-text); }
        .badge-soft-danger { background: var(--danger-soft); color: var(--danger-text); }
        .badge-soft-info { background: var(--info-soft); color: var(--info-text); }
        .badge-soft-warning { background: var(--warning-soft); color: var(--warning-text); }
        .badge-soft-secondary { background: var(--secondary-soft); color: var(--secondary-text); }

        /* ── Pagination (match izin_edar) ── */
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
            background: var(--primary-gradient);
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

        /* ── Fix dropdown in responsive table ── */
        .table-responsive { overflow: visible !important; }

        /* ── Detail modal items table ── */
        .table-detail-modern thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 0.35rem 0.5rem;
        }

        .table-detail-modern tbody td {
            padding: 0.3rem 0.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.8rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        @include('problem.form_modal')
        @include('problem.modal')

        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;"
                data-toggle="collapse" data-target="#filterCollapse">
                <span><i class="fas fa-filter mr-2 text-primary"></i>Advance Filter</span>
                <div>
                    <button type="button" class="btn btn-sm btn-link text-secondary" id="btn_reset">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </button>
                    <i class="fas fa-chevron-down ml-2 text-muted"
                        style="transform: rotate(-90deg); transition: transform 0.3s;"></i>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form id="filter_form">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Type</label>
                                <select name="type" id="filter_type" class="form-control select2">
                                    <option value="">Semua Type</option>
                                    <option value="dus">Dus</option>
                                    <option value="unit">Unit</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Tahun</label>
                                <select name="year" id="filter_year" class="form-control select2">
                                    <option value="">Semua Tahun</option>
                                    @for ($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Product</label>
                                <select name="product_id" id="filter_product" class="form-control select2">
                                    <option value="">Semua Product</option>
                                    @foreach ($products as $item)
                                        <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" id="filter_status" class="form-control select2">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card" style="border-radius:16px; border:none; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
            <div class="card-header d-flex align-items-center flex-wrap" style="gap:8px;">
                <div class="d-flex align-items-center flex-wrap" style="gap:6px; flex:1;">
                    <h6 class="m-0 font-weight-bold text-dark">Daftar Problem</h6>
                    <div class="input-group input-group-sm ml-3" style="max-width:300px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" id="searchInput" class="form-control form-control-sm"
                            placeholder="Cari nomor, PIC, RI/PO...">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Refresh" id="btnRefresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center" style="gap:6px;">
                    <button type="button" id="btn_add_problem" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>Tambah Problem
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0" id="table" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:40px;">No</th>
                                <th>Number</th>
                                <th style="white-space:nowrap">Date</th>
                                <th>Type</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>PIC</th>
                                <th class="text-center" style="width:80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                <div class="d-flex align-items-center" style="gap:8px;">
                    <span class="text-muted small">Tampilkan</span>
                    <select id="perPageSelect" class="per-page-select">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-muted small">data</span>
                </div>
                <small class="text-muted" id="pageInfo"></small>
                <div class="pagination-modern" id="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="detail-number">PRB-001</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="text-secondary small font-weight-bold">DATE</div>
                            <div class="text-dark" id="detail-date">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary small font-weight-bold">TYPE</div>
                            <div id="detail-type">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary small font-weight-bold">PIC</div>
                            <div class="text-dark" id="detail-pic">-</div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-3"><i class="fas fa-boxes mr-2 text-primary"></i>Problem Items</h6>
                    <div class="table-responsive">
                        <table class="table table-detail-modern" id="table-detail-items" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Lot</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" id="detail-edit-link" class="btn btn-primary">
                        <i class="fas fa-edit mr-1"></i>Edit Data
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const API_URL = '{{ route('api.problem.index') }}';
        const API_BASE = '{{ url('api/problem') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const NEXT_NUMBER_URL = '{{ route('api.problem.next_number') }}';

        let currentPage = 1;
        let currentPerPage = 10;
        let currentSearch = '';
        let searchTimeout = null;

        // ── Fix nested modal scroll issue ──
        // When an inner modal is closed, Bootstrap removes modal-open from body,
        // breaking scroll on the parent modal. Re-apply it.
        const nestedModals = ['#modal_inner_item', '#modal_inner_paste', '#modal_inner_log'];
        nestedModals.forEach(sel => {
            $(sel).on('hidden.bs.modal', function() {
                if ($('.modal.show').length > 0) {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = getScrollbarWidth() + 'px';
                }
            });
        });

        function getScrollbarWidth() {
            const scrollDiv = document.createElement('div');
            scrollDiv.style.cssText = 'width:100px;height:100px;overflow:scroll;position:absolute;top:-9999px';
            document.body.appendChild(scrollDiv);
            const width = scrollDiv.offsetWidth - scrollDiv.clientWidth;
            document.body.removeChild(scrollDiv);
            return width;
        }

        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

            // Load data on start
            loadData();

            // Search input with debounce
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentSearch = $('#searchInput').val().trim();
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

            // Refresh button
            $('#btnRefresh').click(function() { loadData(); });

            // Filter triggers
            let isBatchUpdating = false;
            $('.select2').on('change', function() {
                if (!isBatchUpdating) { currentPage = 1; loadData(); }
            });

            // Reset button
            $('#btn_reset').click(function(e) {
                e.stopPropagation();
                isBatchUpdating = true;
                $('#filter_type, #filter_year, #filter_product, #filter_status').val('').trigger('change');
                isBatchUpdating = false;
                currentPage = 1;
                loadData();
            });

            // Toggle icon rotation on collapse
            $('#filterCollapse').on('show.bs.collapse', function() {
                $('.filter-card .fa-chevron-down').css('transform', 'rotate(0deg)');
            }).on('hide.bs.collapse', function() {
                $('.filter-card .fa-chevron-down').css('transform', 'rotate(-90deg)');
            });

            // ── Row click → Detail modal ──
            $(document).on('click', '#table tbody tr td:not(:last-child)', function() {
                let id = $(this).closest('tr').data('id');
                if (!id) return;
                openDetail(id);
            });

            // ── Action dropdown handlers ──
            $(document).on('click', '.btn-edit-problem', function(e) {
                e.stopPropagation();
                openEditModal($(this).data('id'));
            });

            $(document).on('click', '.btn-update-status', function(e) {
                e.stopPropagation();
                let id = $(this).data('id');
                let status = $(this).data('status');
                $.ajax({
                    url: `${API_BASE}/${id}/status`,
                    type: 'POST',
                    data: { status }
                }).done(res => {
                    show_message(res.message, 'success');
                    loadData();
                }).fail(xhr => {
                    show_message(xhr.responseJSON.message || 'Error!', 'error');
                });
            });

            $(document).on('click', '.btn-duplicate', function(e) {
                e.stopPropagation();
                let id = $(this).data('id');
                confirmation('Duplikasi data problem ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            url: `${API_BASE}/${id}/duplicate`,
                            type: 'POST',
                            data: { _token: CSRF_TOKEN }
                        }).done(res => {
                            show_message(res.message, 'success');
                            loadData();
                        }).fail(xhr => {
                            show_message(xhr.responseJSON.message || 'Gagal menduplikasi', 'error');
                        });
                    }
                });
            });

            $(document).on('click', '.btn-delete', function(e) {
                e.stopPropagation();
                let id = $(this).data('id');
                confirmation('Yakin ingin menghapus data ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            url: `${API_BASE}/${id}`,
                            type: 'DELETE',
                            data: { _token: CSRF_TOKEN }
                        }).done(res => {
                            show_message(res.message || 'Berhasil dihapus', 'success');
                            loadData();
                        }).fail(xhr => {
                            show_message(xhr.responseJSON.message || 'Error!');
                        });
                    }
                });
            });

            // Edit from detail modal
            $('#detail-edit-link').click(function(e) {
                e.preventDefault();
                let id = $(this).attr('data-id');
                $('#modalDetail').modal('hide');
                openEditModal(id);
            });
        });

        // ══════════════════════════════════════════════════════
        // ── Server-side Data Loading ──
        // ══════════════════════════════════════════════════════

        function loadData() {
            const $tbody = $('#tableBody');
            $tbody.html(
                `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
            );

            $.ajax({
                url: API_URL,
                type: 'GET',
                data: {
                    page: currentPage,
                    per_page: currentPerPage,
                    search: currentSearch,
                    type: $('#filter_type').val(),
                    year: $('#filter_year').val(),
                    product_id: $('#filter_product').val(),
                    status: $('#filter_status').val(),
                },
                success: function(res) {
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                },
                error: function() {
                    $tbody.html(
                        `<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        // ══════════════════════════════════════════════════════
        // ── Table Rendering ──
        // ══════════════════════════════════════════════════════

        function renderTable(data) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                return;
            }

            let html = '';
            const startNo = (currentPage - 1) * currentPerPage;

            data.forEach((row, idx) => {
                const typeCls = row.type === 'dus' ? 'badge-soft-info' : 'badge-soft-warning';
                const stockCls = row.stock === 'stock' ? 'badge-soft-success' : 'badge-soft-secondary';
                const statusCls = row.status === 'done' ? 'badge-soft-success' : 'badge-soft-danger';
                const statusIcon = row.status === 'done' ? 'check' : 'clock';

                const statusBtn = row.status === 'done'
                    ? `<a class="dropdown-item btn-update-status" href="javascript:void(0)" data-id="${row.id}" data-status="pending"><i class="fas fa-clock mr-2 text-warning"></i>Mark as Pending</a>`
                    : `<a class="dropdown-item btn-update-status" href="javascript:void(0)" data-id="${row.id}" data-status="done"><i class="fas fa-check mr-2 text-success"></i>Mark as Done</a>`;

                html += `<tr data-id="${row.id}">
                    <td class="text-center">${startNo + idx + 1}</td>
                    <td><span class="font-weight-bold">${escapeHtml(row.number)}</span></td>
                    <td style="white-space:nowrap"><span class="text-muted"><i class="far fa-calendar-alt mr-1"></i>${escapeHtml(row.date)}</span></td>
                    <td><span class="badge badge-soft ${typeCls}">${escapeHtml(row.type)}</span></td>
                    <td><span class="badge badge-soft ${stockCls}">${escapeHtml(row.stock)}</span></td>
                    <td><span class="badge badge-soft ${statusCls}"><i class="fas fa-${statusIcon} mr-1"></i>${escapeHtml(row.status)}</span></td>
                    <td>${row.pic ? '<i class="far fa-user mr-1 text-muted"></i>' + escapeHtml(row.pic) : '-'}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light btn-round shadow-none btn-action" type="button" data-toggle="dropdown" data-boundary="viewport">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius:10px;">
                                ${statusBtn}
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item btn-duplicate" href="javascript:void(0)" data-id="${row.id}"><i class="far fa-copy mr-2 text-info"></i>Duplikasi</a>
                                <a class="dropdown-item btn-edit-problem" href="javascript:void(0)" data-id="${row.id}"><i class="fas fa-edit mr-2 text-primary"></i>Edit</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item btn-delete text-danger" href="javascript:void(0)" data-id="${row.id}"><i class="fas fa-trash mr-2"></i>Hapus</a>
                            </div>
                        </div>
                    </td>
                </tr>`;
            });

            $tbody.html(html);
        }

        // ══════════════════════════════════════════════════════
        // ── Pagination ──
        // ══════════════════════════════════════════════════════

        function renderPagination(page, totalPages, total) {
            const $pag = $('#pagination');
            const start = (page - 1) * currentPerPage + 1;
            const end = Math.min(page * currentPerPage, total);

            $('#pageInfo').text(
                total > 0 ? `Menampilkan ${start}–${end} dari ${total.toLocaleString('id-ID')} data` : 'Tidak ada data'
            );

            if (totalPages <= 1) { $pag.html(''); return; }

            let html = '';
            html += `<button class="page-btn" ${page <= 1 ? 'disabled' : ''} data-page="${page - 1}"><i class="fas fa-chevron-left"></i></button>`;

            const pages = getPaginationPages(page, totalPages);
            pages.forEach(p => {
                if (p === '...') {
                    html += `<span class="page-btn" style="border:none;cursor:default;">…</span>`;
                } else {
                    html += `<button class="page-btn ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
            });

            html += `<button class="page-btn" ${page >= totalPages ? 'disabled' : ''} data-page="${page + 1}"><i class="fas fa-chevron-right"></i></button>`;

            $pag.html(html);

            $pag.off('click', '.page-btn').on('click', '.page-btn', function() {
                if ($(this).is(':disabled') || $(this).css('cursor') === 'default') return;
                currentPage = parseInt($(this).data('page'));
                loadData();
                $('html, body').animate({ scrollTop: $('#table').offset().top - 80 }, 200);
            });
        }

        function getPaginationPages(current, total) {
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

            const pages = [1];
            if (current > 3) pages.push('...');

            const start = Math.max(2, current - 1);
            const end = Math.min(total - 1, current + 1);
            for (let i = start; i <= end; i++) pages.push(i);

            if (current < total - 2) pages.push('...');
            pages.push(total);
            return pages;
        }

        // ══════════════════════════════════════════════════════
        // ── Detail Modal ──
        // ══════════════════════════════════════════════════════

        function openDetail(id) {
            $.ajax({
                url: `${API_BASE}/${id}`,
                type: 'GET',
                success: function(res) {
                    let d = res.data;
                    $('#detail-number').text(d.number);
                    $('#detail-date').text(d.date);
                    $('#detail-pic').text(d.pic || '-');

                    let typeCls = d.type === 'dus' ? 'badge-soft-info' : 'badge-soft-warning';
                    $('#detail-type').html(`<span class="badge badge-soft ${typeCls}">${d.type}</span>`);

                    let itemsHtml = '';
                    if (d.items && d.items.length > 0) {
                        d.items.forEach(item => {
                            itemsHtml += `<tr>
                                <td><span class="font-weight-bold">[${escapeHtml(item.product.code)}]</span><br><small class="text-muted">${escapeHtml(item.product.name)}</small></td>
                                <td class="text-center font-weight-bold">${item.qty}</td>
                                <td class="text-center"><span class="badge badge-light border px-2">${escapeHtml(item.lot) || '-'}</span></td>
                                <td>${escapeHtml(item.desc) || '-'}</td>
                            </tr>`;
                        });
                    }
                    $('#table-detail-items tbody').html(itemsHtml);
                    $('#detail-edit-link').attr('data-id', id);
                    $('#modalDetail').modal('show');
                },
                error: function() { show_message('Failed to load details', 'error'); }
            });
        }

        // ══════════════════════════════════════════════════════
        // ── Problem Form Modal Logic ──
        // ══════════════════════════════════════════════════════

        var modalItems = [];
        var originalModalItems = [];
        var modalLogs = [];
        var originalModalLogs = [];
        var tableItems, tableLogs;
        var productMap = {};
        @foreach ($products as $p)
            productMap['{{ strtoupper($p->code) }}'] = {
                id: '{{ $p->id }}',
                code: '{{ $p->code }}',
                name: '{{ addslashes($p->name) }}'
            };
        @endforeach

        $(document).ready(function() {
            $('.select2-modal').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalProblem') });
            $('.select2-inner').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modal_inner_item') });

            // Initialize Modal DataTables
            tableItems = $('#table_modal_items').DataTable({
                paging: false, searching: true, info: false, ordering: false, retrieve: true, dom: 'ft',
                language: { search: "" },
            });
            $('#table_modal_items_filter input').addClass('form-control form-control-sm mb-2').attr('placeholder', 'Cari item...');

            tableLogs = $('#table_modal_logs').DataTable({
                paging: false, searching: true, info: false, ordering: false, retrieve: true, dom: 'ft',
                language: { search: "" },
            });
            $('#table_modal_logs_filter input').addClass('form-control form-control-sm mb-2').attr('placeholder', 'Cari log...');

            $(".datepicker").flatpickr({ dateFormat: "Y-m-d", allowInput: true });

            // OPEN CREATE MODAL
            $('#btn_add_problem').click(function() {
                modalItems = []; originalModalItems = [];
                modalLogs = []; originalModalLogs = [];
                renderModalItems(); renderModalLogs();
                $('#form_problem')[0].reset();
                $('#form_method').val('POST');
                $('#problem_id').val('');
                $('#modalProblemTitle').html('<i class="fas fa-plus-circle mr-2 text-primary"></i>Tambah Problem Baru');
                $('#prob_date').val(new Date().toISOString().split('T')[0]);

                $.get(NEXT_NUMBER_URL, function(res) {
                    $('#prob_number').val(res.number);
                    $('#modalProblem').modal('show');
                });
            });

            // OPEN EDIT MODAL
            function openEditModal(id) {
                $.get(`${API_BASE}/${id}`, function(res) {
                    let d = res.data;
                    $('#modalProblemTitle').html('<i class="fas fa-edit mr-2 text-primary"></i>Edit Problem');
                    $('#form_method').val('PUT');
                    $('#problem_id').val(d.id);
                    $('#prob_number').val(d.number);
                    $('#prob_date').val(d.date);
                    $('#prob_pic').val(d.pic).trigger('change');
                    $('#prob_ri_po').val(d.ri_po);
                    $('#prob_type').val(d.type).trigger('change');
                    $('#prob_stock').val(d.stock).trigger('change');
                    $('#prob_status').val(d.status).trigger('change');
                    $('#prob_email_on').val(d.email_on);

                    modalItems = d.items.map(i => ({
                        product_id: i.product_id, qty: i.qty, lot: i.lot, desc: i.desc,
                        displayCode: i.product.code, displayName: i.product.name
                    }));
                    originalModalItems = JSON.parse(JSON.stringify(modalItems));

                    modalLogs = d.logs.map(l => ({ date: l.date, desc: l.desc }));
                    originalModalLogs = JSON.parse(JSON.stringify(modalLogs));

                    renderModalItems(); renderModalLogs();
                    $('#modalProblem').modal('show');
                });
            }
            window.openEditModal = openEditModal;

            // ── ITEM LOGIC ──
            var editingItemIndex = -1;

            $('#btn_modal_refresh_items').click(function() {
                modalItems = JSON.parse(JSON.stringify(originalModalItems));
                renderModalItems();
                show_message('Daftar item dikembalikan ke data awal', 'info');
            });

            $('#btn_modal_add_item').click(function() {
                editingItemIndex = -1;
                $('#modal_inner_item .modal-title').html('<i class="fas fa-plus mr-2"></i>Tambah Produk');
                $('#btn_modal_save_inner_item').text('Tambahkan');
                $('#modal_select_product').val('').trigger('change');
                $('#modal_item_qty').val(1);
                $('#modal_item_lot').val('');
                $('#modal_item_desc').val('');
                $('#modal_inner_item').modal('show');
            });

            $('#btn_modal_save_inner_item').click(function() {
                let pid = $('#modal_select_product').val();
                let sel = $('#modal_select_product').select2('data')[0];
                if (!pid) return show_message('Pilih produk!');

                let itemData = {
                    product_id: pid,
                    qty: $('#modal_item_qty').val(),
                    lot: $('#modal_item_lot').val(),
                    desc: $('#modal_item_desc').val(),
                    displayCode: sel.element.dataset.code,
                    displayName: sel.element.dataset.name
                };

                if (editingItemIndex > -1) modalItems[editingItemIndex] = itemData;
                else modalItems.push(itemData);

                renderModalItems();
                $('#modal_inner_item').modal('hide');
            });

            $(document).on('click', '.btn-modal-edit-item', function() {
                editingItemIndex = $(this).data('index');
                let item = modalItems[editingItemIndex];
                $('#modal_inner_item .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Produk');
                $('#btn_modal_save_inner_item').text('Simpan Perubahan');
                $('#modal_select_product').val(item.product_id).trigger('change');
                $('#modal_item_qty').val(item.qty);
                $('#modal_item_lot').val(item.lot);
                $('#modal_item_desc').val(item.desc);
                $('#modal_inner_item').modal('show');
            });

            $(document).on('click', '.btn-modal-remove-item', function() {
                modalItems.splice($(this).data('index'), 1);
                renderModalItems();
            });

            function renderModalItems() {
                tableItems.clear();
                modalItems.forEach((it, idx) => {
                    tableItems.row.add([
                        `<div class="text-center small">${idx+1}</div>`,
                        `<div><div class="font-weight-bold tiny">${it.displayCode}</div><div class="small text-muted" style="font-size:0.7rem">${it.displayName}</div>
                            <input type="hidden" name="items[${idx}][product_id]" value="${it.product_id}"></div>`,
                        `<div class="text-center">${it.qty}<input type="hidden" name="items[${idx}][qty]" value="${it.qty}"></div>`,
                        `<div class="small">${it.lot || '-'}<input type="hidden" name="items[${idx}][lot]" value="${it.lot || ''}"></div>`,
                        `<div class="small text-muted">${it.desc || '-'}<input type="hidden" name="items[${idx}][desc]" value="${it.desc || ''}"></div>`,
                        `<div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-xs btn-outline-warning btn-modal-edit-item mr-1" data-index="${idx}"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-modal-remove-item" data-index="${idx}"><i class="fas fa-times"></i></button>
                        </div>`
                    ]);
                });
                tableItems.draw();
            }

            // ── LOG LOGIC ──
            var editingLogIndex = -1;

            $('#btn_modal_refresh_logs').click(function() {
                modalLogs = JSON.parse(JSON.stringify(originalModalLogs));
                renderModalLogs();
                show_message('Daftar log dikembalikan', 'info');
            });

            $('#btn_modal_add_log').click(function() {
                editingLogIndex = -1;
                $('#modal_inner_log .modal-title').html('<i class="fas fa-history mr-2"></i>Tambah Log');
                $('#btn_modal_save_inner_log').text('Tambahkan');
                $('#modal_log_date').val(new Date().toISOString().split('T')[0]);
                $('#modal_log_desc').val('');
                $('#modal_inner_log').modal('show');
            });

            $('#btn_modal_save_inner_log').click(function() {
                let date = $('#modal_log_date').val();
                let desc = $('#modal_log_desc').val();
                if (!date || !desc) return show_message('Tanggal dan Keterangan wajib diisi!');

                if (editingLogIndex > -1) modalLogs[editingLogIndex] = { date, desc };
                else modalLogs.push({ date, desc });

                renderModalLogs();
                $('#modal_inner_log').modal('hide');
            });

            $(document).on('click', '.btn-modal-edit-log', function() {
                editingLogIndex = $(this).data('index');
                let log = modalLogs[editingLogIndex];
                $('#modal_inner_log .modal-title').html('<i class="fas fa-history mr-2"></i>Edit Log');
                $('#btn_modal_save_inner_log').text('Simpan Perubahan');
                $('#modal_log_date').val(log.date);
                $('#modal_log_desc').val(log.desc);
                $('#modal_inner_log').modal('show');
            });

            $(document).on('click', '.btn-modal-remove-log', function() {
                modalLogs.splice($(this).data('index'), 1);
                renderModalLogs();
            });

            function renderModalLogs() {
                tableLogs.clear();
                modalLogs.forEach((it, idx) => {
                    tableLogs.row.add([
                        `<div class="text-center small">${idx+1}</div>`,
                        `<div class="small font-weight-bold">${it.date}<input type="hidden" name="logs[${idx}][date]" value="${it.date}"></div>`,
                        `<div class="small text-muted">${it.desc}<input type="hidden" name="logs[${idx}][desc]" value="${it.desc}"></div>`,
                        `<div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-xs btn-outline-warning btn-modal-edit-log mr-1" data-index="${idx}"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-modal-remove-log" data-index="${idx}"><i class="fas fa-times"></i></button>
                        </div>`
                    ]);
                });
                tableLogs.draw();
            }

            // ── PASTE EXCEL LOGIC ──
            $('#btn_modal_paste_excel').click(() => {
                $('#modal_paste_area').val('');
                $('#modal_preview_container').hide();
                $('#modal_inner_paste').modal('show');
            });

            var parsedModalItems = [];
            var parsedGeneralInfo = { date: '', number: '', ri_po: '', stock: '', email_on: '' };

            $('#modal_paste_area').on('input', function() {
                let lines = $(this).val().trim().split('\n');
                let html = '';
                parsedModalItems = [];
                parsedGeneralInfo = { date: '', number: '', ri_po: '', stock: '', email_on: '' };

                lines.forEach((line) => {
                    if (!line.trim()) return;
                    let cols = line.split('\t');

                    if (parsedGeneralInfo.date === '' && (cols[0] || cols[1] || cols[9] || cols[10])) {
                        if (cols[0]) parsedGeneralInfo.date = cols[0].trim();
                        if (cols[1]) parsedGeneralInfo.number = cols[1].trim();
                        if (cols[9]) parsedGeneralInfo.ri_po = cols[9].trim();
                        if (cols[10]) parsedGeneralInfo.email_on = cols[10].trim();
                        if ((cols[6] || '').toLowerCase() === 'v') parsedGeneralInfo.stock = 'stock';
                        if ((cols[7] || '').toLowerCase() === 'v') parsedGeneralInfo.stock = 'import';
                    }

                    let code = (cols[2] || '').trim().toUpperCase();
                    let p = productMap[code] || null;
                    let qty = parseInt(cols[8]) || 1;

                    parsedModalItems.push({ p, lot: (cols[4] || '').trim(), desc: (cols[5] || '').trim(), qty });

                    let rowStock = '-';
                    if ((cols[6] || '').toLowerCase() === 'v') rowStock = 'Stock';
                    if ((cols[7] || '').toLowerCase() === 'v') rowStock = 'Import';

                    html += `<tr>
                        <td class="small text-muted">${cols[1] || '-'}</td>
                        <td><span class="font-weight-bold text-primary">${cols[2] || '-'}</span></td>
                        <td class="small">${cols[4] || '-'}</td>
                        <td class="small">${cols[9] || '-'}</td>
                        <td class="text-center font-weight-bold" style="color:#6366f1;">${rowStock}</td>
                        <td class="text-center">${p ? '✅' : '❌'}</td>
                    </tr>`;
                });

                $('#modal_preview_body').html(html);
                $('#modal_preview_container').show();
                $('#btn_modal_do_import').prop('disabled', parsedModalItems.filter(i => i.p).length === 0);
            });

            $('#btn_modal_do_import').click(function() {
                if (parsedGeneralInfo.date) {
                    let parts = parsedGeneralInfo.date.split('/');
                    if (parts.length === 3) $('#prob_date').val(`${parts[2]}-${parts[1]}-${parts[0]}`).trigger('change');
                    else $('#prob_date').val(parsedGeneralInfo.date).trigger('change');
                }
                if (parsedGeneralInfo.number) $('#prob_number').val(parsedGeneralInfo.number);
                if (parsedGeneralInfo.ri_po) $('#prob_ri_po').val(parsedGeneralInfo.ri_po);
                if (parsedGeneralInfo.stock) $('#prob_stock').val(parsedGeneralInfo.stock).trigger('change');
                if (parsedGeneralInfo.email_on) {
                    let dEmail = parsedGeneralInfo.email_on;
                    let partsE = dEmail.split('/');
                    if (partsE.length === 3) $('#prob_email_on').val(`${partsE[2]}-${partsE[1]}-${partsE[0]}`).trigger('change');
                    else $('#prob_email_on').val(dEmail).trigger('change');
                }

                parsedModalItems.filter(i => i.p).forEach(i => {
                    modalItems.push({
                        product_id: i.p.id, qty: i.qty, lot: i.lot, desc: i.desc,
                        displayCode: i.p.code, displayName: i.p.name
                    });
                });
                renderModalItems();
                $('#modal_inner_paste').modal('hide');
                show_message('Data berhasil diimpor & info diperbarui', 'info');
            });

            // ── SUBMIT LOGIC ──
            $('#form_problem').submit(function(e) {
                e.preventDefault();
                if (modalItems.length === 0) return show_message('Tambah minimal 1 item!', 'error');

                let id = $('#problem_id').val();
                let method = $('#form_method').val();
                let url = method === 'POST' ? '{{ route("api.problem.store") }}' : `${API_BASE}/${id}`;

                let formData = $(this).serialize();
                $('#btn_submit_problem').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');

                $.ajax({ url, type: 'POST', data: formData })
                    .done(res => {
                        show_message('Berhasil disimpan', 'success');
                        $('#modalProblem').modal('hide');
                        loadData();
                    })
                    .fail(xhr => { show_message(xhr.responseJSON.message || 'Error!', 'error'); })
                    .always(() => {
                        $('#btn_submit_problem').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Simpan Problem');
                    });
            });
        });

        // ── Utility ──
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>
@endpush
