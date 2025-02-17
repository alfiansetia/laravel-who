@php
    $satuan = ['pcs', 'dus', 'pack', 'kotak', 'lusin', 'pad', 'rim', 'roll', 'tube', 'box', 'buah', 'buku'];
@endphp
<form action="" id="form_edit">
    <div class="modal fade" id="modal_edit" data-backdrop="static" data-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_code">Code</label>
                        <input name="code" type="text" class="form-control" id="edit_code" required>
                        <small class="form-text text-danger code" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Name</label>
                        <input name="name" type="text" class="form-control" id="edit_name" required>
                        <small class="form-text text-danger name" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="edit_satuan">Satuan</label>
                        <select name="satuan" id="edit_satuan" class="form-control">
                            @foreach ($satuan as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-danger satuan" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="edit_desc">Desc</label>
                        <textarea name="desc" class="form-control" id="edit_desc"></textarea>
                        <small class="form-text text-danger desc" style="display: :none"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">SAVE</button>
                </div>
            </div>
        </div>
    </div>
</form>


<form action="{{ route('api.atk.store') }}" id="form_add">
    <div class="modal fade" id="modal_add" data-backdrop="static" data-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Add</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_code">Code</label>
                        <input name="code" type="text" class="form-control" id="add_code" required>
                        <small class="form-text text-danger code" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="add_name">Name</label>
                        <input name="name" type="text" class="form-control" id="add_name" required>
                        <small class="form-text text-danger name" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="add_satuan">Satuan</label>
                        <select name="satuan" id="add_satuan" class="form-control">
                            @foreach ($satuan as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-danger satuan" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="add_desc">Desc</label>
                        <textarea name="desc" class="form-control" id="add_desc"></textarea>
                        <small class="form-text text-danger desc" style="display: :none"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">SAVE</button>
                </div>
            </div>
        </div>
    </div>
</form>


<form action="{{ route('api.atktrx.store') }}" id="form_trx">
    <input type="hidden" id="trx_atk_id">
    <div class="modal fade" id="modal_trx" data-backdrop="static" data-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">TRX <span id="trx_title"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="trx_date">Tgl</label>
                        <input name="date" type="text" class="form-control" id="trx_date" required>
                        <small class="form-text text-danger date" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="trx_type">Type</label>
                        <select name="type" id="trx_type" class="form-control">
                            <option value="in">IN</option>
                            <option value="out">OUT</option>
                        </select>
                        <small class="form-text text-danger satuan" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="trx_pic">PIC</label>
                        <input name="pic" type="text" class="form-control" id="trx_pic" required>
                        <small class="form-text text-danger pic" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="trx_qty">QTY</label>
                        <input name="qty" type="number" class="form-control" id="trx_qty" required
                            min="1" value="1">
                        <small class="form-text text-danger qty" style="display: :none"></small>
                    </div>
                    <div class="form-group">
                        <label for="trx_desc">Desc</label>
                        <textarea name="desc" class="form-control" id="trx_desc"></textarea>
                        <small class="form-text text-danger desc" style="display: :none"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">SAVE</button>
                </div>
            </div>
        </div>
    </div>
</form>


<div class="modal fade" id="modal_detail" data-backdrop="static" data-keyboard="false"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">View <span id="detail_title"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-sm" style="width: 100%;cursor: pointer;" id="table_detail">
                    <thead>
                        <th>Tgl</th>
                        <th>PIC</th>
                        <th>IN</th>
                        <th>OUT</th>
                        <th>SALDO</th>
                        <th>Desc</th>
                        <th style="width: 30px">#</th>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
