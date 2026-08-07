<div class="modal fade" id="modal_product" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="modal_productLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_productLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="modalTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab"
                            aria-controls="product" aria-selected="true">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="product-lot-tab" data-toggle="tab" href="#product-lot" role="tab"
                            aria-controls="product-lot" aria-selected="false">Product Lot</a>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="modalTabContent">
                    <div class="tab-pane fade show active" id="product" role="tabpanel" aria-labelledby="product-tab">
                        <table class="table table-hover" id="table_product" style="width: 100%;cursor: pointer;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Desc</th>
                                    <th>AKL</th>
                                    <th style="width: 30px">QTY SO</th>
                                    <th style="width: 30px">QTY DONE</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="product-lot" role="tabpanel" aria-labelledby="product-lot-tab">
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-6">
                                <label for="filter_product_code">Filter Product Code</label>
                                <select id="filter_product_code" class="form-control select2" style="width: 100%;">
                                    <option value=""></option>
                                    <option value="">All Products</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn_reset_filter" class="btn btn-secondary btn-block">
                                    <i class="fas fa-sync-alt mr-1"></i>Reset
                                </button>
                            </div>
                        </div>
                        <table class="table table-hover" id="table_product_lot" style="width: 100%;cursor: pointer;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Desc</th>
                                    <th>Product</th>
                                    <th>AKL</th>
                                    <th>LOT/SN</th>
                                    <th>QTY</th>
                                    <th>EXP DATE</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-2">
                <div class="mt-2">
                    <b>Status</b> :
                    <div id="modal_status"></div>
                </div>
                <div class="mt-2">
                    <b>Notes</b> :
                    <div id="modal_note" style="white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn_print">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
                <button type="button" class="btn btn-warning" id="btn_print_lot">
                    <i class="fas fa-print mr-1"></i>Print Lot
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
