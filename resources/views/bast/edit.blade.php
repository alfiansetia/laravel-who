@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-3">
        <form method="POST" action="{{ route('api.basts.update', $data->id) }}" id="form">
            @csrf
            @method('PUT')
            <div class="card card-sm mt-2">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control" id="input_do" placeholder="CARI No DO..."
                                    value="{{ $data->do ?? 'CENT/OUT/' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="btn_get_do">
                                        <i class="fas fa-search mr-1"></i>GET DO
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <select name="" id="select_do" class="form-control select2" style="width: 100%">
                                <option value="">--- Pilih Hasil Pencarian ---</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-user-tie mr-1"></i> KEPADA</label>
                            <textarea name="name" id="name" class="form-control" placeholder="Nama Penerima" rows="4" required>{{ $data->name }}</textarea>
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-map-marked-alt mr-1"></i> ALAMAT LENGKAP</label>
                            <textarea name="address" id="address" class="form-control" placeholder="Alamat Pengiriman" rows="4" required>{{ $data->address }}</textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-city mr-1"></i> KOTA</label>
                            <input type="text" name="city" id="city" class="form-control" placeholder="Kota"
                                value="{{ $data->city }}">
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-file-invoice mr-1"></i> NO SURAT JALAN / DO</label>
                            <input type="text" name="do" id="do" class="form-control font-weight-bold text-primary" placeholder="Nomor DO"
                                value="{{ $data->do }}">
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light text-center">
                    <button type="submit" id="btn_simpan" class="btn btn-primary px-3 mr-1">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <button type="button" id="add" class="btn btn-info px-3 mr-1">
                        <i class="fas fa-plus mr-1"></i> Tambah Item
                    </button>
                    <button type="button" id="btn_sync" class="btn btn-danger px-3 mr-1">
                        <i class="fas fa-sync mr-1"></i> Sync Odoo
                    </button>

                    <div class="btn-group mr-1" role="group">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('api.basts.download', $data->id) }}?type=tanda_terima"
                                target="_blank"><i class="fas fa-file-alt mr-2 text-info"></i> Tanda Terima</a>
                            <a class="dropdown-item" href="{{ route('api.basts.download', $data->id) }}?type=training"
                                target="_blank"><i class="fas fa-vial mr-2 text-warning"></i> Daftar Training</a>
                            <a class="dropdown-item" href="{{ route('api.basts.download', $data->id) }}?type=bast"
                                target="_blank"><i class="fas fa-file-contract mr-2 text-success"></i> BAST</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('api.basts.download_zip', $data->id) }}"
                                target="_blank"><i class="fas fa-file-archive mr-2 text-danger"></i> Format ZIP</a>
                        </div>
                    </div>

                    <a href="{{ route('basts.index') }}" class="btn btn-outline-secondary px-3 mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="button" onclick="window.close()" class="btn btn-dark px-3 mt-1 mt-md-0">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </form>

        <div class="card card-sm mt-3 overflow-hidden">
            <div class="card-header bg-white py-2">
                <h6 class="font-weight-bold mb-0 text-secondary"><i class="fas fa-box mr-2"></i>ITEM BARANG</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" id="table" style="width: 100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-3">#</th>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Satuan</th>
                                <th>Lot / Serial Number</th>
                                <th class="text-center pr-3">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('bast.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const URL_INDEX = "{{ route('basts.index') }}";
        const URL_INDEX_API = "{{ route('api.basts.index') }}";
        const CURRENT_ID = "{{ $data->id }}";
        var id = 0;

        const URL_INDEX_DETAIL_API = "{{ route('api.detail_basts.index') }}";

        var data = [];
        $(document).ready(function() {

            $('#btn_get_do').click(function() {
                let param = $('#input_do').val()
                $.get("{{ route('api.do.index') }}?search=" + param).done(function(res) {
                    $('#select_do').empty()
                    $('#select_do').append('<option value="">Pilih</option>');
                    let resData = res.data
                    for (let i = 0; i < resData.length; i++) {
                        let name = resData[i].name
                        if (resData[i].group_id != false) {
                            name += ' (' + resData[i].group_id[1] + ')'
                        }
                        if (resData[i].partner_id != false) {
                            name += ' ' + resData[i].partner_id[1]
                        }
                        let option = new Option(name, resData[i].id,
                            true, true);
                        $('#select_do').append(option);
                    }
                    $('#select_do').val('')
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
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
                    show_message(xhr.responseJSON.message || 'Error!')
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
                    url: URL_INDEX_DETAIL_API,
                    data: function(dt) {
                        dt['bast_id'] = CURRENT_ID;
                    },
                },
                searching: false,
                info: false,
                lengthChange: false,
                paging: false,
                rowId: 'id',
                columns: [{
                    data: 'order',
                    className: 'text-left',
                    render: function(data, type, row, meta) {
                        if (type == 'display') {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        } else {
                            return data
                        }
                    }
                }, {
                    data: "product_id",
                    className: 'text-left',
                    render: function(data, type, row, meta) {
                        if (type == 'display') {
                            return `[${row.product.code}] ${row.product.name}`;
                        } else {
                            return data
                        }
                    }
                }, {
                    data: "qty",
                    className: 'text-center',
                }, {
                    data: "satuan",
                    className: 'text-center',
                }, {
                    data: "lot",
                    className: 'text-left',
                }, {
                    data: "id",
                    className: "text-center",
                    render: function(data, type, row, meta) {
                        var totalRows = meta.settings.aoData.length;
                        var isFirst = meta.row === 0;
                        var isLast = meta.row === totalRows - 1;
                        var upDisabled = isFirst ? 'disabled' : '';
                        var downDisabled = isLast ? 'disabled' : '';
                        return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-sm btn-secondary order-up" title="Naikkan" ${upDisabled}><i class="fas fa-arrow-up"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary order-down" title="Turunkan" ${downDisabled}><i class="fas fa-arrow-down"></i></button>
                                <button type="button" class="btn btn-sm btn-primary edit"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger hapus"><i class="fas fa-trash"></i></button>
                            </div>
                            `
                    }
                }]
            })

            $('#add').click(function() {
                $('#product_modal').modal('show')
            })

            $('#product_modal').on('shown.bs.modal', function() {
                $('#qty_prod').focus()
            });

            $('#edit_modal').on('shown.bs.modal', function() {
                $('#qty_prod_edit').focus()
            });


            $('#form_add').submit(function(e) {
                e.preventDefault()
                var selectedData = $('#select_product').select2('data');
                var selectedText = selectedData[0].text;
                if (selectedData[0].id == '') {
                    show_message('Select product!')
                    return
                }
                $.ajax({
                    type: 'POST',
                    url: URL_INDEX_DETAIL_API,
                    data: {
                        bast: CURRENT_ID,
                        product: selectedData[0].id,
                        qty: $('#qty_prod').val(),
                        lot: $('#lot_prod').val(),
                        satuan: $('#satuan').val(),
                    }
                }).done(function(result) {
                    $('#product_modal').modal('hide')
                    table.ajax.reload()
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                })
            })

            $('#form_edit').submit(function(e) {
                e.preventDefault()
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
                    show_message(xhr.responseJSON.message || 'Error!')
                })
            })

            $('#table tbody').on('click', '.hapus', function() {
                let row = table.row($(this).closest('tr'));
                id = row.id();
                $.ajax({
                    type: 'DELETE',
                    url: `${URL_INDEX_DETAIL_API}/${id}`,
                }).done(function(result) {
                    table.ajax.reload()
                    $('#edit_modal').modal('hide')
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                })

            });

            $('#table tbody').on('click', '.edit', function() {
                var row = table.row($(this).closest('tr'));
                id = row.id();
                let data = row.data()
                $('#edit_satuan').val(data.satuan).trigger('change')
                $('#qty_prod_edit').val(data.qty)
                $('#lot_prod_edit').val(data.lot)
                $('#form_edit').attr('action', `${URL_INDEX_DETAIL_API}/${id}`)
                $('#edit_modal').modal('show')
            });

            $('#table tbody').on('click', '.order-up', function() {
                var row = table.row($(this).closest('tr'));
                var itemId = row.id();
                $.ajax({
                    type: 'POST',
                    url: `${URL_INDEX_DETAIL_API}/${itemId}/order`,
                    data: {
                        type: 'up'
                    }
                }).done(function(result) {
                    table.ajax.reload(null, false);
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            $('#table tbody').on('click', '.order-down', function() {
                var row = table.row($(this).closest('tr'));
                var itemId = row.id();
                $.ajax({
                    type: 'POST',
                    url: `${URL_INDEX_DETAIL_API}/${itemId}/order`,
                    data: {
                        type: 'down'
                    }
                }).done(function(result) {
                    table.ajax.reload(null, false);
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
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
                    url: `${URL_INDEX_API}/${CURRENT_ID}`,
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {},
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

            $('#btn_sync').click(function() {
                $.ajax({
                    type: 'GET',
                    url: `${URL_INDEX_API}/${CURRENT_ID}/sync`,
                }).done(function(result) {
                    table.ajax.reload()
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                })
            })

        });
    </script>
@endpush
