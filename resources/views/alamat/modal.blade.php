<!-- Modal -->
<div class="modal fade" id="product_modal" tabindex="-1" aria-labelledby="product_modalLabel" aria-hidden="true">
    <form action="" id="form_add">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="product_modalLabel">Pilih Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <select name="select_product" id="select_product" class="form-control select2"
                            style="width: 100%" required>
                            <option value="">--Select Product--</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" class="form-control" id="desc_prod" placeholder="DESCRIPTION">
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" class="form-control" id="qty_prod" value="100 Ea" placeholder="QTY">
                    </div>
                    <div class="form-group mb-2">
                        <textarea class="form-control" id="lot_prod" placeholder="LOT /ED" rows="7"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="submit" id="btn_modal_save" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="edit_modal" tabindex="-1" aria-labelledby="edit_modalLabel" aria-hidden="true">
    <form action="" id="form_edit">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_modalLabel">Edit Lot/Qty</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <input type="text" class="form-control" id="qty_prod_edit" placeholder="QTY">
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" class="form-control" id="desc_prod_edit" placeholder="DESCRIPTION">
                    </div>
                    <div class="form-group mb-2">
                        <textarea class="form-control" id="lot_prod_edit" placeholder="LOT /ED" rows="7"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="submit" id="btn_modal_save_edit" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
