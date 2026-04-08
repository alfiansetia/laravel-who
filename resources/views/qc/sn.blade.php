<div class="accordion" id="accordionQC">
    <div class="card card-sm mb-2">
        <div class="card-header bg-light border-0 py-2" id="headingOne">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left text-primary font-weight-bold" type="button"
                    data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <i class="fas fa-file-invoice mr-2"></i>PREVIEW LAMPIRAN
                </button>
            </h2>
        </div>

        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionQC">
            <div class="card-body p-3">
                @include('qc.lampiran')
            </div>
        </div>
    </div>
    <div class="card card-sm mb-4">
        <div class="card-header bg-light border-0 py-2" id="headingTwo">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left text-primary font-weight-bold collapsed" type="button"
                    data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <i class="fas fa-table mr-2"></i>PREVIEW TABLE QC
                </button>
            </h2>
        </div>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionQC">
            <div class="card-body p-3">
                <div class="table-responsive">
                    @include('qc.table')
                </div>
            </div>
        </div>
    </div>
</div>
