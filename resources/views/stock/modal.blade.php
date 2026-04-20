<div class="modal fade" id="modal_lot" data-backdrop="static" tabindex="-1" aria-labelledby="modal_lotLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_lotLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover" id="table_lot" style="width: 100%;cursor: pointer;">
                    <thead>
                        <tr>
                            <th>LOCATION</th>
                            <th>Lot/SN</th>
                            <th>ED</th>
                            <th style="width: 30px">QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="form-group">
                    <label for="">Lot/SN</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary" id="btn_copy_lot">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <textarea name="" id="detail_lot" class="form-control"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Serial Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary" id="btn_copy_sn">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <textarea name="" id="detail_sn" class="form-control"></textarea>
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
