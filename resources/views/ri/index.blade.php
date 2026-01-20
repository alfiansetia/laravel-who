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
                            <th>PO</th>
                            <th>NOTES</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal_product" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="modal_productLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_productLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="modalTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab"
                                aria-controls="product" aria-selected="true">Product</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="product-lot-tab" data-toggle="tab" href="#product-lot" role="tab"
                                aria-controls="product-lot" aria-selected="false">Product Lot</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="modalTabContent">
                        <div class="tab-pane fade show active" id="product" role="tabpanel" aria-labelledby="product-tab">
                            <table class="table table-hover" id="table_product" style="width: 100%;cursor: pointer;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Desc</th>
                                        <th>NAME</th>
                                        <th>AKL</th>
                                        <th style="width: 30px">QTY TOTAL</th>
                                        <th style="width: 30px">QTY DONE</th>
                                        <th style="width: 30px">QTY SISA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="product-lot" role="tabpanel" aria-labelledby="product-lot-tab">
                            <table class="table table-hover" id="table_product_lot" style="width: 100%;cursor: pointer;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Desc</th>
                                        <th>LOT/SN</th>
                                        <th>QTY</th>
                                        <th>EXP DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="mt-2">
                        <span><b>Origin/PO</b> : </span>
                        <span id="modal_origin"></span>
                    </div>
                    <div class="mt-2">
                        <span><b>Notes</b> : </span>
                        <span id="modal_note" style="white-space: pre-wrap;"></span>
                    </div>
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
        var URL_INDEX_API = "{{ route('api.ri.index') }}"
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
                        className: 'text-left',
                        render: function(data, type, row) {
                            return `<b>${data}</b>`
                        }
                    },
                    {
                        data: "partner_id",
                        className: 'text-left',
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : data;
                        }
                    }, {
                        data: "origin",
                        className: 'text-left',
                    }, {
                        data: "note_to_wh",
                        className: 'text-left',
                        render: function(data, type, row) {
                            return data.length > 50 ? data.substring(0, 50) + '...' : data
                        }
                    },
                    {
                        data: "state",
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
                pageLength: 50,
                paging: true,
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
                    }, {
                        data: "name",
                    },
                    {
                        data: "akl_id",
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : data;
                        }
                    }, {
                        data: "product_uom_qty",
                        className: 'text-center',
                    }, {
                        data: "quantity_done",
                        className: 'text-center',
                    }, {
                        data: "quantity_done",
                        className: 'text-center',
                        render: function(data, type, row) {
                            return row.product_uom_qty - data;
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

            var table_product_lot = $("#table_product_lot").DataTable({
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
                pageLength: 10,
                paging: true,
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
                    }, {
                        data: "lot_id",
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : (row.lot_name ? row.lot_name :
                                data);
                        }
                    },
                    {
                        data: "qty_done",
                        className: 'text-center',
                    }, {
                        data: "expired_date",
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
                let name = table.row(row).data().name
                let note = table.row(row).data().note_to_wh
                let origin = table.row(row).data().origin
                $('#modal_productLabel').html(`List Item RI No : ${name}`)
                $('#modal_note').html(note)
                $('#modal_origin').html(origin)
                $.ajax({
                    url: `${URL_INDEX_API}/${id}`,
                    type: 'GET',
                    success: function(res) {
                        table_product
                            .clear()
                            .rows
                            .add(res.data.move_without_package_detail)
                            .draw();

                        table_product_lot
                            .clear()
                            .rows
                            .add(res.data.move_line_ids_without_package_detail)
                            .draw();

                        $('#modal_product').modal('show');
                        $('#product-tab').tab('show'); // Ensure first tab is active
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Gagal memuat data RI!',
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
