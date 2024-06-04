@extends('template')

@section('content')
    <div class="container-fluid">
        <h1>{{ $title }}</h1>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>No DO</th>
                            <th>Kepada</th>
                            <th>Kota</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: "{{ route('api.bast.index') }}",
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
                order: [
                    // [0, "asc"]
                ],
                columns: [{
                        data: 'id',
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            } else {
                                return data
                            }
                        }
                    }, {
                        data: "do",
                    }, {
                        data: "name",
                    },
                    {
                        data: "city",
                    },
                ],
                buttons: [{
                        text: '<i class="fa fa-plus mr-1"></i>Tambah Data',
                        className: 'btn btn-sm btn-info bs-tooltip',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Tambah Data'
                        },
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route('bast.create') }}"
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

            $('#table tbody').on('click', 'tr td', function() {
                id = table.row(this).id()
                window.location.href = "{{ url('bast') }}/" + id + '/edit'
            });


        });
    </script>
@endsection
