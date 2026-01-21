@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>NO DO</th>
                            <th>FDATE</th>
                            <th>PARTNER</th>
                            <th>SO</th>
                            <th>STATUS</th>
                            <th>NOTES</th>
                            <th style="width: 80px">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal_product" data-backdrop="static" tabindex="-1" aria-labelledby="modal_productLabel"
        data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_productLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="modalTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab"
                                aria-controls="product" aria-selected="true">Product</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="product-lot-tab" data-toggle="tab" href="#product-lot" role="tab"
                                aria-controls="product-lot" aria-selected="false">Product Lot</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="modalTabContent">
                        <div class="tab-pane fade show active" id="product" role="tabpanel" aria-labelledby="product-tab">
                            <table class="table table-hover" id="table_product" style="width: 100%;cursor: pointer;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Desc</th>
                                        <th>AKL</th>
                                        <th style="width: 30px">QTY SO</th>
                                        <th style="width: 30px">QTY DONE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="product-lot" role="tabpanel" aria-labelledby="product-lot-tab">
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="filter_product_code">Filter Product Code</label>
                                    <select id="filter_product_code" class="form-control select2" style="width: 100%;">
                                        <option value=""></option>
                                        <option value="">All Products</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="btn_reset_filter" class="btn btn-secondary btn-block">
                                        <i class="fas fa-sync-alt mr-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                            <table class="table table-hover" id="table_product_lot" style="width: 100%;cursor: pointer;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Desc</th>
                                        <th>Product</th>
                                        <th>AKL</th>
                                        <th>LOT/SN</th>
                                        <th>QTY</th>
                                        <th>EXP DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="mt-2">
                        <b>Status</b> :
                        <div id="modal_status"></div>
                    </div>
                    <div class="mt-2">
                        <b>Notes</b> :
                        <div id="modal_note" style="white-space: pre-wrap;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btn_print">
                        <i class="fas fa-print mr-1"></i>Print
                    </button>
                    <button type="button" class="btn btn-warning" id="btn_print_lot">
                        <i class="fas fa-print mr-1"></i>Print Lot
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const URL_INDEX = "{{ route('do.index') }}"
        const URL_INDEX_API = "{{ route('api.do.index') }}"
        var id = 0;

        $(document).ready(function() {
            $('#filter').select2();

            var table = $('#table').DataTable({
                rowId: 'id',
                processing: true,
                serverSide: true,
                ajax: {
                    url: URL_INDEX_API,
                    data: function(d) {
                        d.filter = $('#filter').val();
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Gagal memuat data SO!', 'error');
                    },
                },
                order: [
                    [0, 'desc']
                ],
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
                        data: "name",
                        className: 'text-left font-weight-bold',
                        render: function(data, type, row) {
                            if (type === 'display' && row.sistem && row.sistem.toLowerCase() ===
                                'mf') {
                                return `${data} <span class="badge badge-danger">MF</span>`;
                            }
                            return data;
                        }
                    },
                    {
                        data: "force_date",
                        className: 'text-left',
                        render: function(data, type, row) {
                            if (type === 'display' && data) {
                                let date = new Date(data.replace(' ', 'T'));
                                date.setHours(date.getHours() + 7);
                                let d = ("0" + date.getDate()).slice(-2);
                                let m = ("0" + (date.getMonth() + 1)).slice(-2);
                                let y = date.getFullYear();
                                let h = ("0" + date.getHours()).slice(-2);
                                let min = ("0" + date.getMinutes()).slice(-2);
                                let s = ("0" + date.getSeconds()).slice(-2);
                                return `${d}/${m}/${y} ${h}:${min}:${s}`;
                            }
                            return data;
                        }
                    },
                    {
                        data: "partner_id",
                        className: 'text-left',
                        render: function(data, type, row) {
                            if (type === 'display' && Array.isArray(data)) {
                                return data[1].substring(0, 30);
                            }
                            return data ? data[1] : '-';
                        }
                    },
                    {
                        data: "origin",
                        className: 'text-center',
                    },
                    {
                        data: "state",
                        className: 'text-center',
                    },
                    {
                        data: "note_to_wh",
                        className: 'text-left',
                        render: function(data, type) {
                            if (type === 'display' && data) {
                                return data.length > 40 ? data.substring(0, 40) + '...' : data;
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: "id",
                        className: 'text-center',
                        orderable: false,
                        render: function(data) {
                            return `<button class="btn btn-sm btn-success btn-print-do" data-id="${data}" title="Print DO">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning btn-print-lot" data-id="${data}" title="Print Lot">
                                        <i class="fas fa-print"></i>
                                    </button>`;
                        }
                    },
                ],
                buttons: [{
                        extend: "colvis",
                        className: 'btn btn-sm btn-primary',
                        titleAttr: 'Kolom Terlihat'
                    },
                    {
                        extend: "pageLength",
                        className: 'btn btn-sm btn-info'
                    },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download mr-1"></i>Export',
                        className: 'btn btn-sm btn-secondary',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    },
                    {
                        text: '<i class="fas fa-sync mr-1"></i>Refresh',
                        className: 'btn btn-sm btn-info',
                        action: function(e, dt, node, config) {
                            table.ajax.reload();
                        }
                    }
                ],
            });

            var table_product = $("#table_product").DataTable({
                processing: true,
                serverSide: false,
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                paging: false,
                scrollCollapse: true,
                scrollY: '400px',
                columns: [{
                        data: "product_id",
                        render: function(data, type, row) {
                            if (Array.isArray(data)) {
                                return getCode(data[1]);
                            }
                            return data;
                        }
                    },
                    {
                        data: "product_id",
                        render: function(data, type, row) {
                            if (Array.isArray(data)) {
                                return getDesc(data[1]);
                            }
                            return data;
                        }
                    },
                    {
                        data: "akl_id",
                        className: 'text-center',
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : data;
                        }
                    },
                    {
                        data: "product_uom_qty",
                        className: 'text-center'
                    },

                    {
                        data: "quantity_done",
                        className: 'text-center'
                    },
                ],
                buttons: [{
                        extend: "colvis",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Column Visible'
                        },
                        className: 'btn btn-sm btn-primary'
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
            });

            var table_product_lot = $("#table_product_lot").DataTable({
                processing: true,
                serverSide: false,
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
                paging: true,
                columns: [{
                        data: "product_id",
                        render: function(data, type, row) {
                            let text = Array.isArray(data) ? data[1] : data;
                            return getCode(text)
                        }
                    }, {
                        data: "product_id",
                        render: function(data, type, row) {
                            let text = Array.isArray(data) ? data[1] : data;
                            return getDesc(text)
                        }
                    }, {
                        data: "product_id",
                        render: function(data, type, row) {
                            let text = Array.isArray(data) ? data[1] : data;
                            return text
                        }
                    }, {
                        data: "akl_id",
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : data;
                        }
                    }, {
                        data: "lot_id",
                        render: function(data, type, row) {
                            return Array.isArray(data) ? data[1] : (row.lot_name ? row.lot_name :
                                data);
                        }
                    },
                    {
                        data: "qty_done",
                        className: 'text-center',
                    }, {
                        data: "expired_date",
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!data) {
                                return '';
                            }
                            return moment(data).format('YYYY.MM.DD');
                        }
                    },
                ],
                buttons: [{
                        extend: "colvis",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Column Visible'
                        },
                        className: 'btn btn-sm btn-primary'
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
            });

            $('#filter_product_code').select2({
                placeholder: 'Select Product Code',
                allowClear: true,
                theme: 'bootstrap4',
                dropdownParent: $('#modal_product')
            }).on('change', function() {
                var val = $(this).val();
                table_product_lot
                    .column(0)
                    .search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false)
                    .draw();
            });

            $('#btn_reset_filter').on('click', function() {
                $('#filter_product_code').val('').trigger('change.select2');
                table_product_lot.search('').columns().search('').draw();
            });


            // Handle Klik Tombol Print di Tabel
            $('#table tbody').on('click', '.btn-print-do', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const doId = $(this).data('id');
                window.open(`${URL_INDEX}/${doId}/print`, '_blank');
            });

            $('#table tbody').on('click', '.btn-print-lot', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const doId = $(this).data('id');
                window.open(`${URL_INDEX}/${doId}/print?with_lot=true`, '_blank');
            });

            // Handle Klik Baris (Buka Modal Detail)
            $('#table tbody').on('click', 'tr', function(e) {
                if ($(e.target).closest('button').length > 0) return;

                const rowData = table.row(this).data();
                if (!rowData) return;

                id = rowData.id;
                $('#modal_note').html(rowData.note_to_wh);
                $('#modal_status').html(
                    `<span class="badge badge-warning">${rowData.state}</span>`
                );
                $('#modal_productLabel').html(`<i class="fas fa-list mr-2"></i>Item SO: ${rowData.name}`);

                $.ajax({
                    url: `${URL_INDEX_API}/${id}`,
                    type: 'GET',
                    success: function(res) {
                        let products = res.data.move_ids_detail;
                        let lots = res.data.move_line_detail;

                        // Map akl_id from products to lots
                        let aklMap = {};
                        products.forEach(p => {
                            let pid = Array.isArray(p.product_id) ? p.product_id[0] : p
                                .product_id;
                            aklMap[pid] = p.akl_id;
                        });

                        lots.forEach(lot => {
                            let pid = Array.isArray(lot.product_id) ? lot.product_id[
                                0] : lot.product_id;
                            lot.akl_id = aklMap[pid];
                        });

                        table_product.clear().rows.add(products).draw();
                        table_product_lot.clear().search('').columns().search('').rows.add(lots)
                            .draw();

                        // Populate Filter Select2
                        let filterSelect = $('#filter_product_code');
                        filterSelect.empty().append(
                            '<option value=""></option><option value="">All Products</option>'
                        );
                        let codes = [];
                        lots.forEach(function(item) {
                            let text = Array.isArray(item.product_id) ? item.product_id[
                                1] : item.product_id;
                            let code = getCode(text);
                            if (code && !codes.includes(code)) {
                                codes.push(code);
                            }
                        });
                        codes.sort().forEach(function(code) {
                            filterSelect.append(new Option(code, code));
                        });
                        filterSelect.val('').trigger('change.select2');

                        $('#modal_product').modal('show');
                        $('#product-tab').tab('show');
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Gagal memuat data SO!',
                            'error');
                    },
                });
            });

            $('#btn_print').click(function() {
                if (id > 0) {
                    window.open(`${URL_INDEX}/${id}/print`, '_blank');
                }
            });

            $('#btn_print_lot').click(function() {
                if (id > 0) {
                    window.open(`${URL_INDEX}/${id}/print?with_lot=true`, '_blank');
                }
            });

            function getCode(str) {
                if (!str) return '';
                let match = str.match(/\[(.*?)\]/);
                return match ? match[1] : '';
            }

            function getDesc(str) {
                if (!str) return '';
                let match = str.match(/\]\s*(.*)/);
                return match ? match[1] : str;
            }
        });
    </script>
@endpush
