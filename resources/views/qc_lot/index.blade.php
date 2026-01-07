@extends('template', ['title' => 'Data QC Lot'])
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush
@section('content')
    <div class="container-fluid">
        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>PRODUCT</th>
                            <th>LOT / ED</th>
                            <th>DATE</th>
                            <th>QC BY</th>
                            <th>QC NOTE</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    @include('qc_lot.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.qc_lots.index') }}"
        const URL_INDEX = "{{ route('qc_lots.index') }}"
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
            $('#qc_date').daterangepicker({
                singleDatePicker: true,
                "autoApply": true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });
            $('#product_id').select2({
                theme: 'bootstrap4',
            });
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
                columns: [{
                        data: 'id',
                        className: "text-center",
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                        }
                    }, {
                        data: "product_id",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            if (data && type == "display") {
                                return row.product.code;
                            }
                            return data;
                        }
                    },
                    {
                        data: "lot_number",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            return `${row.lot_number} / ${row.lot_expiry}`;
                        }
                    }, {
                        data: "qc_date",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            return moment(data).format('DD-MM-YYYY');
                        }
                    }, {
                        data: "qc_by",
                        className: 'text-left',
                    }, {
                        data: "qc_note",
                        className: 'text-left',
                        visible: false,
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
                            }, {
                                text: 'Import',
                                className: 'btn btn-warning',
                                action: function(e, dt, node, config) {
                                    window.location.href = "{{ route('qc_lots.import') }}"
                                }
                            },
                            {
                                text: 'Refresh',
                                className: 'btn btn-warning',
                                action: function(e, dt, node, config) {
                                    table.ajax.reload()
                                }
                            },
                        ]
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

            function deleteBatch() {
                if (selected()) {
                    confirmation('Delete Selected?', function(confirm) {
                        if (confirm) {
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
                    })
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

            $('#modal_add').on('shown.bs.modal', function(e) {
                $('#lot_number').focus();
            })

            $('#form_add').submit(function(e) {
                e.preventDefault();
                let data = $(this).serialize();
                $.ajax({
                    url: URL_INDEX_API,
                    type: "POST",
                    data: data,
                    success: function(res) {
                        $('#modal_add').modal('hide');
                        $('#lot_number').val('');
                        $('#product_id').val('').trigger('change');
                        $('#lot_expiry').val('');
                        $('#qc_date').val('');
                        $('#qc_by').val('');
                        $('#qc_note').val('');
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
