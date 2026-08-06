<div class="card card-sm">
    <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px;">
        {{-- Left: Search + Filter + Refresh --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
            <div class="input-group input-group-sm" style="max-width: 400px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="search" id="searchInput" class="form-control form-control-sm"
                    placeholder="Cari nomor izin, merk, pendaftar...">
            </div>
            <select id="kategoriSelect" class="form-control form-control-sm" style="width: auto; min-width: 130px;">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriList as $kat)
                    <option value="{{ $kat }}">{{ $kat }}</option>
                @endforeach
            </select>
            <button type="button" id="btnRefresh" class="btn btn-outline-secondary btn-sm" title="Refresh"
                onclick="loadData()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        {{-- Right: Action Buttons --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
            <button type="button" id="btnCheckFiles" class="btn btn-outline-info btn-sm" onclick="checkFiles()">
                <i class="fas fa-file-excel mr-1"></i> Cek File
            </button>
            <button type="button" id="btnUploadFile" class="btn btn-warning btn-sm" onclick="showUploadModal()">
                <i class="fas fa-cloud-upload-alt mr-1"></i> Upload File
            </button>
            <div class="dropdown">
                <button class="btn btn-danger btn-sm dropdown-toggle" type="button" id="btnDeleteDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus Data
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnDeleteDropdown">
                    @foreach ($kategoriList as $kat)
                        <a class="dropdown-item" href="#"
                            onclick="deleteByKategori('{{ $kat }}'); return false;">
                            <i class="fas fa-times-circle text-danger mr-1"></i> Hapus {{ $kat }}
                        </a>
                    @endforeach
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" onclick="deleteAllData(); return false;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Hapus Semua Data
                    </a>
                </div>
            </div>
            <button type="button" id="btnSync" class="btn btn-primary btn-sm" onclick="triggerSync()">
                <i class="fas fa-cloud-download-alt mr-1"></i> Sync Data
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern" id="tableIzinEdar" style="width:100%">
                <thead>
                    <tr>
                        <th>Aksi</th>
                        <th>Kategori</th>
                        <th style="white-space:nowrap">No. Izin Edar</th>
                        <th style="white-space:nowrap">Tgl Terbit</th>
                        <th style="white-space:nowrap">Tgl Exp</th>
                        <th>Merk</th>
                        <th>Jenis Produk</th>
                        <th>Pendaftar</th>
                        <th>Pabrik</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
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
                <option value="200">200</option>
            </select>
            <span class="text-muted small">data</span>
        </div>
        <small class="text-muted" id="pageInfo"></small>
        <div class="pagination-modern" id="pagination"></div>
    </div>
</div>
