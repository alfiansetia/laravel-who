@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">

        <div class="card card-primary mt-3">
            <div class="card-header">
                <div class="row">
                    <div class="form-group col-md-6 mb-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                value="CENT/OUT/" autofocus>
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

            <form method="POST" action="{{ route('api.alamat_baru.store') }}" id="form">
                @csrf
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tujuan">Tujuan</label>
                            <textarea name="tujuan" id="tujuan" class="form-control" placeholder="Tujuan" rows="4" required></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="alamat">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" placeholder="Alamat" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ekspedisi">Ekspedisi</label>
                            <input type="text" name="ekspedisi" id="ekspedisi" class="form-control"
                                placeholder="Ekspedisi" value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="total_koli">Total Koli (Manual)</label>
                            <input type="number" name="total_koli" id="total_koli" class="form-control"
                                placeholder="Total Koli" value="1" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="up">UP</label>
                            <input type="text" name="up" id="up" class="form-control" placeholder="UP"
                                value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tlp">Tlp</label>
                            <input type="text" name="tlp" id="tlp" class="form-control" placeholder="Tlp"
                                value="">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="do">No DO</label>
                            <input type="text" name="do" id="do" class="form-control" placeholder="No DO"
                                value="" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="epur">Epurchasing</label>
                            <input type="radio" name="ep" onclick="setEpur('Pembelian Offline')"> PO
                            <input type="radio" name="ep" onclick="setEpur('Pembelian Reguler')"> REG
                            <input type="radio" name="ep" checked onclick="setEpur('')"> NULL
                            <input type="text" name="epur" id="epur" class="form-control"
                                placeholder="Epurchasing" value="">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="untuk">Untuk</label>
                            <input type="text" name="untuk" id="untuk" class="form-control"
                                placeholder="Untuk" value="">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="note">NOTE</label>
                            <textarea name="note" id="note" class="form-control" placeholder="note" rows="4" maxlength="250"></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="note">NOTE WH</label>
                            <div class="text-danger font-weight-bold" id="n_t_wh"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('alamat_baru.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.alamat_baru.index') }}"
        const URL_INDEX = "{{ route('alamat_baru.index') }}"

        function setEpur(val) {
            $('#epur').val(val)
        }

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
                        if (up == '-') {
                            up = ''
                        }
                    }
                    if (res.data.name != false) {
                        name = res.data.name
                    }
                    if (res.data.no_aks != false) {
                        epur = res.data.no_aks
                        if (epur == '-') {
                            epur = ''
                        }
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
                    $('#n_t_wh').html(note_to_wh)
                    $('#tlp').val('')
                    $('#untuk').val('')
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                });

            });

            $('#form').submit(function(e) {
                e.preventDefault();
                let data = {
                    tujuan: $('#tujuan').val(),
                    alamat: $('#alamat').val(),
                    ekspedisi: $('#ekspedisi').val(),
                    total_koli: $('#total_koli').val(),
                    up: $('#up').val(),
                    tlp: $('#tlp').val(),
                    do: $('#do').val(),
                    epur: $('#epur').val(),
                    untuk: $('#untuk').val(),
                    note: $('#note').val(),
                }
                $.ajax({
                    type: 'POST',
                    url: URL_INDEX_API,
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        let id = res.data.id
                        window.open(URL_INDEX + "/" + id + '/edit', '_blank')
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

        });
    </script>
@endpush
