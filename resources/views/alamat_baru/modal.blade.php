{{-- Modal Add Koli --}}
<div class="modal fade" id="koli_modal" tabindex="-1" role="dialog" aria-labelledby="koliModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="koliModalLabel">Tambah Koli</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_add_koli">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="urutan">Nomor Koli (Urutan)</label>
                        <input type="text" class="form-control" id="urutan" name="urutan"
                            placeholder="Contoh: 1 atau 1-7 atau 1,4,5" required>
                        <small class="form-text text-muted">Format: Angka (1), Range (1-7), atau Koma (1,3,5)</small>
                    </div>
                    <div class="form-group">
                        <label for="nilai_koli">Nilai (Rp)</label>
                        <input type="text" class="form-control mask_angka" id="nilai_koli" name="nilai"
                            placeholder="Nilai">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_do_koli">
                            <label class="custom-control-label" for="is_do_koli">SURAT JALAN/DO</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_pk_koli">
                            <label class="custom-control-label" for="is_pk_koli">PACKING KAYU</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_asuransi_koli" checked>
                            <label class="custom-control-label" for="is_asuransi_koli">ASURANSI</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_banting_koli">
                            <label class="custom-control-label" for="is_banting_koli">JANGAN DIBANTING</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Koli --}}
<div class="modal fade" id="edit_koli_modal" tabindex="-1" role="dialog" aria-labelledby="editKoliModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKoliModalLabel">Edit Koli</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_edit_koli">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="urutan_edit">Nomor Koli (Urutan)</label>
                        <input type="text" class="form-control" id="urutan_edit" name="urutan"
                            placeholder="Contoh: 1 atau 1-7 atau 1,4,5" required>
                        <small class="form-text text-muted">Format: Angka (1), Range (1-7), atau Koma (1,3,5)</small>
                    </div>
                    <div class="form-group">
                        <label for="nilai_koli_edit">Nilai (Rp)</label>
                        <input type="text" class="form-control mask_angka" id="nilai_koli_edit" name="nilai"
                            placeholder="Nilai">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_do_koli_edit">
                            <label class="custom-control-label" for="is_do_koli_edit">SURAT JALAN/DO</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_pk_koli_edit">
                            <label class="custom-control-label" for="is_pk_koli_edit">PACKING KAYU</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_asuransi_koli_edit">
                            <label class="custom-control-label" for="is_asuransi_koli_edit">ASURANSI</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_banting_koli_edit">
                            <label class="custom-control-label" for="is_banting_koli_edit">JANGAN DIBANTING</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Add Item --}}
<div class="modal fade" id="item_modal" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Tambah Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_add_item">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="select_product">Product</label>
                        <select class="form-control select2" id="select_product" style="width: 100%" required>
                            <option value="">Pilih Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">[{{ $product->code }}] {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="qty_item">Qty</label>
                        <input type="text" class="form-control" id="qty_item" name="qty" placeholder="Qty" value="Ea">
                    </div>
                    <div class="form-group">
                        <label for="desc_item">Desc</label>
                        <input type="text" class="form-control" id="desc_item" name="desc"
                            placeholder="Description">
                    </div>
                    <div class="form-group">
                        <label for="lot_item">Lot</label>
                        <textarea class="form-control" id="lot_item" name="lot" placeholder="Lot"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Item --}}
<div class="modal fade" id="edit_item_modal" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_edit_item">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="qty_item_edit">Qty</label>
                        <input type="text" class="form-control" id="qty_item_edit" name="qty"
                            placeholder="Qty">
                    </div>
                    <div class="form-group">
                        <label for="desc_item_edit">Desc</label>
                        <input type="text" class="form-control" id="desc_item_edit" name="desc"
                            placeholder="Description">
                    </div>
                    <div class="form-group">
                        <label for="lot_item_edit">Lot</label>
                        <textarea class="form-control" id="lot_item_edit" name="lot" placeholder="Lot"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
