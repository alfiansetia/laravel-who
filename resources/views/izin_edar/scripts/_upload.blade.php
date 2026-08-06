<script>
    // ── Client-Side Excel Upload & Import ─────────────────
    // Uses SheetJS (xlsx.js) to parse Excel files in the browser,
    // then sends extracted data to the API in batches.
    // Zero server-side memory usage for Excel processing.

    const BATCH_SIZE = 500; // rows per API request

    // Header column mapping: Excel header name → DB column
    const HEADER_TRANSLATION = {
        'NOMOR': 'nomor_izin_edar',
        'TGL TERBIT': 'tgl_terbit',
        'TGL EXP': 'tgl_exp',
        'MERK': 'merk',
        'JENIS PRODUK': 'jenis_produk',
        'PENDAFTAR': 'pendaftar',
        'ALAMAT PENDAFTAR': 'alamat_pendaftar',
        'PABRIK': 'pabrik',
        'ALAMAT PABRIK': 'alamat_pabrik',
        'SUB KATEGORI': 'sub_kategori',
        'KELOMPOK PRODUK': 'kelompok_produk',
        'TIPE': 'tipe',
        'KELAS': 'kelas',
        'KELAS RESIKO': 'kelas_resiko',
        'PABRIK2': 'pabrik2',
    };

    let importCancelled = false;

    // ── Upload Modal Controls ────────────────────────────

    function showUploadModal() {
        $('#uploadForm')[0].reset();
        $('#uploadKategoriSelect .kategori-badge').removeClass('active');
        $('#uploadFileInput').val('');
        $('#uploadPlaceholder').show();
        $('#uploadFilePreview').hide();
        $('#uploadDropzone').removeClass('has-file');
        $('#importProgressWrapper').hide();
        $('#uploadKategoriError').hide();
        $('#uploadFileError').hide();
        $('#btnDoUpload').prop('disabled', true);
        importCancelled = false;
        $('#modalUpload').modal('show');
    }

    // Kategori radio selection
    $('#uploadKategoriSelect').on('click', '.kategori-badge', function() {
        $('#uploadKategoriSelect .kategori-badge').removeClass('active');
        $(this).addClass('active');
        $(this).find('input[type="radio"]').prop('checked', true);
        $('#uploadKategoriError').hide();
        validateUploadForm();
    });

    // Prevent file input click from bubbling to dropzone
    $('#uploadFileInput').on('click', function(e) {
        e.stopPropagation();
    });

    // Click on dropzone to open file picker
    $('#uploadDropzone').on('click', function() {
        $('#uploadFileInput').click();
    });

    // Drag & drop support
    $('#uploadDropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $('#uploadDropzone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    $('#uploadDropzone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $('#uploadFileInput')[0].files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change
    $('#uploadFileInput').on('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    function handleFileSelect(file) {
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ];
        const ext = file.name.split('.').pop().toLowerCase();

        if (!validTypes.includes(file.type) && !['xlsx', 'xls'].includes(ext)) {
            $('#uploadFileError').text('Format file tidak valid. Gunakan .xlsx atau .xls').show();
            $('#uploadFileInput').val('');
            $('#uploadPlaceholder').show();
            $('#uploadFilePreview').hide();
            $('#uploadDropzone').removeClass('has-file');
            validateUploadForm();
            return;
        }

        if (file.size > 200 * 1024 * 1024) { // 200MB — browser can handle larger files
            $('#uploadFileError').text('Ukuran file maksimal 200MB').show();
            $('#uploadFileInput').val('');
            $('#uploadPlaceholder').show();
            $('#uploadFilePreview').hide();
            $('#uploadDropzone').removeClass('has-file');
            validateUploadForm();
            return;
        }

        $('#uploadFileError').hide();
        $('#uploadPlaceholder').hide();
        $('#uploadFileName').text(file.name);
        $('#uploadFileSize').text(formatFileSize(file.size));
        $('#uploadFilePreview').show();
        $('#uploadDropzone').addClass('has-file');
        validateUploadForm();
    }

    function validateUploadForm() {
        const hasKategori = $('#uploadKategoriSelect input[type="radio"]:checked').length > 0;
        const hasFile = $('#uploadFileInput')[0].files.length > 0;
        $('#btnDoUpload').prop('disabled', !(hasKategori && hasFile));
    }

    // ── Main Client-Side Import Flow ─────────────────────

    async function doClientImport() {
        const kategori = $('#uploadKategoriSelect input[type="radio"]:checked').val();
        const file = $('#uploadFileInput')[0].files[0];

        if (!kategori) {
            $('#uploadKategoriError').text('Pilih kategori terlebih dahulu.').show();
            return;
        }
        if (!file) {
            $('#uploadFileError').text('Pilih file Excel terlebih dahulu.').show();
            return;
        }

        // Check if sync is already running
        try {
            const log = await $.ajax({
                url: SYNC_PROGRESS_URL,
                type: 'GET',
                isBlocking: false
            });
            if (log && log.is_running) {
                $('#modalUpload').modal('hide');
                showSyncInProgressToast('A sync is already in progress.');
                $('#syncCard').slideDown(300);
                startPolling();
                return;
            }
        } catch (e) {
            // If check fails, proceed anyway
        }

        // Disable controls during import
        importCancelled = false;
        $('#btnDoUpload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        $('#btnCloseUpload').prop('disabled', true);
        $('#importProgressWrapper').slideDown(200);
        updateImportProgress(0, 'Membaca file Excel di browser...');

        try {
            // Step 1: Read file as ArrayBuffer
            const arrayBuffer = await readFileAsArrayBuffer(file);

            if (importCancelled) return resetUploadForm();

            updateImportProgress(5, 'Mem-parse file Excel...');

            // Step 2: Parse with SheetJS
            const workbook = XLSX.read(arrayBuffer, {
                type: 'array',
                cellDates: true, // Return Date objects for date cells
                cellText: false, // Don't force text
                raw: false, // Get formatted text
            });

            // Free the ArrayBuffer
            arrayBuffer.byteLength; // reference to prevent premature GC

            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];

            if (!worksheet) {
                throw new Error('Sheet tidak ditemukan dalam file Excel.');
            }

            updateImportProgress(10, 'Mengkonversi ke JSON...');

            // Step 3: Convert to JSON (array of arrays)
            const allRows = XLSX.utils.sheet_to_json(worksheet, {
                header: 1,
                defval: null,
                blankrows: false,
            });

            // Free worksheet memory
            delete workbook.Sheets[sheetName];
            workbook.SheetNames = [];

            if (allRows.length === 0) {
                throw new Error('File Excel kosong.');
            }

            updateImportProgress(15, 'Mencari header...');

            // Step 4: Find header row
            let headerRowIndex = -1;
            let headerMap = {};

            for (let i = 0; i < Math.min(allRows.length, 100); i++) {
                const row = allRows[i];
                if (!row) continue;

                const normalized = row.map(h => String(h || '').trim().toUpperCase());

                if (normalized.includes('NOMOR') || normalized.includes('MERK')) {
                    headerRowIndex = i;

                    // Map column index → DB column name
                    for (let colIdx = 0; colIdx < normalized.length; colIdx++) {
                        const headerName = normalized[colIdx];
                        if (HEADER_TRANSLATION[headerName]) {
                            headerMap[colIdx] = HEADER_TRANSLATION[headerName];
                        }
                    }
                    break;
                }
            }

            if (headerRowIndex === -1) {
                throw new Error('Header row tidak ditemukan. Pastikan file memiliki kolom "NOMOR" atau "MERK".');
            }

            // Step 5: Extract data rows (skip header and rows above it)
            const dataRows = [];
            for (let i = headerRowIndex + 1; i < allRows.length; i++) {
                const row = allRows[i];
                if (!row) continue;

                // Skip empty rows
                const hasData = row.some(v => v !== null && v !== undefined && String(v).trim() !== '');
                if (!hasData) continue;

                const record = {};
                for (const [colIdx, dbCol] of Object.entries(headerMap)) {
                    let value = row[parseInt(colIdx)] ?? null;

                    // Trim strings
                    if (typeof value === 'string') {
                        value = value.trim();
                        if (value === '') value = null;
                    }

                    // Format dates
                    if (value instanceof Date) {
                        value = formatDateISO(value);
                    }

                    record[dbCol] = value;
                }

                // Skip if no nomor_izin_edar
                if (!record.nomor_izin_edar || String(record.nomor_izin_edar).trim() === '') {
                    continue;
                }

                dataRows.push(record);
            }

            // Free allRows memory
            allRows.length = 0;

            if (dataRows.length === 0) {
                throw new Error('Tidak ada data valid yang ditemukan setelah header.');
            }

            updateImportProgress(20,
                `${dataRows.length.toLocaleString('id-ID')} baris ditemukan. Mengirim ke server...`);

            // Step 6: Send in batches
            const totalRows = dataRows.length;
            const totalBatches = Math.ceil(totalRows / BATCH_SIZE);
            let imported = 0;
            let failedBatches = 0;

            for (let batchIdx = 0; batchIdx < totalBatches; batchIdx++) {
                if (importCancelled) break;

                const start = batchIdx * BATCH_SIZE;
                const end = Math.min(start + BATCH_SIZE, totalRows);
                const batch = dataRows.slice(start, end);

                const progressPct = 20 + Math.round(((batchIdx) / totalBatches) * 80);
                updateImportProgress(
                    progressPct,
                    `Batch ${batchIdx + 1}/${totalBatches} — ${imported.toLocaleString('id-ID')} / ${totalRows.toLocaleString('id-ID')} baris`
                );

                try {
                    const res = await $.ajax({
                        url: IMPORT_BATCH_URL,
                        isBlocking: false,
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            kategori: kategori,
                            rows: batch,
                        }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                    });

                    if (res.success) {
                        imported += res.imported || 0;
                    } else {
                        failedBatches++;
                        console.error(`Batch ${batchIdx + 1} failed:`, res.message);
                    }
                } catch (xhr) {
                    failedBatches++;
                    console.error(`Batch ${batchIdx + 1} error:`, xhr.responseJSON || xhr.statusText);
                    // Continue with next batch instead of aborting
                }
            }

            // Free dataRows memory
            dataRows.length = 0;

            if (importCancelled) {
                show_message('Import dibatalkan.', 'warning');
                resetUploadForm();
                return;
            }

            // Done!
            updateImportProgress(100, `Selesai! ${imported.toLocaleString('id-ID')} baris berhasil diimport.`);

            setTimeout(() => {
                $('#modalUpload').modal('hide');
                const msg = failedBatches > 0 ?
                    `${imported.toLocaleString('id-ID')} baris diimport (${failedBatches} batch gagal).` :
                    `${imported.toLocaleString('id-ID')} baris berhasil diimport untuk kategori ${kategori}.`;
                show_message(msg, failedBatches > 0 ? 'warning' : 'success');
                loadData();
                resetUploadForm();
            }, 1000);

        } catch (error) {
            show_message('Error: ' + (error.message || 'Gagal memproses file.'), 'error');
            resetUploadForm();
        }
    }

    // ── Helper Functions ─────────────────────────────────

    function readFileAsArrayBuffer(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.onerror = () => reject(new Error('Gagal membaca file.'));
            reader.readAsArrayBuffer(file);
        });
    }

    function formatDateISO(date) {
        if (!date || !(date instanceof Date) || isNaN(date.getTime())) return null;
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function updateImportProgress(pct, text) {
        pct = Math.min(100, Math.max(0, pct));
        $('#importProgressBar').css('width', pct + '%').text(pct + '%');
        $('#importProgressPct').text(pct + '%');
        if (text) {
            $('#importProgressText').text(text);
        }
    }

    function resetUploadForm() {
        $('#btnDoUpload').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Parse & Import');
        $('#btnCloseUpload').prop('disabled', false);
        $('#importProgressWrapper').slideUp(200);
        $('#importProgressBar').css('width', '0%').text('0%');
    }
</script>
