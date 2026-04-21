@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .form-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .form-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .section-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f1f5f9;
            margin-left: 1rem;
        }

        /* Modern Tabs */
        .custom-tabs .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .custom-tabs .nav-link.active {
            color: #6366f1;
            background: transparent;
            border-bottom: 2px solid #6366f1;
        }

        .custom-tabs .nav-link:hover:not(.active) {
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
        }

        .tab-count {
            font-size: 0.75rem;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 6px;
        }

        .nav-link.active .tab-count {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .table-items th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 1rem;
        }

        .table-items td {
            padding: 1rem;
            vertical-align: middle !important;
        }

        .btn-action-sm {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin: 0 2px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <form method="POST" action="{{ route('problems.update', $data->id) }}" id="form">
            @csrf
            @method('PUT')
            
            <!-- Edit Header Card -->
            <div class="card form-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Edit Problem: <span class="text-primary">{{ $data->number }}</span></h5>
                    <a href="{{ route('problems.index') }}" class="btn btn-sm btn-light text-secondary">
                        <i class="fas fa-times mr-1"></i>Batal
                    </a>
                </div>
                <div class="card-body">
                    <div class="section-title">Informasi Utama</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Nomor Problem</label>
                            <input type="text" name="number" id="number" class="form-control"
                                value="{{ $data->number }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal</label>
                            <input type="text" name="date" id="date" class="form-control datepicker"
                                value="{{ $data->date }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>PIC</label>
                            <select name="pic" id="pic" class="form-control select2" required>
                                <option @selected($data->pic == 'Karim') value="Karim">Karim</option>
                                <option @selected($data->pic == 'Sofyan') value="Sofyan">Sofyan</option>
                                <option @selected($data->pic == 'Asep') value="Asep">Asep</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-3">Detail & Status</div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Type</label>
                            <select name="type" id="type" class="form-control select2">
                                <option @selected($data->type == 'dus') value="dus">Dus</option>
                                <option @selected($data->type == 'unit') value="unit">Unit</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Stock</label>
                            <select name="stock" id="stock" class="form-control select2">
                                <option @selected($data->stock == 'stock') value="stock">Stock</option>
                                <option @selected($data->stock == 'import') value="import">Import</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control select2">
                                <option @selected($data->status == 'pending') value="pending">Pending</option>
                                <option @selected($data->status == 'done') value="done">Done</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>RI / PO</label>
                            <input type="text" name="ri_po" id="ri_po" class="form-control" value="{{ $data->ri_po }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Email On</label>
                            <input type="text" name="email_on" id="email_on" class="form-control datepicker" value="{{ $data->email_on }}">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 d-flex justify-content-end" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="submit" id="btn_simpan" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 600;">
                        <i class="fas fa-save mr-2"></i>Update Informasi
                    </button>
                </div>
            </div>
        </form>

        <!-- Managed Content Stats (Tabs) -->
        <div class="card form-card mb-5">
            <div class="card-header p-0">
                <ul class="nav custom-tabs" id="problemTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="items-tab" data-toggle="tab" href="#items" role="tab">
                            <i class="fas fa-boxes mr-2"></i>Produk Bermasalah
                            <span class="tab-count" id="items-count">{{ count($data->items) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="logs-tab" data-toggle="tab" href="#logs" role="tab">
                            <i class="fas fa-history mr-2"></i>History Log
                            <span class="tab-count" id="logs-count">{{ count($data->logs) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="problemTabsContent">
                    <!-- Items Tab -->
                    <div class="tab-pane fade show active" id="items" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="font-weight-bold text-dark m-0">Daftar Item</h6>
                            <button type="button" class="btn btn-sm btn-primary px-3" id="btn_add_item">
                                <i class="fas fa-plus mr-1"></i>Tambah Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-items" id="table_items" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th>Lot / SN</th>
                                        <th>Description</th>
                                        <th style="width: 100px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Logs Tab -->
                    <div class="tab-pane fade" id="logs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="font-weight-bold text-dark m-0">Catatan Progress (Log)</h6>
                            <button type="button" class="btn btn-sm btn-primary px-3" id="btn_add_log">
                                <i class="fas fa-plus mr-1"></i>Tambah Log
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-items" id="table_logs" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th style="width: 150px;">Tanggal</th>
                                        <th>Keterangan / Progress</th>
                                        <th style="width: 100px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('problem.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const PROBLEM_ID = {{ $data->id }};
        const URL_API_PROBLEM = "{{ route('api.problem.show', $data->id) }}";
        const URL_API_ITEM = "{{ url('api/problem-item') }}";
        const URL_API_LOG = "{{ url('api/problem-log') }}";

        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            $(".datepicker").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true,
            });

            // Items DataTable
            var tableItems = $('#table_items').DataTable({
                ajax: {
                    url: URL_API_PROBLEM,
                    dataSrc: function(result) {
                        $('#items-count').text(result.data.items.length);
                        return result.data.items;
                    }
                },
                dom: 'rtip',
                language: {
                    emptyTable: `
                        <div class="py-4 text-muted">
                            <i class="fas fa-box-open d-block mb-2 shadow-sm p-2 rounded-circle mx-auto"
                                style="width: 45px; height: 45px; font-size: 1.25rem; background: #f8fafc; line-height: 29px;"></i>
                            Belum ada item ditambahkan
                        </div>`
                },
                searching: false,
                info: true,
                lengthChange: false,
                paging: true,
                pageLength: 10,
                rowId: 'id',
                columns: [{
                        data: 'id',
                        className: "text-center text-muted small font-weight-bold",
                        render: (d, t, r, m) => m.row + 1
                    },
                    {
                        data: "product",
                        render: function(data) {
                            if (!data) return '-';
                            return `<div class="font-weight-bold text-dark">${data.code}</div><div class="small text-muted">${data.name || ''}</div>`;
                        }
                    },
                    {
                        data: "qty",
                        className: "text-center font-weight-bold text-primary"
                    },
                    {
                        data: "lot",
                        render: d => d ? `<span class="badge badge-light border px-2">${d}</span>` : '<span class="text-muted">-</span>'
                    },
                    {
                        data: "desc",
                        className: "small text-muted",
                        render: d => d || '-'
                    },
                    {
                        data: "id",
                        className: "text-center",
                        render: function(data) {
                            return `<div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-action-sm btn-outline-warning btn-edit-item" title="Edit Item"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-action-sm btn-outline-danger btn-delete-item" title="Hapus Item"><i class="fas fa-trash"></i></button>
                                    </div>`;
                        }
                    }
                ]
            });

            // Logs DataTable
            var tableLogs = $('#table_logs').DataTable({
                ajax: {
                    url: URL_API_PROBLEM,
                    dataSrc: function(result) {
                        $('#logs-count').text(result.data.logs.length);
                        return result.data.logs;
                    }
                },
                dom: 'rtip',
                language: {
                    emptyTable: `
                        <div class="py-4 text-muted">
                            <i class="fas fa-history d-block mb-2 shadow-sm p-2 rounded-circle mx-auto"
                                style="width: 45px; height: 45px; font-size: 1.25rem; background: #f8fafc; line-height: 29px;"></i>
                            Belum ada catatan log
                        </div>`
                },
                searching: false,
                info: true,
                lengthChange: false,
                paging: true,
                pageLength: 5,
                rowId: 'id',
                order: [[1, 'desc']],
                columns: [{
                        data: 'id',
                        className: "text-center text-muted small font-weight-bold",
                        render: (d, t, r, m) => m.row + 1
                    },
                    {
                        data: "date",
                        render: d => `<span class="text-muted small"><i class="far fa-calendar-alt mr-1"></i>${d}</span>`
                    },
                    {
                        data: "desc",
                        className: "text-dark",
                        render: d => d || '-'
                    },
                    {
                        data: "id",
                        className: "text-center",
                        render: function(data) {
                            return `<div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-action-sm btn-outline-warning btn-edit-log" title="Edit Log"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-action-sm btn-outline-danger btn-delete-log" title="Hapus Log"><i class="fas fa-trash"></i></button>
                                    </div>`;
                        }
                    }
                ]
            });

            // Modals Logic - Product select
            $('#select_product').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal_item'),
                width: '100%'
            });

            // ITEM CRUD ACTIONS
            $('#btn_add_item').click(function() {
                $('#modal_item_title').text('Tambah Item Problem');
                $('#form_item')[0].reset();
                $('#form_item').attr('action', 'store');
                $('#select_product').val('').trigger('change');
                $('#modal_item').modal('show');
            });

            $('#btn_save_item').click(function() {
                let action = $('#form_item').attr('action');
                let method = action === 'store' ? 'POST' : 'PUT';
                let url = action === 'store' ? URL_API_ITEM : `${URL_API_ITEM}/${action}`;
                let data = {
                    problem_id: PROBLEM_ID,
                    product_id: $('#select_product').val(),
                    qty: $('#item_qty').val(),
                    lot: $('#item_lot').val(),
                    desc: $('#item_desc').val(),
                };

                if (!data.product_id) { show_message('Pilih product!'); return; }

                $.ajax({ type: method, url: url, data: data }).done(function(res) {
                    show_message(res.message, 'success');
                    $('#modal_item').modal('hide');
                    tableItems.ajax.reload();
                }).fail(xhr => show_message(xhr.responseJSON.message || 'Error!'));
            });

            $('#table_items tbody').on('click', '.btn-edit-item', function() {
                var data = tableItems.row($(this).closest('tr')).data();
                $('#modal_item_title').text('Edit Item Problem');
                $('#form_item').attr('action', data.id);
                $('#select_product').val(data.product_id).trigger('change');
                $('#item_qty').val(data.qty);
                $('#item_lot').val(data.lot || '');
                $('#item_desc').val(data.desc || '');
                $('#modal_item').modal('show');
            });

            $('#table_items tbody').on('click', '.btn-delete-item', function() {
                let id = tableItems.row($(this).closest('tr')).id();
                confirmation('Hapus item ini?', confirmed => {
                    if (confirmed) $.ajax({ type: 'DELETE', url: `${URL_API_ITEM}/${id}` })
                        .done(res => { show_message(res.message, 'success'); tableItems.ajax.reload(); });
                });
            });

            // LOG CRUD ACTIONS
            $('#btn_add_log').click(function() {
                $('#modal_log_title').text('Tambah Catatan Progress');
                $('#form_log')[0].reset();
                $('#form_log').attr('action', 'store');
                $('#log_date').val(new Date().toISOString().split('T')[0]);
                $('#modal_log').modal('show');
            });

            $('#btn_save_log').click(function() {
                let action = $('#form_log').attr('action');
                let method = action === 'store' ? 'POST' : 'PUT';
                let url = action === 'store' ? URL_API_LOG : `${URL_API_LOG}/${action}`;
                let data = { problem_id: PROBLEM_ID, date: $('#log_date').val(), desc: $('#log_desc').val() };

                $.ajax({ type: method, url: url, data: data }).done(function(res) {
                    show_message(res.message, 'success');
                    $('#modal_log').modal('hide');
                    tableLogs.ajax.reload();
                }).fail(xhr => show_message(xhr.responseJSON.message || 'Error!'));
            });

            $('#table_logs tbody').on('click', '.btn-edit-log', function() {
                var data = tableLogs.row($(this).closest('tr')).data();
                $('#modal_log_title').text('Edit Catatan Progress');
                $('#form_log').attr('action', data.id);
                $('#log_date').val(data.date);
                $('#log_desc').val(data.desc || '');
                $('#modal_log').modal('show');
            });

            $('#table_logs tbody').on('click', '.btn-delete-log', function() {
                let id = tableLogs.row($(this).closest('tr')).id();
                confirmation('Hapus log ini?', confirmed => {
                    if (confirmed) $.ajax({ type: 'DELETE', url: `${URL_API_LOG}/${id}` })
                        .done(res => { show_message(res.message, 'success'); tableLogs.ajax.reload(); });
                });
            });
        });
    </script>
@endpush
