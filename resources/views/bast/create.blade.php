@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <form method="POST" action="{{ route('api.basts.store') }}" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                    value="CENT/OUT/">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="btn_get_do">
                                        <i class="fas fa-search mr-1"></i>GET
                                    </button>
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

                @method('PUT')
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Kepada</label>
                            <textarea name="name" id="name" class="form-control" placeholder="Kepada" rows="4" required></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="address">Alamat</label>
                            <textarea name="address" id="address" class="form-control" placeholder="Alamat" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city">Kota</label>
                            <input type="text" name="city" id="city" class="form-control" placeholder="Kota"
                                value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="do">No DO</label>
                            <input type="text" name="do" id="do" class="form-control" placeholder="No DO"
                                value="">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('basts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
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
