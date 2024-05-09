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
                    <div class="form-group col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                value="CENT/OUT/">
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

            <form method="POST" action="{{ route('alamat.store') }}" id="form">
                @csrf
                @method('PUT')
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
                            <label for="koli">Jumlah Koli</label>
                            <input type="number" name="koli" id="koli" class="form-control" min="1"
                                placeholder="Jumlah Koli" value="1" required>
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
                        <div class="form-group col-md-6">
                            <label for="nilai">Nilai Barang</label>
                            <input type="text" name="nilai" id="nilai" class="form-control"
                                placeholder="Nilai Barang" value="">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_do" value="yes"
                                    id="is_do">
                                <label class="form-check-label" for="is_do">Surat JALAN?</label>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_pk" value="yes"
                                    id="is_pk">
                                <label class="form-check-label" for="is_pk">P KAYU ?</label>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('alamat.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" id="btn_simpan" class="btn btn-primary">Simpan</button>
            </form>
        </div>
        @if (session()->has('message'))
            <script>
                alert("{{ session('message') }}")
            </script>
        @endif

        @push('js')
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        @endpush

        <script>
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
                        if (res.data.length > 0) {
                            let tujuan = ''
                            let ekspedisi = ''
                            let up = ''
                            let name = ''
                            let epur = ''
                            if (res.data[0].partner_id != false) {
                                tujuan = res.data[0].partner_id[1]
                            }
                            if (res.data[0].ekspedisi_id != false) {
                                ekspedisi = res.data[0].ekspedisi_id[1]
                            }
                            if (res.data[0].delivery_manual != false) {
                                up = res.data[0].delivery_manual
                            }
                            if (res.data[0].name != false) {
                                name = res.data[0].name
                            }
                            if (res.data[0].no_aks != false) {
                                epur = res.data[0].no_aks
                            }
                            let alamat = res.data[0].partner_address
                            if (res.data[0].partner_address2 != false) {
                                alamat += '\n' + res.data[0].partner_address2
                            }
                            if (res.data[0].partner_address3 != false) {
                                alamat += '\n' + res.data[0].partner_address3
                            }
                            if (res.data[0].partner_address4 != false) {
                                alamat += '\n' + res.data[0].partner_address4
                            }

                            $('#do').val(name)
                            $('#up').val(up)
                            $('#alamat').val(alamat)
                            $('#tujuan').val(tujuan)
                            $('#ekspedisi').val(ekspedisi)
                            $('#epur').val(epur)

                        }
                    }).fail(function(xhr) {
                        alert('Odoo Error!')
                    });

                });

                $('#form').submit(function(e) {
                    e.preventDefault();
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
                        is_do: $('#is_do').prop('checked') ? 'yes' : 'no',
                        is_pk: $('#is_pk').prop('checked') ? 'yes' : 'no',
                    }
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('api.alamat.store') }}",
                        data: data,
                        beforeSend: function() {},
                        success: function(res) {
                            let id = res.data.id
                            window.open("{{ url('alamat') }}/" + id + '/edit', '_blank')
                        },
                        error: function(xhr, status, error) {
                            alert(xhr.responseJSON.message);
                        }
                    });
                })

            });
        </script>
    @endsection
