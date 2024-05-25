@extends('template')

@section('content')
    <div class="container-fluid">
        <h1>{{ $title }} Center</h1>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>PRODUCT</th>
                            <th>QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    @if (session()->has('message'))
        <script>
            alert("{{ session('message') }}")
        </script>
    @endif
    <script>
        $(document).ready(function() {
            var table = $('#table').DataTable({
                // rowId: 'id',
                ajax: {
                    url: "{{ route('api.stock.index') }}",
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Odoo Error, code : ' + jqXHR.status)
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
                        data: 'product_id',
                        className: "text-center",
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, {
                        data: "product_id",
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return data[1]
                            } else {
                                return data
                            }
                        }
                    },
                    {
                        data: "quantity",
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return hrg(data)
                            } else {
                                return data
                            }
                        }
                    },
                ],
                buttons: [{
                        extend: "collection",
                        text: '<i class="fas fa-cogs mr-1"></i>Actions',
                        autoClose: true, // Menutup menu saat tombol submenu dipilih
                        buttons: [{
                            text: '<i class="fas fa-sync mr-1"></i>Syncronize from Odoo',
                            className: 'btn btn-sm btn-danger',
                            attr: {
                                'data-toggle': 'tooltip',
                                'title': 'Syncronize from Odoo'
                            },
                            action: function(e, dt, node, config) {
                                reload_table()
                            }
                        }, ]
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

            function reload_table() {
                table.ajax.reload()
            }

            function hrg(x) {
                return parseInt(x).toLocaleString('en-US')
            }
        });
    </script>
@endsection
