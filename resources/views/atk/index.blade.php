@extends('template', ['title' => 'Data ATK'])

@section('content')
    <!-- daterange picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <div class="container-fluid">
        <h1>Data ATK</h1>

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>KODE</th>
                            <th>NAME</th>
                            <th>Satuan</th>
                            <th>STOK</th>
                            <th>DESC</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    {{-- <button id="export">Eksport</button> --}}

    @include('atk.modal')

    @if (session()->has('message'))
        <script>
            alert("{{ session('message') }}")
        </script>
    @endif


    <!-- date-range-picker -->

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


    <script>
        $.fn.dataTable.ext.errMode = 'none';

        var BASE_URL_API = "{{ route('api.atk.index') }}"
        var ID = 0;

        var table_detail;
        var table;

        function delete_trx(id) {
            if (!confirm('Hapus?')) {
                return
            }
            $.ajax({
                type: 'DELETE',
                url: `{{ route('api.atktrx.index') }}/${id}`,
                data: {},
                contentType: "application/json",
                beforeSend: function() {},
                success: function(res) {
                    try {
                        $('#modal_detail').modal('hide')
                        table.ajax.reload()
                    } catch (error) {
                        // 
                    }

                },
                error: function(xhr, status, error) {}
            });
        }

        $(document).ready(function() {

            $('#trx_date').daterangepicker({
                singleDatePicker: true,
                "autoApply": true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });

            table = $('#table').DataTable({
                rowId: 'id',
                ajax: BASE_URL_API,
                dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
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
                pageLength: 10,
                lengthChange: false,
                // order: [
                //     [1, "asc"]
                // ],
                columns: [{
                        data: 'id',
                        className: "text-center",
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                        }
                    }, {
                        data: "code",
                    },
                    {
                        data: "name",
                    },
                    {
                        data: "satuan",
                    }, {
                        data: "stok",
                    },
                    {
                        data: "desc",
                        visible: false,
                    }, {
                        data: 'id',
                        className: "text-center",
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            let text = `<button type="button" class="btn btn-sm btn-success in">+ IN</button> 
                            <button type="button" class="btn btn-sm btn-warning out">- OUT</button>
                            <button type="button" class="btn btn-sm btn-info detail">i View</button>
                            <button type="button" class="btn btn-sm btn-danger delete">x Del</button>
                            `
                            if (type == 'display') {
                                return text
                            }
                            return data
                        }
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-plus mr-1"></i> Add',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Add Data'
                        },
                        action: function(e, dt, node, config) {
                            $('#form_add')[0].reset()
                            $('#modal_add').modal('show')
                        }
                    }, {
                        extend: "colvis",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Column Visible'
                        },
                        className: 'btn btn-sm btn-primary'
                    },
                    {
                        extend: "pageLength",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Page Length'
                        },
                        className: 'btn btn-sm btn-info'
                    },
                    {
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
                    }, {
                        text: '<i class="fas fa-upload mr-1"></i> Import',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Import Data'
                        },
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route('atk.import') }}"
                        }
                    },
                ],
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            multiCheck(table);

            $('#table').on('click', 'tr td:not(:first-child,:last-child)', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                ID = data.id
                $.ajax({
                    type: 'GET',
                    url: `${BASE_URL_API}/${data.id}`,
                    data: {},
                    beforeSend: function() {},
                    success: function(res) {
                        $('#edit_code').val(res.data.code)
                        $('#edit_name').val(res.data.name)
                        $('#edit_satuan').val(res.data.satuan).change()
                        $('#edit_desc').val(res.data.desc)
                        $('#modal_edit').modal('show')
                    },
                    error: function(xhr, status, error) {}
                });
            });

            $('#table').on('click', '.in', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                $('#trx_atk_id').val(data.id)
                $('#trx_title').html(`[${data.code}] ${data.name} (${data.satuan})`)
                let datenow = "{{ date('d/m/Y') }}"
                $('#trx_date').data('daterangepicker').setStartDate(datenow);
                $('#trx_date').data('daterangepicker').setEndDate(datenow);
                $('#trx_pic').val('Tika')
                $('#trx_qty').val(1)
                $('#trx_type').val('in').change()
                $('#modal_trx').modal('show')
            });

            $('#table').on('click', '.out', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                $('#trx_atk_id').val(data.id)
                $('#trx_title').html(`[${data.code}] ${data.name} (${data.satuan})`)
                let datenow = "{{ date('d/m/Y') }}"
                $('#trx_date').data('daterangepicker').setStartDate(datenow);
                $('#trx_date').data('daterangepicker').setEndDate(datenow);
                $('#trx_pic').val('Tika')
                $('#trx_qty').val(1)
                $('#trx_type').val('out').change()
                $('#modal_trx').modal('show')
            });

            $('#table').on('click', '.delete', function() {
                if (!confirm('Hapus Data?')) {
                    return
                }
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                delete_data(data.id)
            });

            $('#table').on('click', '.detail', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                $('#detail_title').html(`[${data.code}] ${data.name} (${data.satuan})`)
                $('#table_detail').DataTable().clear().destroy();

                table_detail = $("#table_detail").DataTable({
                    rowId: 'id',
                    dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
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
                    ordering: false,
                    columns: [{
                            data: "date",
                        },
                        {
                            data: "pic",
                        }, {
                            data: "qty",
                            render: function(data, type, row, meta) {
                                return row.type == 'in' ? data : ''
                            }
                        }, {
                            data: "qty",
                            render: function(data, type, row, meta) {
                                return row.type == 'out' ? data : ''
                            }
                        }, {
                            data: "saldo",
                        }, {
                            data: "desc",
                        }, {
                            data: "id",
                            render: function(data, type, row, meta) {
                                if (type == 'display') {
                                    return `<button class="btn btn-sm btn-danger" type="button" onclick="delete_trx(${data})">Del</button>`
                                }
                                return data
                            }
                        }
                    ],
                    buttons: [{
                            extend: "colvis",
                            attr: {
                                'data-toggle': 'tooltip',
                                'title': 'Column Visible'
                            },
                            className: 'btn btn-sm btn-primary'
                        },
                        {
                            extend: "collection",
                            text: '<i class="fas fa-download"></i>Export',
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
                        }
                    ],
                });
                $.ajax({
                    type: 'GET',
                    url: `{{ route('api.atktrx.index') }}?atk_id=${data.id}`,
                    data: {},
                    beforeSend: function() {},
                    success: function(res) {
                        var saldo = 0

                        res.data.forEach(element => {
                            if (element.type == 'in') {
                                saldo = saldo + element.qty
                            }
                            if (element.type == 'out') {
                                saldo = saldo - element.qty
                            }
                            let data = {
                                id: element.id,
                                date: element.date,
                                pic: element.pic,
                                type: element.type,
                                qty: element.qty,
                                qty: element.qty,
                                saldo: saldo
                            }
                            table_detail.row.add(data).draw()
                        });
                    },
                    error: function(xhr, status, error) {}
                });
                $('#modal_detail').modal('show')
            });

            function delete_data(id) {
                $.ajax({
                    type: 'DELETE',
                    url: `${BASE_URL_API}/${id}`,
                    data: {},
                    beforeSend: function() {},
                    success: function(res) {
                        table.ajax.reload()
                    },
                    error: function(xhr, status, error) {}
                });
            }

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    alert("No Selected Data!")
                    return false
                } else {
                    return true
                }
            }

            $('#form_edit').submit(function(event) {
                event.preventDefault()
                $.ajax({
                    type: 'PUT',
                    url: `${BASE_URL_API}/${ID}`,
                    data: $('#form_edit').serialize(),
                    beforeSend: function() {
                        $('#form_edit .text-danger').hide();
                    },
                    success: function(res) {
                        table.ajax.reload()
                        $('#modal_edit').modal('hide')
                    },
                    error: function(xhr, status, error) {
                        er = xhr.responseJSON.errors
                        if (xhr.status != 422) {
                            alert(xhr.responseJSON.message || 'Error!')
                        } else {
                            erlen = Object.keys(er).length
                            for (i = 0; i < erlen; i++) {
                                obname = Object.keys(er)[i];
                                // $('#' + obname).addClass('is-invalid');
                                $('#form_edit .' + obname).text(er[obname][0]);
                                $('#form_edit .' + obname).show();
                            }
                        }
                    }
                });
            })

            $('#form_add').submit(function(event) {
                event.preventDefault()
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.atk.store') }}",
                    data: $('#form_add').serialize(),
                    beforeSend: function() {
                        $('#form_add .text-danger').hide();
                    },
                    success: function(res) {
                        table.ajax.reload()
                        $('#modal_add').modal('hide')
                    },
                    error: function(xhr, status, error) {
                        er = xhr.responseJSON.errors
                        if (xhr.status != 422) {
                            alert(xhr.responseJSON.message || 'Error!')
                        } else {
                            erlen = Object.keys(er).length
                            for (i = 0; i < erlen; i++) {
                                obname = Object.keys(er)[i];
                                // $('#' + obname).addClass('is-invalid');
                                $('#form_add .' + obname).text(er[obname][0]);
                                $('#form_add .' + obname).show();
                            }
                        }
                    }
                });
            })

            $('#form_trx').submit(function(event) {
                event.preventDefault()
                let data = {
                    atk_id: $('#trx_atk_id').val(),
                    date: $('#trx_date').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                    type: $('#trx_type').val(),
                    pic: $('#trx_pic').val(),
                    qty: $('#trx_qty').val(),
                    desc: $('#trx_desc').val(),
                }
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.atktrx.store') }}",
                    data: data,
                    beforeSend: function() {
                        $('#form_trx .text-danger').hide();
                    },
                    success: function(res) {
                        table.ajax.reload()
                        $('#modal_trx').modal('hide')
                    },
                    error: function(xhr, status, error) {
                        er = xhr.responseJSON.errors
                        if (xhr.status != 422) {
                            alert(xhr.responseJSON.message || 'Error!')
                        } else {
                            erlen = Object.keys(er).length
                            for (i = 0; i < erlen; i++) {
                                obname = Object.keys(er)[i];
                                // $('#' + obname).addClass('is-invalid');
                                $('#form_trx .' + obname).text(er[obname][0]);
                                $('#form_trx .' + obname).show();
                            }
                        }
                    }
                });
            })


        });
    </script>

    <script>
        $('#export').click(function() {
            dttbl = table.rows().data().toArray();

            // Data yang akan ditambahkan ke dalam sheet
            var data = [{
                "date": "31-Dec-24",
                "name": "Opname (31 Des 2024)",
                "masuk": 1,
                "keluar": 0,
                "saldo": 1,
                "keterangan": ""
            }];

            // Header tabel
            var header = ["Tgl", "Nama", "Mutasi Masuk", "Mutasi Keluar", "Saldo", "Keterangan"];

            // Membuat data array dari data JSON
            var excelData = data.map(function(row) {
                return [row.date, row.name, row.masuk, row.keluar, row.saldo, row.keterangan];
            });

            // Menambahkan header ke excelData
            excelData.unshift(header);

            // Membuat worksheet pertama dengan teks tambahan
            var ws = XLSX.utils.aoa_to_sheet([
                ["KARTU STOCK"],
                [], // Baris kosong
                ["Tahun : 2025"],
                ["Nama Barang : Buku Voucher (Kas) / biru", null, null, null, null, "Satuan : Buku"],
                [], // Baris kosong
                [] // Baris kosong
            ]);

            // Menambahkan data tabel ke worksheet
            XLSX.utils.sheet_add_aoa(ws, excelData, {
                origin: -1
            });

            var wb = XLSX.utils.book_new();

            dttbl.forEach(element => {
                var sheetData = [
                    ["KARTU STOCK"],
                    [],
                    ["Tahun : 2025"],
                    [`Nama Barang : ${element.name}`, null, null, null, null,
                        `Satuan : ${element.satuan}`
                    ],
                    [],
                    []
                ];
                sheetData = sheetData.concat(excelData);

                var newSheet = XLSX.utils.aoa_to_sheet(sheetData);

                XLSX.utils.book_append_sheet(wb, newSheet, element.code);
            });

            XLSX.writeFile(wb, "Kartu_Stock_2025.xlsx");
            return

            var data = [{
                "date": "31-Dec-24",
                "name": "Opname (31 Des 2024)",
                "masuk": 1,
                "keluar": 0,
                "saldo": 1,
                "keterangan": ""
            }];
            var header = ["Tgl", "Nama", "IN", "OUT", "Saldo", "Keterangan"];

            var excelData = data.map(function(row) {
                return [row.date, row.name, row.masuk, row.keluar, row.saldo, row.keterangan];
            });

            excelData.unshift(header);

            var ws = XLSX.utils.aoa_to_sheet([
                ["KARTU STOCK"],
                [],
                ["Tahun : 2025"],
                ["Nama Barang : Buku Voucher (Kas) / biru", null, null, null, null,
                    "Satuan : Buku"
                ],
                [],
                []
            ]);

            XLSX.utils.sheet_add_aoa(ws, excelData, {
                origin: -1
            });
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Kartu Stock");

            XLSX.writeFile(wb, "Kartu_Stock_2025.xlsx");
        });
    </script>
@endsection
