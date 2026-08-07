<div class="modal fade modal-modern" id="modal_lot" data-backdrop="static" tabindex="-1" aria-labelledby="modal_lotLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="modal_lotLabel">
                    <i class="fas fa-layer-group mr-2"></i>Detail Lot/SN
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table lot-table" id="table_lot" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="text-center">Lot/SN/ED</th>
                                <th style="width: 80px;" class="text-center">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="copy-group">
                            <label><i class="fas fa-barcode mr-1"></i>Lot/SN</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-primary btn-sm" id="btn_copy_lot"
                                        title="Copy Lot/SN">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <textarea id="detail_lot" class="form-control form-control-sm" rows="3" readonly></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="copy-group">
                            <label><i class="fas fa-hashtag mr-1"></i>Serial Number</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-primary btn-sm" id="btn_copy_sn"
                                        title="Copy Serial Number">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <textarea id="detail_sn" class="form-control form-control-sm" rows="3" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
