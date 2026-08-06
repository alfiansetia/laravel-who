<div class="card filter-card" id="fileCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-excel mr-2 text-success"></i>File Excel Tersimpan</span>
        <button type="button" class="close" onclick="$('#fileCard').slideUp(200);">
            <span>&times;</span>
        </button>
    </div>
    <div class="card-body">
        <div class="row" id="fileList">
            {{-- Filled dynamically --}}
        </div>
    </div>
</div>
