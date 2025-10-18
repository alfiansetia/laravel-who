@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('problem.update', $data->id) }}" id="form">
            <div class="card card-primary mt-3">
                <div class="card-header">
                    {{-- <h3 class="card-title">{{ $title }} </h3> --}}
                </div>
                @csrf
                @method('PUT')
                <div class="card-body">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" placeholder="Date"
                                value="{{ $data->date }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="number">Number</label>
                            <input type="text" name="number" id="number" class="form-control" placeholder="Number"
                                value="{{ $data->number }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control">
                                <option @selected($data->type == 'dus') value="dus">Dus</option>
                                <option @selected($data->type == 'unit') value="unit">Unit</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stock">Stock</label>
                            <select name="stock" id="stock" class="form-control">
                                <option @selected($data->stock == 'stock') value="stock">Stock</option>
                                <option @selected($data->stock == 'import') value="import">Import</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ri_po">RI/PO</label>
                            <input type="text" name="ri_po" id="ri_po" class="form-control" placeholder="RI/PO"
                                value="{{ $data->ri_po }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_on">Email On</label>
                            <input type="date" name="email_on" id="email_on" class="form-control" placeholder="Email On"
                                value="{{ $data->email_on }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option @selected($data->status == 'done') value="done">Done</option>
                                <option @selected($data->status == 'done') value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pic">PIC</label>
                            <input type="text" name="pic" id="pic" class="form-control" placeholder="PIC"
                                value="{{ $data->pic }}" required>
                        </div>
                    </div>
                </div>
                <table class="table" id="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Desc</th>
                            <th>Qty</th>
                            <th>Lot</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="card-footer">
                <a href="{{ route('alamats.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="button" id="add" class="btn btn-info">Tambah Product</button>
                <button type="submit" id="btn_simpan" class="btn btn-primary">Print</button>
                <button type="button" id="btn_sync" class="btn btn-danger">Sync Product</button>
                <button type="button" id="btn_duplicate" class="btn btn-warning">Duplicate</button>
            </div>
        </form>
    </div>
    @include('alamat.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var id = '';
        var data = [];
        $(document).ready(function() {

            var table = $('#table').DataTable({
                ajax: {
                    url: "{{ route('api.alamats.show', $data->id) }}",
                    dataSrc: function(result) {
                        return result.data.detail
                    }
                },
                searching: false,
                info: false,
                lengthChange: false,
                paging: false,
                rowId: 'id',
                columns: [{
                    data: 'id',
                    render: function(data, type, row, meta) {
                        if (type == 'display') {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        } else {
                            return data
                        }
                    }
                }, {
                    data: "product_id",
                    render: function(data, type, row, meta) {
                        if (type == 'display') {
                            return row.product.code + ' ' + (row.product.name || '')
                        } else {
                            return data
                        }
                    }
                }, {
                    data: "desc",
                }, {
                    data: "qty",
                }, {
                    data: "lot",
                }, {
                    data: "id",
                    className: "text-center",
                    render: function(data, type, row, meta) {
                        return `<button type="button" class="btn btn-sm btn-warning mr-1 edit">Edit</button><button type="button" class="btn btn-sm btn-primary hapus">Hapus</button>`
                    }
                }]
            })

            $('#add').click(function() {
                $('#product_modal').modal('show')
            })


            $('#btn_modal_save').click(function() {
                var selectedData = $('#select_product').select2('data');
                var selectedText = selectedData[0].text;
                if (selectedData[0].id == '') {
                    alert('Imput product')
                    return
                }
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.detail_alamat.store') }}",
                    data: {
                        alamat: "{{ $data->id }}",
                        product: selectedData[0].id,
                        qty: $('#qty_prod').val(),
                        lot: $('#lot_prod').val(),
                        desc: $('#desc_prod').val(),
                    }
                }).done(function(result) {
                    $('#product_modal').modal('hide')
                    table.ajax.reload()
                }).fail(function(xhr) {
                    alert('error!')
                })
            })

            $('#btn_modal_save_edit').click(function() {
                let url = $('#form_edit').attr('action')
                $.ajax({
                    type: 'PUT',
                    url: url,
                    data: {
                        qty: $('#qty_prod_edit').val(),
                        lot: $('#lot_prod_edit').val(),
                        desc: $('#desc_prod_edit').val(),
                    }
                }).done(function(result) {
                    table.ajax.reload()
                    $('#edit_modal').modal('hide')
                }).fail(function(xhr) {
                    alert('error!')
                })
            })

            $('#table tbody').on('click', '.hapus', function() {
                var row = table.row($(this).closest('tr'));
                id = row.id();
                // row.remove().draw(true);
                let url = "{{ url('api/detail_alamat') }}/" + id
                $.ajax({
                    type: 'DELETE',
                    url: url,
                }).done(function(result) {
                    table.ajax.reload()
                    $('#edit_modal').modal('hide')
                }).fail(function(xhr) {
                    alert('error!')
                })

            });

            $('#table tbody').on('click', '.edit', function() {
                var row = table.row($(this).closest('tr'));
                id = row.id();
                let data = row.data()
                $('#qty_prod_edit').val(data.qty)
                $('#lot_prod_edit').val(data.lot)
                $('#desc_prod_edit').val(data.desc)
                $('#form_edit').attr('action', "{{ url('api/detail_alamat/') }}/" + id)
                $('#edit_modal').modal('show')
            });

        });
    </script>
@endpush
