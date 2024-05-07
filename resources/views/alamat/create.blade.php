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
                <select name="" id="select_kontak" class="form-control select2" style="width: 100%">
                    <option value="">Pilih</option>
                </select>
            </div>

            <form method="POST" action="{{ route('alamat.store') }}" id="form">
                @csrf
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
                        <div class="form-group col-md-6">
                            <label for="koli">Jumlah Koli</label>
                            <input type="number" name="koli" id="koli" class="form-control" min="0"
                                placeholder="Jumlah Koli" value="{{ $data->koli }}">
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
                            <input type="text" name="epur" id="epur" class="form-control"
                                placeholder="Epurchasing" value="{{ $data->epur }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="untuk">Untuk</label>
                            <input type="text" name="untuk" id="untuk" class="form-control" placeholder="Untuk"
                                value="{{ $data->untuk }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nilai">Nilai Barang</label>
                            <input type="text" name="nilai" id="nilai" class="form-control"
                                placeholder="Nilai Barang" value="{{ $data->nilai }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_do" value="yes"
                                    id="is_do" {{ $data->is_do == 'yes' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_do">Surat JALAN?</label>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_pk" value="yes"
                                    id="is_pk" {{ $data->is_pk == 'yes' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_pk">P KAYU ?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table" id="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Lot</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
        </div>

        <div class="card-footer">
            <button type="button" id="add" class="btn btn-info">Tambah Product</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a id="print" href="{{ route('alamat.show', $data->id) }}" type="button" target="_blank"
                class="btn btn-warning">Print</a>
            <button type="button" id="btn_tes" class="btn btn-danger">TES</button>
        </div>
        </form>
    </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="product_modal" tabindex="-1" aria-labelledby="product_modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="product_modalLabel">Pilih Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <select name="select_product" id="select_product" class="form-control select2" style="width: 100%">
                    </select>
                    <input type="text" class="form-control" id="qty_prod" value="100">
                    <textarea class="form-control" id="lot_prod">E002/2023.02</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="btn_modal_save" class="btn btn-primary">Save changes</button>
                </div>
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
        var data = [];
        $(document).ready(function() {
            $('#btn_tes').click(function() {
                $.get('{{ url('api/alamat/get') }}').done(function(result) {
                    console.log(result);
                })
            })
            $.get("{{ route('product.index') }}").done(function(res) {
                for (let i = 0; i < res.data.length; i++) {
                    let option = new Option(`${res.data[i].code} ${res.data[i].name}`, res.data[i].id,
                        true, true);
                    $('#select_product').append(option);
                }
            });

            $.get("{{ route('kontak.index') }}").done(function(res) {
                for (let i = 0; i < res.data.length; i++) {
                    let option = new Option(res.data[i].name, res.data[i].id,
                        true, true);
                    $('#select_kontak').append(option);
                }
            });

            $('#select_kontak').select2({
                theme: 'bootstrap4',
            }).on('change', function() {
                let data = $(this).select2('data');
                let id = data[0].id
                $.get("{{ route('kontak.show', '') }}/" + id).done(function(res) {
                    $('#tujuan').val(res.data.name)
                    $('#alamat').val(res.data.street)
                    $('#tlp').val(res.data.phone ?? '')
                });

            });

            $('#select_product').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#product_modal"),
            })

            var table = $('#table').DataTable({
                searching: false,
                info: false,
                lengthChange: false,
                paging: false,
                rowId: 'id',
                columns: [{
                    data: "code",
                }, {
                    data: "qty",
                }, {
                    data: "lot",
                }, {
                    data: "id",
                    className: "text-center",
                    render: function(data, type, row, meta) {
                        return `<button type="button" id="hapus" class="btn btn-sm btn-primary">Hapus</button>`
                    }
                }]
            })


            var old = @json($data->detail->load('product'));
            old.forEach(element => {
                let data = {
                    code: element.product.code + ' ' + element.product.name,
                    id: element.product_id,
                    qty: element.qty,
                    lot: element.lot,
                }
                table.row.add(data).draw(true)
            });


            $('#add').click(function() {
                $('#product_modal').modal('show')
            })

            $('#btn_modal_save').click(function() {
                var selectedData = $('#select_product').select2('data');
                var selectedText = selectedData[0].text;

                let data = {
                    code: selectedText,
                    id: $('#select_product').val(),
                    qty: $('#qty_prod').val(),
                    lot: $('#lot_prod').val(),
                }
                table.row.add(data).draw(true)
            })

            $('#table tbody').on('click', '#hapus', function() {
                var row = table.row($(this).closest('tr'));
                var id = row.id();
                row.remove().draw(true);
            });


            $('#form').submit(function(e) {
                e.preventDefault();
                let dt = table.rows().data().toArray();
                let data = {
                    detail: dt,
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

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: "{{ route('alamat.store') }}",
                    data: data,
                    beforeSend: function() {
                        // block();
                    },
                    success: function(res) {
                        // $('#print').attr('href', "{{ route('alamat.show', '') }}/" + res.id);
                        window.open("{{ route('alamat.show', '') }}/" + res.id, '_blank')
                        // alert('OK')
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message);
                    }
                });
            })

        });
    </script>
@endsection
