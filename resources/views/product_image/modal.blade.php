<div class="modal fade" id="modal_add" data-backdrop="static" data-keyboard="false" aria-labelledby="staticBackdropLabelAdd"
    aria-hidden="true">
    <form id="form_add" action="">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabelAdd">Add Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="form-group col-12">
                        <label for="product_id">Product</label>
                        <div class="input-group">
                            <select name="product_id" id="product_id" class="custom-select select2" style="width: 100%"
                                required>
                                <option value="">Select Product</option>
                                @foreach ($products as $item)
                                    <option data-id="{{ $item->id }}" data-code="{{ $item->code }}"
                                        data-name="{{ $item->name }}" value="{{ $item->id }}">
                                        [{{ $item->code }}] {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-12">
                        <label>Upload Gambar Produk</label>
                        <input type="file" id="images" name="images[]" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
