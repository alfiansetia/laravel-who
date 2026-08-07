<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title font-weight-bold" id="modalTitle">
                    <i class="fas fa-info-circle mr-2"></i>Detail SO
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover" id="tableProduct" style="width:100%">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Desc</th>
                            <th>Origin</th>
                            <th>Price</th>
                            <th style="width: 30px" class="text-center">QTY Order</th>
                            <th style="width: 30px" class="text-center">QTY Delivered</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <hr class="my-2">
                <div class="mt-2">
                    <b>Notes</b> :
                    <div class="so-notes" id="modalNote"></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-success" id="btnPrint">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
