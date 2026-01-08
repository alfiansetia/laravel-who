@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        #table th,
        #table td {
            white-space: nowrap;
            vertical-align: middle !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Filters -->
        <form class="form-inline mb-3" id="filter_form">
            <div class="form-group mr-2">
                <label for="filter_type" class="mr-2">Type:</label>
                <select name="type" id="filter_type" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="dus">Dus</option>
                    <option value="unit">Unit</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <label for="filter_year" class="mr-2">Tahun:</label>
                <select name="year" id="filter_year" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @for ($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group mr-2">
                <label for="filter_product" class="mr-2">Product:</label>
                <select name="product_id" id="filter_product" class="form-control form-control-sm" style="width: 250px;">
                    <option value="">Semua Product</option>
                    @foreach ($products as $item)
                        <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-2">
                <label for="filter_status" class="mr-2">Status:</label>
                <select name="status" id="filter_status" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="pending">Pending</option>
                    <option value="done">Done</option>
                </select>
            </div>
            <button type="button" class="btn btn-sm btn-primary" id="btn_filter">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
            <button type="button" class="btn btn-sm btn-secondary ml-1" id="btn_reset">
                <i class="fas fa-undo mr-1"></i>Reset
            </button>
        </form>

        <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Number</th>
                    <th style="width: 100px;">Date</th>
                    <th style="width: 60px;">Type</th>
                    <th style="width: 60px;">Stock</th>
                    <th style="width: 80px;">Status</th>
                    <th>PIC</th>
                    <th style="width: 50px;">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all filters
            $('#filter_type').select2({
                theme: 'bootstrap4',
                minimumResultsForSearch: Infinity
            });

            $('#filter_year').select2({
                theme: 'bootstrap4',
                minimumResultsForSearch: Infinity
            });

            $('#filter_product').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: 'Semua Product'
            });

            $('#filter_status').select2({
                theme: 'bootstrap4',
                minimumResultsForSearch: Infinity
            });

            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: {
                    url: "{{ route('api.problem.index') }}",
                    data: function(d) {
                        d.type = $('#filter_type').val();
                        d.year = $('#filter_year').val();
                        d.product_id = $('#filter_product').val();
                        d.status = $('#filter_status').val();
                    }
                },
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
                lengthChange: false,
                order: [
                    [2, "desc"]
                ],
                columns: [{
                        data: 'id',
                        className: 'text-center',
                        width: '40px',
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            } else {
                                return data
                            }
                        }
                    }, {
                        data: "number",
                        className: 'text-left',
                    },
                    {
                        data: "date",
                        className: 'text-center',
                    },
                    {
                        data: "type",
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                let badge = data == 'dus' ? 'badge-info' : 'badge-warning';
                                return `<span class="badge ${badge}">${data.toUpperCase()}</span>`;
                            }
                            return data;
                        }
                    },
                    {
                        data: "stock",
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                let badge = data == 'stock' ? 'badge-success' : 'badge-secondary';
                                return `<span class="badge ${badge}">${data.toUpperCase()}</span>`;
                            }
                            return data;
                        }
                    },
                    {
                        data: "status",
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                let badge = data == 'done' ? 'badge-success' : 'badge-danger';
                                return `<span class="badge ${badge}">${data.toUpperCase()}</span>`;
                            }
                            return data;
                        }
                    },
                    {
                        data: "pic",
                        className: 'text-left',
                    },
                    {
                        data: "id",
                        className: 'text-center',
                        orderable: false,
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return `<button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>`
                            } else {
                                return data
                            }
                        }
                    },
                ],
                buttons: [{
                        text: '<i class="fa fa-plus mr-1"></i>Tambah',
                        className: 'btn btn-sm btn-info bs-tooltip',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Tambah Data'
                        },
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route('problems.create') }}"
                        }
                    },
                    {
                        extend: "colvis",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Column Visible'
                        },
                        className: 'btn btn-sm btn-primary'
                    },
                    {
                        extend: "pageLength",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Page Length'
                        },
                        className: 'btn btn-sm btn-info'
                    }
                ],
            });

            // Filter button
            $('#btn_filter').click(function() {
                table.ajax.reload();
            });

            // Reset button
            $('#btn_reset').click(function() {
                $('#filter_type').val('').trigger('change');
                $('#filter_year').val('').trigger('change');
                $('#filter_product').val('').trigger('change');
                $('#filter_status').val('').trigger('change');
                table.ajax.reload();
            });

            $('#table tbody').on('click', 'tr td:not(:last-child)', function() {
                id = table.row(this).id()
                window.location.href = "{{ url('problem') }}/" + id + '/edit'
            });


            $('#table tbody').on('click', '.btn-delete', function() {
                id = table.row($(this).parents('tr')[0]).id()
                confirmation('Yakin ingin menghapus data ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            url: `{{ route('api.problem.index') }}/${id}`,
                            type: 'DELETE',
                            success: function(result) {
                                show_message(result.message || 'Berhasil dihapus',
                                    'success')
                                table.ajax.reload()
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message || 'Error!')
                            }
                        })
                    }
                });
            });
        });
    </script>
@endpush
