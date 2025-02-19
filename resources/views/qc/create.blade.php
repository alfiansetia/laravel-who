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
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }} </h3>
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
                                    <button type="button" class="btn btn-primary" id="btn_get_product"
                                        onclick="get_data()">PILIH</button>
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
                    <div class="row">
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
                        </div>
                        <h3>FISIK</h3>
                        <div class="col-12" id="fisik">
                        </div>
                        <h3>REAGEN</h3>
                        <div class="col-12" id="reagen">
                        </div>
                        <h3>KELENGKAPAN <button type="button" class="btn btn-sm btn-info"
                                onclick="generate_form_kelengkapan('')">+ add</button></h3>
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

        <div class="card card-primary mt-3 mb-3">
            <div class="card-header">
                <h3 class="card-title">Table</h3>
            </div>

            <div class="card-body">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Description</th>
                            <th>Fisik Alat / Reagen</th>
                            <th>Baik</th>
                            <th>Tidak Ada</th>
                            <th>Pemeriksaan Strip / Alat / Reagen</th>
                            <th>Baik</th>
                            <th>Tidak Ada</th>
                            <th>Kelengkapan Alat Sesuai Order</th>
                            <th>Ada</th>
                            <th>Tidak Ada</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Nama Alat</td>
                            <td id="t_name"></td>
                            <td>Kondisi Fisik</td>
                            <td class="text-center" id="t_f0_y"></td>
                            <td class="text-center" id="t_f0_n"></td>
                            <td>Strip ( Keakuratan )</td>
                            <td class="text-center" id="t_r0_y"></td>
                            <td class="text-center" id="t_r0_n"></td>
                            <td>Buku Manual</td>
                            <td class="text-center" id="t_k0_y"></td>
                            <td class="text-center" id="t_k0_n"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Merk</td>
                            <td id="t_merk"></td>
                            <td>Pembungkus Alat</td>
                            <td class="text-center" id="t_f1_y"></td>
                            <td class="text-center" id="t_f1_n"></td>
                            <td>Reagen ( Suhu )</td>
                            <td class="text-center" id="t_r1_y"></td>
                            <td class="text-center" id="t_r1_n"></td>
                            <td>Kabel Power</td>
                            <td class="text-center" id="t_k1_y"></td>
                            <td class="text-center" id="t_k1_n"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Tipe</td>
                            <td id="t_type"></td>
                            <td>Pengaman Alat</td>
                            <td class="text-center" id="t_f2_y"></td>
                            <td class="text-center" id="t_f2_n"></td>
                            <td>Fungsi Alat ( On/Off )</td>
                            <td class="text-center" id="t_r2_y"></td>
                            <td class="text-center" id="t_r2_n"></td>
                            <td>SOP</td>
                            <td class="text-center" id="t_k2_y"></td>
                            <td class="text-center" id="t_k2_n"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Nomor SN / LOT</td>
                            <td id="t_sn"></td>
                            <td>Kondisi Kardus</td>
                            <td class="text-center" id="t_f3_y"></td>
                            <td class="text-center" id="t_f3_n"></td>
                            <td>Kerja Alat</td>
                            <td class="text-center" id="t_r3_y"></td>
                            <td class="text-center" id="t_r3_n"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Qty</td>
                            <td id="t_qty"></td>
                            <td>Akl di Alat</td>
                            <td class="text-center" id="t_f4_y"></td>
                            <td class="text-center" id="t_f4_n"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Jenis QC</td>
                            <td id="t_jenis"></td>
                            <td>Akl di Dus</td>
                            <td class="text-center" id="t_f5_y"></td>
                            <td class="text-center" id="t_f5_n"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>QC Sebelumnya</td>
                            <td id="t_qc_sbl"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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

        generate_form_fisik('Kondisi Fisik')
        generate_form_fisik('Pembungkus alat dalam kardus atau peti')
        generate_form_fisik('Pengaman alat dalam kardus atau peti')
        generate_form_fisik('Kondisi kardus atau peti')
        generate_form_fisik('Penempelan penandaan AKL dialat')
        generate_form_fisik('Penempelan nomor izin edar pada dus')

        generate_form_reagen('Pemeriksaan keakuratan')
        generate_form_reagen('Pemeriksaan suhu')
        generate_form_reagen('Panel saklar ON/OFF')
        generate_form_reagen('Sistem kerja alat')

        generate_form_kelengkapan('Buku Manual Bahasa Indonesia')
        generate_form_kelengkapan('Kabel Power')
        generate_form_kelengkapan('SOP')


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

        function generate_form_kelengkapan(text) {
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
                                            class="custom-control-input" value="yes">
                                        <label class="custom-control-label" for="k${number}1">Baik</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="kelengkapan_radio[${number}]" id="k${number}2"
                                            class="custom-control-input" value="no">
                                        <label class="custom-control-label" for="k${number}2">Tidak</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="kelengkapan_radio[${number}]" id="k${number}3"
                                            class="custom-control-input" value="other" checked>
                                        <label class="custom-control-label" for="k${number}3">Kosong</label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" name="kelengkapan_desc[${number}]" class="form-control" placeholder="Keterangan">
                                </div>
                            </div>`
            $('#kelengkapan').append(html)
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
            });

            $('#btn_get_table').click(function() {
                generate_table()
            })

            function generate_table() {
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
            }

            // $('#form').submit(function(e) {
            //     e.preventDefault();
            //     console.log($('#form').serializeArray());
            // })

        });
    </script>
@endsection
