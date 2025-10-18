@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">

        <div class="card card-primary mt-3">
            <div class="card-header">
                {{-- <h3 class="card-title">{{ $title }} </h3> --}}
                <div class="row">
                    <div class="form-group col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                value="{{ $data->do ?? 'CENT/OUT/' }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btn_get_do">GET</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <select name="" id="select_do" class="form-control col-md-6 select2" style="width: 100%">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('bast.update', $data->id) }}" id="form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Kepada</label>
                            <textarea name="name" id="name" class="form-control" placeholder="Kepada" rows="4" required>{{ $data->name }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="address">Alamat</label>
                            <textarea name="address" id="address" class="form-control" placeholder="Alamat" rows="4" required>{{ $data->address }}</textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city">Kota</label>
                            <input type="text" name="city" id="city" class="form-control" placeholder="Kota"
                                value="{{ $data->city }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="do">No DO</label>
                            <input type="text" name="do" id="do" class="form-control" placeholder="No DO"
                                value="{{ $data->do }}">
                        </div>
                    </div>
                </div>
                <table class="table" id="table" style="width: 100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Lot</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
        </div>

        <div class="card-footer">
            <a href="{{ route('bast.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="button" id="add" class="btn btn-info">Tambah Product</button>
            <button type="submit" id="btn_simpan" class="btn btn-primary">SAVE</button>
            <button type="button" id="btn_sync" class="btn btn-danger">Sync Product</button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                    aria-expanded="false">
                    Download
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('bast.show', $data->id) }}?type=tanda_terima"
                        target="_blank">Tanda Terima</a>
                    <a class="dropdown-item" href="{{ route('bast.show', $data->id) }}?type=training" target="_blank">Daftar
                        Training</a>
                    <a class="dropdown-item" href="{{ route('bast.show', $data->id) }}?type=bast" target="_blank">BAST</a>
                </div>
            </div>
        </div>
        </form>
    </div>

    @include('bast.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var id = 0;
        var data = [];
        $(document).ready(function() {

            $('#btn_get_do').click(function() {
                let param = $('#input_do').val()
                $.get("{{ route('api.do.index') }}?param=" + param).done(function(res) {
                    $('#select_do').empty()
                    $('#select_do').append('<option value="">Pilih</option>');
                    for (let i = 0; i < res.data.records.length; i++) {
                        let name = res.data.records[i].name
                        if (res.data.records[i].group_id != false) {
                            name += ' (' + res.data.records[i].group_id[1] + ')'
                        }
                        if (res.data.records[i].partner_id != false) {
                            name += ' ' + res.data.records[i].partner_id[1]
                        }
                        let option = new Option(name, res.data.records[i].id,
                            true, true);
                        $('#select_do').append(option);
                    }
                    $('#select_do').val('')
                }).fail(function(xhr) {
                    alert('Odoo Error!')
                });
            })
            $('#select_do').select2({
                theme: 'bootstrap4',
            }).on('change', function() {
                let data = $(this).select2('data');
                if (data[0].id == '') {
                    return;
                }
                let sid = data[0].id
                $.get("{{ url('api/do') }}/" + sid).done(function(res) {
                    let tujuan = ''
                    let no_do = ''
                    if (res.data.partner_id != false) {
                        tujuan = res.data.partner_id[1]
                    }
                    if (res.data.name != false) {
                        no_do = res.data.name
                    }
                    let alamat = res.data.partner_address
                    if (res.data.partner_address2 != false) {
                        alamat += ' ' + res.data.partner_address2
                    }
                    if (res.data.partner_address3 != false) {
                        alamat += ' ' + res.data.partner_address3
                    }
                    if (res.data.partner_address4 != false) {
                        alamat += ' ' + res.data.partner_address4
                    }

                    $('#name').val(tujuan)
                    $('#address').val(alamat)
                    $('#city').val(res.data.partner_address3 || '')
                    $('#do').val(no_do)
                }).fail(function(xhr) {
                    alert('Odoo Error!')
                });

            });

            $('#select_product').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#product_modal"),
            })

            $('#select_product2').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#edit_modal"),
            })

            var table = $('#table').DataTable({
                ajax: {
                    url: "{{ route('api.bast.show', $data->id) }}",
                    dataSrc: function(result) {
                        return result.data.details
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
                            return row.product.code + ' ' + row.product.name
                        } else {
                            return data
                        }
                    }
                }, {
                    data: "qty",
                }, {
                    data: "satuan",
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
                    alert('Input product')
                    return
                }
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.detail_bast.store') }}",
                    data: {
                        bast: "{{ $data->id }}",
                        product: selectedData[0].id,
                        qty: $('#qty_prod').val(),
                        lot: $('#lot_prod').val(),
                        satuan: $('#satuan').val(),
                    }
                }).done(function(result) {
                    $('#product_modal').modal('hide')
                    table.ajax.reload()
                }).fail(function(xhr) {
                    alert('error!')
                })
            })

            $('#form_edit').submit(function(e) {
                e.preventDefault()
            })

            $('#btn_modal_save_edit').click(function() {
                let url = $('#form_edit').attr('action')
                $.ajax({
                    type: 'PUT',
                    url: url,
                    data: {
                        qty: $('#qty_prod_edit').val(),
                        lot: $('#lot_prod_edit').val(),
                        satuan: $('#edit_satuan').val(),
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
                let url = "{{ url('api/detail_bast') }}/" + id
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
                $('#edit_satuan').val(data.satuan).trigger('change')
                $('#qty_prod_edit').val(data.qty)
                $('#lot_prod_edit').val(data.lot)
                $('#form_edit').attr('action', "{{ url('api/detail_bast/') }}/" + id)
                $('#edit_modal').modal('show')
            });

            $('#form').submit(function(e) {
                e.preventDefault();
                let dt = table.rows().data().toArray();
                let data = {
                    do: $('#do').val(),
                    name: $('#name').val(),
                    address: $('#address').val(),
                    city: $('#city').val(),
                }
                $.ajax({
                    type: 'PUT',
                    url: "{{ route('api.bast.update', $data->id) }}",
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        // window.open("{{ route('bast.show', $data->id) }}", '_blank')
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message);
                    }
                });
            })

            $('#btn_sync').click(function() {
                let url = "{{ route('api.bast.sync', $data->id) }}"
                $.ajax({
                    type: 'GET',
                    url: url,
                }).done(function(result) {
                    table.ajax.reload()
                }).fail(function(xhr) {
                    alert(xhr.responseJSON.message || 'Error!')
                })
            })

        });
    </script>
@endpush
