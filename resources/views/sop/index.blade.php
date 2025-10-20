@extends('template', ['title' => 'SOP QC'])

@section('content')
    <div class="container-fluid">
        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>Kode Product</th>
                            <th>Name Product</th>
                            <th>Target</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    @include('sop.modal_index')
@endsection

@push('js')
    <script>
        const URL_INDEX_API = "{{ route('api.sops.index') }}"
        const URL_INDEX = "{{ route('sops.index') }}"
        var id = 0;

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
                        data: "product.code",
                        className: 'text-left',
                    }, {
                        data: "product.name",
                        className: 'text-left',
                    },
                    {
                        data: "target",
                        className: 'text-left',
                    }, {
                        data: "id",
                        className: 'text-center',
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-sm btn-info btn-download"><i class="fas fa-download"></i></button>
                            </div>
                            `
                        }
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-tasks mr-1"></i>Manage SOP QC',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Manage SOP QC'
                        },
                        action: function(e, dt, node, config) {
                            window.location.href = URL_INDEX + '/create'
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
                    }, {
                        text: '<i class="fas fa-sync mr-1"></i>',
                        className: 'btn btn-sm btn-warning',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Refresh Data'
                        },
                        action: function(e, dt, node, config) {
                            table.ajax.reload()
                        }
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

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    show_message("No Selected Data!")
                    return false
                } else {
                    return true
                }
            }

            $('#table tbody').on('click', 'tr td:not(:last-child):not(:first-child)', function() {
                id = table.row(this).data().id

                $.get(URL_INDEX_API + '/' + id).done(function(res) {
                    $('#modal_pl').modal('show')
                    $('#table_target tbody').empty();
                    $('#target_value').html('');

                    $('#title_detail_product').html(
                        `[${res.data.product.code}] ${res.data.product.name}`);
                    $('#target_value').html(res.data.target);
                    res.data.items.forEach((item, index) => {
                        $('#table_target tbody').append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.item}</td>
                            </tr>
                        `);
                    });
                }).fail(function(xhr) {
                    show_message('Data Tidak ada!')
                })

            });

            $('#table tbody').on('click', 'tr .btn-download', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                window.open(`${URL_INDEX_API}/${id}/download`)
            });

        });
    </script>
@endpush
