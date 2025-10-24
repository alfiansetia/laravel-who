@extends('template', ['title' => 'Edit Packing List'])
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .input-group>.select2-container--bootstrap {
            width: auto;
            flex: 1 1 auto;
        }

        .input-group>.select2-container--bootstrap .select2-selection--single {
            height: 100%;
            line-height: inherit;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('api.packs.update', $data->id) }}" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="vendor_id">VENDOR</label>
                            <div class="input-group">
                                <select name="vendor_id" id="vendor_id" class="custom-select select2" style="width: 100%"
                                    required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $item)
                                        <option data-id="{{ $item->id }}" value="{{ $item->id }}"
                                            @selected($data->vendor_id == $item->id)>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-6">
                            <label for="product_id">PRODUCT</label>
                            <div class="input-group">
                                <select name="product_id" id="product_id" class="custom-select select2" style="width: 100%"
                                    required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $item)
                                        <option data-id="{{ $item->id }}" data-code="{{ $item->code }}"
                                            data-name="{{ $item->name }}" value="{{ $item->id }}"
                                            @selected($data->product_id == $item->id)>
                                            {{ $item->code }} {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="name">PACKING LIST NAME</label>
                            <div class="input-group">
                                <textarea name="name" id="name" class="form-control" maxlength="200" required>{{ $data->name }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="vendor_desc">VENDOR DESC</label>
                            <div class="input-group">
                                <textarea name="vendor_desc" id="vendor_desc" class="form-control" maxlength="200">{{ $data->vendor_desc }}</textarea>
                            </div>
                        </div>
                        <div class="form-group col-6">
                            <label for="desc">PACKING LIST DESC</label>
                            <div class="input-group">
                                <textarea name="desc" id="desc" class="form-control" maxlength="200">{{ $data->desc }}</textarea>
                            </div>
                        </div>
                        <div class="form-group col-lg-6">
                            <label for="import">IMPORT FROM TEXT</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" id="btn_import_clear" class="input-group-text">X</button>
                                </div>
                                <textarea name="import" id="import" class="form-control"></textarea>
                                <div class="input-group-append">
                                    <button type="button" id="btn_import" class="input-group-text">IMPORT</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <a href="{{ route('packs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <a href="{{ route('packs.edit', $data->id) }}" class="btn btn-warning">
                        <i class="fas fa-sync mr-1"></i>Refresh
                    </a>
                    <a href="{{ route('api.packs.download', $data->id) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-download mr-1"></i>Download
                    </a>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>

        <div class="card card-primary mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-hover" style="width: 100%;cursor: pointer;">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 30px">#</th>
                                <th>ITEM</th>
                                <th>QTY</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal_item" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="form_item">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Add Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="item">ITEM</label>
                                <input name="item" type="text" class="form-control" id="item" required>
                            </div>
                            <div class="form-group">
                                <label for="item">QTY</label>
                                <input name="qty" type="text" class="form-control" id="qty">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Close
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fab fa-telegram-plane mr-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const URL_INDEX_API = "{{ route('api.packs.index') }}"
        const URL_INDEX_ITEM_API = "{{ route('api.pack_items.index') }}"
        $(document).ready(function() {
            $('#product_id').select2({
                theme: 'bootstrap4',
            })

            $('#vendor_id').select2({
                theme: 'bootstrap4',
            })

            var table = $('#table').DataTable({
                ajax: {
                    url: URL_INDEX_ITEM_API,
                    data: function(dt) {
                        dt['pack_id'] = "{{ $data->id }}"
                    },
                },
                pageLength: false,
                lengthChange: false,
                ordering: false,
                paging: false,
                searching: false,
                info: false,
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                columns: [{
                        data: "item",
                        className: "text-center",
                        width: '10%',
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <button type="button" class="btn btn-sm btn-danger del-item"><i class="fas fa-trash"></i></button>
                                    <button type="button" class="btn btn-sm btn-secondary btn-up"><i class="fas fa-arrow-up"></i></button>
                                    <button type="button" class="btn btn-sm btn-secondary btn-down"><i class="fas fa-arrow-down"></i></button>
                                </div>
                            `
                        }
                    }, {
                        data: "item",
                        className: 'text-left',
                    },
                    {
                        data: "qty",
                        className: 'text-center',
                    },
                ],
                buttons: [{
                    text: '<i class="fas fa-plus mr-1"></i>Add ITEM',
                    className: 'btn btn-sm btn-info',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Add ITEM'
                    },
                    action: function(e, dt, node, config) {
                        $('#modal_item').modal('show')
                    }
                }, {
                    text: '<i class="fas fa-trash mr-1"></i>Empty ITEM',
                    className: 'btn btn-sm btn-danger',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Empty ITEM'
                    },
                    action: function(e, dt, node, config) {
                        table
                            .rows()
                            .remove()
                            .draw();
                    }
                }, {
                    text: '<i class="fas fa-sync mr-1"></i>Refresh',
                    className: 'btn btn-sm btn-warning',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Refresh'
                    },
                    action: function(e, dt, node, config) {
                        table
                            .ajax
                            .reload()
                    }
                }],
            });

            $('#table').on('click', '.btn-up', function() {
                const row = $(this).closest('tr');
                const prev = row.prev();

                if (prev.length) {
                    row.insertBefore(prev);
                }
                const newData = [];
                $('#table tbody tr').each(function() {
                    newData.push(table.row(this).data());
                });
                table.clear().rows.add(newData).draw(false);
            });

            $('#table').on('click', '.btn-down', function() {
                const row = $(this).closest('tr');
                const next = row.next();

                if (next.length) {
                    row.insertAfter(next);
                }
                const newData = [];
                $('#table tbody tr').each(function() {
                    newData.push(table.row(this).data());
                });
                table.clear().rows.add(newData).draw(false);
            });

            $('#modal_item').on('shown.bs.modal', function() {
                $('#item').focus()
            });

            $('#form_item').submit(function(e) {
                e.preventDefault()
                let item = $('#item').val()
                let qty = $('#qty').val()
                if (item == '' || item == null) {
                    show_message('Item empty!')
                    $('#item').focus()
                    return
                }
                console.log(item, qty);
                table.row.add({
                    item: item,
                    qty: qty
                }).draw()
                $('#item').val('')
                $('#qty').val('')
                $('#modal_item').modal('hide')
            })

            $('#table').on('click', '.del-item', function() {
                let row = $(this).parents('tr')[0];
                table
                    .row($(this).parents('tr'))
                    .remove()
                    .draw();

            });

            $('#form').submit(function(e) {
                e.preventDefault()
                let product = $('#product_id').val()
                let vendor = $('#vendor_id').val()
                let vendor_desc = $('#vendor_desc').val()
                let name = $('#name').val()
                let desc = $('#desc').val()
                let data = table.rows().data().toArray();
                if (product == '' || product == null) {
                    show_message('select Product!')
                    return
                }
                if (vendor == '' || vendor == null) {
                    show_message('select Product!')
                    return
                }
                if (data.length < 1) {
                    show_message('Item Empty!')
                    return
                }
                $.ajax({
                    url: $('#form').attr('action'),
                    type: 'PUT',
                    data: {
                        product_id: product,
                        vendor_id: vendor,
                        vendor_desc: vendor_desc,
                        name: name,
                        desc: desc,
                        items: data
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

            $('#btn_import').click(function() {
                let imp = $('#import').val().trim();
                let rows = imp.split('\n');
                if (rows.length > 0 && rows[0] !== '') {
                    rows.forEach((row, index) => {
                        let cols = row.split('\t'); // Excel tab-delimited

                        let item = cols[0]?.trim() || '';
                        let qty = cols[1]?.trim() || '';

                        if (item) {
                            table.row.add({
                                item: item,
                                qty: qty
                            });
                        }
                    });

                    // Refresh tabel
                    table.draw();
                }
            });

            $('#btn_import_clear').click(function() {
                let imp = $('#import').val('')
            });


            $('#table tbody').on('click', 'td:not(:first-child)', function() {
                let cell = table.cell(this);
                let oldValue = cell.data();

                // Cegah double input
                if ($(this).find('input').length > 0) return;

                // Ganti isi jadi input
                $(this).html(
                    `<input type="text" class="form-control edit-input" value="${oldValue||''}" />`);
                let input = $(this).find('input');
                input.focus();

                // Handle keluar dari input (blur)
                input.on('blur', function() {
                    let newValue = $(this).val().trim();

                    // Update value di tabel
                    cell.data(newValue).draw();
                });

                // Optional: tekan Enter untuk simpan
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        $(this).blur(); // trigger blur
                    }
                });
            });

        });
    </script>
@endpush
