<div class="card setting-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-cog mr-2 text-primary"></i>Odoo Session</h5>
        <div class="d-flex" style="gap: 4px;">
            <button type="button" id="btn_refresh" class="btn btn-outline-secondary btn-sm" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button type="button" id="btn_fix" class="btn btn-outline-danger btn-sm" title="Fix Session">
                <i class="fas fa-hammer"></i>
            </button>
        </div>
    </div>
    <form id="form" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="small font-weight-bold text-muted">Session Name</label>
                    <input type="text" disabled class="form-control form-control-sm" id="odoo_session_name"
                        placeholder="ODOO SESSION USER">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="small font-weight-bold text-muted">Username</label>
                    <input type="text" disabled class="form-control form-control-sm" id="odoo_session_username"
                        placeholder="ODOO SESSION USERNAME">
                </div>
                <div class="col-12">
                    <label class="small font-weight-bold text-muted">Session ID</label>
                    <textarea class="form-control form-control-sm" id="odoo_env" placeholder="ODOO SESSION"
                        rows="3" required></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center" style="gap: 6px;">
            <div class="d-flex flex-wrap" style="gap: 6px;">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
                <button type="button" id="btn_notif" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-bell mr-1"></i>Tes Notif
                </button>
                <button type="button" id="btn_cek_odoo" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-wifi mr-1"></i>Cek Odoo
                </button>
                <button type="button" id="btn_logout" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </button>
            </div>
            <button type="submit" id="btn_simpan" class="btn btn-primary btn-sm">
                <i class="fas fa-save mr-1"></i>Simpan
            </button>
        </div>
    </form>
</div>
