@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #007bff;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('problem.update', $data->id) }}" id="form">
            @csrf
            @method('PUT')
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">Edit Problem: {{ $data->number }}</h3>
                </div>

                <div class="card-body">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" placeholder="Date"
                                value="{{ $data->date }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="number">Number</label>
                            <input type="text" name="number" id="number" class="form-control" placeholder="Number"
                                value="{{ $data->number }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control" style="width: 100%;">
                                <option @selected($data->type == 'dus') value="dus">Dus</option>
                                <option @selected($data->type == 'unit') value="unit">Unit</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stock">Stock</label>
                            <select name="stock" id="stock" class="form-control" style="width: 100%;">
                                <option @selected($data->stock == 'stock') value="stock">Stock</option>
                                <option @selected($data->stock == 'import') value="import">Import</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ri_po">RI/PO</label>
                            <input type="text" name="ri_po" id="ri_po" class="form-control" placeholder="RI/PO"
                                value="{{ $data->ri_po }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_on">Email On</label>
                            <input type="date" name="email_on" id="email_on" class="form-control" placeholder="Email On"
                                value="{{ $data->email_on }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" style="width: 100%;">
                                <option @selected($data->status == 'pending') value="pending">Pending</option>
                                <option @selected($data->status == 'done') value="done">Done</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pic">PIC</label>
                            <select name="pic" id="pic" class="form-control" style="width: 100%;" required>
                                <option @selected($data->pic == 'Karim') value="Karim">Karim</option>
                                <option @selected($data->pic == 'Sofyan') value="Sofyan">Sofyan</option>
                                <option @selected($data->pic == 'Asep') value="Asep">Asep</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('problem.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>

        <!-- Tabs for Items and Logs -->
        <div class="card mt-3">
            <div class="card-header p-0">
                <ul class="nav nav-tabs" id="problemTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="items-tab" data-toggle="tab" href="#items" role="tab">
                            <i class="fas fa-box mr-1"></i>Items
                            <span class="badge badge-primary" id="items-count">{{ count($data->items) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="logs-tab" data-toggle="tab" href="#logs" role="tab">
                            <i class="fas fa-history mr-1"></i>Logs
                            <span class="badge badge-secondary" id="logs-count">{{ count($data->logs) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="problemTabsContent">
                    <!-- Items Tab -->
                    <div class="tab-pane fade show active" id="items" role="tabpanel">
                        <div class="mb-3">
                            <button type="button" class="btn btn-info" id="btn_add_item">
                                <i class="fas fa-plus mr-1"></i>Tambah Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="table_items" style="width: 100%;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Lot</th>
                                        <th>Desc</th>
                                        <th style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Logs Tab -->
                    <div class="tab-pane fade" id="logs" role="tabpanel">
                        <div class="mb-3">
                            <button type="button" class="btn btn-info" id="btn_add_log">
                                <i class="fas fa-plus mr-1"></i>Tambah Log
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="table_logs" style="width: 100%;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th style="width: 150px;">Date</th>
                                        <th>Description</th>
                                        <th style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
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
            // Initialize Select2 for all form selects
            $('#type, #stock, #status, #pic').select2({
                theme: 'bootstrap4',
                minimumResultsForSearch: Infinity
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
                searching: false,
                info: false,
                lengthChange: false,
                paging: false,
                rowId: 'id',
                columns: [{
                        data: 'id',
                        width: '40px',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: "product",
                        render: function(data, type, row, meta) {
                            if (data) {
                                return `[${data.code}] ${data.name || ''}`;
                            }
                            return '-';
                        }
                    },
                    {
                        data: "qty",
                        className: "text-center"
                    },
                    {
                        data: "lot",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: "desc",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: "id",
                        className: "text-center",
                        render: function(data, type, row, meta) {
                            return `<button type="button" class="btn btn-sm btn-warning mr-1 btn-edit-item"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-item"><i class="fas fa-trash"></i></button>`;
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
                searching: false,
                info: false,
                lengthChange: false,
                paging: false,
                rowId: 'id',
                order: [
                    [1, 'desc']
                ],
                columns: [{
                        data: 'id',
                        width: '40px',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: "date",
                        className: "text-left"
                    },
                    {
                        data: "desc",
                        className: "text-left",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: "id",
                        className: "text-center",
                        render: function(data, type, row, meta) {
                            return `<button type="button" class="btn btn-sm btn-warning mr-1 btn-edit-log"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-log"><i class="fas fa-trash"></i></button>`;
                        }
                    }
                ]
            });

            // Initialize Select2 for product
            $('#select_product').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal_item'),
            });

            // =================== ITEM CRUD ===================

            // Add Item
            $('#btn_add_item').click(function() {
                $('#modal_item_title').text('Tambah Item');
                $('#form_item')[0].reset();
                $('#form_item').attr('action', 'store');
                $('#select_product').val('').trigger('change');
                $('#modal_item').modal('show');
            });

            // Save Item
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

                if (!data.product_id) {
                    show_message('Pilih product terlebih dahulu!');
                    return;
                }

                $.ajax({
                    type: method,
                    url: url,
                    data: data
                }).done(function(result) {
                    show_message(result.message, 'success');
                    $('#modal_item').modal('hide');
                    tableItems.ajax.reload();
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            // Edit Item
            $('#table_items tbody').on('click', '.btn-edit-item', function() {
                var row = tableItems.row($(this).closest('tr'));
                var data = row.data();

                $('#modal_item_title').text('Edit Item');
                $('#form_item').attr('action', data.id);
                $('#select_product').val(data.product_id).trigger('change');
                $('#item_qty').val(data.qty);
                $('#item_lot').val(data.lot || '');
                $('#item_desc').val(data.desc || '');
                $('#modal_item').modal('show');
            });

            // Delete Item
            $('#table_items tbody').on('click', '.btn-delete-item', function() {
                var row = tableItems.row($(this).closest('tr'));
                var id = row.id();

                confirmation('Yakin ingin menghapus item ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            type: 'DELETE',
                            url: `${URL_API_ITEM}/${id}`,
                        }).done(function(result) {
                            show_message(result.message, 'success');
                            tableItems.ajax.reload();
                        }).fail(function(xhr) {
                            show_message(xhr.responseJSON.message || 'Error!');
                        });
                    }
                });
            });

            // =================== LOG CRUD ===================

            // Add Log
            $('#btn_add_log').click(function() {
                $('#modal_log_title').text('Tambah Log');
                $('#form_log')[0].reset();
                $('#form_log').attr('action', 'store');
                $('#log_date').val(new Date().toISOString().split('T')[0]);
                $('#modal_log').modal('show');
            });

            // Save Log
            $('#btn_save_log').click(function() {
                let action = $('#form_log').attr('action');
                let method = action === 'store' ? 'POST' : 'PUT';
                let url = action === 'store' ? URL_API_LOG : `${URL_API_LOG}/${action}`;

                let data = {
                    problem_id: PROBLEM_ID,
                    date: $('#log_date').val(),
                    desc: $('#log_desc').val(),
                };

                if (!data.date) {
                    show_message('Masukkan tanggal!');
                    return;
                }

                $.ajax({
                    type: method,
                    url: url,
                    data: data
                }).done(function(result) {
                    show_message(result.message, 'success');
                    $('#modal_log').modal('hide');
                    tableLogs.ajax.reload();
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            // Edit Log
            $('#table_logs tbody').on('click', '.btn-edit-log', function() {
                var row = tableLogs.row($(this).closest('tr'));
                var data = row.data();

                $('#modal_log_title').text('Edit Log');
                $('#form_log').attr('action', data.id);
                $('#log_date').val(data.date);
                $('#log_desc').val(data.desc || '');
                $('#modal_log').modal('show');
            });

            // Delete Log
            $('#table_logs tbody').on('click', '.btn-delete-log', function() {
                var row = tableLogs.row($(this).closest('tr'));
                var id = row.id();

                confirmation('Yakin ingin menghapus log ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            type: 'DELETE',
                            url: `${URL_API_LOG}/${id}`,
                        }).done(function(result) {
                            show_message(result.message, 'success');
                            tableLogs.ajax.reload();
                        }).fail(function(xhr) {
                            show_message(xhr.responseJSON.message || 'Error!');
                        });
                    }
                });
            });
        });
    </script>
@endpush
