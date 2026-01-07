@extends('template', ['title' => 'Import Data Lot'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><i class="fas fa-file-excel mr-1"></i>Import Data Lot</h4>
                    </div>
                    <div class="card-body">

                        <form action="">
                            <div class="form-group col-6">
                                <label for="file">File input</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file" accept=".xls,.xlsx">
                                        <label class="custom-file-label" for="file">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            <div id="sheet_section" style="display: none;">
                                <div class="form-group mt-2">
                                    <label>Pilih Sheet (Klik untuk Load):</label>
                                    <div id="sheet_buttons" class="d-flex flex-wrap" style="gap: 5px;"></div>
                                </div>
                            </div>
                            <a href="{{ route('qc_lots.index') }}" class="btn btn-secondary mt-2">
                                <i class="fas fa-arrow-left mr-1"></i>Kembali
                            </a>
                            <button type="reset" class="btn btn-info mt-2">
                                <i class="fas fa-redo mr-1"></i>Ulangi
                            </button>
                            <a href="{{ asset('master/qc_lots.xlsx') }}" class="btn btn-secondary mt-2" target="_blank">
                                <i class="fas fa-file-excel mr-1"></i>Sample file
                            </a>
                        </form>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>
                            <i class="fas fa-eye mr-1"></i>Preview Data
                            <span class="badge badge-primary" id="total"></span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="responsive">
                            <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Lot</th>
                                        <th>ED</th>
                                        <th>Date</th>
                                        <th>QC By</th>
                                        <th>Note</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- date-range-picker -->

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $("#table").DataTable({
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
                        data: 'product',
                        className: "text-left",
                    }, {
                        data: 'lot',
                        className: "text-left",
                    }, {
                        data: 'ed',
                        className: "text-left",
                    }, {
                        data: 'date',
                        className: "text-left",
                    }, {
                        data: 'qc_by',
                        className: "text-left",
                    }, {
                        data: 'qc_note',
                        className: "text-left",
                        width: '20%'
                    }, {
                        data: 'lot',
                        width: '30px',
                        className: "text-center",
                        render: function(data, type, row) {
                            return `<button class="btn btn-sm btn-danger del-item"><i class="fas fa-trash mr-1"></i></button>`;
                        }
                    },

                ],
                buttons: [{
                    text: '<i class="fas fa-save mr-1"></i>Simpan Data',
                    className: 'btn btn-sm btn-info',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Simpan Data'
                    },
                    action: function(e, dt, node, config) {
                        save_data()
                    }
                }, {
                    text: '<i class="fas fa-trash mr-1"></i>Hapus Data',
                    className: 'btn btn-sm btn-danger',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Hapus Data'
                    },
                    action: function(e, dt, node, config) {
                        table.clear().draw();
                    }
                }, ],
                drawCallback: function(settings) {
                    $('#total').text(settings.fnRecordsDisplay());
                },
            });

            function proces() {
                $('#file').trigger('change')
            }

            let currentWorkbook = null;

            function formatDateExcel(serial) {
                if (!serial || isNaN(serial)) return serial;
                // Excel serial date to JS Date
                let date = new Date(Math.round((serial - 25569) * 86400 * 1000));
                return moment(date).format('YYYY-MM-DD');
            }

            document.getElementById('file').addEventListener('change', function(event) {
                let file = event.target.files[0];
                let reader = new FileReader();

                reader.onload = function(e) {
                    let data = new Uint8Array(e.target.result);
                    currentWorkbook = XLSX.read(data, {
                        type: 'array'
                    });

                    // Tampilkan pilihan tombol sheet
                    let sheetBtnContainer = $('#sheet_buttons');
                    sheetBtnContainer.empty();
                    currentWorkbook.SheetNames.forEach(name => {
                        sheetBtnContainer.append(
                            `<button type="button" class="btn btn-outline-primary btn-load-sheet" data-sheet="${name}"><i class="fas fa-file-excel mr-1"></i>${name}</button>`
                        );
                    });
                    $('#sheet_section').fadeIn();
                };

                reader.readAsArrayBuffer(file);
            });

            // Handle klik tombol sheet
            $('#sheet_buttons').on('click', '.btn-load-sheet', function() {
                if (!currentWorkbook) return;

                let sheetName = $(this).data('sheet');

                // Ubah styling tombol yang aktif
                $('.btn-load-sheet').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');

                let sheet = currentWorkbook.Sheets[sheetName];

                // Konversi ke JSON
                let json = XLSX.utils.sheet_to_json(sheet, {
                    header: 1
                });

                $.fn.dataTable.ext.errMode = 'none';

                table.on('error.dt', function(e, settings, techNote, message) {
                    // console.log('An error has been reported by DataTables: ', message);
                });
                table.clear().draw();

                json.forEach((element, index) => {
                    if (index != 0 && index != 1 && index != 2) {
                        let product = element[2] || '';
                        let lot = element[4] || '';
                        let ed = element[5];
                        let date = formatDateExcel(element[12]);
                        let qc_note = element[13];
                        let qc_by = element[14];
                        if (!product || !lot || !ed || !date) return;
                        let param = {
                            product: product,
                            lot: lot,
                            ed: ed,
                            date: date,
                            qc_by: qc_by,
                            qc_note: qc_note,
                        }
                        table.row.add(param).draw();
                    }
                });
            });

            $('button[type="reset"]').click(function() {
                $('#sheet_section').hide();
                $('#sheet_buttons').empty();
                table.clear().draw();
                currentWorkbook = null;
            });

            function save_data() {
                var data = table
                    .rows()
                    .data().toArray();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.qc_lots.import') }}",
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

            $('#table tbody').on('click', 'td:not(:last-child)', function() {
                let cell = table.cell(this);
                let oldValue = cell.data();
                if ($(this).find('input').length > 0) return;
                $(this).html(
                    `<input type="text" class="form-control edit-input" value="${oldValue||''}" />`);
                let input = $(this).find('input');
                input.focus();
                input.on('blur', function() {
                    let newValue = $(this).val().trim();
                    cell.data(newValue).draw();
                });
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        $(this).blur();
                    }
                });
            });

            $('#table').on('click', '.del-item', function() {
                let row = $(this).parents('tr')[0];
                table
                    .row($(this).parents('tr'))
                    .remove()
                    .draw();
            });
        })
    </script>
@endpush
