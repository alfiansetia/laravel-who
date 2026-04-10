@extends('template', ['title' => 'Add Packing List'])
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
    <div class="container-fluid py-3">
        <form method="POST" action="{{ route('api.packs.store') }}" id="form">
            @csrf
            <div class="card card-sm">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-plus-circle mr-2"></i>BUAT PACKING LIST BARU</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-building mr-1"></i> VENDOR</label>
                            <select name="vendor_id" id="vendor_id" class="custom-select select2" style="width: 100%"
                                required>
                                <option value="">--- Pilih Vendor ---</option>
                                @foreach ($vendors as $item)
                                    <option data-id="{{ $item->id }}" value="{{ $item->id }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-box mr-1"></i> PRODUCT</label>
                            <select name="product_id" id="product_id" class="custom-select select2" style="width: 100%"
                                required>
                                <option value="">--- Pilih Produk ---</option>
                                @foreach ($products as $item)
                                    <option data-id="{{ $item->id }}" data-code="{{ $item->code }}"
                                        data-name="{{ $item->name }}" value="{{ $item->id }}">
                                        [{{ $item->code }}] {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-tag mr-1"></i> PACKING LIST NAME</label>
                            <textarea name="name" id="name" class="form-control" rows="1" maxlength="200" required>Default</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-info-circle mr-1"></i> PRODUCT NAME</label>
                            <textarea id="p_name" class="form-control text-primary font-weight-bold" rows="1" maxlength="200" readonly></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-comment-dots mr-1"></i> VENDOR DESC</label>
                            <textarea name="vendor_desc" id="vendor_desc" class="form-control" rows="1" maxlength="200" placeholder="Keterangan Vendor..."></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-align-left mr-1"></i> PACKING LIST DESC</label>
                            <textarea name="desc" id="desc" class="form-control" rows="1" maxlength="200" placeholder="Keterangan Packing List..."></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-6">
                            <label class="small font-weight-bold"><i class="fas fa-file-import mr-1"></i> IMPORT FROM TEXT (EXCEL TAB)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" id="btn_import_clear" class="btn btn-outline-danger" title="Bersihkan">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <textarea name="import" id="import" class="form-control" placeholder="Paste data dari Excel di sini..." rows="1"></textarea>
                                <div class="input-group-append">
                                    <button type="button" id="btn_import" class="btn btn-info font-weight-bold">
                                        <i class="fas fa-download mr-1"></i> IMPORT
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-lg-6">
                            <label class="small font-weight-bold text-secondary">TEMPLATE PACKS TERSEDIA</label>
                            <div id="current_pack" class="p-2 border rounded bg-light" style="min-height: 45px;">
                                <span class="text-muted small">Pilih produk untuk melihat template...</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-light text-center">
                    <button type="submit" id="btn_simpan" class="btn btn-primary px-4 mr-1">
                        <i class="fas fa-save mr-1"></i> Simpan Packing List
                    </button>
                    <button id="btn_download" type="button" class="btn btn-success px-4 mr-1" disabled>
                        <i class="fas fa-file-excel mr-1"></i> Download Excel
                    </button>
                    <a href="{{ route('packs.create') }}" class="btn btn-outline-warning px-3 mr-1">
                        <i class="fas fa-sync mr-1"></i> Reset Form
                    </a>
                    <a href="{{ route('packs.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </form>

        <div class="card card-sm mt-3 shadow-sm">
            <div class="card-header bg-light py-2">
                <h6 class="font-weight-bold mb-0 text-dark"><i class="fas fa-clipboard-list mr-2"></i>DATA ITEM PACKING LIST</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-hover mb-0" style="width: 100%;">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th style="width: 80px" class="text-center">AKSI</th>
                                <th>NAMA ITEM / DESKRIPSI</th>
                                <th style="width: 150px">QUANTITY</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal_item" tabindex="-1" aria-labelledby="staticBackdropLabel"
            aria-hidden="true">
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
        const URL_INDEX = "{{ route('packs.index') }}"
        const URL_INDEX_API = "{{ route('api.packs.index') }}"
        const URL_INDEX_ITEM_API = "{{ route('api.pack_items.index') }}"
        $(document).ready(function() {
            $('#product_id').select2({
                theme: 'bootstrap4',
            }).on('change', function() {
                $('#current_pack').html('');
                let product_id = $('#product_id').val();
                if (product_id == '' || product_id == null) {
                    return;
                }
                let $opt = $(this).find('option[value="' + product_id + '"]');
                let name = $opt.attr('data-name');
                $('#p_name').val(name || '');
                $.ajax({
                    url: "{{ route('api.products.index') }}/" + product_id,
                    type: 'GET',
                    beforeSend: function() {},
                    success: function(res) {
                        let packs = res.data.packs || [];
                        if (packs.length === 0) {
                            show_message('No packs found!');
                            return;
                        }

                        // generate tombol di modal
                        let html = '';
                        packs.forEach((p, i) => {
                            html += `
                            <button type="button" class="btn btn-outline-primary mb-2"
                                data-pack-index="${i}">
                                ${p.name || '(Tanpa Nama)'}
                            </button>`;
                        });

                        $('#current_pack').html(html);

                        // klik pilih pack
                        $('#current_pack button').off('click').on('click', function() {
                            let index = $(this).data('pack-index');
                            let chosenPack = packs[index];
                            chosenPack.items.forEach(item => {
                                table.row.add({
                                    item: item.item,
                                    qty: item.qty
                                }).draw()
                            });
                            // $('#modalPilihPack').modal('hide');
                        });
                        // show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });

            })

            $('#vendor_id').select2({
                theme: 'bootstrap4',
            })

            var table = $('#table').DataTable({
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
                        className: 'text-left',
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
                }, ],
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
                    show_message('Pilih Produk!', 'warning')
                    return
                }
                if (vendor == '' || vendor == null) {
                    show_message('Pilih Vendor!', 'warning')
                    return
                }
                if (data.length < 1) {
                    show_message('Item minimal harus ada 1!', 'warning')
                    return
                }

                // Validasi tiap item tidak boleh kosong
                let emptyItem = data.find(i => !i.item || i.item.trim() === '');
                if (emptyItem) {
                    show_message('Nama Item tidak boleh ada yang kosong!', 'warning');
                    return;
                }

                $.ajax({
                    url: $('#form').attr('action'),
                    type: 'POST',
                    data: {
                        product_id: product,
                        vendor_id: vendor,
                        vendor_desc: vendor_desc,
                        name: name,
                        desc: desc,
                        items: data
                    },
                    beforeSend: function() {
                        bloc(); // Tambahkan loading
                    },
                    success: function(res) {
                        unbloc();
                        $('#product_id').val('').change()
                        $('#vendor_id').val('').change()
                        $('#name').val('Default')
                        $('#desc').val('')
                        table
                            .rows()
                            .remove()
                            .draw();

                        $('#btn_download').prop('disabled', false)
                        $('#btn_download').val(res.data.id)
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        unbloc();
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

            $('#btn_download').click(function() {
                let data = $('#btn_download').val()
                if (data) {
                    window.open(`${URL_INDEX_API}/${data}/download`)
                }

            })

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
