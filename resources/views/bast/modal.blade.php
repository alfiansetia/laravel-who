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
                            style="width: 100%">
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-6">
                            <input type="number" class="form-control" id="qty_prod" value="100" placeholder="QTY">
                        </div>
                        <div class="col-md-6">
                            <select name="satuan" class="form-control" id="satuan">
                                <option value="Pcs">Pcs</option>
                                <option value="Pck">Pck</option>
                                <option value="Unit">Unit</option>
                                <option value="EA" selected>EA</option>
                                <option value="Box">Box</option>
                                <option value="Btl">Btl</option>
                                <option value="Vial">Vial</option>
                            </select>
                        </div>
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
                    <h5 class="modal-title" id="edit_modalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row mb-2">
                        <div class="col-md-6">
                            <input type="number" class="form-control" id="qty_prod_edit" placeholder="QTY">
                        </div>
                        <div class="col-md-6">
                            <select name="satuan" class="form-control" id="edit_satuan">
                                <option value="Pcs">Pcs</option>
                                <option value="Pck">Pck</option>
                                <option value="Unit">Unit</option>
                                <option value="EA">EA</option>
                                <option value="Box">Box</option>
                                <option value="Btl">Btl</option>
                                <option value="Vial">Vial</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <textarea class="form-control" id="lot_prod_edit" placeholder="LOT /ED" rows="7"></textarea>
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
