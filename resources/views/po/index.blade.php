@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <h1>{{ $title }}</h1>
        <div class="row mb-2">
            <div class="form-group col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="search" placeholder="CARI No PO" value="PO">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="btn_get_po">SEARCH</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>NO</th>
                            <th>VENDOR</th>
                            <th>USER</th>
                            <th>NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal_lot" data-backdrop="static" tabindex="-1" aria-labelledby="modal_lotLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_lotLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover" id="table_lot" style="width: 100%;cursor: pointer;">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Desc</th>
                                <th>AKL</th>
                                <th style="width: 30px">QTY</th>
                                <th style="width: 30px">QTY RI</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    @if (session()->has('message'))
        <script>
            alert("{{ session('message') }}")
        </script>
    @endif

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush
    <script>
        $(document).ready(function() {
            var url_index = "{{ route('api.po.index') }}"
            var id = 0
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: {
                    url: url_index,
                    // dataSrc: 'records',
                    data: function(dt) {
                        search = $('#search').val()
                        dt['search'] = search
                        dt['limit'] = 500
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert((jqXHR.responseJSON.message || 'Odoo Error! ') + ', code : ' + jqXHR
                            .status)
                        console.log(jqXHR);
                    },
                },
                order: [
                    [0, 'desc']
                ],
                dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
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
                    },
                    {
                        data: "vendor",
                    }, {
                        data: "user",
                    }, {
                        data: "notes",
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
                        text: '<i class="fas fa-download"></i>Export',
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
                var lines = table.row(row).data().order_line
                $('#modal_lotLabel').html(`List Item PO No : ${name}`)
                $('#table_lot').DataTable().clear().destroy();
                table_lot = $("#table_lot").DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: `{{ url('api/po') }}/order-line`,
                        // dataSrc: 'result',
                        data: function(dt) {
                            dt['lines[]'] = lines
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert((jqXHR.responseJSON.message || 'Odoo Error! ') + ', code : ' +
                                jqXHR
                                .status)
                            console.log(jqXHR);
                        },
                    },
                    dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
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
                            data: "code",
                        }, {
                            data: "name",
                        },
                        {
                            data: "akl",
                        }, {
                            data: "quantity",
                        }, {
                            data: "qty_ri",
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
                            text: '<i class="fas fa-download"></i>Export',
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

                $('#modal_lot').modal('show')

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
@endsection
