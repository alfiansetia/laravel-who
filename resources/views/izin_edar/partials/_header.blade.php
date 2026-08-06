<div class="d-flex justify-content-end align-items-center py-2 mb-2">
    <div class="d-flex" style="gap: 8px;">
        <button type="button" id="btnCheckFiles" class="btn btn-outline-info" onclick="checkFiles()">
            <i class="fas fa-file-excel mr-1"></i> Cek File
        </button>
        <button type="button" id="btnUploadFile" class="btn btn-warning" onclick="showUploadModal()">
            <i class="fas fa-cloud-upload-alt mr-1"></i> Upload File
        </button>
        <div class="dropdown">
            <button class="btn btn-danger dropdown-toggle" type="button" id="btnDeleteDropdown" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
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
        <button type="button" id="btnSync" class="btn btn-primary" onclick="triggerSync()">
            <i class="fas fa-cloud-download-alt mr-1"></i> Sync Data
        </button>
    </div>
</div>
