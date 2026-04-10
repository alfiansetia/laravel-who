<div class="modal fade" id="modal_pl" tabindex="-1" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header dengan Tab Buttons (Pills) -->
            <div class="modal-header bg-light py-2 d-flex align-items-center justify-content-between">
                <ul class="nav nav-pills" id="sopTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold px-4" id="detail-tab" data-toggle="tab" href="#tab-detail" role="tab">
                            <i class="fas fa-eye mr-1"></i> VIEW DETAIL
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold px-4" id="edit-tab" data-toggle="tab" href="#tab-edit" role="tab">
                            <i class="fas fa-edit mr-1"></i> EDIT SOP
                        </a>
                    </li>
                </ul>
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <!-- Header Info SOP -->
                <div class="bg-white px-4 py-3 border-bottom rounded-top">
                    <div class="row">
                        <div class="col-md-8 border-right">
                            <label class="small text-muted mb-0">Product Name</label>
                            <h6 id="title_detail_product" class="font-weight-bold mb-0 text-dark">-</h6>
                        </div>
                        <div class="col-md-4 pl-md-4">
                            <label class="small text-muted mb-0">Target QC</label>
                            <h6 id="target_value_display" class="text-primary font-weight-bold mb-0">-</h6>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="sopTabContent">
                    <!-- Tab Detail (Tampilan Bersih) -->
                    <div class="tab-pane fade show active px-4 py-3" id="tab-detail" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered mb-0" id="table_sop_view">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width: 40px">No</th>
                                        <th>Item SOP / Cara Pemeriksaan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Edit (Interaktif) -->
                    <div class="tab-pane fade px-4 py-3" id="tab-edit" role="tabpanel">
                        <div class="form-group mb-3 px-1">
                            <label class="small font-weight-bold text-secondary"><i class="fas fa-bullseye mr-1"></i> EDIT TARGET QC</label>
                            <input type="text" id="input_target_sop" class="form-control form-control-sm font-weight-bold" placeholder="Contoh: 100% Lolos QC">
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <h6 class="small font-weight-bold text-dark mb-0"><i class="fas fa-list mr-1"></i> DAFTAR ITEM SOP</h6>
                            <button type="button" class="btn btn-xs btn-info" onclick="addSopRow()">
                                <i class="fas fa-plus mr-1"></i> Tambah Item
                            </button>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 400px">
                            <table class="table table-sm table-hover border" id="table_sop_edit">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" style="width: 40px">#</th>
                                        <th>Keterangan Item</th>
                                        <th class="text-center" style="width: 50px"><i class="fas fa-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary d-none" id="btn_save_sop">
                    <i class="fas fa-save mr-1"></i> Simpan SOP
                </button>
            </div>
        </div>
    </div>
</div>
