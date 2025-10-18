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
                            <th>Tujuan</th>
                            <th>Ekspedisi</th>
                            <th>Koli</th>
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

            const URL_INDEX_API = "{{ route('api.alamats.index') }}"
            const URL_INDEX = "{{ route('alamats.index') }}"

            var id = 0;
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
                    [5, "desc"]
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
                        data: "do",
                    },
                    {
                        data: "tujuan",
                    },
                    {
                        data: "ekspedisi",
                    }, {
                        data: "koli",
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if (type == 'display') {
                                let text =
                                    `${data} Koli`
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
                                    `
                                    <button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
                                    `
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
                            window.location.href = "{{ route('alamats.create') }}"
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
                    }, {
                        text: '<i class="fa fa-tools"></i> Action',
                        className: 'btn btn-sm btn-warning bs-tooltip',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Action'
                        },
                        extend: 'collection',
                        autoClose: true,
                        buttons: [{
                            text: '<i class="fas fa-sync mr-1"></i>Refresh Data',
                            className: 'btn btn-sm btn-warning',
                            attr: {
                                'data-toggle': 'tooltip',
                                'title': 'Refresh Data'
                            },
                            action: function(e, dt, node, config) {
                                table.ajax.reload()
                            }
                        }, {
                            text: '<i class="fas fa-trash mr-1"></i>Delete Selected Data',
                            className: 'btn btn-danger',
                            action: function(e, dt, node, config) {
                                deleteBatch()
                            }
                        }, ]
                    }
                ],
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            $('#table tbody').on('click', 'tr td:not(:last-child):not(:first-child)', function() {
                id = table.row(this).id()
                window.location.href = URL_INDEX + "/" + id + '/edit'
            });

            $('#table tbody').on('click', '.btn-delete', function() {
                id = table.row($(this).parents('tr')[0]).id()
                $.ajax({
                    url: URL_INDEX_API + "/" + id,
                    type: 'DELETE',
                    success: function(result) {
                        alert(result.message)
                        table.ajax.reload()
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message || 'Error!')
                    }
                })
            });

            multiCheck(table);

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
                        success: function(response) {
                            table.ajax.reload();
                            alert(response.message);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseJSON);
                            alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message ||
                                'Unknown error'));
                        }
                    });
                }
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
