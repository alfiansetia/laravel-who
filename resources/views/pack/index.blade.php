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

    <div class="modal fade" id="modal_pl" tabindex="-1" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-2 d-flex align-items-center justify-content-between">
                    <ul class="nav nav-pills" id="plTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold px-4" id="detail-tab" data-toggle="tab" href="#tab-detail" role="tab">
                                <i class="fas fa-eye mr-1"></i> VIEW DETAIL
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold px-4" id="edit-tab" data-toggle="tab" href="#tab-edit" role="tab">
                                <i class="fas fa-edit mr-1"></i> EDIT ITEMS
                            </a>
                        </li>
                    </ul>
                    <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <!-- Header Info -->
                    <div class="bg-white px-4 py-3 border-bottom">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <label class="small text-muted mb-0">Packing List Name</label>
                                <h6 id="detail_name" class="font-weight-bold mb-0 text-dark">-</h6>
                                <label class="small text-muted mb-0 mt-2">Vendor</label>
                                <h6 id="detail_vendor" class="font-weight-bold mb-0 text-dark">-</h6>
                            </div>
                            <div class="col-md-6 pl-md-4">
                                <label class="small text-muted mb-0">Product</label>
                                <h6 id="detail_product" class="text-primary font-weight-bold mb-0">-</h6>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="plTabContent">
                        <!-- Tab Detail (Screenshot Friendly) -->
                        <div class="tab-pane fade show active px-4 py-3" id="tab-detail" role="tabpanel">
                            <div class="table-responsive" style="max-height: 500px">
                                <table class="table table-sm table-striped table-bordered mb-0" id="table_pl_view">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center" style="width: 40px">No</th>
                                            <th>Item Description</th>
                                            <th class="text-center" style="width: 100px">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Edit (Interactive) -->
                        <div class="tab-pane fade px-4 py-3" id="tab-edit" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted font-italic text-danger">* Perubahan di sini tidak otomatis tersimpan sebelum klik tombol "Simpan"</span>
                                <button type="button" class="btn btn-sm btn-info" onclick="addItemRow()">
                                    <i class="fas fa-plus mr-1"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="table-responsive" style="max-height: 500px">
                                <table class="table table-sm table-hover border" id="table_pl">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px">#</th>
                                            <th>Item Nama</th>
                                            <th style="width: 120px">Qty</th>
                                            <th class="text-center" style="width: 50px"><i class="fas fa-cog"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary d-none" id="btn_save_pl">
                        <i class="fas fa-save mr-1 text-white"></i> Simpan Perubahan
                    </button>
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

            var currentPack = {};

            window.addItemRow = function(item = '', qty = '') {
                let rowCount = $('#table_pl tbody tr').length + 1;
                $('#table_pl tbody').append(`
                    <tr>
                        <td class="text-center align-middle font-weight-bold">${rowCount}</td>
                        <td>
                            <textarea class="form-control form-control-sm pl-item" rows="1" required placeholder="Nama Item...">${item}</textarea>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm pl-qty" value="${qty}" placeholder="Qty...">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="$(this).closest('tr').remove()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            }

            // Fungsi untuk tarik data dan isi tabel di modal
            function fetchPackDetail(packId) {
                $.get(URL_INDEX_API + '/' + packId).done(function(res) {
                    let pack = res.data;
                    currentPack = {
                        name: pack.name,
                        desc: pack.desc,
                        product_id: pack.product_id,
                        vendor_id: pack.vendor_id,
                        vendor_desc: pack.vendor_desc
                    };

                    // Isi Header Detail
                    $('#detail_name').html(pack.name ?? '-');
                    $('#detail_vendor').html(pack.vendor?.name ?? '-');
                    $('#detail_product').html(`[${pack.product?.code ?? ''}] ${pack.product?.name ?? ''}`);

                    // Kosongkan dan Isi Tabel Detail (View)
                    $('#table_pl_view tbody').empty();
                    // Kosongkan dan Isi Tabel Edit
                    $('#table_pl tbody').empty();

                    pack.items.forEach((item, index) => {
                        // Table View
                        $('#table_pl_view tbody').append(`
                            <tr>
                                <td class="text-center text-muted small">${index + 1}</td>
                                <td class="font-weight-bold">${item.item}</td>
                                <td class="text-center bg-light font-weight-bold">${item.qty || '-'}</td>
                            </tr>
                        `);
                        // Table Edit
                        addItemRow(item.item, item.qty || '');
                    });

                }).fail(function(xhr) {
                    show_message('Gagal mengambil data!', 'error')
                });
            }

            // Handle Perpindahan Tab
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                let target = $(e.target).attr("href");
                
                // Fetch data ulang saat pindah tab
                if (id) fetchPackDetail(id);

                // Tampilkan tombol simpan hanya di tab edit
                if (target === '#tab-edit') {
                    $('#btn_save_pl').removeClass('d-none');
                } else {
                    $('#btn_save_pl').addClass('d-none');
                }
            });

            $('#btn_save_pl').click(function() {
                let items = [];
                let isValid = true;
                
                if($('#table_pl tbody tr').length === 0) {
                    show_message('Minimal harus ada 1 item!', 'warning');
                    return;
                }

                $('#table_pl tbody tr').each(function() {
                    let item = $(this).find('.pl-item').val().trim();
                    let qty = $(this).find('.pl-qty').val().trim();
                    
                    if (!item) {
                        isValid = false;
                        $(this).find('.pl-item').addClass('is-invalid');
                    } else {
                        $(this).find('.pl-item').removeClass('is-invalid');
                        items.push({
                            item: item,
                            qty: qty
                        });
                    }
                });

                if (!isValid) {
                    show_message('Nama item tidak boleh kosong!', 'warning');
                    return;
                }

                let data = {
                    ...currentPack,
                    items: items
                };

                $.ajax({
                    url: URL_INDEX_API + '/' + id,
                    type: "PUT",
                    data: data,
                    success: function(res) {
                        show_message(res.message, 'success');
                        // Setelah simpan, balik ke tab detail untuk lihat hasilnya
                        $('#detail-tab').tab('show');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error simpan data!')
                    }
                });
            });

            $('#table tbody').on('click', 'tr td:not(:last-child):not(:first-child)', function() {
                let rowData = table.row(this).data();
                id = rowData.id;
                
                // Reset Modal state ke Tab Detail
                $('#detail-tab').tab('show');
                $('#btn_save_pl').addClass('d-none');
                
                // Loading state
                $('#detail_vendor').html('<i class="fas fa-spinner fa-spin"></i>');
                $('#table_pl_view tbody').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');

                fetchPackDetail(id);
                $('#modal_pl').modal('show');
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
