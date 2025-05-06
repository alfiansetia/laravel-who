@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .input-group>.select2-container--bootstrap {
            width: auto;
            flex: 1 1 auto;
        }

        .input-group>.select2-container--bootstrap .select2-selection--single {
            height: 100%;
            line-height: inherit;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('qc.store') }}" id="form">
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }} <button class="btn btn-primary" type="button"
                            data-toggle="collapse" data-target="#collapse_form" aria-expanded="false"
                            aria-controls="collapse_form">
                            Show
                        </button></h3>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <div class="input-group">
                                <select name="" id="select_product" class="custom-select select2" style="width: 75%">
                                    <option value="">Terlampir</option>
                                    @foreach ($products as $item)
                                        <option data-id="{{ $item->id }}" data-code="{{ $item->code }}"
                                            data-name="{{ $item->name }}" value="{{ $item->id }}">
                                            {{ $item->code }} {{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="btn_get_product">PILIH</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="form-group row">
                                <label for="pic" class="col-sm-2 col-form-label">PIC</label>
                                <div class="col-sm-10">
                                    <select name="pic" id="pic" class="form-control">
                                        <option value="Karim A S">Karim</option>
                                        <option value="Sofyan S">Sofyan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row collapse" id="collapse_form">
                        <div class="col-6">
                            <div class="form-group row">
                                <label for="no" class="col-sm-2 col-form-label">NO</label>
                                <div class="col-sm-10">
                                    <input name="no" type="number" class="form-control" id="no" value="1"
                                        min="1" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tgl" class="col-sm-2 col-form-label">Tgl</label>
                                <div class="col-sm-10">
                                    <input name="tgl" type="date" class="form-control" id="tgl"
                                        value="{{ date('Y-m-d', strtotime('-1 day')) }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="nama_alat" class="col-sm-2 col-form-label">Nama Alat</label>
                                <div class="col-sm-10">
                                    <input name="name" type="text" class="form-control" id="nama_alat" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="merk" class="col-sm-2 col-form-label">Merk</label>
                                <div class="col-sm-10">
                                    <input name="merk" type="text" class="form-control" id="merk" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tipe" class="col-sm-2 col-form-label">Tipe</label>
                                <div class="col-sm-10">
                                    <input name="type" type="text" class="form-control" id="tipe" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group row">
                                <label for="sn_lot" class="col-sm-2 col-form-label">SN/Lot</label>
                                <div class="col-sm-10">
                                    <input name="sn_lot" type="text" class="form-control" id="sn_lot"
                                        value="Tanpa Lot/Sn">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="qty" class="col-sm-2 col-form-label">QTY</label>
                                <div class="col-sm-10">
                                    <input name="qty" type="text" class="form-control" id="qty"
                                        value="1 Unit" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="jenis" class="col-sm-2 col-form-label">Jenis</label>
                                <div class="col-sm-10">
                                    <select name="jenis" id="jenis" class="form-control">
                                        <option value="QC Import">QC Import</option>
                                        <option value="QC Ulang">QC Ulang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="qc_sebelumnya" class="col-sm-2 col-form-label">Qc Sblmny</label>
                                <div class="col-sm-10">
                                    <input name="qc_sebelumnya" type="text" class="form-control" id="qc_sebelumnya"
                                        value="2025" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="jenis_qc" class="col-sm-2 col-form-label">Jenis QC</label>
                                <div class="col-sm-10">
                                    <select name="jenis_qc" id="jenis_qc" class="form-control">
                                        <option value="Uji Fungsi & Check kelengkapan alat">Uji Fungsi</option>
                                        <option value="Cek Fisik">Fisik</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <h3>
                            FISIK
                            <button id="btn_reset_fisik" type="button" class="btn btn-sm btn-warning"> Reset</button>
                        </h3>
                        <div class="col-12" id="fisik">
                        </div>
                        <h3>
                            REAGEN
                            <button id="btn_reset_reagen" type="button" class="btn btn-sm btn-warning"> Reset</button>
                        </h3>
                        <div class="col-12" id="reagen">
                        </div>
                        <h3>
                            KELENGKAPAN <button type="button" class="btn btn-sm btn-info"
                                onclick="generate_form_kelengkapan('', true)">+ add</button>
                            <button id="btn_get_pl" type="button" class="btn btn-sm btn-info">+ Get PL</button>
                            <button id="btn_reset_pl" type="button" class="btn btn-sm btn-warning"> Reset</button>
                        </h3>
                        <div class="col-12" id="kelengkapan">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('alamat.index') }}" class="btn btn-secondary">Kembali</a>
                <a href="{{ route('qc.create') }}" class="btn btn-warning">Refresh</a>
                <button type="button" id="btn_get_table" class="btn btn-info">Buat Tabel</button>
                <button type="submit" id="btn_simpan" class="btn btn-primary">Simpan</button>
            </div>
        </form>

        @include('qc.rekap')
        @include('qc.lampiran')
        @include('qc.table')

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
        var FISIK = 0;
        var REAGEN = 0;
        var KEL = 0;

        reset_form_fisik()
        reset_form_reagen()
        reset_kelengkapan()

        function reset_form_fisik() {
            FISIK = 0
            $('#fisik').html('')
            generate_form_fisik('Kondisi Fisik')
            generate_form_fisik('Pembungkus alat dalam kardus atau peti')
            generate_form_fisik('Pengaman alat dalam kardus atau peti')
            generate_form_fisik('Kondisi kardus atau peti')
            generate_form_fisik('Penempelan penandaan AKL dialat')
            generate_form_fisik('Penempelan nomor izin edar pada dus')
        }

        function reset_form_reagen() {
            REAGEN = 0
            $('#reagen').html('')
            generate_form_reagen('Pemeriksaan keakuratan')
            generate_form_reagen('Pemeriksaan suhu')
            generate_form_reagen('Panel saklar ON/OFF')
            generate_form_reagen('Sistem kerja alat')
        }

        function reset_kelengkapan() {
            KEL = 0
            $('#kelengkapan').html('')
            generate_form_kelengkapan('Buku Manual Bahasa Indonesia')
            generate_form_kelengkapan('Kabel Power')
            generate_form_kelengkapan('SOP')
        }


        function generate_form_fisik(text) {
            number = FISIK
            FISIK = FISIK + 1
            let html = `<div class="form-group row">
                                <div class="col-sm-4">
                                    <input type="text" name="fisik[${number}]" class="form-control" value="${text}"
                                        required>
                                </div>
                                <div class="col-sm-4 text-center">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="fisik_radio[${number}]" id="f${number}1"
                                            class="custom-control-input" value="yes">
                                        <label class="custom-control-label" for="f${number}1">Baik</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="fisik_radio[${number}]" id="f${number}2"
                                            class="custom-control-input" value="no">
                                        <label class="custom-control-label" for="f${number}2">Tidak</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="fisik_radio[${number}]" id="f${number}3"
                                            class="custom-control-input" value="other" checked>
                                        <label class="custom-control-label" for="f${number}3">Kosong</label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" name="fisik_desc[${number}]" class="form-control" placeholder="Keterangan">
                                </div>
                            </div>`
            $('#fisik').append(html)
        }

        function generate_form_reagen(text) {
            number = REAGEN
            REAGEN = REAGEN + 1
            let html = `<div class="form-group row">
                                <div class="col-sm-4">
                                    <input type="text" name="reagen[${number}]" class="form-control" value="${text}"
                                        required>
                                </div>
                                <div class="col-sm-4 text-center">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="reagen_radio[${number}]" id="r${number}1"
                                            class="custom-control-input" value="yes">
                                        <label class="custom-control-label" for="r${number}1">Baik</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="reagen_radio[${number}]" id="r${number}2"
                                            class="custom-control-input" value="no">
                                        <label class="custom-control-label" for="r${number}2">Tidak</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="reagen_radio[${number}]" id="r${number}3"
                                            class="custom-control-input" value="other" checked>
                                        <label class="custom-control-label" for="r${number}3">Kosong</label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" name="reagen_desc[${number}]" class="form-control" placeholder="Keterangan">
                                </div>
                            </div>`
            $('#reagen').append(html)
        }

        function generate_form_kelengkapan(text, checked = false) {
            number = KEL
            KEL = KEL + 1

            let html = `<div class="form-group row" id="kel${number}">
                                <div class="col-sm-4">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="del_kel(kel${number})">-</button>
                                        </div>
                                            <input type="text" name="kelengkapan[${number}]" class="form-control" value="${text}" required>
                                    </div>
                                </div>
                                <div class="col-sm-4 text-center">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="kelengkapan_radio[${number}]" id="k${number}1"
                                            class="custom-control-input" value="yes" ${checked ? 'checked' : ''}>
                                        <label class="custom-control-label" for="k${number}1">Baik</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="kelengkapan_radio[${number}]" id="k${number}2"
                                            class="custom-control-input" value="no">
                                        <label class="custom-control-label" for="k${number}2">Tidak</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="kelengkapan_radio[${number}]" id="k${number}3"
                                            class="custom-control-input" value="other" ${checked ? '' : 'checked'}>
                                        <label class="custom-control-label" for="k${number}3">Kosong</label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" name="kelengkapan_desc[${number}]" class="form-control" placeholder="Keterangan">
                                </div>
                            </div>`
            $('#kelengkapan').append(html)
            console.log(KEL);

        }

        function del_kel(id) {
            $(id).remove()
        }

        function get_prefix(str) {
            let pref = str.includes('.') ? str.split('.')[0] : str;
            let pl = pref.toLowerCase()
            const lookup = {
                'bdf': 'BEDFONT',
                'djo': 'CHATTANOOGA',
                'glb': 'GENERAL LIFE B',
                'hc': 'HISENSE',
                'ibd': 'INBODY',
                'cw': 'CAREWELL',
                'jpd': 'JUMPER',
                'mrid': 'MINDRAY',
                'lfn': 'LIFOTRONIC',
                'pts': 'POLIMER TECHNOLOGY',
                'nox': 'NOXBOX',
                'nx': 'NOXBOX',
                'var': 'VARITEKS',
            };
            return lookup[pref.toLowerCase()] || pref;
        }

        function get_data() {
            let data = $('#select_product').select2('data');
            if (data[0].id == '') {
                $('#nama_alat').val('TERLAMPIR')
                $('#merk').val('TERLAMPIR')
                $('#tipe').val('TERLAMPIR')
                return;
            }
            let sdata = data[0].element.dataset
            $('#nama_alat').val(sdata.name)
            $('#merk').val(get_prefix(sdata.code))
            $('#tipe').val(sdata.code)
        }

        $(document).ready(function() {
            $('#select_product').select2({
                theme: 'bootstrap4',
            }).on('change', function() {
                get_data()
                generate_table()
            });


            $('#btn_get_product').click(function() {
                get_data()
                generate_table()
            })

            $('#btn_get_table').click(function() {
                generate_table()
            })




            function generate_table() {
                $('#t_no').html($('#no').val())
                $('#t_name').html($('#nama_alat').val())
                $('#t_merk').html($('#merk').val())
                $('#t_type').html($('#tipe').val())
                $('#t_sn').html($('#sn_lot').val())
                $('#t_qty').html($('#qty').val())
                $('#t_jenis').html($('#jenis').val())
                $('#t_qc_sbl').html($('#qc_sebelumnya').val())
                for (let i = 0; i < 6; i++) {
                    let state = $(`input[name="fisik_radio[${i}]"]:checked`).val();
                    $(`#t_f${i}_y`).html(state == 'yes' ? 'v' : '')
                    $(`#t_f${i}_n`).html(state == 'no' ? 'v' : '')
                }

                for (let i = 0; i < 4; i++) {
                    let state = $(`input[name="reagen_radio[${i}]"]:checked`).val();
                    $(`#t_r${i}_y`).html(state == 'yes' ? 'v' : '')
                    $(`#t_r${i}_n`).html(state == 'no' ? 'v' : '')
                }

                for (let i = 0; i < 3; i++) {
                    let state = $(`input[name="kelengkapan_radio[${i}]"]:checked`).val();
                    $(`#t_k${i}_y`).html(state == 'yes' ? 'v' : '')
                    $(`#t_k${i}_n`).html(state == 'no' ? 'v' : '')
                }
                generate_rekap()
                generate_lampiran()
            }

            function generate_rekap() {
                let sbl = $('#qc_sebelumnya').val()
                let jenis = $('#jenis').val()
                if (jenis == 'QC Ulang') {
                    jenis = 'Ulang';
                } else {
                    jenis = 'Import';
                }
                $('#r_no').html($('#no').val())
                $('#r_tgl').html($('#tgl').val())
                $('#r_code').html($('#tipe').val())
                $('#r_qty').html($('#qty').val())
                $('#r_sn').html($('#sn_lot').val())
                $('#r_cat').html(`${jenis} (${sbl})`)
                $('#r_y').html('V')
                $('#r_n').html('')
                $('#r_desc').html($('#jenis_qc').val())
            }

            function generate_lampiran() {
                $('#l_code').html('Kode Barang : ' + $('#tipe').val())
                $('#l_name').html('Nama Barang : ' + $('#nama_alat').val())
                $('#l_sn').html($('#sn_lot').val())
                $('#l_y').html('V')
                $('#l_desc').html($('#jenis_qc').val())
            }

            // $('#form').submit(function(e) {
            //     e.preventDefault();
            //     console.log($('#form').serializeArray());
            // })

            $('#btn_get_pl').click(function() {
                console.log('ok');

                let prod = $('#select_product').val();
                if (prod == '' || prod == null) {
                    alert('Product Not Found!')
                    return;
                }

                $.ajax({
                    type: 'GET',
                    url: `{{ route('api.product.index') }}/${prod}`,
                    data: {},
                    beforeSend: function() {},
                    success: function(res) {
                        res.data.pls.forEach(item => {
                            generate_form_kelengkapan(`${item.item} (${item.qty})`,
                                true)
                        });
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message || 'Error!')

                    }
                });
            })

            $('#btn_reset_pl').click(function() {
                reset_kelengkapan()
            })

            $('#btn_reset_fisik').click(function() {
                reset_form_fisik()
            })

            $('#btn_reset_reagen').click(function() {
                reset_form_reagen()
            })


        });
    </script>
@endsection
