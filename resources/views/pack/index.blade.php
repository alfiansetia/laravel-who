@extends('template', ['title' => 'Packing List'])
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
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
                            placeholder="Cari product, PL name, vendor...">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Refresh"
                        onclick="loadData()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                {{-- Right: Action Buttons --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <button type="button" class="btn btn-info btn-sm" onclick="window.location.href='{{ route('packs.create') }}'">
                        <i class="fas fa-plus mr-1"></i> Add PL
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="changeData()">
                        <i class="fas fa-exchange-alt mr-1"></i> Change Vendor
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteBatch()">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern" id="tablePack" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="chkAll" class="new-control-input">
                                </th>
                                <th>Kode Product</th>
                                <th>Name Product</th>
                                <th>PL Name</th>
                                <th>PL Desc</th>
                                <th>Vendor</th>
                                <th>Vendor Desc</th>
                                <th style="width: 120px;">Aksi</th>
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

    {{-- Modal View/Edit PL Detail --}}
    <div class="modal fade" id="modal_pl" tabindex="-1" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-2 d-flex align-items-center justify-content-between">
                    <ul class="nav nav-pills" id="plTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold px-4" id="detail-tab" data-toggle="tab" href="#tab-detail" role="tab">
                                <i class="fas fa-eye mr-1"></i> VIEW DETAIL
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold px-4" id="edit-tab" data-toggle="tab" href="#tab-edit" role="tab">
                                <i class="fas fa-edit mr-1"></i> EDIT ITEMS
                            </a>
                        </li>
                    </ul>
                    <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <!-- Header Info -->
                    <div class="bg-white px-4 py-3 border-bottom">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <label class="small text-muted mb-0">Packing List Name</label>
                                <h6 id="detail_name" class="font-weight-bold mb-0 text-dark">-</h6>
                                <label class="small text-muted mb-0 mt-2">Vendor</label>
                                <h6 id="detail_vendor" class="font-weight-bold mb-0 text-dark">-</h6>
                            </div>
                            <div class="col-md-6 pl-md-4">
                                <label class="small text-muted mb-0">Product</label>
                                <h6 id="detail_product" class="text-primary font-weight-bold mb-0">-</h6>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="plTabContent">
                        <!-- Tab Detail -->
                        <div class="tab-pane fade show active px-4 py-3" id="tab-detail" role="tabpanel">
                            <div class="table-responsive" style="max-height: 500px">
                                <table class="table table-sm table-striped table-bordered mb-0" id="table_pl_view">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center" style="width: 40px">No</th>
                                            <th>Item Description</th>
                                            <th class="text-center" style="width: 100px">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Edit -->
                        <div class="tab-pane fade px-4 py-3" id="tab-edit" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted font-italic text-danger">* Perubahan di sini tidak otomatis tersimpan sebelum klik tombol "Simpan"</span>
                                <button type="button" class="btn btn-sm btn-info" onclick="addItemRow()">
                                    <i class="fas fa-plus mr-1"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="table-responsive" style="max-height: 500px">
                                <table class="table table-sm table-hover border" id="table_pl">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px">#</th>
                                            <th>Item Nama</th>
                                            <th style="width: 120px">Qty</th>
                                            <th class="text-center" style="width: 50px"><i class="fas fa-cog"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary d-none" id="btn_save_pl">
                        <i class="fas fa-save mr-1 text-white"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Change Vendor --}}
    <div class="modal fade" id="modal_change" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Vendor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_change" action="">
                        @csrf
                        <div class="form-group col-12">
                            <label for="vendor_id">VENDOR</label>
                            <div class="input-group">
                                <select name="vendor_id" id="vendor_id" class="custom-select select2" style="width: 100%" required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="btn_change" type="button" class="btn btn-primary">Change</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.packs.index') }}";
        const URL_INDEX = "{{ route('packs.index') }}";
        let currentPage = 1;
        let currentPerPage = 25;
        let currentSearch = '';
        let searchTimeout = null;
        let currentPackId = null;
        let currentPack = {};
        let changeSelectedIds = [];

        $(document).ready(function() {
            $('#vendor_id').select2({ theme: 'bootstrap4' });

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

            // Row Click → Open Modal
            $(document).on('click', '#tableBody tr', function(e) {
                if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.btn-action').length) return;
                const id = $(this).data('id');
                if (id) {
                    currentPackId = id;
                    $('#detail-tab').tab('show');
                    $('#btn_save_pl').addClass('d-none');
                    $('#detail_vendor').html('<i class="fas fa-spinner fa-spin"></i>');
                    $('#table_pl_view tbody').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');
                    fetchPackDetail(id);
                    $('#modal_pl').modal('show');
                }
            });

            // Action buttons
            $(document).on('click', '.btn-download', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                window.open(`${URL_INDEX_API}/${id}/download`);
            });

            $(document).on('click', '.btn-print', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                window.open(`${URL_INDEX}/${id}/print`, '_blank');
            });

            // Handle Tab Switching in modal
            $(document).on('shown.bs.tab', '#plTab a[data-toggle="tab"]', function (e) {
                let target = $(e.target).attr("href");
                if (currentPackId) fetchPackDetail(currentPackId);
                if (target === '#tab-edit') {
                    $('#btn_save_pl').removeClass('d-none');
                } else {
                    $('#btn_save_pl').addClass('d-none');
                }
            });

            // Save PL items
            $('#btn_save_pl').click(function() {
                let items = [];
                let isValid = true;

                if ($('#table_pl tbody tr').length === 0) {
                    show_message('Minimal harus ada 1 item!', 'warning');
                    return;
                }

                $('#table_pl tbody tr').each(function() {
                    let item = $(this).find('.pl-item').val().trim();
                    let qty = $(this).find('.pl-qty').val().trim();
                    if (!item) {
                        isValid = false;
                        $(this).find('.pl-item').addClass('is-invalid');
                    } else {
                        $(this).find('.pl-item').removeClass('is-invalid');
                        items.push({ item: item, qty: qty });
                    }
                });

                if (!isValid) {
                    show_message('Nama item tidak boleh kosong!', 'warning');
                    return;
                }

                let data = { ...currentPack, items: items };

                $.ajax({
                    url: URL_INDEX_API + '/' + currentPackId,
                    type: "PUT",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: data,
                    success: function(res) {
                        show_message(res.message, 'success');
                        $('#detail-tab').tab('show');
                        loadData();
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Error simpan data!');
                    }
                });
            });

            // Change vendor form
            $('#btn_change').click(function() {
                $('#form_change').submit();
            });

            $('#form_change').on('submit', function(e) {
                e.preventDefault();
                let vendor_id = $('#vendor_id').val();
                if (!vendor_id) {
                    show_message('Pilih vendor terlebih dahulu!');
                    return;
                }
                $.ajax({
                    url: "{{ route('api.packs.change') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        vendor_id: vendor_id,
                        ids: changeSelectedIds,
                    },
                    success: function(res) {
                        $('#modal_change').modal('hide');
                        $('#vendor_id').val('').change();
                        loadData();
                        show_message(res.message, 'success');
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
                `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
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
                        `<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        // ── Table Rendering ──────────────────────────────────

        function renderTable(data) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                return;
            }

            let html = '';
            data.forEach(row => {
                const productCode = escapeHtml(row.product?.code || '');
                const productName = escapeHtml(row.product?.name || '');
                const vendorName = escapeHtml(row.vendor?.name || '');
                html += `<tr data-id="${row.id}">
                    <td class="text-center">
                        <input type="checkbox" name="id[]" value="${row.id}" class="new-control-input child-chk">
                    </td>
                    <td>${productCode}</td>
                    <td>${productName}</td>
                    <td>${escapeHtml(row.name)}</td>
                    <td>${escapeHtml(row.desc)}</td>
                    <td>${vendorName}</td>
                    <td>${escapeHtml(row.vendor_desc)}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center" style="gap: 4px;">
                            <button type="button" class="btn btn-action btn-outline-success btn-download" data-id="${row.id}" title="Export Excel">
                                <i class="fas fa-file-excel"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-outline-secondary btn-print" data-id="${row.id}" title="Print HTML">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });

            $tbody.html(html);
            $('#chkAll').prop('checked', false);
        }

        // ── PL Detail Fetch ──────────────────────────────────

        function fetchPackDetail(packId) {
            $.get(URL_INDEX_API + '/' + packId).done(function(res) {
                let pack = res.data;
                currentPack = {
                    name: pack.name,
                    desc: pack.desc,
                    product_id: pack.product_id,
                    vendor_id: pack.vendor_id,
                    vendor_desc: pack.vendor_desc
                };

                $('#detail_name').html(pack.name ?? '-');
                $('#detail_vendor').html(pack.vendor?.name ?? '-');
                $('#detail_product').html(`[${pack.product?.code ?? ''}] ${pack.product?.name ?? ''}`);

                $('#table_pl_view tbody').empty();
                $('#table_pl tbody').empty();

                pack.items.forEach((item, index) => {
                    $('#table_pl_view tbody').append(`
                        <tr>
                            <td class="text-center text-muted small">${index + 1}</td>
                            <td class="font-weight-bold">${item.item}</td>
                            <td class="text-center bg-light font-weight-bold">${item.qty || '-'}</td>
                        </tr>
                    `);
                    addItemRow(item.item, item.qty || '');
                });
            }).fail(function(xhr) {
                show_message('Gagal mengambil data!', 'error');
            });
        }

        function addItemRow(item = '', qty = '') {
            let rowCount = $('#table_pl tbody tr').length + 1;
            $('#table_pl tbody').append(`
                <tr>
                    <td class="text-center align-middle font-weight-bold">${rowCount}</td>
                    <td>
                        <textarea class="form-control form-control-sm pl-item" rows="1" required placeholder="Nama Item...">${item}</textarea>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm pl-qty" value="${qty}" placeholder="Qty...">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="$(this).closest('tr').remove()">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        }

        // ── Change Vendor ────────────────────────────────────

        function changeData() {
            changeSelectedIds = $('input[name="id[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (changeSelectedIds.length === 0) {
                show_message("No Selected Data!");
                return;
            }
            $('#modal_change').modal('show');
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
                    scrollTop: $('#tablePack').offset().top - 80
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
