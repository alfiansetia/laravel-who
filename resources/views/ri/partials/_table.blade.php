<div class="card card-sm">
    <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px;">
        {{-- Left: Search + Refresh --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
            <div class="input-group input-group-sm" style="max-width: 400px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="search" id="searchInput" class="form-control form-control-sm"
                    placeholder="Cari nomor RI...">
            </div>
            <button type="button" id="btnRefresh" class="btn btn-outline-secondary btn-sm" title="Refresh"
                onclick="loadData()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern" id="tableRI" style="width:100%">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>VENDOR</th>
                        <th>PO</th>
                        <th>NOTES</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
        <div class="d-flex align-items-center" style="gap: 8px;">
            <span class="text-muted small">Tampilkan</span>
            <select id="perPageSelect" class="per-page-select">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
            </select>
            <span class="text-muted small">data</span>
        </div>
        <small class="text-muted" id="pageInfo"></small>
        <div class="pagination-modern" id="pagination"></div>
    </div>
</div>
