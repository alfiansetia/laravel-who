@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="form-group col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="search" placeholder="CARI No SO">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="btn_get_po">
                            <i class="fas fa-search mr-1"></i>SEARCH
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>NO SO</th>
                            <th>DATE</th>
                            <th>CUSTOMER</th>
                            <th>NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal_product" data-backdrop="static" tabindex="-1" aria-labelledby="modal_productLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_productLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover" id="table_product" style="width: 100%;cursor: pointer;">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Desc</th>
                                <th>Price</th>
                                <th style="width: 30px">QTY Order</th>
                                <th style="width: 30px">QTY Delivered</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const URL_INDEX = "{{ route('api.so.index') }}"
        var id = 0;

        $(document).ready(function() {
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: {
                    url: URL_INDEX,
                    data: function(dt) {
                        search = $('#search').val()
                        dt['search'] = search
                        dt['limit'] = 80
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    },
                },
                order: [
                    [0, 'desc']
                ],
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
                columns: [{
                        data: "name",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return `<b>${data}</b>`
                            }
                            return data
                        }
                    },
                    {
                        data: "date_order",
                        className: 'text-left',
                    }, {
                        data: "customer",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            let name = ''
                            if (type === 'display') {
                                name = `${row.partner_id == false ? '': row.partner_id[1]}`
                                return name.substr(0, 25)
                            }
                            return name
                        }
                    }, {
                        data: "note_to_wh",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return `${data.substr(0, 35)}`
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
                    }
                ],
            });

            var table_product = $("#table_product").DataTable({
                processing: true,
                serverSide: false,
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
                paging: false,
                scrollCollapse: true,
                scrollY: '400px',
                columns: [{
                        data: "default_code",
                    }, {
                        data: "name",
                    },
                    {
                        data: "unit_price1",
                    }, {
                        data: "product_uom_qty",
                        className: 'text-center',
                    }, {
                        data: "qty_delivered",
                        className: 'text-center',
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
                    }
                ],
            });

            $('#table tbody').on('click', 'tr td', function() {
                row = $(this).parents('tr')[0];
                id = table.row(this).id()
                let name = table.row(this).data().name
                $('#modal_productLabel').html(`List Item SO No : ${name}`)
                $.ajax({
                    url: `${URL_INDEX}/${id}`,
                    type: 'GET',
                    success: function(res) {
                        table_product.clear().draw();
                        table_product.rows.add(res.data.order_line_detail).draw();
                        $('#modal_product').modal('show')
                    }
                })

            });

            $('#refresh').click(function() {
                table.ajax.reload()
            })

            $('#location').select2({
                allowClear: false
            })

            function reload_table() {
                table.ajax.reload()
            }

            function hrg(x) {
                return parseInt(x).toLocaleString('en-US')
            }

            $('#btn_get_po').click(function() {
                table.ajax.reload()
            })
        });
    </script>
@endpush
