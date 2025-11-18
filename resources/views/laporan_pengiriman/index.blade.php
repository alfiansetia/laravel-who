@extends('template')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- <h1>{{ $title }}</h1> --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                        value="CENT/OUT/">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" id="btn_get_do">
                                            <i class="fas fa-search mr-1"></i>GET
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <select name="" id="select_do" class="form-control col-md-6 select2"
                                    style="width: 100%">
                                    <option value="">Pilih</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="table_tool" class="table table-sm table-hover" style="width: 100%;cursor: pointer;">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 30px">#</th>
                                    <th>Nama Customer</th>
                                    <th>No DO</th>
                                    <th>PO NO</th>
                                    <th>Koli</th>
                                    <th>Ekspedisi</th>
                                    <th>Keterangan</th>
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
                    width: '40px',
                    render: function(data, type, row, meta) {
                        return `<span class="text-danger del-item"><i class="fas fa-trash"></i></span>`
                    }
                }, {
                    data: "name",
                    className: 'text-left',

                }, {
                    data: "do",
                    className: 'text-left',

                }, {
                    data: "po",
                    className: 'text-left',

                }, {
                    data: "koli",
                    className: 'text-center',

                }, {
                    data: "ekspedisi",
                    className: 'text-left',

                }, {
                    data: "desc",
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
                    show_message(xhr.responseJSON.message || 'Error!')
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
                    let name = ''
                    let no_do = ''
                    let no_po = res.data.no_po
                    let ekspedisi = ''
                    let desc = ''
                    if (res.data.partner_id != false) {
                        name = res.data.partner_id[1]
                    }
                    if (res.data.name != false) {
                        no_do = res.data.name
                    }
                    if (res.data.ekspedisi_id != false) {
                        ekspedisi = res.data.ekspedisi_id[1]
                    }
                    if (res.data.partner_address3) {
                        name += ` / ${res.data.partner_address3}`
                    }
                    let descArr = [];

                    res.data.move_ids_detail.forEach(item => {
                        if (item.product_id) {
                            let name = item.product_id[1];
                            let match = name.match(/\[(.*?)\]/);
                            if (match) {
                                descArr.push(`${match[1]} (${item.quantity_done})`);
                            }
                        }
                    });

                    desc = descArr.join(', ');

                    table_tool.row.add({
                        name: name,
                        do: no_do,
                        po: no_po,
                        koli: 1,
                        ekspedisi: ekspedisi,
                        desc: desc,
                    }).draw();
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                });

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

        });
    </script>
@endpush
