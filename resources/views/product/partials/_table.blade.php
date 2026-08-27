<div class="card card-sm" style="border-radius: 12px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px; border-radius: 12px 12px 0 0;">
        {{-- Left: Search + Refresh --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
            <div class="input-group input-group-sm" style="max-width: 350px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="search" id="searchInput" class="form-control form-control-sm"
                    placeholder="Cari kode, nama, AKL...">
            </div>
            <button type="button" id="btnRefresh" class="btn btn-outline-secondary btn-sm" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        {{-- Right: Action Buttons --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
            {{-- Filter Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-outline-warning btn-sm dropdown-toggle" type="button" id="btnFilterDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
                <div class="dropdown-menu dropdown-menu-right p-3" aria-labelledby="btnFilterDropdown"
                    style="min-width:220px;">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">PLTBB</label>
                        <select id="filterPltbb" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="ada">Ada</option>
                            <option value="lengkap">Lengkap</option>
                            <option value="tidak_ada">Tidak Ada</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">Image</label>
                        <select id="filterImage" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="ada">Ada</option>
                            <option value="tidak_ada">Tidak Ada</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">PL (Pack)</label>
                        <select id="filterPl" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="ada">Ada</option>
                            <option value="tidak_ada">Tidak Ada</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold mb-1">SOP</label>
                        <select id="filterSop" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="ada">Ada</option>
                            <option value="tidak_ada">Tidak Ada</option>
                        </select>
                    </div>
                    <hr class="my-2">
                    <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-undo mr-1"></i>Reset Filter
                    </button>
                </div>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="btnColvisDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-columns mr-1"></i>Kolom
                </button>
                <div class="dropdown-menu dropdown-menu-right p-2" id="colvisMenu" aria-labelledby="btnColvisDropdown"
                    style="min-width:180px;">
                </div>
            </div>
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
                    <a class="dropdown-item" href="#" id="exportPrint"><i
                            class="fas fa-print mr-2"></i>Print</a>
                </div>
            </div>
            <button type="button" id="btnSync" class="btn btn-danger btn-sm">
                <i class="fas fa-sync mr-1"></i>Sync
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern" id="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                        <th>KODE</th>
                        <th>NAME</th>
                        <th>AKL</th>
                        <th style="white-space:nowrap">AKL EXP</th>
                        <th>DESC</th>
                        <th>PLTBB</th>
                        <th>IMAGE</th>
                        <th>PL</th>
                        <th>SOP</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
