@extends('template')

@section('content')
    <div class="container-fluid">
        <h1>{{ $title }} <span id="total">: 0</span></h1>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>TGL</th>
                            <th>NO DO</th>
                            <th>NO SO</th>
                            <th>ITEM</th>
                            <th>QTY</th>
                            <th>NOTE</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

    <script>
        function jam(date, hours = 7) {
            if (date == '' || date == null) {
                return ''
            }
            return moment(date).add(hours, 'hours').format('DD/MM/YYYY HH:mm:ss');
        }

        $(document).ready(function() {
            var table = $('#table').DataTable({
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
                ordering: false,
                lengthChange: false,
                buttons: [{
                        text: '<i class="fa fa-sync mr-1"></i>Refresh',
                        className: 'btn btn-sm btn-info bs-tooltip',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Refresh'
                        },
                        action: function(e, dt, node, config) {
                            reload()
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
                    },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download"></i> Export',
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

            function reload() {
                table.clear().draw();
                $.get('{{ route('api.monitor.do') }}').done(function(result) {
                    let i = 1;
                    result.data.forEach(item => {
                        // table.row.add([
                        //     i++,
                        //     jam(item.confirmation_date_so),
                        //     item.name,
                        //     item.origin,
                        //     '',
                        //     '',
                        //     item.note_to_wh
                        // ]).draw()

                        item.move_ids_without_package_detail.forEach(detail => {
                            table.row.add([
                                i++,
                                jam(item.confirmation_date_so),
                                item.name,
                                item.origin,
                                detail.product_id[1],
                                detail.product_uom_qty,
                                item.note_to_wh
                            ]).draw()
                        });
                    });
                    $('#total').text(': ' + result.data.length)
                })
            }

            reload()


        });
    </script>
@endpush
