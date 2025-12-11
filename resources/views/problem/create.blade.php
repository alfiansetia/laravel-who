@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('problem.store') }}" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">Tambah Problem</h3>
                </div>

                <div class="card-body">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" placeholder="Date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="number">Number</label>
                            <input type="text" name="number" id="number" class="form-control" placeholder="Number"
                                value="{{ strtoupper(date('Y-M-')) }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control" style="width: 100%;">
                                <option value="dus">Dus</option>
                                <option value="unit">Unit</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stock">Stock</label>
                            <select name="stock" id="stock" class="form-control" style="width: 100%;">
                                <option value="stock">Stock</option>
                                <option value="import">Import</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ri_po">RI/PO</label>
                            <input type="text" name="ri_po" id="ri_po" class="form-control" placeholder="RI/PO">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_on">Email On</label>
                            <input type="date" name="email_on" id="email_on" class="form-control"
                                placeholder="Email On">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" style="width: 100%;">
                                <option value="pending">Pending</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pic">PIC</label>
                            <select name="pic" id="pic" class="form-control" style="width: 100%;" required>
                                <option value="Karim" selected>Karim</option>
                                <option value="Sofyan">Sofyan</option>
                                <option value="Asep">Asep</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box mr-1"></i>Items
                        <span class="badge badge-primary" id="items-count">0</span>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-info" id="btn_add_item">
                            <i class="fas fa-plus mr-1"></i>Tambah Item
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" id="table_items">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Product</th>
                                <th style="width: 80px;">Qty</th>
                                <th>Lot</th>
                                <th>Desc</th>
                                <th style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="items_body">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer mt-3">
                <a href="{{ route('problem.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
                <button type="submit" id="btn_simpan" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Modal Add Item -->
    <div class="modal fade" id="modal_item" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="select_product">Product <span class="text-danger">*</span></label>
                        <select id="select_product" class="form-control select2" style="width: 100%">
                            <option value="">-- Pilih Product --</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}" data-code="{{ $item->code }}"
                                    data-name="{{ $item->name }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="item_qty">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="item_qty" placeholder="Qty" min="1"
                            value="1">
                    </div>
                    <div class="form-group">
                        <label for="item_lot">Lot/SN</label>
                        <textarea class="form-control" id="item_lot" placeholder="Lot/SN" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="item_desc">Description</label>
                        <textarea class="form-control" id="item_desc" placeholder="Description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="button" id="btn_save_item" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Tambah
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var itemIndex = 0;
        var items = [];

        $(document).ready(function() {
            // Initialize Select2 for all form selects
            $('#type, #stock, #status, #pic').select2({
                theme: 'bootstrap4',
                minimumResultsForSearch: Infinity
            });

            // Initialize Select2 for product modal
            $('#select_product').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal_item'),
            });

            // Open modal
            $('#btn_add_item').click(function() {
                $('#select_product').val('').trigger('change');
                $('#item_qty').val(1);
                $('#item_lot').val('');
                $('#item_desc').val('');
                $('#modal_item').modal('show');
            });

            // Add item to table
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

                // Add to items array
                items.push({
                    product_id: productId,
                    qty: qty,
                    lot: lot,
                    desc: desc,
                    display: `[${code}] ${name}`
                });

                renderItems();
                $('#modal_item').modal('hide');
            });

            // Remove item
            $(document).on('click', '.btn-remove-item', function() {
                let index = $(this).data('index');
                items.splice(index, 1);
                renderItems();
            });
        });

        function renderItems() {
            let html = '';
            items.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.display}
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        </td>
                        <td>
                            ${item.qty}
                            <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                        </td>
                        <td>
                            ${item.lot || '-'}
                            <input type="hidden" name="items[${index}][lot]" value="${item.lot || ''}">
                        </td>
                        <td>
                            ${item.desc || '-'}
                            <input type="hidden" name="items[${index}][desc]" value="${item.desc || ''}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-item" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#items_body').html(html);
            $('#items-count').text(items.length);
        }
    </script>
@endpush
