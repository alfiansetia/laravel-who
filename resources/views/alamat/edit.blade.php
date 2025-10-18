@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">

        <div class="card card-primary mt-3">
            <div class="card-header">
                <h3 class="card-title">{{ $title }} </h3>
                <div class="row">
                    <div class="form-group col-md-6 mb-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                value="{{ $data->do ?? 'CENT/OUT/' }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btn_get_do">
                                    <i class="fas fa-search mr-1"></i>GET
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6 mb-0">
                        <select name="" id="select_do" class="form-control col-md-6 select2" style="width: 100%">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('api.alamats.update', $data->id) }}" id="form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tujuan">Tujuan</label>
                            <textarea name="tujuan" id="tujuan" class="form-control" placeholder="Tujuan" rows="4" required>{{ $data->tujuan }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="alamat">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" placeholder="Alamat" rows="4" required>{{ $data->alamat }}</textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ekspedisi">Ekspedisi</label>
                            <input type="text" name="ekspedisi" id="ekspedisi" class="form-control"
                                placeholder="Ekspedisi" value="{{ $data->ekspedisi }}">
                        </div>
                        {{-- <div class="form-group col-md-6">
                            <label for="koli">Jumlah Koli</label>
                            <input type="number" name="koli" id="koli" class="form-control" min="1"
                                placeholder="Jumlah Koli" value="{{ $data->koli }}" required>
                        </div> --}}
                        <div class="form-group col-md-6">
                            <label for="koli">Jumlah Koli</label>
                            <div class="input-group mb-3">
                                <input type="number" name="koli" id="koli" class="form-control" min="1"
                                    placeholder="Jumlah Koli" value="{{ $data->koli }}" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input class="mr-1" type="checkbox" name="is_last_koli" value="yes"
                                            id="is_last_koli" @checked($data->is_last_koli == 'yes')> Koli Terakhir
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="up">UP</label>
                            <input type="text" name="up" id="up" class="form-control" placeholder="UP"
                                value="{{ $data->up }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tlp">Tlp</label>
                            <input type="text" name="tlp" id="tlp" class="form-control" placeholder="Tlp"
                                value="{{ $data->tlp }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="do">No DO</label>
                            <input type="text" name="do" id="do" class="form-control" placeholder="No DO"
                                value="{{ $data->do }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="epur">Epurchasing</label>
                            <input type="radio" name="ep" onclick="setEpur('Pembelian Offline')"> PO
                            <input type="radio" name="ep" onclick="setEpur('Pembelian Reguler')"> REG
                            <input type="radio" name="ep" checked onclick="setEpur('')"> NULL
                            <input type="text" name="epur" id="epur" class="form-control"
                                placeholder="Epurchasing" value="{{ $data->epur }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="untuk">Untuk</label>
                            <input type="text" name="untuk" id="untuk" class="form-control"
                                placeholder="Untuk" value="{{ $data->untuk }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="nilai">Nilai Barang</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" name="nilai" id="nilai" class="form-control mask_angka"
                                    placeholder="Nilai Barang" value="{{ str_replace([',', '.'], '', $data->nilai) }}">
                            </div>

                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="note">NOTE</label>
                            <textarea name="note" id="note" class="form-control" placeholder="note" rows="4" maxlength="250">{{ $data->note }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="note">NOTE WH</label>
                            <div class="text-danger font-weight-bold" id="n_t_wh"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="is_do" value="yes"
                                    id="is_do" @checked($data->is_do == 'yes')>
                                <label class="custom-control-label" for="is_do">SURAT JALAN</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="is_pk" value="yes"
                                    id="is_pk" @checked($data->is_pk == 'yes')>
                                <label class="custom-control-label" for="is_pk">PACKING KAYU</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="is_banting" value="yes"
                                    id="is_banting" @checked($data->is_banting == 'yes')>
                                <label class="custom-control-label" for="is_banting">JANGAN DIBANTING</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="is_asuransi" value="yes"
                                    id="is_asuransi" @checked($data->is_asuransi == 'yes')>
                                <label class="custom-control-label" for="is_asuransi">ASURANSI</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('alamats.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="button" id="add" class="btn btn-info">
                        <i class="fas fa-plus mr-1"></i>Tambah Product
                    </button>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fas fa-print mr-1"></i>Simpan & Print
                    </button>
                    <button type="button" id="btn_sync" class="btn btn-danger">
                        <i class="fas fa-sync mr-1"></i>Sync Product
                    </button>
                    <button type="button" id="btn_duplicate" class="btn btn-warning">
                        <i class="fas fa-clone mr-1"></i>Duplicate
                    </button>
                </div>
            </form>
        </div>

        <div class="card card-primary mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table mt-0" id="table" style="width: 100%">
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
            </div>
        </div>



    </div>
    @include('alamat.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"
        integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.alamats.index') }}"
        const URL_INDEX = "{{ route('alamats.index') }}"

        function setEpur(val) {
            $('#epur').val(val)
        }

        $('.mask_angka').inputmask({
            alias: 'numeric',
            groupSeparator: '.',
            autoGroup: true,
            digits: 0,
            rightAlign: false,
            removeMaskOnSubmit: true,
            autoUnmask: true,
            min: 0,
        });

        var id = '';
        var data = [];
        $(document).ready(function() {
            $.get("{{ route('api.product.index') }}").done(function(res) {
                for (let i = 0; i < res.data.length; i++) {
                    let option = new Option(`${res.data[i].code} ${res.data[i].name || ''}`, res.data[i].id,
                        true, true);
                    $('#select_product').append(option);
                    $('#select_product2').append(option);
                }
            });

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
                    let ekspedisi = ''
                    let up = ''
                    let name = ''
                    let epur = ''
                    let note_to_wh = ''
                    if (res.data.partner_id != false) {
                        tujuan = res.data.partner_id[1]
                    }
                    if (res.data.ekspedisi_id != false) {
                        ekspedisi = res.data.ekspedisi_id[1]
                    }
                    if (res.data.delivery_manual != false) {
                        up = res.data.delivery_manual
                    }
                    if (res.data.name != false) {
                        name = res.data.name
                    }
                    if (res.data.no_aks != false) {
                        epur = res.data.no_aks
                    }
                    let alamat = res.data.partner_address
                    if (res.data.partner_address2 != false) {
                        alamat += '\n' + res.data.partner_address2
                    }
                    if (res.data.partner_address3 != false) {
                        alamat += '\n' + res.data.partner_address3
                    }
                    if (res.data.partner_address4 != false) {
                        alamat += '\n' + res.data.partner_address4
                    }

                    if (res.data.note_to_wh != false) {
                        note_to_wh = res.data.note_to_wh
                        note_to_wh = note_to_wh.replace(/\n/g, '<br>');
                    }

                    $('#do').val(name)
                    $('#up').val(up)
                    $('#alamat').val(alamat)
                    $('#tujuan').val(tujuan)
                    $('#ekspedisi').val(ekspedisi)
                    $('#epur').val(epur)
                    $('#n_t_wh').text(note_to_wh)
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
                order: [
                    [0, 'asc'],
                ],
                columns: [{
                    data: 'order',
                    render: function(data, type, row, meta) {
                        return parseInt(data) + 1
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
                        let isFirst = meta.row === 0;
                        let isLast = meta.row === meta.settings.json.data.detail.length - 1;

                        return `
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-sm btn-secondary up" ${isFirst ? 'disabled' : ''}>
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary down" ${isLast ? 'disabled' : ''}>
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning edit"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-danger hapus"><i class="fas fa-trash"></i></button>
                        </div>
                        `
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

            function order_item(id, type) {
                // console.log(id, type);
                let url = "{{ url('api/detail_alamat') }}/" + id + '/order'
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        type: type
                    }
                }).done(function(result) {
                    table.ajax.reload()
                }).fail(function(xhr) {
                    alert('error!')
                })
            }

            $('#table tbody').on('click', '.up', function() {
                var row = table.row($(this).closest('tr'));
                id = row.id();
                let data = row.data()
                order_item(data.id, 'up')

            });

            $('#table tbody').on('click', '.down', function() {
                var row = table.row($(this).closest('tr'));
                id = row.id();
                let data = row.data()
                order_item(data.id, 'down')
            });

            $('#form').submit(function(e) {
                e.preventDefault();
                let dt = table.rows().data().toArray();
                let data = {
                    // detail: dt,
                    tujuan: $('#tujuan').val(),
                    alamat: $('#alamat').val(),
                    ekspedisi: $('#ekspedisi').val(),
                    koli: $('#koli').val(),
                    up: $('#up').val(),
                    tlp: $('#tlp').val(),
                    do: $('#do').val(),
                    epur: $('#epur').val(),
                    untuk: $('#untuk').val(),
                    nilai: $('#nilai').val(),
                    note: $('#note').val(),
                    is_do: $('#is_do').prop('checked') ? 'yes' : 'no',
                    is_pk: $('#is_pk').prop('checked') ? 'yes' : 'no',
                    is_banting: $('#is_banting').prop('checked') ? 'yes' : 'no',
                    is_last_koli: $('#is_last_koli').prop('checked') ? 'yes' : 'no',
                    is_asuransi: $('#is_asuransi').prop('checked') ? 'yes' : 'no',
                }
                $.ajax({
                    type: 'PUT',
                    url: URL_INDEX_API + "/{{ $data->id }}",
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        window.open(URL_INDEX + "/{{ $data->id }}", '_blank')
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message);
                    }
                });
            })

            $('#btn_sync').click(function() {
                let url = URL_INDEX_API + "/{{ $data->id }}/sync"
                $.ajax({
                    type: 'GET',
                    url: url,
                }).done(function(result) {
                    table.ajax.reload()
                }).fail(function(xhr) {
                    alert(xhr.responseJSON.message || 'Error!')
                })
            })

            $('#btn_duplicate').click(function() {
                $.ajax({
                    type: 'POST',
                    url: URL_INDEX_API + "/{{ $data->id }}/duplicate",
                    data: {}
                }).done(function(result) {

                    window.open(URL_INDEX + "/" + result.data.id + '/edit')
                }).fail(function(xhr) {
                    alert('error!')
                })
            })

        });
    </script>
@endpush
