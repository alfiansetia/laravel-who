<!-- Modal Add/Edit Item -->
<div class="modal fade" id="modal_item" tabindex="-1" aria-labelledby="modal_item_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modal_item_title">Tambah Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_item" action="store">
                <div class="modal-body pt-3">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Pilih Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="select_product" class="form-control select2" style="width: 100%" required>
                            <option value="">-- Pilih Product --</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="item_qty" name="qty" placeholder="Qty" min="1" value="1" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Lot / Serial Number</label>
                        <input type="text" class="form-control" id="item_lot" name="lot" placeholder="SN123...">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-secondary">Description / Note</label>
                        <textarea class="form-control" id="item_desc" name="desc" placeholder="Detail..." rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Tutup</button>
                    <button type="button" id="btn_save_item" class="btn btn-primary px-4">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Log -->
<div class="modal fade" id="modal_log" tabindex="-1" aria-labelledby="modal_log_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modal_log_title">Tambah Catatan Progress</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_log" action="store">
                <div class="modal-body pt-3">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Tanggal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control datepicker" id="log_date" name="date" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-secondary">Keterangan / Progress</label>
                        <textarea class="form-control" id="log_desc" name="desc" placeholder="Tulis progress atau catatan di sini..." rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Tutup</button>
                    <button type="button" id="btn_save_log" class="btn btn-primary px-4">Simpan Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
