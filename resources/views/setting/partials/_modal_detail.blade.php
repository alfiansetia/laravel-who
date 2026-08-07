<div class="modal fade modal-modern" id="modal_detail" data-backdrop="static" tabindex="-1"
    aria-labelledby="modal_detailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="modal_detailLabel">
                    <i class="fas fa-info-circle mr-2"></i>Detail Device
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Platform</label>
                        <input type="text" id="detail_platform" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">IP Address</label>
                        <input type="text" id="detail_ip" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="small font-weight-bold text-muted">User Agent</label>
                        <textarea id="detail_user_agent" class="form-control form-control-sm" rows="2" readonly></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="small font-weight-bold text-muted">
                            Token <span id="token_status" class="badge badge-success ml-1" style="display: none;">Device Ini</span>
                        </label>
                        <textarea id="detail_token" class="form-control form-control-sm" rows="3" readonly></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Last Status</label>
                        <input type="text" id="detail_last_status" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Last Status At</label>
                        <input type="text" id="detail_last_status_at" class="form-control form-control-sm" readonly>
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
