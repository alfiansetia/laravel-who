@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        }

        .content-header {
            padding: 1.5rem 0.5rem;
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
        }

        .kategori-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            transition: all 0.2s ease;
        }

        .kategori-badge:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: #eef2ff;
        }

        .kategori-badge.active {
            background: var(--primary-gradient);
            color: #fff;
            border-color: transparent;
        }

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
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }

        .table-modern tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .table-modern tbody tr {
            cursor: pointer;
            transition: background 0.15s;
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
        }

        .badge-kategori {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-akd {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-akl {
            background: #d1fae5;
            color: #059669;
        }

        .badge-pkd {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-pkl {
            background: #fce7f3;
            color: #db2777;
        }

        .badge-lainnya {
            background: #f1f5f9;
            color: #64748b;
        }

        .expired-text {
            color: #dc2626;
            font-weight: 600;
        }

        .expiring-text {
            color: #d97706;
            font-weight: 600;
        }

        /* Pagination */
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

        .loading-overlay {
            position: relative;
        }

        .loading-overlay::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            z-index: 10;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .loading-overlay.loading::after {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Content Header --}}
        <div class="content-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-certificate mr-2 text-primary"></i>{{ $title ?? 'Data Izin Edar' }}
                    </h4>
                    <p class="text-muted small mb-0">Daftar Izin Edar Produk</p>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="card filter-card">
            <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;"
                data-toggle="collapse" data-target="#filterCollapse">
                <span><i class="fas fa-filter mr-2 text-primary"></i>Filter</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="collapse show" id="filterCollapse">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-12 mb-3">
                            <label class="small font-weight-bold text-muted">Kategori</label>
                            <div class="d-flex flex-wrap" id="kategoriFilter" style="gap: 8px;">
                                <span class="kategori-badge active" data-kategori="">Semua</span>
                                @foreach ($kategoriList as $kat)
                                    <span class="kategori-badge"
                                        data-kategori="{{ $kat }}">{{ $kat }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="small font-weight-bold text-muted">Pencarian</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="search" id="searchInput" class="form-control"
                                    placeholder="Nomor izin, merk, pendaftar, jenis produk...">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="btnResetFilter" class="btn btn-secondary btn-block">
                                <i class="fas fa-sync-alt mr-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card card-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern" id="tableIzinEdar" style="width:100%">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>No. Izin Edar</th>
                                <th>Tgl Terbit</th>
                                <th>Tgl Exp</th>
                                <th>Merk</th>
                                <th>Jenis Produk</th>
                                <th>Pendaftar</th>
                                <th>Pabrik</th>
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
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap"
                style="gap: 10px;">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="text-muted small">Tampilkan</span>
                    <select id="perPageSelect" class="per-page-select">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
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

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header"
                    style="background: var(--primary-gradient); color: #fff; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>Detail Izin Edar
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailBody"></div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const API_URL = '{{ route('api.izin_edars.index') }}';
        let currentPage = 1;
        let currentPerPage = 50;
        let currentKategori = '';
        let currentSearch = '';
        let searchTimeout = null;

        $(document).ready(function() {
            loadData();

            // Kategori filter click
            $('#kategoriFilter').on('click', '.kategori-badge', function() {
                $('#kategoriFilter .kategori-badge').removeClass('active');
                $(this).addClass('active');
                currentKategori = $(this).data('kategori');
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

            // Reset filter
            $('#btnResetFilter').on('click', function() {
                currentKategori = '';
                currentSearch = '';
                currentPage = 1;
                $('#searchInput').val('');
                $('#kategoriFilter .kategori-badge').removeClass('active');
                $('#kategoriFilter .kategori-badge[data-kategori=""]').addClass('active');
                loadData();
            });

            // Per page change
            $('#perPageSelect').on('change', function() {
                currentPerPage = parseInt($(this).val());
                currentPage = 1;
                loadData();
            });

            // Row click -> detail modal
            $(document).on('click', '#tableBody tr[data-id]', function() {
                const id = $(this).data('id');
                // Find row data from last loaded data
                const rowData = window.lastRowDataMap && window.lastRowDataMap[id];
                if (rowData) showDetail(rowData);
            });
        });

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
                    kategori: currentKategori,
                    search: currentSearch,
                },
                success: function(res) {
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                    window.lastRowDataMap = {};
                    res.data.forEach(row => {
                        window.lastRowDataMap[row.id] = row;
                    });
                },
                error: function() {
                    $tbody.html(
                        `<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        function renderTable(data) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                return;
            }

            const kelasMap = {
                'AKD': 'badge-akd',
                'AKL': 'badge-akl',
                'PKD': 'badge-pkd',
                'PKL': 'badge-pkl',
            };

            let html = '';
            data.forEach(row => {
                const badgeClass = kelasMap[row.kategori] || 'badge-lainnya';
                const tglTerbit = row.tgl_terbit ? formatDate(row.tgl_terbit) : '-';
                const tglExpHtml = formatExpDate(row.tgl_exp);

                html += `<tr data-id="${row.id}">
                    <td><span class="badge-kategori ${badgeClass}">${escapeHtml(row.kategori)}</span></td>
                    <td class="font-weight-bold">${escapeHtml(row.nomor_izin_edar)}</td>
                    <td>${tglTerbit}</td>
                    <td>${tglExpHtml}</td>
                    <td>${escapeHtml(row.merk)}</td>
                    <td>${escapeHtml(row.jenis_produk || '-')}</td>
                    <td>${escapeHtml(row.pendaftar)}</td>
                    <td>${escapeHtml(row.pabrik || '-')}</td>
                </tr>`;
            });

            $tbody.html(html);
        }

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

            // Prev
            html +=
                `<button class="page-btn" ${page <= 1 ? 'disabled' : ''} data-page="${page - 1}"><i class="fas fa-chevron-left"></i></button>`;

            // Page numbers (smart: show first, last, current ±2)
            const pages = getPaginationPages(page, totalPages);
            pages.forEach(p => {
                if (p === '...') {
                    html += `<span class="page-btn" style="border:none;cursor:default;">…</span>`;
                } else {
                    html += `<button class="page-btn ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
            });

            // Next
            html +=
                `<button class="page-btn" ${page >= totalPages ? 'disabled' : ''} data-page="${page + 1}"><i class="fas fa-chevron-right"></i></button>`;

            $pag.html(html);

            // Bind click
            $pag.off('click', '.page-btn').on('click', '.page-btn', function() {
                if ($(this).is(':disabled') || $(this).css('cursor') === 'default') return;
                currentPage = parseInt($(this).data('page'));
                loadData();
                // Scroll to top of table
                $('html, body').animate({
                    scrollTop: $('#tableIzinEdar').offset().top - 80
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

        function showDetail(d) {
            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><td class="font-weight-bold text-muted" style="width:140px">Kategori</td><td>${escapeHtml(d.kategori)}</td></tr>
                            <tr><td class="font-weight-bold text-muted">No. Izin Edar</td><td>${escapeHtml(d.nomor_izin_edar)}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Tgl Terbit</td><td>${d.tgl_terbit ? formatDate(d.tgl_terbit) : '-'}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Tgl Expired</td><td>${formatExpDate(d.tgl_exp)}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Merk</td><td>${escapeHtml(d.merk)}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Jenis Produk</td><td>${escapeHtml(d.jenis_produk || '-')}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><td class="font-weight-bold text-muted" style="width:140px">Pendaftar</td><td>${escapeHtml(d.pendaftar)}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Alamat Pendaftar</td><td>${escapeHtml(d.alamat_pendaftar || '-')}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Pabrik</td><td>${escapeHtml(d.pabrik || '-')}</td></tr>
                            <tr><td class="font-weight-bold text-muted">Alamat Pabrik</td><td>${escapeHtml(d.alamat_pabrik || '-')}</td></tr>
                            ${d.sub_kategori ? `<tr><td class="font-weight-bold text-muted">Sub Kategori</td><td>${escapeHtml(d.sub_kategori)}</td></tr>` : ''}
                            ${d.kelompok_produk ? `<tr><td class="font-weight-bold text-muted">Kelompok Produk</td><td>${escapeHtml(d.kelompok_produk)}</td></tr>` : ''}
                            ${d.tipe ? `<tr><td class="font-weight-bold text-muted">Tipe</td><td>${escapeHtml(d.tipe)}</td></tr>` : ''}
                            ${d.kelas ? `<tr><td class="font-weight-bold text-muted">Kelas</td><td>${escapeHtml(d.kelas)}</td></tr>` : ''}
                            ${d.kelas_resiko ? `<tr><td class="font-weight-bold text-muted">Kelas Resiko</td><td>${escapeHtml(d.kelas_resiko)}</td></tr>` : ''}
                        </table>
                    </div>
                </div>
            `;
            $('#detailBody').html(html);
            $('#modalDetail').modal('show');
        }

        // Utility functions
        function formatDate(dateStr) {
            const d = new Date(dateStr);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        function formatExpDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            const now = new Date();
            const diff = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
            let cls = '';
            if (diff < 0) cls = 'expired-text';
            else if (diff <= 30) cls = 'expiring-text';
            return `<span class="${cls}">${formatDate(dateStr)}</span>`;
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>
@endpush
