@extends('template', ['title' => 'Packing List'])
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- <h1>Packing List</h1> --}}

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>Kode Product</th>
                            <th>Name Product</th>
                            <th>PL Name</th>
                            <th>PL Desc</th>
                            <th>Vendor</th>
                            <th>Vendor Desc</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal_pl" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Packing List : <span id="detail_name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Vendor : <span id="detail_vendor"></span></h5>
                    <h5>Product : <span id="detail_product"></span></h5>
                    <table id="table_pl" class="table table-sm table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width: 30px;">No</th>
                                <th>ITEM</th>
                                <th>QTY</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_change" data-backdrop="static" data-keyboard="false"
        aria-labelledby="staticBackdropLabelChange" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabelChange">Change Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_change" action="">
                        @csrf
                        <div class="form-group col-12">
                            <label for="vendor_id">VENDOR</label>
                            <div class="input-group">
                                <select name="vendor_id" id="vendor_id" class="custom-select select2" style="width: 100%"
                                    required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $item)
                                        <option data-id="{{ $item->id }}" value="{{ $item->id }}">
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="btn_change" type="button" class="btn btn-primary">Change</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.packs.index') }}"
        const URL_INDEX = "{{ route('packs.index') }}"
        var id = 0;
        var selectedIds = [];

        $(document).ready(function() {
            $('#vendor_id').select2({
                theme: 'bootstrap4',
            })

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
                    },
                    {
                        data: "product.name",
                        className: 'text-left',
                    }, {
                        data: "name",
                        className: 'text-left',
                    }, {
                        data: "desc",
                        className: 'text-left',
                    }, {
                        data: "vendor.name",
                        defaultContent: '',
                        className: 'text-left',
                    }, {
                        data: "vendor_desc",
                        defaultContent: '',
                        className: 'text-left',
                    }, {
                        data: "id",
                        className: 'text-center',
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-sm btn-info btn-download" title="Export Excel"><i class="fas fa-file-excel"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary btn-print" title="Print HTML"><i class="fas fa-print"></i></button>
                                <button type="button" class="btn btn-sm btn-primary btn-edit"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
                            </div>
                            `
                        }
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-plus mr-1"></i>Add PL',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Add PL'
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
                            text: '<i class="fas fa-exchange-alt mr-1"></i>Change Data',
                            className: 'btn btn-sm btn-danger',
                            attr: {
                                'data-toggle': 'tooltip',
                                'title': 'Change Data'
                            },
                            action: function(e, dt, node, config) {
                                change_data()
                            }
                        }]
                    },
                ],
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            multiCheck(table);

            function deleteData() {

            }

            $('#btn_change').click(function() {
                $('#form_change').submit()
            })

            function change_data() {
                if (selected()) {
                    selectedIds = $('input[name="id[]"]:checked')
                        .map(function() {
                            return $(this).val();
                        }).get();
                    $('#modal_change').modal('show');
                }
            }

            // Handler submit form
            $('#form_change').on('submit', function(e) {
                e.preventDefault();
                let vendor_id = $('#vendor_id').val();
                if (!vendor_id) {
                    show_message('Pilih vendor terlebih dahulu!');
                    return;
                }
                $.ajax({
                    url: "{{ route('api.packs.change') }}", // atau "/packs-change"
                    type: "POST",
                    data: {
                        vendor_id: vendor_id,
                        ids: selectedIds,
                    },
                    success: function(res) {
                        $('#modal_change').modal('hide');
                        $('#vendor_id').val('').change();
                        table.ajax.reload();
                        show_message(res.message, 'success')
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            });

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
                $('#detail_vendor').html('')
                $('#detail_product').html('')
                $('#detail_name').html('')
                $.get(URL_INDEX_API + '/' + id).done(function(res) {
                    $('#table_pl tbody').empty();
                    res.data.items.forEach((item, index) => {
                        $('#table_pl tbody').append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.item}</td>
                                <td>${item.qty || ''}</td>
                            </tr>
                        `);
                    });

                    $('#detail_name').html(res.data.name ?? '');
                    $('#detail_vendor').html(res.data.vendor?.name ?? '');
                    $('#detail_product').html(
                        `[${res.data.product?.code ?? ''}] ${res.data.product?.name ?? ''}`);

                    $('#modal_pl').modal('show')
                }).fail(function(xhr) {
                    show_message('Data Tidak ada!')
                })

            });

            $('#table tbody').on('click', 'tr .btn-edit', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                window.open(`${URL_INDEX}/${id}/edit`)
            });

            $('#table tbody').on('click', 'tr .btn-download', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                window.open(`${URL_INDEX_API}/${id}/download`)
            });

            $('#table tbody').on('click', 'tr .btn-print', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                window.open(`${URL_INDEX}/${id}/print`, '_blank')
            });

            $('#table tbody').on('click', 'tr .btn-delete', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                confirmation('Delete Data?', function(confirm) {
                    if (confirm) {
                        $.ajax({
                            url: URL_INDEX_API + '/' + id,
                            type: "DELETE",
                            success: function(res) {
                                table.ajax.reload();
                                show_message(res.message, 'success')
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message || 'Error!')
                            }
                        });
                    }
                })
            });

        });
    </script>
@endpush
