<div class="card filter-card" id="syncCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-sync-alt mr-2 text-primary"></i>Sync Progress</span>
        <div>
            <button type="button" id="btnStopSync" class="btn btn-sm btn-outline-danger mr-1" onclick="stopSync()"
                style="display:none;">
                <i class="fas fa-stop-circle mr-1"></i> Cancel
            </button>
            <button type="button" id="btnForceReset" class="btn btn-sm btn-outline-danger mr-1"
                onclick="forceResetSync()" style="display:none;">
                <i class="fas fa-trash-alt mr-1"></i> Reset
            </button>
            <span id="syncStatusBadge" class="badge badge-info">idle</span>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted" id="syncProgressText">Menyiapkan...</small>
                <small class="font-weight-bold" id="syncProgressPct">0%</small>
            </div>
            <div class="progress" style="height: 22px; border-radius: 12px;">
                <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                </div>
            </div>
        </div>
        <div class="row" id="syncCategories">
            {{-- Filled dynamically --}}
        </div>
        <div class="mt-2" id="syncMessage" style="display:none;">
            <small class="text-muted" id="syncDetailMsg"></small>
        </div>
    </div>
</div>
