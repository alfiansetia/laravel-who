<div class="card card-sm" style="border-radius: 12px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px; border-radius: 12px 12px 0 0;">
        {{-- Left: Search + Status Filter + Refresh --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
            <div class="input-group input-group-sm" style="max-width: 350px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="search" id="searchInput" class="form-control form-control-sm"
                    placeholder="Cari kode atau nama...">
            </div>
            <select id="status_filter" class="form-control form-control-sm" style="width: 200px;">
                <option value="all">-- Semua Status Data --</option>
                <option value="lengkap">Lengkap (PLTB Terisi)</option>
                <option value="tidak_lengkap">Tidak Lengkap</option>
            </select>
            <button type="button" id="refresh" class="btn btn-outline-secondary btn-sm" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        {{-- Right: Action Buttons --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="btnExportDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download mr-1"></i>Export
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnExportDropdown">
                    <a class="dropdown-item" href="#" id="exportCopy"><i class="fas fa-copy mr-2"></i>Copy</a>
                    <a class="dropdown-item" href="#" id="exportCsv"><i class="fas fa-file-csv mr-2"></i>CSV</a>
                    <a class="dropdown-item" href="#" id="exportExcel"><i
                            class="fas fa-file-excel mr-2 text-success"></i>Excel</a>
                    <a class="dropdown-item" href="#" id="exportPdf"><i
                            class="fas fa-file-pdf mr-2 text-danger"></i>PDF</a>
                    <a class="dropdown-item" href="#" id="exportPrint"><i class="fas fa-print mr-2"></i>Print</a>
                </div>
            </div>
            <button type="button" id="btnSyncAll" class="btn btn-danger btn-sm" title="Sync Semua Data">
                <i class="fas fa-sync mr-1"></i>Sync All
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <form id="selected">
                <table class="table table-modern" id="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th style="width: 60px;" class="text-center">UOM</th>
                            <th style="width: 150px;" class="text-center">PLTBB</th>
                            <th>Note</th>
                            <th>Tgl Input</th>
                            <th>Status</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>
