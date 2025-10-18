@extends('template', ['title' => 'Data Kontak'])

@section('content')
    <div class="container-fluid">
        {{-- <h1>Data Kontak</h1> --}}

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>NAME</th>
                            <th>STREET</th>
                            <th>PHONE</th>
                            <th class="text-center">#</th>
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
        const URL_INDEX = "{{ route('kontak.index') }}"
        const URL_INDEX_API = "{{ route('api.kontak.index') }}"
        var id = 0;
        $(document).ready(function() {
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: URL_INDEX_API,
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
                        data: "street",
                    },
                    {
                        data: "phone",
                    }, {
                        data: 'id',
                        className: "text-center",
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `<button type="button" class="btn btn-sm btn-primary btn-vendor"><i class="fas fa-share"></i>Vendor</button>`
                        }
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-sync mr-1"></i>Sync from Odoo',
                        className: 'btn btn-sm btn-danger',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Syncronize from Odoo'
                        },
                        action: function(e, dt, node, config) {
                            $.post("{{ route('api.kontak.sync') }}")
                                .done(function(res) {
                                    table.ajax.reload()
                                    alert(res.message)
                                }).fail(function(xhr) {
                                    alert(xhr.responseJSON.message || 'Odoo Error!')
                                });
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
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            $('#table tbody').on('click', 'tr .btn-vendor', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                let name = table.row(row).data().name
                if (!confirm('Add to vendor?')) {
                    return
                }
                console.log(name);

                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.vendors.store') }}",
                    data: {
                        name: name,
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        alert(res.message)
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message || 'Error!')
                    }
                });
            });


            multiCheck(table);

            function deleteData() {

            }

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    alert("No Selected Data!")
                    return false
                } else {
                    return true
                }
            }

        });
    </script>
@endpush
