<div class="modal fade" id="modalUpload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload & Import Excel
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold text-muted">Pilih Kategori <span
                                class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap" id="uploadKategoriSelect" style="gap: 8px;">
                            @foreach ($kategoriList as $kat)
                                @if ($kat !== 'Lainnya')
                                    <label class="kategori-badge" style="cursor:pointer;"
                                        data-kategori="{{ $kat }}">
                                        <input type="radio" name="kategori" value="{{ $kat }}"
                                            class="d-none"> {{ $kat }}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        <small class="text-muted mt-1 d-block" id="uploadKategoriError"
                            style="display:none !important;"></small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-muted">File Excel <span
                                class="text-danger">*</span></label>
                        <div class="upload-dropzone" id="uploadDropzone">
                            <input type="file" id="uploadFileInput" name="file" accept=".xlsx,.xls"
                                style="display:none;">
                            <div class="text-center" id="uploadPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                <p class="mb-1 font-weight-bold text-muted">Klik atau seret file ke sini</p>
                                <small class="text-muted">Format: .xlsx, .xls — Diproses di browser, tidak menghabiskan memory server</small>
                            </div>
                            <div class="text-center" id="uploadFilePreview" style="display:none;">
                                <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                                <p class="mb-1 font-weight-bold" id="uploadFileName"></p>
                                <small class="text-muted" id="uploadFileSize"></small>
                            </div>
                        </div>
                        <small class="text-danger" id="uploadFileError" style="display:none;"></small>
                    </div>
                    <div id="importProgressWrapper" style="display:none;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted" id="importProgressText">Membaca file...</small>
                            <small class="font-weight-bold" id="importProgressPct">0%</small>
                        </div>
                        <div class="progress" style="height: 20px; border-radius: 10px;">
                            <div id="importProgressBar"
                                class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%;">0%</div>
                        </div>
                        <div class="import-progress-detail" id="importProgressDetail"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                    id="btnCloseUpload">Batal</button>
                <button type="button" class="btn btn-warning font-weight-bold" id="btnDoUpload"
                    onclick="doClientImport()" disabled>
                    <i class="fas fa-upload mr-1"></i> Parse & Import
                </button>
            </div>
        </div>
    </div>
</div>
