@extends('template', ['title' => 'Import Kiriman Luar Kota'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><i class="fas fa-file-excel mr-1"></i>Import Data Kiriman Luar Kota</h4>
                    </div>
                    <div class="card-body pb-5">
                        <form action="">
                            <div class="form-group">
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
                        </form>

                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><i class="fas fa-clipboard mr-1"></i>Template Kirim Pesan</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="template">Template Kirim Pesan</label>
                            <textarea id="template" class="form-control" rows="5" placeholder="Paste Text"></textarea>
                        </div>
                        <div class="form-group" id="variable_template"></div>
                        <button type="bßutton" id="btn_reset_template" class="btn btn-warning mt-2">
                            <i class="fas fa-sync mr-1"></i>Reset
                        </button>
                        <button type="button" id="btn_delete_template" class="btn btn-primary mt-2">
                            <i class="fas fa-save mr-1"></i>Save
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12">
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
        const columns = [
            "tgl_kirim",
            "no_do",
            "tgl_do",
            "customer",
            "area",
            "no_telp",
            "ekspedisi",
            "jenis_kiriman",
            "tgl_estimasi",
            "tgl_real",
            "tgl_confirm",
            "confirm_with",
            "con_brg_y",
            "con_brg_n",
            "con_qty_y",
            "con_qty_n",
            "no_resi",
            "jenis_barang",
            "koli",
            "berat_estimasi",
            "berat_real",
            "ongkir_estimasi",
            "ongkir_real"
        ];

        const defaultTemplate = `Selamat Siang Bpk/Ibu *{confirm_with}*,
Perkenalkan saya dari *PT Mitra Asa Pratama* ingin mengonfirmasi kiriman berikut
No Do : *{no_do}*
Tgl Kirim : *{tgl_kirim}*
Customer/Area : *{customer} / {area}*
Ekspedisi/Jenis : *{ekspedisi} / {jenis_kiriman}*
Jumlah Koli : *{koli}*
Isi Kiriman : *{jenis_barang}*
Apakah kiriman tersebut sudah diterima dan sesuai dengan surat jalan/DO Bpk/Ibu ?
Mohon konfirmasinya
Terima kasih.`

        $(document).ready(function() {
            columns.forEach(element => {
                $('#variable_template').append(`<span class="badge badge-success m-1">{${element}}</span>`)
            });

            loadLocal()


            function loadLocal() {
                let template = localStorage.getItem('template')
                if (!template) {
                    template = defaultTemplate
                }
                $('#template').val(template)
            }

            function saveDefaultLocal() {
                localStorage.setItem('template', defaultTemplate)
                loadLocal()
                show_message('Tersimpan', 'success')
            }

            function saveLocal() {
                let template = $('#template').val()
                if (!template) {
                    template = defaultTemplate
                }
                localStorage.setItem('template', template)
                show_message('Tersimpan', 'success')
            }

            function parsePhoneNumber(phone) {
                if (!phone) return null;

                // hapus karakter selain angka dan +
                phone = String(phone).trim().replace(/[^0-9+]/g, '');

                // +62 -> 62
                if (phone.startsWith('+62')) {
                    phone = phone.slice(1);
                }

                // 08 -> 628
                if (phone.startsWith('08')) {
                    phone = '62' + phone.slice(1);
                }

                // kalau sudah 62 biarkan
                if (phone.startsWith('62')) {
                    return phone;
                }

                return phone;
            }

            function excelDateToString(serial, separator = '/') {
                const excelEpoch = new Date(1899, 11, 30);
                const date = new Date(excelEpoch.getTime() + serial * 86400000);

                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();

                return `${day}${separator}${month}${separator}${year}`;
            }

            $('#btn_delete_template').click(function() {
                saveLocal()
            })

            $('#btn_reset_template').click(function() {
                saveDefaultLocal()
            })

            function parseToDecimal(value, digit = 2) {
                if (!value) return 0;
                return Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: digit,
                    maximumFractionDigits: digit
                });
            }

            function parseTemplate(data) {
                let template = $('#template').val();
                if (!template) return '';

                return template.replace(/{(.*?)}/g, (_, key) => {
                    if (data[key] === undefined || data[key] === null) return '';
                    return data[key];
                });
            }

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
                        data: "tgl_kirim",
                        title: "Tgl Kirim",
                    }, {
                        data: "no_do",
                        title: "No DO",
                    }, {
                        data: "tgl_do",
                        title: "Tgl DO",
                    }, {
                        data: "customer",
                        title: "Cust",
                    }, {
                        data: "area",
                        title: "Area",
                    }, {
                        data: "no_telp",
                        title: "Telp",
                    }, {
                        data: "ekspedisi",
                        title: "Ekspd",
                    }, {
                        data: "jenis_kiriman",
                        title: "Jenis",
                    }, {
                        data: "tgl_estimasi",
                        title: "Tgl Est",
                    }, {
                        data: "tgl_real",
                        title: "Tgl Real",
                    }, {
                        data: "tgl_confirm",
                        title: "Tgl Conf",
                    }, {
                        data: "confirm_with",
                        title: "Conf With",
                    }, {
                        data: "con_brg_y",
                        title: "Con Brg Y",
                        visible: false,
                    }, {
                        data: "con_brg_n",
                        title: "Con Brg N",
                        visible: false,
                    }, {
                        data: "con_qty_y",
                        title: "Con Qty Y",
                        visible: false,
                    }, {
                        data: "con_qty_n",
                        title: "Con Qty N",
                        visible: false,
                    }, {
                        data: "no_resi",
                        title: "No Resi",
                    }, {
                        data: "jenis_barang",
                        title: "Jenis Brg",
                    }, {
                        data: "koli",
                        title: "Koli",
                    }, {
                        data: "berat_estimasi",
                        title: "Berat Est",
                        visible: false,
                    }, {
                        data: "berat_real",
                        title: "Berat Real",
                        visible: false,
                    }, {
                        data: "ongkir_estimasi",
                        title: "Ongkir Est",
                        visible: false,
                    }, {
                        data: "ongkir_real",
                        title: "Ongkir Real",
                        visible: false,
                    },
                    {
                        title: "#",
                        data: 'id',
                        width: '30px',
                        className: "text-center",
                        render: function(data, type, row) {
                            return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                            <button class="btn btn-sm btn-danger del-item"><i class="fas fa-trash mr-1"></i></button>
                            <button class="btn btn-sm btn-success wa-item"><i class="fab fa-whatsapp mr-1"></i></button>
                            </div>`;
                        }
                    },

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
                    },
                ],
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
                bloc()
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
                unbloc()
            });

            // Handle klik tombol sheet
            $('#sheet_buttons').on('click', '.btn-load-sheet', function() {
                if (!currentWorkbook) return;
                bloc()

                let sheetName = $(this).data('sheet');

                // Ubah styling tombol yang aktif
                $('.btn-load-sheet').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');

                let sheet = currentWorkbook.Sheets[sheetName];

                // Konversi ke JSON
                let json = XLSX.utils.sheet_to_json(sheet, {
                    header: 1,
                    defval: null,
                    blankrows: false
                });
                let result = [];
                let id = 1;
                console.log(json);

                for (let row of json) {

                    // ambil kolom 0 - 22
                    let values = [];
                    for (let i = 0; i <= 22; i++) {
                        values[i] = row[i] ?? null;
                    }

                    // validasi
                    if (!Number.isInteger(values[0])) continue;
                    if (![0, 1, 2, 3].every(i => values[i] && String(values[i]).trim() !== "")) continue;

                    let obj = {
                        id: id++
                    };

                    columns.forEach((col, i) => {

                        let val = values[i];

                        if (['tgl_kirim', 'tgl_do', 'tgl_estimasi', 'tgl_real', 'tgl_confirm']
                            .includes(col)) {

                            obj[col] = val ? excelDateToString(val) : null;

                        } else if (col === 'no_telp') {

                            obj[col] = parsePhoneNumber(val);

                        } else if (['berat_estimasi', 'berat_real'].includes(col)) {

                            obj[col] = val ? parseToDecimal(val) : 0;

                        } else {

                            obj[col] = val;

                        }

                    });

                    result.push(obj);
                }
                console.log(result);

                $.fn.dataTable.ext.errMode = 'none';

                table.on('error.dt', function(e, settings, techNote, message) {
                    // console.log('An error has been reported by DataTables: ', message);
                });
                table.clear().draw();
                table.rows.add(result).draw();
                unbloc()
                if (result.length < 1) {
                    show_message('Tidak ada data yang valid!', 'error')
                    return
                } else {
                    show_message(`Berhasil Import ${result.length} Data`, 'success')
                }
            });

            $('button[type="reset"]').click(function() {
                $('#sheet_section').hide();
                $('#sheet_buttons').empty();
                table.clear().draw();
                currentWorkbook = null;
            });

            $('#btn_import_text').click(function() {
                let text = $('#import_text').val().trim();
                if (!text) {
                    show_message('Please enter some text to import!', 'error');
                    return;
                }

                let rows = text.split('\n');
                let count = 0;

                rows.forEach((row) => {
                    if (row.trim() !== "") {
                        let cols = row.split('\t');
                        // Mapping disamakan dengan Excel:
                        // element[2] = Product, element[4] = Lot, element[5] = ED, 
                        // element[12] = Date, element[13] = QC Note, element[14] = QC By

                        let product = cols[2] ? cols[2].trim() : '';
                        let lot = cols[4] ? cols[4].trim() : '';
                        let ed = cols[5] ? cols[5].trim() : '';
                        let dateRaw = cols[12] ? cols[12].trim() : '';
                        let qc_note = cols[13] ? cols[13].trim() : '';
                        let qc_by = cols[14] ? cols[14].trim() : '';

                        // Standardisasi tanggal (Wajib Valid)
                        let date = '';
                        if (dateRaw) {
                            let mDate = moment(dateRaw, ['YYYY-MM-DD', 'DD-MM-YYYY', 'DD/MM/YYYY'],
                                true);
                            if (mDate.isValid()) {
                                date = mDate.format('YYYY-MM-DD');
                            }
                        }

                        // Hanya masukkan jika Product, Lot, dan Date valid tersedia
                        if (product && lot && date) {
                            table.row.add({
                                product: product,
                                lot: lot,
                                ed: ed,
                                date: date,
                                qc_by: qc_by,
                                qc_note: qc_note,
                            });
                            count++;
                        }
                    }
                });

                table.draw();
                show_message(`Successfully imported ${count} items from text!`, 'success');
            });

            function save_data() {
                var data = table
                    .rows()
                    .data().toArray();
                if (data.length < 1) {
                    show_message('No data to import!', 'error');
                    return;
                }
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
                console.log(table.row(row).data());
                table
                    .row($(this).parents('tr'))
                    .remove()
                    .draw();
            });

            $('#table').on('click', '.wa-item', function() {
                let row = $(this).parents('tr')[0];
                let data = table.row(row).data()
                let text = parseTemplate(data)
                let phone = data.no_telp
                let text_encode = encodeURIComponent(text);
                if (!data.no_telp) {
                    show_message('No telp Tidak valid!', 'error')
                    return
                }
                window.open(`https://api.whatsapp.com/send/?phone=${phone}&text=${text_encode}`, '_blank')
            });

            $('#btn_delete_text').click(function() {
                $('#import_text').val('')
            })
        })
    </script>
@endpush
