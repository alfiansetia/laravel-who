@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <form class="form-inline">
            <div class="form-group col-md-4 mb-2 pl-0">
                <select name="product" class="form-control" id="product" style="width: 100%">
                    <option value="">Semua Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->code }}">[{{ $product->code }}] {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-primary ml-1 mb-2" id="refresh">
                <i class="fas fa-sync mr-1"></i>REFRESH
            </button>
        </form>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Lot</th>
                            <th>Qty</th>
                            <th>Product</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    @include('lot.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const url_index = "{{ route('api.lots.index') }}"
            var id = 0


            $('#product').select2({
                theme: "bootstrap4",
                allowClear: true
            }).on('change', function(e) {
                table.ajax.reload()
            });


            var table = $('#table').DataTable({
                rowId: 'id',
                processing: true,
                serverSide: true,
                ajax: {
                    url: url_index,
                    data: function(dt) {
                        product = $('#product').val()
                        if (product != '') {
                            dt['product'] = product
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        let message = xhr.responseJSON?.message || 'Gagal memuat data PO!';
                        $('.dt-empty').text(message);
                        $('#table_processing').hide();
                        show_message(message, 'error');
                    },
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
                order: [
                    // [1, 'asc']
                ],
                pageLength: 10,
                lengthChange: false,
                columns: [{
                        data: "id",
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-sm btn-info btn-trace" data-id="${data}"><i class="fas fa-info-circle"></i></button>
                            </div>
                            `
                        }
                    }, {
                        data: "name",
                        className: "text-left",
                    }, {
                        data: "product_qty1",
                        className: "text-center",
                    },
                    {
                        data: "product_id",
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if (Array.isArray(data)) {
                                return data[1]
                            }
                            return data
                        }
                    },
                ],
                buttons: [{
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
                    },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download mr-1"></i>Export',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Export Data'
                        },
                        className: 'btn btn-sm btn-primary',
                        buttons: [{
                            extend: 'copy',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'csv',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }],
                    }, {
                        text: '<i class="fas fa-download mr-1"></i>Opname',
                        className: 'btn btn-sm btn-danger',
                        action: function(e, dt, node, config) {
                            getOpname();
                        }
                    }
                ],
            });

            $('#table tbody').on('click', 'tr td:not(:first-child)', function() {
                row = $(this).parents('tr')[0];
                id = table.row(this).id()
            });

            $('#table tbody').on('click', '.btn-trace', function() {
                let row = $(this).closest('tr');
                let id = table.row(row).id()
                $.ajax({
                    url: `${url_index}/${id}/trace`,
                    type: "GET",
                    success: function(response) {
                        let htmlContent = response.data.html || 'Tidak ada data';
                        $('#modal_trace_content').html(htmlContent);
                        if ($('#modal_trace_content table').length > 0) {
                            $('#modal_trace_content table').DataTable();
                        } else {
                            $('#modal_trace_content').append(
                                '<p class="text-center">Tidak ada data</p>');
                        }
                        $('#modal_trace').modal('show');
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        show_message(xhr.responseJSON?.message || 'Gagal memuat data PO!',
                            'error');
                    },
                })
            });

            $('#refresh').click(function() {
                table.ajax.reload()
            })

            function reload_table() {
                table.ajax.reload()
            }

            function hrg(x) {
                return parseInt(x).toLocaleString('en-US')
            }
        });
    </script>
@endpush
