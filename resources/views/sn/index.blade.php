@extends('template')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- <h1>{{ $title }}</h1> --}}
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        Featured
                    </div>
                    <div class="card-body">
                        <table id="table_tool" class="table table-sm table-hover" style="width: 100%;cursor: pointer;">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 30px">#</th>
                                    <th>SN</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        Featured
                    </div>
                    <div class="card-body">
                        <div class="form-group col-12">
                            <label for="import">IMPORT FROM TEXT</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" id="btn_import_clear" class="input-group-text">X</button>
                                </div>
                                <textarea name="import" id="import" class="form-control" rows="5"></textarea>
                                <div class="input-group-append">
                                    <button type="button" id="btn_import" class="input-group-text">IMPORT</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-12">
                            <label for="skip">Skip</label>
                            <select name="skip" id="skip" class="form-control" multiple>
                                @for ($i = 0; $i < 20; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <br>MDS skip 2 & 4
                        <br>FR202 Skip 3

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {


            var table_tool = $('#table_tool').DataTable({
                // pageLength: false,
                lengthMenu: [
                    [10, 50, 100, 500, 1000],
                    ['10 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
                ],
                pageLength: 10,
                lengthChange: false,
                paging: true,
                searching: true,
                info: true,
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                columns: [{
                    data: "item",
                    className: 'text-center',
                    width: '10%',
                    render: function(data, type, row, meta) {
                        return `<span class="text-danger del-item"><i class="fas fa-trash"></i></span>`
                    }
                }, {
                    data: "item",
                    className: 'text-left',

                }, ],
                buttons: [{
                    text: '<i class="fas fa-trash mr-1"></i>Empty ITEM',
                    className: 'btn btn-sm btn-danger',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Empty ITEM'
                    },
                    action: function(e, dt, node, config) {
                        table_tool
                            .rows()
                            .remove()
                            .draw();
                    }
                }, {
                    extend: "pageLength",
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Page Length'
                    },
                    className: 'btn btn-sm btn-info'
                }, {
                    extend: "collection",
                    text: '<i class="fas fa-download"></i> Export',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Export Data'
                    },
                    className: 'btn btn-sm btn-primary',
                    buttons: [{
                        extend: 'copy',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }, {
                        extend: 'csv',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }, {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }, {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }, {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                }, ],
            });


            $('#btn_import').click(function() {
                let skip = $('#skip').val() || []; // hasilnya array
                skip = skip.filter(v => v !== "").map(Number);
                console.log(skip);

                let imp = $('#import').val().trim();
                let rows = imp.split('\n');
                if (rows.length > 0) {
                    rows.forEach((row, index) => {
                        // misal skip ke array skip
                        if (row != '' || row != null) {
                            let cols = row.split('\t');
                            // console.log(cols);

                            cols.forEach((col, i) => {
                                if (col) {
                                    if (!skip.includes(i + 1)) {
                                        table_tool.row.add({
                                            item: col.trim(),
                                        });
                                    }
                                }
                            });
                        }

                    });
                    table_tool.draw();
                }
            });

            $('#btn_import_clear').click(function() {
                let imp = $('#import').val('')
            });


            $('#table_tool tbody').on('click', 'td:not(:first-child)', function() {
                let cell = table_tool.cell(this);
                let oldValue = cell.data();

                // Cegah double input
                if ($(this).find('input').length > 0) return;

                // Ganti isi jadi input
                $(this).html(
                    `<input type="text" class="form-control edit-input" value="${oldValue||''}" />`);
                let input = $(this).find('input');
                input.focus();

                // Handle keluar dari input (blur)
                input.on('blur', function() {
                    let newValue = $(this).val().trim();

                    // Update value di tabel
                    cell.data(newValue).draw();
                });

                // Optional: tekan Enter untuk simpan
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        $(this).blur(); // trigger blur
                    }
                });
            });

            $('#table_tool').on('click', '.del-item', function() {
                let row = $(this).parents('tr')[0];
                table_tool
                    .row($(this).parents('tr'))
                    .remove()
                    .draw();
            });


            $('#skip').select2({
                placeholder: "Select a skip",
                allowClear: true
            })



        });
    </script>
@endpush
