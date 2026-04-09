{{-- Modal Add Koli --}}
<div class="modal fade" id="koli_modal" tabindex="-1" role="dialog" aria-labelledby="koliModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="koliModalLabel text-primary"><i
                        class="fas fa-plus-circle mr-2"></i>Tambah Koli</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_add_koli">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="urutan"><i class="fas fa-list-ol mr-1"></i> Nomor Koli (Urutan)</label>
                        <input type="text" class="form-control " id="urutan" name="urutan"
                            placeholder="Contoh: 1 atau 1-7" required>
                        <small class="form-text text-muted">Format: Angka (1), Range (1-7), atau Koma (1,3,5)</small>
                    </div>
                    <div class="form-group">
                        <label for="nilai_koli"><i class="fas fa-money-bill-wave mr-1"></i> Nilai (Rp)</label>
                        <input type="text" class="form-control  mask_angka" id="nilai_koli" name="nilai"
                            placeholder="0">
                    </div>
                    <div class="form-group border-top pt-3 mt-3">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_do_koli">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_do_koli">SURAT JALAN/DO</label>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_pk_koli">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_pk_koli">PACKING KAYU</label>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_asuransi_koli" checked>
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_asuransi_koli">ASURANSI</label>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_banting_koli">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_banting_koli">FRAGILE</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary " data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="submit" class="btn btn-primary ">
                        <i class="fas fa-save mr-1"></i>Simpan Koli
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Koli --}}
<div class="modal fade" id="edit_koli_modal" tabindex="-1" role="dialog" aria-labelledby="editKoliModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="editKoliModalLabel"><i class="fas fa-edit mr-2"></i>Update
                    Data Koli</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_edit_koli">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="urutan_edit"><i class="fas fa-list-ol mr-1"></i> Nomor Koli (Urutan)</label>
                        <input type="text" class="form-control " id="urutan_edit" name="urutan"
                            placeholder="Contoh: 1" required>
                    </div>
                    <div class="form-group">
                        <label for="nilai_koli_edit"><i class="fas fa-money-bill-wave mr-1"></i> Nilai (Rp)</label>
                        <input type="text" class="form-control  mask_angka" id="nilai_koli_edit" name="nilai"
                            placeholder="0">
                    </div>
                    <div class="form-group border-top pt-3 mt-3">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_do_koli_edit">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_do_koli_edit">SURAT JALAN/DO</label>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_pk_koli_edit">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_pk_koli_edit">PACKING KAYU</label>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_asuransi_koli_edit">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_asuransi_koli_edit">ASURANSI</label>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="is_banting_koli_edit">
                                    <label class="custom-control-label small font-weight-bold text-muted"
                                        for="is_banting_koli_edit">FRAGILE</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary " data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary ">
                        <i class="fas fa-save mr-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Add Item --}}
<div class="modal fade" id="item_modal" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" data-backdrop="static" data-keyboard="false"
        tabindex="-1" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pb-0">
                <ul class="nav nav-pills" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active  mr-2" id="product-tab" data-toggle="tab"
                            data-target="#productTab" type="button" role="tab" aria-controls="productTab"
                            aria-selected="true"><i class="fas fa-box mr-1"></i> Data Produk</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link  mr-2" id="do-tab" data-toggle="tab" data-target="#doTab"
                            type="button" role="tab" aria-controls="doTab" aria-selected="false"><i
                                class="fas fa-search mr-1"></i> Ambil dari DO</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="it-tab" data-toggle="tab" data-target="#itTab"
                            type="button" role="tab" aria-controls="itTab" aria-selected="false"><i
                                class="fas fa-exchange-alt mr-1"></i> Ambil dari IT</button>
                    </li>
                </ul>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="productTab" role="tabpanel"
                        aria-labelledby="product-tab">
                        <form id="form_add_item">
                            <div class="form-group">
                                <label for="select_product"><i class="fas fa-box mr-1"></i> Nama/Kode Produk</label>
                                <select class="form-control select2" id="select_product" style="width: 100%"
                                    required>
                                    <option value="">Cari Produk...</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">[{{ $product->code }}] {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="qty_item"><i class="fas fa-sort-numeric-up mr-1"></i> Kuantitas
                                        (Qty)</label>
                                    <input type="text" class="form-control " id="qty_item" name="qty"
                                        placeholder="Mis: 1 Ea" value="Ea">
                                </div>
                                <div class="form-group col-md-8">
                                    <label for="desc_item"><i class="fas fa-align-left mr-1"></i> Deskripsi
                                        Item</label>
                                    <input type="text" class="form-control " id="desc_item" name="desc"
                                        placeholder="Keterangan tambahan barang">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lot_item"><i class="fas fa-barcode mr-1"></i> Nomor Lot / Batch</label>
                                <textarea class="form-control " id="lot_item" name="lot" placeholder="Ketik nomor lot jika ada..."
                                    rows="3"></textarea>
                            </div>

                            <div class="modal-footer bg-light border-top-0 mt-4 mx-n3 mb-n3"
                                style="border-radius: 0 0 12px 12px;">
                                <button type="button" class="btn btn-secondary " data-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary ">
                                    <i class="fas fa-plus mr-1"></i>Tambahkan Barang
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="doTab" role="tabpanel" aria-labelledby="do-tab">
                        <label class="small font-weight-bold text-muted"><i class="fas fa-search mr-1"></i> CARI NOMOR
                            DO</label>
                        <select name="" id="select_do_tab" class="form-control select2 mb-3"
                            style="width: 100%">
                        </select>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover border" id="table_do">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Lot/ED</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center" style="width: 50px;">#</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer bg-light border-top-0 mt-3 mx-n3 mb-n3"
                            style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary " data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="itTab" role="tabpanel" aria-labelledby="it-tab">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted">GUDANG ASAL</label>
                                <select name="" id="select_it_gudang_tab" class="form-control select2"
                                    style="width: 100%">
                                    <option value="5">CENTER</option>
                                    <option value="760">BADSTOCK</option>
                                    <option value="990">KARANTINA</option>
                                    <option value="1310">CIBUBUR</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="small font-weight-bold text-muted">CARI NOMOR IT</label>
                                <select name="" id="select_it_tab" class="form-control select2"
                                    style="width: 100%">
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover border" id="table_it">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Qty</th>
                                        <th>Deskripsi</th>
                                        <th class="text-center" style="width: 50px;">#</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer bg-light border-top-0 mt-3 mx-n3 mb-n3"
                            style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary " data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Item --}}
<div class="modal fade" id="edit_item_modal" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="editItemModalLabel"><i class="fas fa-edit mr-2"></i>Ubah
                    Item Pengiriman</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_edit_item">
                <div class="modal-body py-4">
                    <div class="form-group">
                        <label for="qty_item_edit"><i class="fas fa-sort-numeric-up mr-1"></i> Kuantitas (Qty)</label>
                        <input type="text" class="form-control " id="qty_item_edit" name="qty"
                            placeholder="Qty">
                    </div>
                    <div class="form-group">
                        <label for="desc_item_edit"><i class="fas fa-align-left mr-1"></i> Deskripsi Item</label>
                        <input type="text" class="form-control " id="desc_item_edit" name="desc"
                            placeholder="Deskripsi">
                    </div>
                    <div class="form-group">
                        <label for="lot_item_edit"><i class="fas fa-barcode mr-1"></i> Nomor Lot / Batch</label>
                        <textarea class="form-control " id="lot_item_edit" name="lot" placeholder="Lot" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary " data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary ">
                        <i class="fas fa-save mr-1"></i>Simpan Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
