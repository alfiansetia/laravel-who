<!-- Modal Tracking TIKI -->
<style>
    .tracking-accordion .card {
        border: none;
        margin-bottom: 8px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .tracking-accordion .card-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 12px 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: none;
    }

    .tracking-accordion .card-header:hover {
        background: linear-gradient(135deg, #e9ecef, #dee2e6);
    }

    .tracking-accordion .card-header.active-header {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: #fff !important;
        box-shadow: 0 3px 10px rgba(23, 162, 184, 0.3);
    }

    .tracking-accordion .card-header.active-header * {
        color: #fff !important;
    }

    .tracking-accordion .card-header.active-header small {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .tracking-accordion .card-header .badge-status {
        float: right;
        margin-top: 2px;
    }
</style>
<div class="modal fade" id="modalTrackingTiki" tabindex="-1" role="dialog" aria-labelledby="modalTrackingTikiLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalTrackingTikiLabel">
                    <i class="fas fa-truck mr-1"></i>Tracking TIKI
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="trackingLoading" class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data tracking...</p>
                </div>
                <div id="trackingContent" style="display:none;">
                    <div class="tracking-accordion" id="trackingAccordion"></div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="mr-auto text-muted" id="trackingSummary"></span>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
