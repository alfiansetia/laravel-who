@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-3">
        <form method="POST" action="{{ route('api.basts.store') }}" id="form">
            @csrf
            <div class="card card-sm mt-2">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control" id="input_do" placeholder="CARI No DO..."
                                    value="CENT/OUT/" autofocus>
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

                @method('PUT')
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-user-tie mr-1"></i> KEPADA</label>
                            <textarea name="name" id="name" class="form-control" placeholder="Nama Penerima" rows="4" required></textarea>
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-map-marked-alt mr-1"></i> ALAMAT LENGKAP</label>
                            <textarea name="address" id="address" class="form-control" placeholder="Alamat Pengiriman" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-city mr-1"></i> KOTA</label>
                            <input type="text" name="city" id="city" class="form-control" placeholder="Kota"
                                value="">
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold ml-1"><i class="fas fa-file-invoice mr-1"></i> NO SURAT JALAN / DO</label>
                            <input type="text" name="do" id="do" class="form-control font-weight-bold text-primary" placeholder="Nomor DO"
                                value="">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-center">
                    <a href="{{ route('basts.index') }}" class="btn btn-secondary px-4">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" id="btn_simpan" class="btn btn-primary px-4">
                        <i class="fab fa-telegram-plane mr-1 text-white"></i> Simpan & Lanjut ke Item
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection


@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const URL_INDEX = "{{ route('basts.index') }}";
        const URL_INDEX_API = "{{ route('api.basts.index') }}";
        var id = 0;

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

            $('#form').submit(function(e) {
                e.preventDefault();
                let data = {
                    do: $('#do').val(),
                    name: $('#name').val(),
                    address: $('#address').val(),
                    city: $('#city').val(),
                }
                $.ajax({
                    type: 'POST',
                    url: URL_INDEX_API,
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        window.open(`${URL_INDEX}/${res.data.id}/edit`, '_blank')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

        });
    </script>
@endpush
