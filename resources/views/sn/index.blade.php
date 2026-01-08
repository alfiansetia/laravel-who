@extends('template')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
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

                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-magic mr-1"></i>SN GENERATOR
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Base SN</label>
                                    <input type="text" id="gen_base" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Qty (±)</label>
                                    <input type="number" id="gen_qty" class="form-control" value="100">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="button" id="btn_generate" class="btn btn-primary btn-block">
                                    <i class="fas fa-cog mr-1"></i>Generate
                                </button>
                            </div>
                        </div>
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
                        <div class="form-group col-12">
                            <button class="btn btn-sm btn-outline btn-outline-primary btn-block"
                                onclick="set_skip([2,4])">MDS 2,4</button>
                            <button class="btn btn-sm btn-outline btn-outline-primary btn-block"
                                onclick="set_skip([2,3])">FR202 2,3</button>
                            <button class="btn btn-sm btn-outline btn-outline-primary btn-block"
                                onclick="set_skip([2,4,6,8,10])">500E 2,4,6,8,10</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function set_skip(values) {
            $('#skip').val(values).change()
        }
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
                let skip = $('#skip').val() || [];
                skip = skip.filter(v => v !== "").map(Number);

                let imp = $('#import').val().trim();
                if (!imp) {
                    show_message('Please enter some text to import!', 'error');
                    return;
                }

                let rows = imp.split('\n');
                let count = 0;
                if (rows.length > 0) {
                    rows.forEach((row, index) => {
                        if (row != '' || row != null) {
                            let cols = row.split('\t');
                            cols.forEach((col, i) => {
                                if (col) {
                                    if (!skip.includes(i + 1)) {
                                        table_tool.row.add({
                                            item: col.trim(),
                                        });
                                        count++;
                                    }
                                }
                            });
                        }

                    });
                    table_tool.draw();
                    show_message(`Successfully imported ${count} items!`, 'success');
                }
            });

            $('#btn_import_clear').click(function() {
                let imp = $('#import').val('')
            });

            $('#table_tool tbody').on('click', 'td:not(:first-child)', function() {
                let cell = table_tool.cell(this);
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

            $('#table_tool').on('click', '.del-item', function() {
                let row = $(this).parents('tr')[0];
                table_tool
                    .row($(this).parents('tr'))
                    .remove()
                    .draw();
            });

            $('#btn_generate').click(function() {
                let base = $('#gen_base').val().trim();
                let qty = parseInt($('#gen_qty').val());

                if (!base) {
                    show_message('Base SN is required!');
                    return;
                }

                // Regex to find trailing digits
                let match = base.match(/^(.*?)(\d+)$/);
                if (!match) {
                    show_message('Base SN must end with a number!');
                    return;
                }

                let prefix = match[1];
                let numStr = match[2];
                let startNum = parseInt(numStr);
                let padLength = numStr.length;

                let start = 0;
                let end = Math.abs(qty);
                let step = qty > 0 ? 1 : -1;

                for (let i = 0; i < end; i++) {
                    let currentNum = startNum + (i * step);
                    if (currentNum < 0) break;

                    let generatedNum = currentNum.toString().padStart(padLength, '0');
                    table_tool.row.add({
                        item: prefix + generatedNum,
                    });
                }
                table_tool.draw();
                show_message(`Generated ${end} SNs!`, 'success');
            });

            $('#skip').select2({
                placeholder: "Select a skip",
                allowClear: true
            })

        });
    </script>
@endpush
