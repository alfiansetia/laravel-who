<div class="card filter-card">
    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;"
        data-toggle="collapse" data-target="#filterCollapse">
        <span><i class="fas fa-filter mr-2 text-primary"></i>Filter</span>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="collapse" id="filterCollapse">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-12 mb-3">
                    <label class="small font-weight-bold text-muted">Kategori</label>
                    <div class="d-flex flex-wrap" id="kategoriFilter" style="gap: 8px;">
                        <span class="kategori-badge active" data-kategori="">Semua</span>
                        @foreach ($kategoriList as $kat)
                            <span class="kategori-badge" data-kategori="{{ $kat }}">{{ $kat }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="small font-weight-bold text-muted">Pencarian</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" id="searchInput" class="form-control"
                            placeholder="Nomor izin, merk, pendaftar, jenis produk...">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="btnResetFilter" class="btn btn-secondary btn-block">
                        <i class="fas fa-sync-alt mr-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
