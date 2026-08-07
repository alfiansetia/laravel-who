<div class="modal fade modal-modern" id="modal_compare" data-backdrop="static" tabindex="-1"
    aria-labelledby="modal_compareLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="modal_compareLabel">
                    <i class="fas fa-balance-scale mr-2"></i>Perbandingan Data
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="compare-group">
                            <h6><i class="fas fa-table mr-1 text-warning"></i>PLTB (Spreadsheet)</h6>
                            <table class="compare-table">
                                <tr><td>P</td><td><span id="pltbb_p"></span></td></tr>
                                <tr><td>L</td><td><span id="pltbb_l"></span></td></tr>
                                <tr><td>T</td><td><span id="pltbb_t"></span></td></tr>
                                <tr><td>B</td><td><span id="pltbb_b"></span></td></tr>
                                <tr><td>Note</td><td><span id="pltbb_note"></span></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="compare-group">
                            <h6><i class="fas fa-database mr-1 text-primary"></i>Product (Database)</h6>
                            <table class="compare-table">
                                <tr><td>P</td><td><span id="product_p"></span></td></tr>
                                <tr><td>L</td><td><span id="product_l"></span></td></tr>
                                <tr><td>T</td><td><span id="product_t"></span></td></tr>
                                <tr><td>B</td><td><span id="product_b"></span></td></tr>
                                <tr><td>Note</td><td><span id="product_note"></span></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
