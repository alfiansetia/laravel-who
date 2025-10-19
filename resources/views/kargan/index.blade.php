@extends('template')

@section('content')
    <div class="container-fluid">
        {{-- <h1>{{ $title }}</h1> --}}

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>No</th>
                            <th>SN</th>
                            <th>Product</th>
                            <th>Action</th>
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
    <script>
        $(document).ready(function() {
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: "{{ route('api.kargan.index') }}",
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
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            } else {
                                return data
                            }
                        }
                    }, {
                        data: "number",
                    }, {
                        data: "sn",
                    },
                    {
                        data: "id",
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                let text = row.product.name || '-'
                                return text
                            } else {
                                return data
                            }
                        }
                    }, {
                        data: "id",
                        className: 'text-center',
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                let text =
                                    `<button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>`
                                return text
                            } else {
                                return data
                            }
                        }
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
                            window.location.href = "{{ route('kargan.create') }}"
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

            $('#table tbody').on('click', 'tr td:not(:last-child)', function() {
                id = table.row(this).id()
                window.location.href = "{{ url('kargan') }}/" + id + '/edit'
            });

            $('#table tbody').on('click', '.btn-delete', function() {
                id = table.row($(this).parents('tr')[0]).id()
                $.ajax({
                    url: `{{ route('api.kargan.index') }}/${id}`,
                    type: 'DELETE',
                    success: function(result) {
                        show_message(result.message, 'success')
                        table.ajax.reload()
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                })
            });


        });
    </script>
@endpush
