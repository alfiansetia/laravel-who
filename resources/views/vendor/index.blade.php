@extends('template', ['title' => 'Data Vendor'])

@section('content')
    <div class="container-fluid">
        {{-- <h1>Data Vendor</h1> --}}

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>NAME</th>
                            <th>DESC</th>
                            <th class="text-nowrap">PACK COUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    @include('vendor.modal')
@endsection

@push('js')
    <script>
        const URL_INDEX_API = "{{ route('api.vendors.index') }}"
        const URL_INDEX = "{{ route('vendors.index') }}"
        var id = 0;

        const BTN_EXPORT = {
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

        $(document).ready(function() {
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: URL_INDEX_API,
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
                order: [
                    [1, "asc"]
                ],
                columns: [{
                        data: 'id',
                        className: "text-center",
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                        }
                    }, {
                        data: "name",
                    },
                    {
                        data: "desc",
                    }, {
                        data: "packs_count",
                        className: 'text-center',
                        searchable: false,
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-plus mr-1"></i>Tambah',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Tambah Data'
                        },
                        action: function(e, dt, node, config) {
                            $('#modal_add').modal('show')
                        }
                    }, {
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
                    BTN_EXPORT, {
                        text: '<i class="fa fa-tools"></i> Action',
                        className: 'btn btn-sm btn-warning bs-tooltip',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Action'
                        },
                        extend: 'collection',
                        autoClose: true,
                        buttons: [{
                            text: 'Delete Selected Data',
                            className: 'btn btn-danger',
                            action: function(e, dt, node, config) {
                                deleteBatch()
                            }
                        }, ]
                    },
                ],
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            var id;

            multiCheck(table);

            function deleteData() {

            }

            function deleteBatch() {
                if (selected()) {
                    if (!confirm('Delete Selected?')) {
                        return
                    }
                    selectedIds = $('input[name="id[]"]:checked')
                        .map(function() {
                            return $(this).val();
                        }).get();
                    $.ajax({
                        url: URL_INDEX_API,
                        type: "DELETE",
                        data: {
                            ids: selectedIds,
                        },
                        success: function(res) {
                            table.ajax.reload();
                            show_message(res.message, 'success')
                        },
                        error: function(xhr) {
                            show_message(xhr.responseJSON.message || 'Error!')
                        }
                    });
                }
            }

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    show_message("No Selected Data!")
                    return false
                } else {
                    return true
                }
            }

            $('#btn_add').click(function() {
                $('#form_add').submit()
            })

            $('#form_add').on('submit', function(e) {
                e.preventDefault();
                let name = $('#name').val();
                let desc = $('#desc').val();
                if (!name) {
                    show_message('Name Wajib diisi!');
                    return;
                }
                $.ajax({
                    url: URL_INDEX_API,
                    type: "POST",
                    data: {
                        name: name,
                        desc: desc,
                    },
                    success: function(res) {
                        $('#modal_add').modal('hide');
                        $('#name').val('');
                        $('#desc').val('');
                        show_message(res.message, 'success')
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            });

        });
    </script>
@endpush
