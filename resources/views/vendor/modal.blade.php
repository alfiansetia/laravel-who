<div class="modal fade" id="modal_add" data-backdrop="static" data-keyboard="false" aria-labelledby="staticBackdropLabelAdd"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabelAdd">Add Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form_add" action="">
                    @csrf
                    <div class="form-group col-12">
                        <label for="name">VENDOR NAME</label>
                        <input name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group col-12">
                        <label for="desc">VENDOR DESC</label>
                        <div class="input-group">
                            <textarea name="desc" id="desc" class="form-control" maxlength="200"></textarea>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
                <button id="btn_add" type="button" class="btn btn-primary">
                    <i class="fab fa-telegram-plane mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>
