@extends('template', ['title' => 'Data ATK'])
@push('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
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
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card card-sm">
            <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px;">
                {{-- Left: Search + Filter + Refresh --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
                    <div class="input-group input-group-sm" style="max-width: 400px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" id="searchInput" class="form-control form-control-sm"
                            placeholder="Cari kode, nama, satuan...">
                    </div>
                    @php
                        $satuanList = ['pcs','dus','pack','kotak','lusin','pad','rim','roll','tube','box','buah','buku'];
                    @endphp
                    <select id="filterSatuan" class="form-control form-control-sm" style="width: auto; min-width: 130px;">
                        <option value="">Semua Satuan</option>
                        @foreach ($satuanList as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Refresh"
                        onclick="fetchData()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                {{-- Right: Action Buttons --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <button type="button" class="btn btn-info btn-sm" id="btnAdd">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" id="btnImport">
                        <i class="fas fa-upload mr-1"></i> Import
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteBatch">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern" id="table" style="width:100%;cursor:pointer;">
                        <thead>
                            <tr>
                                <th style="width:30px;">
                                    <input type="checkbox" id="chkAll">
                                </th>
                                <th class="text-center" style="width:30px;">No</th>
                                <th>KODE</th>
                                <th>NAME</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-center">STOK</th>
                                <th>DESC</th>
                                <th class="text-center">AKSI</th>
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

    @include('atk.modal')
@endsection

@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        const URL_INDEX_API = "{{ route('api.atk.index') }}";
        const URL_TRX_API   = "{{ route('api.atktrx.index') }}";
        const URL_STORE     = "{{ route('api.atk.store') }}";
        const URL_TRX_STORE = "{{ route('api.atktrx.store') }}";
        const URL_IMPORT    = "{{ route('atk.import') }}";
        const CSRF          = $('meta[name="csrf-token"]').attr('content');

        let currentPage  = 1;
        let perPage      = 10;
        let searchQuery  = '';
        let satuanFilter = '';
        let currentData  = [];
        let searchTimer  = null;

        // ─── escapeHtml ───
        function escapeHtml(str) {
            if (str == null) return '';
            return String(str).replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"');
        }

        // ─── fetch data ───
        function fetchData() {
            const $tbody = $('#tableBody');
            $tbody.html(
                `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
            );

            let params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                search: searchQuery,
            });
            if (satuanFilter) params.append('satuan', satuanFilter);

            $.ajax({
                url: URL_INDEX_API + '?' + params.toString(),
                type: 'GET',
                success: function(res) {
                    currentData = res.data;
                    renderTable(res.data, res.page, res.per_page);
                    renderPagination(res.page, res.total_pages, res.total);
                    const start = (res.page - 1) * res.per_page + 1;
                    const end = Math.min(res.page * res.per_page, res.total);
                    $('#pageInfo').text(res.total > 0
                        ? `Menampilkan ${start}–${end} dari ${res.total.toLocaleString('id-ID')} data`
                        : 'Tidak ada data');
                },
                error: function() {
                    $tbody.html(
                        `<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        // ─── render table ───
        function renderTable(data, page, perPageVal) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                $('#chkAll').prop('checked', false);
                return;
            }

            let html = '';
            data.forEach(function(row, i) {
                let no = (page - 1) * perPageVal + i + 1;
                html += `<tr data-id="${row.id}" data-index="${i}">
                    <td><input type="checkbox" class="chk-row" value="${row.id}"></td>
                    <td class="text-center">${no}</td>
                    <td class="col-click font-weight-bold" style="white-space:nowrap">${escapeHtml(row.code)}</td>
                    <td class="col-click">${escapeHtml(row.name)}</td>
                    <td class="text-center col-click">${escapeHtml(row.satuan)}</td>
                    <td class="text-center col-click">${escapeHtml(row.stok)}</td>
                    <td class="col-click" style="display:none;">${escapeHtml(row.desc)}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center" style="gap: 4px;">
                            <button type="button" class="btn btn-action btn-outline-success" onclick="trxIn(${i})" title="Stock Masuk">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-outline-warning" onclick="trxOut(${i})" title="Stock Keluar">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-outline-info" onclick="showDetail(${i})" title="Detail Transaksi">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-outline-danger" onclick="deleteRow(${row.id})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
            $tbody.html(html);
            $('#chkAll').prop('checked', false);
        }

        // ─── pagination ───
        function getPaginationPages(current, total) {
            if (total <= 7) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }
            const pages = [];
            pages.push(1);
            if (current > 3) pages.push('...');
            const start = Math.max(2, current - 1);
            const end = Math.min(total - 1, current + 1);
            for (let i = start; i <= end; i++) pages.push(i);
            if (current < total - 2) pages.push('...');
            pages.push(total);
            return pages;
        }

        function renderPagination(page, totalPages, total) {
            const $pag = $('#pagination');

            if (totalPages <= 1) {
                $pag.html('');
                return;
            }

            let html = '';
            html += `<button class="page-btn" ${page <= 1 ? 'disabled' : ''} data-page="${page - 1}"><i class="fas fa-chevron-left"></i></button>`;

            getPaginationPages(page, totalPages).forEach(function(p) {
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
                fetchData();
                $('html, body').animate({
                    scrollTop: $('#table').offset().top - 80
                }, 200);
            });
        }

        // ─── row click → edit ───
        $(document).on('click', '.col-click', function() {
            let idx = $(this).closest('tr').data('index');
            let row = currentData[idx];
            if (!row) return;
            $.get(`${URL_INDEX_API}/${row.id}`, function(res) {
                let d = res.data;
                $('#edit_code').val(d.code);
                $('#edit_name').val(d.name);
                $('#edit_satuan').val(d.satuan).change();
                $('#edit_desc').val(d.desc);
                window._editId = d.id;
                $('#modal_edit').modal('show');
            });
        });

        // ─── transaction in ───
        function trxIn(idx) {
            let row = currentData[idx];
            $('#trx_atk_id').val(row.id);
            $('#trx_title').html(`[${escapeHtml(row.code)}] ${escapeHtml(row.name)} (${escapeHtml(row.satuan)})`);
            let today = "{{ date('d/m/Y') }}";
            $('#trx_date').data('daterangepicker').setStartDate(today);
            $('#trx_date').data('daterangepicker').setEndDate(today);
            $('#trx_pic').val('Tika');
            $('#trx_qty').val(1);
            $('#trx_type').val('in').change();
            $('#form_trx .text-danger').hide();
            $('#modal_trx').modal('show');
        }

        // ─── transaction out ───
        function trxOut(idx) {
            let row = currentData[idx];
            $('#trx_atk_id').val(row.id);
            $('#trx_title').html(`[${escapeHtml(row.code)}] ${escapeHtml(row.name)} (${escapeHtml(row.satuan)})`);
            let today = "{{ date('d/m/Y') }}";
            $('#trx_date').data('daterangepicker').setStartDate(today);
            $('#trx_date').data('daterangepicker').setEndDate(today);
            $('#trx_pic').val('Tika');
            $('#trx_qty').val(1);
            $('#trx_type').val('out').change();
            $('#form_trx .text-danger').hide();
            $('#modal_trx').modal('show');
        }

        // ─── detail modal ───
        var table_detail;
        function showDetail(idx) {
            let row = currentData[idx];
            $('#detail_title').html(`[${escapeHtml(row.code)}] ${escapeHtml(row.name)} (${escapeHtml(row.satuan)})`);
            $('#table_detail').DataTable().clear().destroy();

            table_detail = $("#table_detail").DataTable({
                rowId: 'id',
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
                info: false,
                ordering: false,
                columns: [
                    { data: "date", className: "text-left" },
                    { data: "pic", className: "text-left" },
                    {
                        data: "qty", className: "text-center",
                        render: function(data, type, row, meta) {
                            return row.type == 'in' ? data : '';
                        }
                    },
                    {
                        data: "qty", className: "text-center",
                        render: function(data, type, row, meta) {
                            return row.type == 'out' ? data : '';
                        }
                    },
                    { data: "saldo", className: "text-center" },
                    { data: "desc" },
                    {
                        data: "id", className: "text-center",
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return `<button class="btn btn-action btn-outline-danger" type="button" onclick="delete_trx(${data})" title="Hapus"><i class="fas fa-trash"></i></button>`;
                            }
                            return data;
                        }
                    }
                ],
                buttons: [
                    { extend: "colvis", className: 'btn btn-sm btn-primary' },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download"></i> Export',
                        className: 'btn btn-sm btn-primary',
                        buttons: [
                            { extend: 'copy', exportOptions: { columns: ':visible' } },
                            { extend: 'csv', exportOptions: { columns: ':visible' } },
                            { extend: 'pdf', exportOptions: { columns: ':visible' } },
                            { extend: 'excel', exportOptions: { columns: ':visible' } },
                            { extend: 'print', exportOptions: { columns: ':visible' } }
                        ],
                    }
                ],
            });

            $.ajax({
                type: 'GET',
                url: `${URL_TRX_API}?atk_id=${row.id}`,
                success: function(res) {
                    var saldo = 0;
                    res.data.forEach(function(element) {
                        if (element.type == 'in')  saldo = saldo + element.qty;
                        if (element.type == 'out') saldo = saldo - element.qty;
                        table_detail.row.add({
                            id: element.id,
                            date: element.date,
                            pic: element.pic,
                            type: element.type,
                            qty: element.qty,
                            saldo: saldo,
                            desc: element.desc
                        }).draw();
                    });
                }
            });
            $('#modal_detail').modal('show');
        }

        // ─── delete trx ───
        function delete_trx(id) {
            if (!confirm('Hapus?')) return;
            $.ajax({
                type: 'DELETE',
                url: `${URL_TRX_API}/${id}`,
                contentType: "application/json",
                success: function(res) {
                    $('#modal_detail').modal('hide');
                    fetchData();
                }
            });
        }

        // ─── delete single row ───
        function deleteRow(id) {
            if (!confirm('Hapus Data?')) return;
            $.ajax({
                type: 'DELETE',
                url: `${URL_INDEX_API}/${id}`,
                contentType: "application/json",
                success: function() { fetchData(); }
            });
        }

        // ─── batch delete ───
        function deleteBatch() {
            let ids = [];
            $('.chk-row:checked').each(function() { ids.push($(this).val()); });
            if (ids.length === 0) { show_message("No Selected Data!"); return; }
            if (!confirm(`Hapus ${ids.length} data?`)) return;
            $.ajax({
                type: 'DELETE',
                url: URL_INDEX_API,
                data: JSON.stringify({ ids: ids }),
                contentType: "application/json",
                headers: { 'X-CSRF-TOKEN': CSRF },
                success: function() { fetchData(); }
            });
        }

        // ─── search debounce ───
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimer);
            const val = $(this).val().trim();
            searchTimer = setTimeout(function() {
                searchQuery = val;
                currentPage = 1;
                fetchData();
            }, 400);
        });

        // ─── per page change ───
        $('#perPageSelect').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            fetchData();
        });

        // ─── satuan filter ───
        $('#filterSatuan').on('change', function() {
            satuanFilter = $(this).val();
            currentPage = 1;
            fetchData();
        });

        // ─── check all ───
        $('#chkAll').on('change', function() {
            $('.chk-row').prop('checked', $(this).is(':checked'));
        });

        // ─── buttons ───
        $('#btnAdd').on('click', function() {
            $('#form_add')[0].reset();
            $('#form_add .text-danger').hide();
            $('#modal_add').modal('show');
        });

        $('#btnImport').on('click', function() {
            window.location.href = URL_IMPORT;
        });

        $('#btnDeleteBatch').on('click', function() {
            deleteBatch();
        });

        // ─── form_add submit ───
        $('#form_add').submit(function(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: URL_STORE,
                data: $(this).serialize(),
                beforeSend: function() { $('#form_add .text-danger').hide(); },
                success: function() {
                    fetchData();
                    $('#modal_add').modal('hide');
                },
                error: function(xhr) {
                    if (xhr.status != 422) {
                        show_message(xhr.responseJSON.message || 'Error!');
                    } else {
                        let er = xhr.responseJSON.errors;
                        Object.keys(er).forEach(function(key) {
                            $('#form_add .' + key).text(er[key][0]).show();
                        });
                    }
                }
            });
        });

        // ─── form_edit submit ───
        $('#form_edit').submit(function(event) {
            event.preventDefault();
            $.ajax({
                type: 'PUT',
                url: `${URL_INDEX_API}/${window._editId}`,
                data: $(this).serialize(),
                beforeSend: function() { $('#form_edit .text-danger').hide(); },
                success: function() {
                    fetchData();
                    $('#modal_edit').modal('hide');
                },
                error: function(xhr) {
                    if (xhr.status != 422) {
                        show_message(xhr.responseJSON.message || 'Error!');
                    } else {
                        let er = xhr.responseJSON.errors;
                        Object.keys(er).forEach(function(key) {
                            $('#form_edit .' + key).text(er[key][0]).show();
                        });
                    }
                }
            });
        });

        // ─── form_trx submit ───
        $('#form_trx').submit(function(event) {
            event.preventDefault();
            let data = {
                atk_id: $('#trx_atk_id').val(),
                date: $('#trx_date').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                type: $('#trx_type').val(),
                pic: $('#trx_pic').val(),
                qty: $('#trx_qty').val(),
                desc: $('#trx_desc').val(),
            };
            $.ajax({
                type: 'POST',
                url: URL_TRX_STORE,
                data: data,
                beforeSend: function() { $('#form_trx .text-danger').hide(); },
                success: function() {
                    fetchData();
                    $('#modal_trx').modal('hide');
                },
                error: function(xhr) {
                    if (xhr.status != 422) {
                        show_message(xhr.responseJSON.message || 'Error!');
                    } else {
                        let er = xhr.responseJSON.errors;
                        Object.keys(er).forEach(function(key) {
                            $('#form_trx .' + key).text(er[key][0]).show();
                        });
                    }
                }
            });
        });

        // ─── modal shown focus ───
        $('#modal_trx').on('shown.bs.modal', function() { $('#trx_qty').focus(); });
        $('#modal_edit').on('shown.bs.modal', function() { $('#edit_code').focus(); });
        $('#modal_add').on('shown.bs.modal', function() { $('#add_code').focus(); });

        // ─── init ───
        $(document).ready(function() {
            $('#trx_date').daterangepicker({
                singleDatePicker: true,
                autoApply: true,
                locale: { format: 'DD/MM/YYYY' }
            });

            fetchData();
        });
    </script>
@endpush
