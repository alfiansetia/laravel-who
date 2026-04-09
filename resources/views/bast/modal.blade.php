<!-- Modal Add -->
<div class="modal fade" id="product_modal" tabindex="-1" aria-labelledby="product_modalLabel" aria-hidden="true">
    <form action="" id="form_add">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h5 class="modal-title font-weight-bold text-primary" id="product_modalLabel"><i class="fas fa-plus-circle mr-2"></i>Tambah Item Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-dark"><i class="fas fa-box mr-1"></i> PILIH PRODUK</label>
                        <select name="select_product" id="select_product" class="form-control select2"
                            style="width: 100%">
                            <option value="">--- Cari Kode atau Nama Produk ---</option>
                            @foreach ($products as $item)
                                <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark"><i class="fas fa-sort-amount-up mr-1"></i> QUANTITY</label>
                                <input type="number" class="form-control" id="qty_prod" value="100" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark"><i class="fas fa-tag mr-1"></i> SATUAN</label>
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
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-dark"><i class="fas fa-barcode mr-1"></i> LOT / EXPIRATION DATE (ED)</label>
                        <textarea class="form-control" id="lot_prod" placeholder="Masukkan keterangan Lot atau ED..." rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" id="btn_modal_save" class="btn btn-primary px-4 font-weight-bold">
                        <i class="fas fa-save mr-1 text-white"></i> Simpan Item
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>


<!-- Modal Edit -->
<div class="modal fade" id="edit_modal" tabindex="-1" aria-labelledby="edit_modalLabel" aria-hidden="true">
    <form action="" id="form_edit">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h5 class="modal-title font-weight-bold text-warning" id="edit_modalLabel"><i class="fas fa-edit mr-2"></i>Edit Detail Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark"><i class="fas fa-sort-amount-up mr-1"></i> QUANTITY</label>
                                <input type="number" class="form-control" id="qty_prod_edit" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark"><i class="fas fa-tag mr-1"></i> SATUAN</label>
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
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-dark"><i class="fas fa-barcode mr-1"></i> LOT / EXPIRATION DATE (ED)</label>
                        <textarea class="form-control" id="lot_prod_edit" placeholder="Masukkan keterangan Lot atau ED..." rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" id="btn_modal_save" class="btn btn-warning px-4 font-weight-bold">
                        <i class="fas fa-save mr-1 text-dark"></i> Update Item
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
