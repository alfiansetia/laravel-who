<div class="modal fade" id="modal_pl" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-block">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="modal-title font-weight-bold" id="detail_product_name">Product Detail</h5>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-success mr-2" id="btn-download-zip">
                            <i class="fas fa-file-archive mr-1"></i> Download ZIP
                        </button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div id="detail_product_code" class="text-muted small"></div>
                <div id="detail_product_desc" class="text-secondary small mt-1"
                    style="white-space: pre-wrap; font-style: italic;"></div>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-sop" data-toggle="tab" href="#paneSop" role="tab"
                            aria-controls="paneSop" aria-selected="true">
                            <i class="fas fa-clipboard-check mr-1"></i> SOP QC
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pl" data-toggle="tab" href="#panePl" role="tab"
                            aria-controls="panePl" aria-selected="false">
                            <i class="fas fa-box-open mr-1"></i> Packing List
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-images" data-toggle="tab" href="#paneImages" role="tab"
                            aria-controls="paneImages" aria-selected="false">
                            <i class="fas fa-images mr-1"></i> Images
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pltbb" data-toggle="tab" href="#panePltbb" role="tab"
                            aria-controls="panePltbb" aria-selected="false">
                            <i class="fas fa-ruler-combined mr-1"></i> PLTBB
                            <span id="pltbb_is_complete" class="badge badge-success ml-1">Complete</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="productTabContent">
                    <div class="tab-pane fade show active" id="paneSop" role="tabpanel" aria-labelledby="tab-sop">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Target : <span id="target_value"></span></h6>
                            <button type="button" class="btn btn-xs btn-outline-secondary d-none" id="btn-print-sop">
                                <i class="fas fa-print"></i> Cetak SOP
                            </button>
                        </div>
                        <table id="table_target" class="table table-sm table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 30px;">No</th>
                                    <th>ITEM</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="panePl" role="tabpanel" aria-labelledby="tab-pl">
                        <div id="table_pl_container"></div>
                    </div>
                    <div class="tab-pane fade" id="paneImages" role="tabpanel" aria-labelledby="tab-images">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span></span>
                            <button type="button" class="btn btn-xs btn-outline-primary d-none"
                                id="btn-print-collage">
                                <i class="fas fa-print"></i> Cetak Kolase
                            </button>
                        </div>
                        <div class="text-center">
                            <div id="detail_images"
                                class="d-flex flex-wrap gap-2 justify-content-center align-items-center"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="panePltbb" role="tabpanel" aria-labelledby="tab-pltbb">
                        <div id="table_pltbb_container">
                            <ul class="list-group">
                                <li class="list-group-item">P: <span id="pltbb_p"></span></li>
                                <li class="list-group-item">L: <span id="pltbb_l"></span></li>
                                <li class="list-group-item">T: <span id="pltbb_t"></span></li>
                                <li class="list-group-item">B: <span id="pltbb_b"></span></li>
                                <li class="list-group-item">Note: <span id="pltbb_note"></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal_move" tabindex="-1" aria-labelledby="modal_move_label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_move_label">Product Move</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover" id="table_move" style="width: 100%;cursor: pointer;">
                    <thead>
                        <tr>
                            <th>No DO</th>
                            <th>From</th>
                            <th>Destination</th>
                            <th>Date</th>
                            <th>Lot/SN</th>
                            <th style="width: 30px">QTY</th>
                            <th>Doc</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
