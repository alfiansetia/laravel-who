@extends('template')

@section('content')
    <div class="container-fluid">
        <h1>{{ $title }}</h1>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>KODE</th>
                            <th>NAME</th>
                            <th>AKL</th>
                            <th>AKL EXP</th>
                            <th>DESC</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>


    <div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Import Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <div class="custom-file mb-3">
                                <input type="file" name="product" class="custom-file-input" required>
                                <label class="custom-file-label" for="validatedCustomFile">Choose file...</label>
                            </div>
                            <button class="btn btn-warning" type="reset">Reset</button>
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
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
                rowId: 'id',
                ajax: "{{ route('product.index') }}",
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
                        data: "code",
                    },
                    {
                        data: "name",
                    },
                    {
                        data: "akl",
                    },
                    {
                        data: "akl_exp",
                        render: function(data, type, row, meta) {
                            let text;
                            let now = moment(new Date()); //todays date
                            let end = moment(data); // another date
                            let duration = moment.duration(now.diff(end));
                            let days = duration.asDays();
                            if (days >= 0) {
                                text =
                                    `<span class="badge badge-danger">${data == null ? '' : data}</span>`;
                            } else {
                                text =
                                    `<span class="badge badge-success">${data == null ? '' : data}</span>`;
                            }
                            if (type == 'display') {
                                return text;
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: "desc",
                        visible: false
                    },
                ],
                buttons: [{
                        extend: "collection",
                        text: '<i class="fas fa-cogs mr-1"></i>Actions',
                        autoClose: true, // Menutup menu saat tombol submenu dipilih
                        buttons: [{
                                text: '<i class="fa fa-plus mr-1"></i>Import Data',
                                className: 'btn btn-sm btn-primary bs-tooltip',
                                attr: {
                                    'data-toggle': 'tooltip',
                                    'title': 'Add Data'
                                },
                                action: function(e, dt, node, config) {
                                    $('#modalAdd').modal('show');
                                    $('#name').focus();
                                }
                            },
                            {
                                text: '<i class="fas fa-download mr-1"></i>Download Example',
                                className: 'btn btn-sm btn-primary',
                                attr: {
                                    'data-toggle': 'tooltip',
                                    'title': 'Download Example File'
                                },
                                action: function(e, dt, node, config) {
                                    let url = "{{ route('product.download.sample') }}"
                                    window.open(url, "_blank")
                                }
                            },
                            {
                                text: '<i class="fas fa-trash mr-1"></i>Delete Data',
                                className: 'btn btn-sm btn-danger',
                                attr: {
                                    'data-toggle': 'tooltip',
                                    'title': 'Delete Selected Data'
                                },
                                action: function(e, dt, node, config) {
                                    deleteData()
                                }
                            },
                            {
                                text: '<i class="fas fa-sync mr-1"></i>Syncronize from Odoo',
                                className: 'btn btn-sm btn-danger',
                                attr: {
                                    'data-toggle': 'tooltip',
                                    'title': 'Syncronize from Odoo'
                                },
                                action: function(e, dt, node, config) {
                                    $.post("{{ route('api.product_sync') }}")
                                        .done(function(res) {
                                            table.ajax.reload()
                                            alert(res.message)
                                        }).fail(function(xhr) {
                                            alert('Odoo Error!')
                                        });
                                }
                            },
                        ]
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
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            var id;

            multiCheck(table);

            // $('#table tbody').on('click', 'tr td:not(:first-child)', function() {
            //     id = table.row(this).id()
            //     $.ajax({
            //         url: "{{ route('product.index') }}" + id,
            //         method: 'GET',
            //         success: function(result) {
            //             console.log(result)
            //             $('#edit_reset').val(result.data.prod_id);
            //             $('#edit_id').val(result.data.prod_id);
            //             $('#edit_name').val(result.data.name);
            //         },
            //         beforeSend: function() {
            //             console.log('otw')
            //         },
            //         error: function(xhr, status, error) {
            //             er = xhr.responseJSON.errors
            //             alert('Server Error');
            //         }
            //     });
            //     $('#modalEdit').modal('show');
            // });

            function deleteData() {
                if (selected()) {
                    if (confirm('Delete Selected Data?')) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: 'DELETE',
                            url: "{{ route('product.destroy') }}",
                            data: $("#selected").serialize(),
                            beforeSend: function() {},
                            success: function(res) {
                                table.ajax.reload();
                                alert(res.message)
                            },
                            error: function(xhr, status, error) {
                                er = xhr.responseJSON.errors
                                alert("Server Error")
                            }
                        });
                    }
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
@endsection
