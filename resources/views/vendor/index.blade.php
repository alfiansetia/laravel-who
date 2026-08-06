@extends('template', ['title' => 'Data Vendor'])
@push('css')
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
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
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
                            placeholder="Cari nama, desc...">
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
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteBatch()">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern" id="tableVendor" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="chkAll" class="new-control-input">
                                </th>
                                <th>NAME</th>
                                <th>DESC</th>
                                <th class="text-nowrap text-center">PACK COUNT</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
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

    @include('vendor.modal')
@endsection

@push('js')
    <script>
        const URL_INDEX_API = "{{ route('api.vendors.index') }}";
        let currentPage = 1;
        let currentPerPage = 10;
        let currentSearch = '';
        let searchTimeout = null;

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

            // Select all checkbox
            $('#chkAll').on('change', function() {
                const checked = $(this).is(':checked');
                $('input[name="id[]"]').prop('checked', checked);
            });

            // Add vendor form
            $('#btn_add').click(function() {
                $('#form_add').submit();
            });

            $('#form_add').on('submit', function(e) {
                e.preventDefault();
                let name = $('#name').val();
                let desc = $('#desc').val();
                if (!name) {
                    show_message('Name Wajib diisi!');
                    return;
                }
                $.ajax({
                    url: URL_INDEX_API,
                    type: "POST",
                    data: {
                        name: name,
                        desc: desc,
                    },
                    success: function(res) {
                        $('#modal_add').modal('hide');
                        $('#name').val('');
                        $('#desc').val('');
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
                `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
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
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                },
                error: function() {
                    $tbody.html(
                        `<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        // ── Table Rendering ──────────────────────────────────

        function renderTable(data) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                return;
            }

            let html = '';
            data.forEach(row => {
                html += `<tr data-id="${row.id}">
                    <td class="text-center">
                        <input type="checkbox" name="id[]" value="${row.id}" class="new-control-input child-chk">
                    </td>
                    <td>${escapeHtml(row.name)}</td>
                    <td>${escapeHtml(row.desc)}</td>
                    <td class="text-center">${row.packs_count ?? 0}</td>
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
                    scrollTop: $('#tableVendor').offset().top - 80
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
