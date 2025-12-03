@extends('template', ['title' => 'Data ATK'])

@section('content')
    <form action="">
        <div class="form-group p-3">
            <label for="file">File input</label>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="file" accept=".xls,.xlsx">
                    <label class="custom-file-label" for="file">Choose file</label>
                </div>
                {{-- <div class="input-group-append">
                <a href="{{ route('atk.index') }}" class="input-group-text">Back</a>
            </div> --}}
            </div>
            <a href="{{ route('atk.index') }}" class="btn btn-secondary mt-2">Kembali</a>
            <button type="reset" class="btn btn-info mt-2">Ulangi</button>
            <a href="{{ asset('stock.xls') }}" class="btn btn-secondary mt-2" target="_blank">Sample file</a>
        </div>
        <div class="container-fluid">
            <h1>Preview Data</h1>

            <div class="responsive">
                <table class="table table-sm table-hover mb-5" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th>KODE</th>
                            <th>NAME</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <!-- date-range-picker -->

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        function proces() {
            $('#file').trigger('change')
        }

        document.getElementById('file').addEventListener('change', function(event) {
            let file = event.target.files[0];
            let reader = new FileReader();

            reader.onload = function(e) {
                let data = new Uint8Array(e.target.result);
                let workbook = XLSX.read(data, {
                    type: 'array'
                });

                let sheetName = workbook.SheetNames[0];
                let sheet = workbook.Sheets[sheetName];

                // Konversi ke JSON
                let json = XLSX.utils.sheet_to_json(sheet, {
                    header: 1
                });

                $.fn.dataTable.ext.errMode = 'none';

                $('#table').on('error.dt', function(e, settings, techNote, message) {
                    // console.log('An error has been reported by DataTables: ', message);
                }).DataTable();
                $('#table').DataTable().clear().destroy();

                table = $("#table").DataTable({
                    dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                        "<'table-responsive'tr>" +
                        "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                    oLanguage: {
                        "sSearchPlaceholder": "Search...",
                        "sLengthMenu": "Results :  _MENU_",
                    },
                    lengthMenu: [
                        [10, 50, 100, 500, 1000],
                        ['10 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
                    ],
                    info: false,
                    // ordering: false,
                    columns: [{
                        data: 'code',
                        className: "text-left",
                    }, {
                        data: 'name'
                    }, {
                        data: 'satuan'
                    }, ],
                    buttons: [{
                        text: '<i class="fas fa-plus mr-1"></i> Simpan Data',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Simpan Data'
                        },
                        action: function(e, dt, node, config) {
                            save_data()
                        }
                    }, {
                        text: '<i class="fas fa-trash mr-1"></i> Hapus Data',
                        className: 'btn btn-sm btn-danger',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Hapus Data'
                        },
                        action: function(e, dt, node, config) {
                            $('#table').DataTable().clear().destroy();
                        }
                    }, ],
                });
                json.forEach((element, index) => {
                    if (index != 0) {
                        let param = {
                            code: element[0],
                            name: element[1],
                            satuan: element[2],
                        }
                        table.row.add(param).draw()
                    }
                });

            };

            reader.readAsArrayBuffer(file);
        });

        function save_data() {
            var tb = $('#table').DataTable();
            var data = tb
                .rows()
                .data().toArray();

            $.ajax({
                type: 'POST',
                url: "{{ route('api.atk.import') }}",
                data: JSON.stringify({
                    data: data
                }),
                contentType: "application/json",
                beforeSend: function() {
                    // $('#form_trx .text-danger').hide();
                },
                success: function(res) {
                    show_message('Success!', 'success')
                },
                error: function(xhr, status, error) {
                    show_message(xhr.responseJSON.message || 'Error!')
                }
            });

        }
    </script>
@endpush
