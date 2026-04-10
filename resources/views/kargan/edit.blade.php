@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-3">
        <form method="POST" action="" id="form">
            @csrf
            <div class="card card-sm mt-2">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-edit mr-2"></i>EDIT DATA KARGAN</h5>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6 text-dark">
                            <label class="small font-weight-bold"><i class="fas fa-id-card mr-1"></i> NO KARGAN</label>
                            <input type="text" name="number" id="number" class="form-control" placeholder="No Kargan"
                                value="{{ $data->number }}" required>
                        </div>
                        <div class="form-group col-md-6 text-dark">
                            <label class="small font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> TANGGAL</label>
                            <input type="date" name="date" id="date" class="form-control" placeholder="Tanggal"
                                value="{{ $data->date ?? date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold"><i class="fas fa-box mr-1"></i> PRODUCT</label>
                            <select name="product" id="product" class="form-control select2" style="width: 100%" required>
                                <option value="">--- Pilih Produk ---</option>
                                @foreach ($products as $item)
                                    <option value="{{ $item->id }}" @selected($data->product_id == $item->id)>{{ $item->code }}
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold"><i class="fas fa-barcode mr-1"></i> SERIAL NUMBER (SN)</label>
                            <input type="text" name="sn" id="sn" class="form-control font-weight-bold text-primary" placeholder="SN"
                                value="{{ $data->sn }}">
                        </div>
                        <div class="form-group col-md-6 text-dark font-weight-normal">
                            <label class="small font-weight-bold"><i class="fas fa-user-check mr-1"></i> PIC QC</label>
                            <select name="pic" id="pic" class="form-control select2" style="width: 100%" required>
                                <option value="Karim Ash Shidik" @selected($data->pic == 'Karim Ash Shidik')>Karim</option>
                                <option value="Sofyan Saputra" @selected($data->pic == 'Sofyan Saputra')>Sofyan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-center">
                    <button type="submit" id="btn_simpan" class="btn btn-primary px-3 mr-1">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <button type="button" id="btn_duplicate" class="btn btn-warning px-3 mr-1">
                        <i class="fas fa-clone mr-1"></i> Duplicate
                    </button>
                    <a href="{{ route('api.kargans.download', $data->id) }}" id="btn_download" class="btn btn-info px-3 mr-1"
                        target="_blank">
                        <i class="fas fa-file-download mr-1"></i> Download
                    </a>
                    
                    <a href="{{ route('kargans.index') }}" class="btn btn-outline-secondary px-3 mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="button" onclick="window.close()" class="btn btn-dark px-3">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        const URL_INDEX = "{{ route('kargans.index') }}"
        const URL_INDEX_API = "{{ route('api.kargans.index') }}"
        const CURRENT_ID = "{{ $data->id }}"

        function get_number() {
            let dateVal = $('#date').val()
            if (!dateVal) {
                dateVal = "{{ date('Y-m-d') }}"

            }
            let [year, month] = dateVal.split('-'); // [2025, 05]
            let romawi = bulanRomawi[parseInt(month) - 1]; // V
            let currentNumber = $('#number').val();
            let parts = currentNumber.split('/');
            if (parts.length >= 4) {
                parts[1] = romawi;
                parts[2] = year;
                $('#number').val(parts.join('/'));
            }
        }

        $(document).ready(function() {
            get_number()

            $('.select2').select2({
                theme: 'bootstrap4',
            })

            $('#form').submit(function(e) {
                e.preventDefault();
                let data = {
                    _method: 'PUT',
                    number: $('#number').val(),
                    date: $('#date').val(),
                    product_id: $('#product').val(),
                    sn: $('#sn').val(),
                    pic: $('#pic').val(),
                }
                $.ajax({
                    type: 'PUT',
                    url: `${URL_INDEX_API}/${CURRENT_ID}`,
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!', 'error')
                    }
                });
            })

            $('#date').change(function() {
                get_number()
            })

            $('#btn_duplicate').click(function() {
                $.ajax({
                    type: 'POST',
                    url: `${URL_INDEX_API}/${CURRENT_ID}/duplicate`,
                    data: {},
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
