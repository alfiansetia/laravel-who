@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .form-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .form-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .section-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f1f5f9;
            margin-left: 1rem;
        }

        .table-items th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 1rem;
        }

        .table-items td {
            padding: 1rem;
            vertical-align: middle !important;
        }

        .btn-action-sm {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <form method="POST" action="{{ route('problems.store') }}" id="form">
            @csrf
            <!-- Main Info Card -->
            <div class="card form-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Tambah Problem Baru</h5>
                    <a href="{{ route('problems.index') }}" class="btn btn-sm btn-light text-secondary">
                        <i class="fas fa-times mr-1"></i>Batal
                    </a>
                </div>
                <div class="card-body">
                    <div class="section-title">Informasi Utama</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Nomor Problem</label>
                            <input type="text" name="number" id="number" class="form-control"
                                value="{{ strtoupper(date('Y-M-')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal</label>
                            <input type="text" name="date" id="date" class="form-control datepicker"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>PIC</label>
                            <select name="pic" id="pic" class="form-control select2" required>
                                <option value="Karim" selected>Karim</option>
                                <option value="Sofyan">Sofyan</option>
                                <option value="Asep">Asep</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-3">Detail & Status</div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Type</label>
                            <select name="type" id="type" class="form-control select2">
                                <option value="unit">Unit</option>
                                <option value="dus">Dus</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Stock</label>
                            <select name="stock" id="stock" class="form-control select2">
                                <option value="stock">Stock</option>
                                <option value="import">Import</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control select2">
                                <option value="pending" selected>Pending</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>RI / PO</label>
                            <input type="text" name="ri_po" id="ri_po" class="form-control" placeholder="RI-123456">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Email On</label>
                            <input type="text" name="email_on" id="email_on" class="form-control datepicker">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card form-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Daftar Produk Bermasalah</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-soft-warning mr-2" id="btn_paste_excel"
                            style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: none; font-weight: 600;">
                            <i class="fas fa-paste mr-1"></i>Paste Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btn_add_item">
                            <i class="fas fa-plus mr-1"></i>Tambah Item
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-items" id="table_items">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Product</th>
                                <th class="text-center" style="width: 100px;">Qty</th>
                                <th>Lot / SN</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="items_body">
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open d-block mb-2 shadow-sm p-2 rounded-circle mx-auto"
                                        style="width: 45px; height: 45px; font-size: 1.25rem; background: #f8fafc; line-height: 29px;"></i>
                                    Belum ada item ditambahkan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" id="btn_simpan" class="btn btn-primary btn-lg px-5 shadow-sm"
                    style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-save mr-2"></i>Simpan Problem
                </button>
            </div>
        </form>
    </div>

    <!-- Modal Add Item -->
    <div class="modal fade" id="modal_item" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold">Tambah Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Product <span class="text-danger">*</span></label>
                        <select id="select_product" class="form-control select2" style="width: 100%">
                            <option value="">-- Pilih Product --</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}" data-code="{{ $item->code }}"
                                    data-name="{{ $item->name }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label>Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="item_qty" min="1" value="1">
                    </div>
                    <div class="form-group mt-3">
                        <label>Lot / Serial Number</label>
                        <input type="text" class="form-control" id="item_lot" placeholder="Contoh: SN123456">
                    </div>
                    <div class="form-group mt-3">
                        <label>Description / Note</label>
                        <textarea class="form-control" id="item_desc" rows="2" placeholder="Detail kerusakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Batal</button>
                    <button type="button" id="btn_save_item" class="btn btn-primary px-4">Tambah</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Paste Excel -->
    <div class="modal fade" id="modal_paste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-paste mr-2 text-warning"></i>Paste Excel
                        Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3">
                    <div class="p-3 rounded-lg mb-3"
                        style="background: rgba(99, 102, 241, 0.05); border: 1px dashed rgba(99, 102, 241, 0.2);">
                        <small class="text-primary d-block font-weight-bold mb-1">FORMAT EXCEL (Copy baris
                            lengkap):</small>
                        <span class="text-muted small">Item No (Tab) Description (Tab) Serial Number (Tab) Hasil QC (Tab)
                            QTY</span>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control border-primary" id="paste_area" rows="6"
                            placeholder="Paste data dari Excel di sini..." style="background: #f8fafc; resize: none;"></textarea>
                    </div>
                    <div id="preview_container" style="display: none;">
                        <label class="font-weight-bold small text-secondary">PREVIEW DATA</label>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-bordered" style="font-size: 0.8rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th>#</th>
                                        <th>Item No</th>
                                        <th>Description</th>
                                        <th>Serial Number</th>
                                        <th>Hasil QC</th>
                                        <th>QTY</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="preview_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="button" id="btn_import_items" class="btn btn-success px-4" disabled>
                        <i class="fas fa-check-circle mr-1"></i>Impor Data
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var items = [];

        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            $(".datepicker").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true,
            });

            $('#btn_add_item').click(function() {
                $('#modal_item').modal('show');
            });

            $('#btn_save_item').click(function() {
                let productId = $('#select_product').val();
                let selectedData = $('#select_product').select2('data')[0];

                if (!productId) {
                    show_message('Pilih product terlebih dahulu!');
                    return;
                }

                let qty = $('#item_qty').val();
                let lot = $('#item_lot').val();
                let desc = $('#item_desc').val();
                let code = selectedData.element.dataset.code;
                let name = selectedData.element.dataset.name || '';

                items.push({
                    product_id: productId,
                    qty: qty,
                    lot: lot,
                    desc: desc,
                    displayCode: code,
                    displayName: name
                });

                renderItems();
                $('#modal_item').modal('hide');
                // Reset fields
                $('#select_product').val('').trigger('change');
                $('#item_qty').val(1);
                $('#item_lot').val('');
                $('#item_desc').val('');
            });

            $(document).on('click', '.btn-remove-item', function() {
                let index = $(this).data('index');
                items.splice(index, 1);
                renderItems();
            });

            function renderItems() {
                let html = '';
                if (items.length === 0) {
                    html = `
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open d-block mb-2 shadow-sm p-2 rounded-circle mx-auto"
                                    style="width: 45px; height: 45px; font-size: 1.25rem; background: #f8fafc; line-height: 29px;"></i>
                                Belum ada item ditambahkan
                            </td>
                        </tr>`;
                } else {
                    items.forEach((item, index) => {
                        html += `
                            <tr>
                                <td class="text-center font-weight-bold text-muted small">${index + 1}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">${item.displayCode}</div>
                                    <div class="small text-muted">${item.displayName}</div>
                                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                </td>
                                <td class="text-center font-weight-bold text-primary">${item.qty}
                                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                                </td>
                                <td>${item.lot || '<span class="text-muted italic">-</span>'}
                                    <input type="hidden" name="items[${index}][lot]" value="${item.lot || ''}">
                                </td>
                                <td class="small text-muted">${item.desc || '-'}
                                    <input type="hidden" name="items[${index}][desc]" value="${item.desc || ''}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-action-sm btn-outline-danger btn-remove-item" data-index="${index}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                }
                $('#items_body').html(html);
            }

            // Export Product Map for quick processing
            var productMap = {};
            @foreach ($products as $item)
                productMap['{{ strtoupper($item->code) }}'] = {
                    id: '{{ $item->id }}',
                    code: '{{ $item->code }}',
                    name: '{{ addslashes($item->name) }}'
                };
            @endforeach

            var parsedItems = [];
            $('#btn_paste_excel').click(function() {
                $('#paste_area').val('');
                $('#preview_container').hide();
                $('#btn_import_items').prop('disabled', true);
                $('#modal_paste').modal('show');
            });

            $('#paste_area').on('input', function() {
                let text = $(this).val().trim();
                let lines = text.split('\n');
                let html = '';
                parsedItems = [];

                if (text) $('#preview_container').show();
                else $('#preview_container').hide();

                lines.forEach((line, idx) => {
                    if (!line.trim()) return;
                    let cols = line.split('\t');
                    let itemNo = (cols[0] || '').trim().toUpperCase();
                    let description = (cols[1] || '').trim();
                    let serialNumber = (cols[2] || '').trim();
                    let hasilQC = (cols[3] || '').trim();
                    let qty = parseInt(cols[4]) || 1;

                    let product = productMap[itemNo] || null;
                    let statText = product ?
                        '<span class="badge badge-success px-2 py-1">OK</span>' :
                        '<span class="badge badge-danger px-2 py-1">Not Found</span>';

                    parsedItems.push({
                        itemNo,
                        description,
                        serialNumber,
                        hasilQC,
                        qty,
                        product
                    });

                    html += `<tr class="${product ? '' : 'table-danger'}">
                        <td>${idx + 1}</td>
                        <td class="font-weight-bold">${itemNo}</td>
                        <td>${description}</td>
                        <td>${serialNumber}</td>
                        <td>${hasilQC}</td>
                        <td class="text-center">${qty}</td>
                        <td class="text-center">${statText}</td>
                    </tr>`;
                });
                $('#preview_body').html(html);
                $('#btn_import_items').prop('disabled', parsedItems.filter(i => i.product).length === 0);
            });

            $('#btn_import_items').click(function() {
                let validItems = parsedItems.filter(i => i.product);
                validItems.forEach(item => {
                    items.push({
                        product_id: item.product.id,
                        qty: item.qty,
                        lot: item.serialNumber,
                        desc: item.hasilQC,
                        displayCode: item.product.code,
                        displayName: item.product.name
                    });
                });
                renderItems();
                $('#modal_paste').modal('hide');
                show_message(`${validItems.length} item berhasil diimpor!`, 'success');
            });
        });
    </script>
@endpush
