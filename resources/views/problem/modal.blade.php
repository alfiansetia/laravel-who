<!-- Modal Add/Edit Item -->
<div class="modal fade" id="modal_item" tabindex="-1" aria-labelledby="modal_item_title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_item_title">Tambah Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_item" action="store">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="select_product">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="select_product" class="form-control select2" style="width: 100%"
                            required>
                            <option value="">-- Pilih Product --</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="item_qty">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="item_qty" name="qty" placeholder="Qty"
                            min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="item_lot">Lot/SN</label>
                        <textarea class="form-control" id="item_lot" name="lot" placeholder="Lot/SN" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="item_desc">Description</label>
                        <textarea class="form-control" id="item_desc" name="desc" placeholder="Description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="button" id="btn_save_item" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Log -->
<div class="modal fade" id="modal_log" tabindex="-1" aria-labelledby="modal_log_title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_log_title">Tambah Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_log" action="store">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="log_date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="log_date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="log_desc">Description</label>
                        <textarea class="form-control" id="log_desc" name="desc" placeholder="Description / Note / Progress" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="button" id="btn_save_log" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
