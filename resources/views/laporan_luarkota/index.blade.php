@extends('template', ['title' => 'Import Kiriman Luar Kota'])

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
    <style>
        .dropzone-custom {
            border: 2px dashed #007bff;
            border-radius: 12px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            cursor: pointer;
            padding: 20px;
        }

        .dropzone-custom:hover {
            background: #e9ecef;
            border-color: #0056b3;
        }

        .dropzone-custom .dz-message {
            font-weight: 500;
            color: #495057;
            text-align: center;
        }

        .dropzone-custom .dz-message i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 10px;
            display: block;
        }

        #sheet_buttons .btn {
            margin-bottom: 5px;
            text-transform: none;
            font-weight: 500;
        }

        .dz-preview {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-5">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><i class="fas fa-file-excel mr-1"></i>Import Data Kiriman Luar Kota</h4>
                    </div>
                    <div class="card-body pb-5">
                        <form action="javascript:void(0);">
                            <div class="form-group">
                                <label class="font-weight-bold">Upload File Excel</label>
                                <div id="excel-dropzone" class="dropzone-custom">
                                    <div class="dz-message">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Drop file Excel (.xlsx/.xls) di sini atau klik</span>
                                    </div>
                                </div>
                            </div>
                            <div id="sheet_section" style="display: none;">
                                <div class="form-group mt-2">
                                    <label>Pilih Sheet (Klik untuk Load):</label>
                                    <div id="sheet_buttons" class="d-flex flex-wrap" style="gap: 5px;"></div>
                                </div>
                            </div>
                            <a href="{{ route('home') }}" class="btn btn-secondary mt-2">
                                <i class="fas fa-arrow-left mr-1"></i>Kembali
                            </a>
                            <button type="reset" class="btn btn-info mt-2">
                                <i class="fas fa-redo mr-1"></i>Ulangi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><i class="fas fa-clipboard mr-1"></i>Template Kirim Pesan</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea id="template" class="form-control" rows="5" placeholder="Paste Text"></textarea>
                        </div>
                        <div class="form-group" id="variable_template"></div>
                        <button type="button" id="btn_reset_template" class="btn btn-warning mt-2">
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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        Dropzone.autoDiscover = false;

        const columns = [
            "tgl_kirim", "no_do", "tgl_do", "customer", "area", "no_telp",
            "ekspedisi", "jenis_kiriman", "tgl_estimasi", "tgl_real",
            "tgl_confirm", "confirm_with", "con_brg_y", "con_brg_n",
            "con_qty_y", "con_qty_n", "no_resi", "jenis_barang", "koli",
            "berat_estimasi", "berat_real", "ongkir_estimasi", "ongkir_real"
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
Terima kasih.`;

        $(document).ready(function() {
            columns.forEach(element => {
                $('#variable_template').append(`<span class="badge badge-success m-1">{${element}}</span>`)
            });

            loadLocal();

            function loadLocal() {
                let template = localStorage.getItem('template') || defaultTemplate;
                $('#template').val(template);
            }

            function saveLocal() {
                localStorage.setItem('template', $('#template').val() || defaultTemplate);
                show_message('Tersimpan', 'success');
            }

            function saveDefaultLocal() {
                localStorage.setItem('template', defaultTemplate);
                loadLocal();
                show_message('Tersimpan', 'success');
            }

            function parsePhoneNumber(phone) {
                if (!phone) return null;
                phone = String(phone).trim().replace(/[^0-9+]/g, '');
                if (phone.startsWith('+62')) phone = phone.slice(1);
                if (phone.startsWith('08')) phone = '62' + phone.slice(1);
                return phone.startsWith('62') ? phone : phone;
            }

            function excelDateToString(serial, separator = '/') {
                const excelEpoch = Date.UTC(1899, 11, 30);
                const date = new Date(excelEpoch + serial * 86400000);
                const day = String(date.getUTCDate()).padStart(2, '0');
                const month = String(date.getUTCMonth() + 1).padStart(2, '0');
                const year = date.getUTCFullYear();
                return `${day}${separator}${month}${separator}${year}`;
            }

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
                return template.replace(/{(.*?)}/g, (_, key) => (data[key] === undefined || data[key] === null) ?
                    '' : data[key]);
            }

            const table = $("#table").DataTable({
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                columns: [{
                        data: "tgl_kirim",
                        title: "Tgl Kirim"
                    },
                    {
                        data: "no_do",
                        title: "No DO"
                    },
                    {
                        data: "tgl_do",
                        title: "Tgl DO"
                    },
                    {
                        data: "customer",
                        title: "Cust"
                    },
                    {
                        data: "area",
                        title: "Area"
                    },
                    {
                        data: "no_telp",
                        title: "Telp"
                    },
                    {
                        data: "ekspedisi",
                        title: "Ekspd"
                    },
                    {
                        data: "jenis_kiriman",
                        title: "Jenis"
                    },
                    {
                        data: "tgl_estimasi",
                        title: "Tgl Est"
                    },
                    {
                        data: "tgl_real",
                        title: "Tgl Real"
                    },
                    {
                        data: "tgl_confirm",
                        title: "Tgl Conf"
                    },
                    {
                        data: "confirm_with",
                        title: "Conf With"
                    },
                    {
                        data: "con_brg_y",
                        title: "Con Brg Y",
                        visible: false
                    },
                    {
                        data: "con_brg_n",
                        title: "Con Brg N",
                        visible: false
                    },
                    {
                        data: "con_qty_y",
                        title: "Con Qty Y",
                        visible: false
                    },
                    {
                        data: "con_qty_n",
                        title: "Con Qty N",
                        visible: false
                    },
                    {
                        data: "no_resi",
                        title: "No Resi"
                    },
                    {
                        data: "jenis_barang",
                        title: "Jenis Brg"
                    },
                    {
                        data: "koli",
                        title: "Koli"
                    },
                    {
                        data: "berat_estimasi",
                        title: "Berat Est",
                        visible: false
                    },
                    {
                        data: "berat_real",
                        title: "Berat Real",
                        visible: false
                    },
                    {
                        data: "ongkir_estimasi",
                        title: "Ongkir Est",
                        visible: false
                    },
                    {
                        data: "ongkir_real",
                        title: "Ongkir Real",
                        visible: false
                    },
                    {
                        title: "#",
                        data: 'id',
                        width: '30px',
                        className: "text-center",
                        render: () => `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-danger del-item"><i class="fas fa-trash"></i></button>
                                <button class="btn btn-sm btn-success wa-item"><i class="fab fa-whatsapp"></i></button>
                            </div>`
                    }
                ],
                buttons: [{
                        extend: "colvis",
                        className: 'btn btn-sm btn-primary'
                    },
                    {
                        extend: "pageLength",
                        className: 'btn btn-sm btn-info'
                    },
                    {
                        text: '<i class="fas fa-trash mr-1"></i>Hapus Data',
                        className: 'btn btn-sm btn-danger',
                        action: () => table.clear().draw()
                    }
                ],
                drawCallback: (settings) => $('#total').text(settings.fnRecordsDisplay())
            });

            const myDropzone = new Dropzone("#excel-dropzone", {
                url: "/",
                autoProcessQueue: false,
                acceptedFiles: ".xlsx,.xls",
                maxFiles: 1,
                init: function() {
                    this.on("addedfile", (file) => {
                        if (this.files.length > 1) this.removeFile(this.files[0]);
                        handleFileSelection(file);
                    });
                }
            });

            let currentWorkbook = null;

            function handleFileSelection(file) {
                bloc();
                const reader = new FileReader();
                reader.onload = (e) => {
                    const data = new Uint8Array(e.target.result);
                    currentWorkbook = XLSX.read(data, {
                        type: 'array'
                    });
                    const sheetBtnContainer = $('#sheet_buttons').empty();
                    currentWorkbook.SheetNames.forEach(name => {
                        sheetBtnContainer.append(
                            `<button type="button" class="btn btn-outline-primary btn-load-sheet" data-sheet="${name}"><i class="fas fa-file-excel mr-1"></i>${name}</button>`
                        );
                    });
                    $('#sheet_section').fadeIn();
                    unbloc();
                };
                reader.readAsArrayBuffer(file);
            }

            $('#sheet_buttons').on('click', '.btn-load-sheet', function() {
                if (!currentWorkbook) return;
                bloc();
                $('.btn-load-sheet').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');

                const sheet = currentWorkbook.Sheets[$(this).data('sheet')];
                const json = XLSX.utils.sheet_to_json(sheet, {
                    header: 1,
                    defval: null,
                    blankrows: false
                });
                const result = [];
                let id = 1;

                json.forEach(row => {
                    if (!Number.isInteger(row[0])) return;
                    if (![0, 1, 2, 3].every(i => row[i] && String(row[i]).trim() !== "")) return;

                    let obj = {
                        id: id++
                    };
                    columns.forEach((col, i) => {
                        let val = row[i] ?? null;
                        if (['tgl_kirim', 'tgl_do', 'tgl_estimasi', 'tgl_real',
                                'tgl_confirm'
                            ].includes(col)) {
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
                });

                table.clear().rows.add(result).draw();
                unbloc();
                show_message(result.length < 1 ? 'Tidak ada data valid!' :
                    `Berhasil Import ${result.length} Data`, result.length < 1 ? 'error' : 'success');
            });

            $('#btn_delete_template').click(saveLocal);
            $('#btn_reset_template').click(saveDefaultLocal);

            $('button[type="reset"]').click(() => {
                $('#sheet_section').hide();
                $('#sheet_buttons').empty();
                table.clear().draw();
                currentWorkbook = null;
                myDropzone.removeAllFiles();
            });

            $('#table').on('click', '.del-item', function() {
                table.row($(this).parents('tr')).remove().draw();
            });

            $('#table').on('click', '.wa-item', function() {
                const data = table.row($(this).parents('tr')).data();
                if (!data.no_telp) return show_message('No telp Tidak valid!', 'error');
                window.open(
                    `https://api.whatsapp.com/send/?phone=${data.no_telp}&text=${encodeURIComponent(parseTemplate(data))}`,
                    '_blank');
            });
        });
    </script>
@endpush
