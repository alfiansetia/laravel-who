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
            </div>

            <form method="POST" action="" id="form">
                @csrf
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="number">No Kargan</label>
                            <input type="text" name="number" id="number" class="form-control" placeholder="No Kargan"
                                value="SUPP-GAR/V/2025/" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="date">Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" placeholder="Tanggal"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="product">Product</label>
                            <select name="product" id="product" class="form-control select2" style="width: 100%" required>
                                <option value="">Pilih</option>
                                @foreach ($products as $item)
                                    <option value="{{ $item->id }}">{{ $item->code }} {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="sn">SN</label>
                            <input type="text" name="sn" id="sn" class="form-control" placeholder="SN">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pic">PIC QC</label>
                            <select name="pic" id="pic" class="form-control select2" style="width: 100%" required>
                                <option value="Karim Ash Shidik">Karim</option>
                                <option value="Sofyan Saputra">Sofyan</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">

                    </div>
                </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('kargan.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" id="btn_simpan" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

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
                    number: $('#number').val(),
                    date: $('#date').val(),
                    product_id: $('#product').val(),
                    sn: $('#sn').val(),
                    pic: $('#pic').val(),
                }
                console.log(data);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.kargan.store') }}",
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        let id = res.data.id
                        window.open("{{ url('kargan') }}/" + id + '/edit', '_blank')
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message);
                    }
                });
            })

            $('#date').change(function() {
                get_number()
            })

        });
    </script>
@endpush
