<div class="modal fade" id="modal_add" data-backdrop="static" data-keyboard="false" aria-labelledby="staticBackdropLabelAdd"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form_add" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabelAdd">Add Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group col-12">
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" class="form-control" style="width: 100%;">
                            <option value="">--Pilih--</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">[{{ $product->code }}] {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-12">
                        <label for="lot_number">Lot Number</label>
                        <input name="lot_number" id="lot_number" class="form-control" required>
                    </div>
                    <div class="form-group col-12">
                        <label for="lot_expiry">Lot Expiry</label>
                        <input name="lot_expiry" id="lot_expiry" class="form-control" type="text">
                    </div>
                    <div class="form-group col-12">
                        <label for="qc_date">QC Date</label>
                        <input name="qc_date" id="qc_date" class="form-control" type="text">
                    </div>
                    <div class="form-group col-12">
                        <label for="qc_by">QC By</label>
                        <input name="qc_by" id="qc_by" class="form-control" required>
                    </div>
                    <div class="form-group col-12">
                        <label for="qc_note">QC Note</label>
                        <div class="input-group">
                            <textarea name="qc_note" id="qc_note" class="form-control" maxlength="200"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button id="btn_add" type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
