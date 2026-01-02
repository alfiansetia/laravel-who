@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>NO</th>
                            <th>VENDOR</th>
                            <th>USER</th>
                            <th>NOTES</th>
                            <th>RI</th>
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
                    <div class="mb-2">
                        <span><b>Note</b> : </span>
                        <span id="modal_note"></span>
                    </div>
                    <table class="table table-hover" id="table_product" style="width: 100%;cursor: pointer;">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Desc</th>
                                <th>AKL</th>
                                <th style="width: 30px">QTY</th>
                                <th style="width: 30px">QTY RI</th>
                                <th style="width: 30px">QTY SISA</th>
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
        var URL_INDEX_API = "{{ route('api.po.index') }}"
        var id = 0
        $(document).ready(function() {

            var table = $('#table').DataTable({
                rowId: 'id',
                processing: true,
                serverSide: true,
                ajax: {
                    url: URL_INDEX_API,
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Gagal memuat data SO!', 'error');
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
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return `<b>${data}</b>`
                            }
                            return data;
                        }
                    },
                    {
                        data: "partner_id",
                        render: function(data, type, row) {
                            if (Array.isArray(data)) {
                                return data[1]
                            }
                            return data || '-';
                        }
                    }, {
                        data: "user_id",
                        render: function(data, type, row) {
                            if (Array.isArray(data)) {
                                return data[1]
                            }
                            return data || '-';
                        }
                    }, {
                        data: "notes",
                        render: function(data, type, row) {
                            if (type === 'display' && data) {
                                return data.length > 40 ? data.substring(0, 40) + '...' : data
                            }
                            return data || '-';
                        }
                    }, {
                        data: "picking_count",
                        className: 'text-center'
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
                        data: "product_id",
                        render: function(data, type, row) {
                            let text = Array.isArray(data) ? data[1] : data;
                            return getCode(text)
                        }
                    }, {
                        data: "product_id",
                        render: function(data, type, row) {
                            let text = Array.isArray(data) ? data[1] : data;
                            return getDesc(text)
                        }
                    },
                    {
                        data: "akl",
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : data;
                        }
                    }, {
                        data: "product_qty",
                        className: 'text-center',
                    }, {
                        data: "qty_received",
                        className: 'text-center',
                    }, {
                        data: "qty_received",
                        className: 'text-center',
                        render: function(data, type, row) {
                            return row.product_qty - data
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
                let name = table.row(row).data().name
                let note = table.row(row).data().notes
                $('#modal_productLabel').html(`List Item PO No : ${name}`)
                $('#modal_note').html(note)
                $.ajax({
                    url: `${URL_INDEX_API}/${id}`,
                    type: 'GET',
                    success: function(res) {
                        table_product.clear().rows.add(res.data.order_line_detail).draw();
                        $('#modal_product').modal('show');
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Gagal memuat data SO!',
                            'error');
                    },
                });
            });

            function getCode(str) {
                if (!str) return '';
                let match = str.match(/\[(.*?)\]/);
                return match ? match[1] : '';
            }

            function getDesc(str) {
                if (!str) return '';
                let match = str.match(/\]\s*(.*)/);
                return match ? match[1] : str;
            }

            function hrg(x) {
                return parseInt(x).toLocaleString('en-US')
            }

        });
    </script>
@endpush
