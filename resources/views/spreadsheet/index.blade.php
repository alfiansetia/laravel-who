@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="status_filter" class="form-control form-control-sm">
                    <option value="all">-- Semua Status Data --</option>
                    <option value="lengkap">Lengkap (PLTB Terisi)</option>
                    <option value="tidak_lengkap">Tidak Lengkap</option>
                </select>
            </div>
        </div>
        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    @include('spreadsheet.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const URL_INDEX_API = "{{ route('api.spreadsheet.index') }}"
            const URL_SYNC_PRODUCT_API = "{{ route('api.spreadsheet.sync_product') }}"
            const URL_COMPARE_PRODUCT_API = "{{ route('api.products.compare', ':product') }}"

            function parseDecimal(value, decimals = 2) {
                if (!value) return (0).toFixed(decimals);

                // ganti koma dengan titik
                let num = value.toString().replace(',', '.');

                // parse jadi number
                num = parseFloat(num);

                if (isNaN(num)) num = 0;

                // format 2 decimal
                return num.toFixed(decimals);
            }

            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: {
                    url: URL_INDEX_API,
                    error: function(xhr, textStatus, errorThrown) {
                        let message = xhr.responseJSON?.message || 'Gagal memuat data!';
                        $('.dt-empty').text(message);
                        $('#table_processing').hide();
                        show_message(message, 'error');
                    },
                },
                createdRow: function(row, data, dataIndex) {
                    // Indeks data: 8(P), 9(L), 10(T), 11(B)
                    // Jika ada salah satu yang kosong, beri warna peringatan
                    if (!data[8] || !data[9] || !data[10] || !data[11]) {
                        $(row).addClass('bg-light-danger').css('color', '#f8081cff');
                    }
                },
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
                pageLength: 10,
                lengthChange: false,
                columns: [{
                        data: "1",
                        className: "text-left",
                        title: "NO",
                        width: "30px"
                    }, {
                        data: "3",
                        className: "text-left",
                        title: "KODE"
                    }, {
                        data: "4",
                        className: "text-left",
                        title: "NAME"
                    },
                    {
                        data: "5",
                        className: 'text-left',
                        title: "Cat"
                    },
                    {
                        data: "6",
                        className: 'text-center',
                        title: "UOM"
                    },
                    {
                        data: "8",
                        className: 'text-center',
                        title: "P"
                    },
                    {
                        data: "9",
                        className: 'text-center',
                        title: "L"
                    },
                    {
                        data: "10",
                        className: 'text-center',
                        title: "T"
                    },
                    {
                        data: "11",
                        className: 'text-center',
                        title: "B"
                    },
                    {
                        data: "12",
                        className: 'text-left',
                        title: "Note",
                        visible: false
                    },
                    {
                        data: "13",
                        className: 'text-left',
                        title: "Tgl Input",
                        visible: false
                    },
                    {
                        data: null,
                        title: "Status",
                        visible: false,
                        render: function(data, type, row) {
                            var p = row[8] ? String(row[8]).trim() : '';
                            var l = row[9] ? String(row[9]).trim() : '';
                            var t = row[10] ? String(row[10]).trim() : '';
                            var b = row[11] ? String(row[11]).trim() : '';

                            var isComplete = (p !== '' && p !== '0') &&
                                (l !== '' && l !== '0') &&
                                (t !== '' && t !== '0') &&
                                (b !== '' && b !== '0');

                            return isComplete ? 'lengkap' : 'tidak_lengkap';
                        }
                    },
                    {
                        data: "3",
                        className: 'text-center',
                        title: "#",
                        render: function(data, type, row) {
                            return `
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-warning btn-sync"><i class="fas fa-sync"></i></button>
                                <button type="button" class="btn btn-sm btn-info btn-compare"><i class="fas fa-balance-scale"></i></button>
                            </div>
                            `
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
                        extend: "pageLength",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Page Length'
                        },
                        className: 'btn btn-sm btn-info'
                    },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download mr-1"></i>Export',
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
                    },
                    {
                        text: '<i class="fas fa-sync mr-1"></i>Refresh',
                        className: 'btn btn-sm btn-info',
                        action: function(e, dt, node, config) {
                            table.ajax.reload();
                        }
                    }, {
                        text: '<i class="fas fa-sync mr-1"></i>Sync',
                        className: 'btn btn-sm btn-danger',
                        action: function(e, dt, node, config) {
                            syncAll();
                        }
                    }
                ],
            });

            // Filter Status via Hidden Column (Indeks Kolom Status adalah 11, karena kolom NO di index 0)
            $('#status_filter').on('change', function() {
                var val = $(this).val();
                if (val === 'all') {
                    table.column(11).search('').draw();
                } else {
                    table.column(11).search('^' + val + '$', true, false).draw();
                }
            });

            $(document).on('click', '.btn-sync', function() {
                var row = table.row($(this).parents('tr')).data();
                let code = row[3];
                let p = row[8];
                let l = row[9];
                let t = row[10];
                let b = row[11];
                let note = row[12];
                if (!code || !p || !l || !t || !b) {
                    show_message('Data tidak valid!', 'error');
                    return;
                }
                confirmation(`Sync ${code} dengan Data Produk?`, function(confirm) {
                    if (confirm) {
                        $.ajax({
                            url: URL_SYNC_PRODUCT_API,
                            type: 'POST',
                            data: {
                                code: code,
                                p: parseDecimal(p),
                                l: parseDecimal(l),
                                t: parseDecimal(t),
                                b: parseDecimal(b),
                                note: note
                            },
                            success: function(result) {
                                show_message(result.message, 'success')
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message || 'Error!')
                            }
                        })
                    }
                })
            });

            $(document).on('click', '.btn-compare', function() {
                var row = table.row($(this).parents('tr')).data();
                let code = row[3];
                if (!code) {
                    show_message('Data tidak valid!', 'error');
                    return;
                }
                $.ajax({
                    url: URL_COMPARE_PRODUCT_API.replace(':product', code),
                    type: 'GET',
                    success: function(result) {
                        console.log(result);
                        $('#pltbb_p').text(row[8]);
                        $('#pltbb_l').text(row[9]);
                        $('#pltbb_t').text(row[10]);
                        $('#pltbb_b').text(row[11]);
                        $('#pltbb_note').text(row[12]);
                        if (result.data.pltbb) {
                            $('#product_p').text(result.data.pltbb.p);
                            $('#product_l').text(result.data.pltbb.l);
                            $('#product_t').text(result.data.pltbb.t);
                            $('#product_b').text(result.data.pltbb.b);
                            $('#product_note').text(result.data.pltbb.note);
                        } else {
                            $('#product_p').text('-');
                            $('#product_l').text('-');
                            $('#product_t').text('-');
                            $('#product_b').text('-');
                            $('#product_note').text('-');
                        }
                        $('#modal_compare').modal('show');
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                })
            });

            function syncAll() {
                confirmation(`Sync All Data dengan Data Produk?`, function(confirm) {
                    if (confirm) {
                        $.ajax({
                            url: "{{ route('api.spreadsheet.sync_all') }}",
                            type: 'POST',
                            success: function(result) {
                                show_message(result.message, 'success');
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message || 'Error!')
                            }
                        })
                    }
                })
            }

        });
    </script>
@endpush
