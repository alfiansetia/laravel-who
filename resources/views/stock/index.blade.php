@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <h1>{{ $title }}</h1>
        <form class="form-inline">
            <div class="form-group col-md-4 mb-2 pl-0">
                <select name="location" class="form-control" id="location" multiple style="width: 100%">
                    <option value="center" selected>CENTER</option>
                    <option value="cbb">CIBUBUR</option>
                    <option value="krtn">KARANTINA</option>
                    <option value="badstock">BADSTOCK</option>
                    <option value="demo">DEMO</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary ml-1 mb-2" id="refresh">REFRESH</button>
        </form>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>KODE</th>
                            <th>NAME</th>
                            <th>QTY</th>
                            {{-- <th>#</th> --}}
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
        <div class="modal-dialog modal-lg">
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
                                <th>LOCATION</th>
                                <th>Lot/SN</th>
                                <th>ED</th>
                                <th style="width: 30px">QTY</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <br>
                    <textarea name="" id="detail_lot" class="form-control mb-2"></textarea>
                    <textarea name="" id="detail_sn" class="form-control"></textarea>
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
            var url_index = "{{ route('api.stock.index') }}"
            var id = 0
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: {
                    url: url_index,
                    data: function(dt) {
                        loc = $('#location').val()
                        if (loc.length < 1) {
                            loc[0] = 'center'
                            $('#location').val('center').change()
                        }
                        dt['location[]'] = loc
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert((jqXHR.responseJSON.message || 'Odoo Error! ') + ', code : ' + jqXHR
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
                pageLength: 10,
                lengthChange: false,
                columns: [{
                        data: "code",
                    }, {
                        data: "name",
                    },
                    {
                        data: "quantity",
                    },
                    // {
                    //     data: "id",
                    //     render: function(data, type, row, meta) {
                    //         if (type == 'display') {
                    //             return `<button type="button" class="btn btn-sm btn-info btn-lot">View</button>`
                    //         } else {
                    //             return data
                    //         }
                    //     }
                    // },
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
                //     id = table.row(this).id()
                //     window.location.href = "{{ url('alamat') }}/" + id + '/edit'
                // });

                // $('#table tbody').on('click', 'tr .btn-lot', function() {
                row = $(this).parents('tr')[0];
                id = table.row(this).id()
                // id = table.row(row).data().id
                let code = table.row(row).data().code
                let name = table.row(row).data().name
                var qty = table.row(row).data().quantity
                let loc = $('#location').val()
                if (loc.length < 1) {
                    loc[0] = 'center'
                    $('#location').val('center').change()
                }
                $('#modal_lotLabel').html(`Lot/SN : ${code} ${name}`)
                $('#table_lot').DataTable().clear().destroy();
                table_lot = $("#table_lot").DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: `${url_index}/${id}`,
                        data: function(dt) {
                            loc = $('#location').val()
                            if (loc.length < 1) {
                                loc[0] = 'center'
                                $('#location').val('center').change()
                            }
                            dt['location[]'] = loc
                            dt['limit'] = qty
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
                            data: "location",
                        }, {
                            data: "lot",
                        },
                        {
                            data: "expired",
                        }, {
                            data: "quantity",
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
                    initComplete: function(settings, json) {
                        let data = json.data || []
                        let text_lot = 'Lot '
                        let text_sn = `${data.length} Pcs, SN : `
                        data.forEach(item => {
                            text_lot += `${item.lot} = ${item.quantity} Pcs, `
                            text_sn += `${item.lot}, `
                        });

                        $('#detail_lot').val(text_lot)
                        $('#detail_sn').val(text_sn)

                    }
                });


                $('#modal_lot').modal('show')

            });

            $('#refresh').click(function() {
                table.ajax.reload()
            })

            $('#location').select2({
                allowClear: false
            }).on('change', function(e) {
                table.ajax.reload()
            });

            function reload_table() {
                table.ajax.reload()
            }

            function hrg(x) {
                return parseInt(x).toLocaleString('en-US')
            }
        });
    </script>
@endsection
