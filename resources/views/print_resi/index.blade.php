@extends('template', ['title' => 'Import Resi'])

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <style>
        .dropzone {
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
        }

        .dropzone:hover {
            background: #e9ecef;
            border-color: #0056b3;
        }

        .dropzone .dz-message {
            font-weight: 500;
            color: #495057;
        }

        .dropzone .dz-message i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
        }

        #imageContainer {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .image-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: #fff;
            border: 1px solid #eee;
        }

        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .image-card img {
            width: 100%;
            height: 160px;
            object-fit: contain;
            background: #fdfdfd;
            padding: 10px;
        }

        .image-card-body {
            padding: 8px 12px;
            background: #fff;
            border-top: 1px solid #eee;
        }

        .image-card-title {
            font-size: 11px;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
        }

        .crop-btn {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            z-index: 10;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 10px;
            z-index: 10;
            cursor: pointer;
        }

        .empty-state {
            grid-column: 1 / -1;
            padding: 40px;
            text-align: center;
            color: #adb5bd;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }

        .cropper-container-wrapper {
            max-height: 450px;
            overflow: hidden;
            background: #000;
        }

        #cropperImage {
            max-width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-weight-bold text-primary">
                            <i class="fas fa-file-excel mr-2"></i>Import Data Resi
                        </h5>
                    </div>
                    <div class="card-body py-4">
                        <div id="excel-dropzone" class="dropzone">
                            <div class="dz-message">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Drop file Excel (.xlsx) di sini atau klik untuk upload</span>
                                <p class="text-muted small mt-2">Hanya file .xlsx yang didukung untuk ekstraksi gambar</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="{{ route('qc_lots.index') }}" class="btn btn-light px-4">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                            <div id="action_buttons" style="display: none;">
                                <button type="button" id="btn-reset" class="btn btn-outline-danger mr-2">
                                    <i class="fas fa-trash-alt mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-primary px-4 btn-print-all">
                                    <i class="fas fa-print mr-1"></i> Print Semua
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">
                            <i class="fas fa-images mr-2 text-info"></i>Preview Gambar Resi
                        </h5>
                        <span class="badge badge-pill badge-primary" id="image-count" style="display:none;">0 Gambar</span>
                    </div>
                    <div class="card-body">
                        <div id="imageContainer">
                            <div class="empty-state">
                                <i class="fas fa-parachute-box fa-3x mb-3"></i>
                                <p>Belum ada gambar yang diekstrak. Silakan upload file Excel terlebih dahulu.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crop -->
    <div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-labelledby="cropModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropModalLabel">Potong Gambar (Crop)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="cropper-container-wrapper">
                        <img id="cropperImage" src="" alt="Image to crop">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-crop">Simpan Potongan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        Dropzone.autoDiscover = false;

        $(document).ready(function() {
            let extractedImages = [];
            let cropper = null;
            let currentCropId = null;

            const myDropzone = new Dropzone("#excel-dropzone", {
                url: "/",
                autoProcessQueue: false,
                acceptedFiles: ".xlsx",
                maxFiles: 1,
                dictInvalidFileType: "Format file tidak didukung. Harap gunakan .xlsx (Excel modern)",
                init: function() {
                    this.on("addedfile", async function(file) {
                        const extension = file.name.split('.').pop().toLowerCase();
                        if (extension === 'xls') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Format .xls Terdeteksi',
                                text: 'File .xls tidak mendukung ekstraksi di browser. Harap Save As sebagai .xlsx.',
                            });
                            this.removeFile(file);
                            return;
                        }
                        if (this.files.length > 1) this.removeFile(this.files[0]);
                        await handleFileExtraction(file);
                    });
                }
            });

            async function handleFileExtraction(file) {
                $('#imageContainer').html(
                    '<div class="empty-state text-primary"><i class="fas fa-spinner fa-spin fa-3x mb-3"></i><p>Membaca seluruh lembar kerja...</p></div>'
                    );
                try {
                    const zip = await JSZip.loadAsync(file);
                    extractedImages = [];
                    const drawings = [];

                    // 1. Dapatkan daftar file drawing
                    const drawingFiles = Object.keys(zip.files).filter(name => name.startsWith(
                        'xl/drawings/drawing') && name.endsWith('.xml'));

                    for (const dFile of drawingFiles) {
                        // 2. Dapatkan file relasi (.rels) untuk drawing ini secara spesifik
                        const dNum = dFile.match(/drawing(\d+)\.xml/)[1];
                        const relFile = `xl/drawings/_rels/drawing${dNum}.xml.rels`;

                        let currentRels = {};
                        if (zip.files[relFile]) {
                            const relContent = await zip.files[relFile].async('string');
                            const relXml = new DOMParser().parseFromString(relContent, 'application/xml');
                            const relNodes = relXml.getElementsByTagName('Relationship');
                            for (const rel of relNodes) {
                                currentRels[rel.getAttribute('Id')] = rel.getAttribute('Target').replace(
                                    '../media/', 'xl/media/');
                            }
                        }

                        // 3. Baca konten drawing
                        const dContent = await zip.files[dFile].async('string');
                        const dXml = new DOMParser().parseFromString(dContent, 'application/xml');
                        const pics = dXml.getElementsByTagNameNS('*', 'pic');

                        for (const pic of pics) {
                            const blip = pic.getElementsByTagNameNS('*', 'blip')[0];
                            if (!blip) continue;
                            const rId = blip.getAttributeNS(
                                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                                'embed');
                            const mediaPath = currentRels[rId];
                            if (!mediaPath || !zip.files[mediaPath]) continue;

                            const srcRect = pic.getElementsByTagNameNS('*', 'srcRect')[0];
                            const crop = {
                                l: 0,
                                t: 0,
                                r: 0,
                                b: 0
                            };
                            if (srcRect) {
                                crop.l = (parseInt(srcRect.getAttribute('l') || 0) / 100000);
                                crop.t = (parseInt(srcRect.getAttribute('t') || 0) / 100000);
                                crop.r = (parseInt(srcRect.getAttribute('r') || 0) / 100000);
                                crop.b = (parseInt(srcRect.getAttribute('b') || 0) / 100000);
                            }
                            drawings.push({
                                mediaPath,
                                crop
                            });
                        }
                    }

                    // 4. Proses Ekstraksi Akhir
                    if (drawings.length === 0) {
                        // Fallback jika tidak ada drawing metadata (ambil semua gambar folder media)
                        const mediaFiles = Object.keys(zip.files).filter(name => name.startsWith('xl/media/'));
                        for (const mPath of mediaFiles) {
                            const blob = await zip.files[mPath].async('blob');
                            const url = URL.createObjectURL(blob);
                            addToList(url, blob, mPath.split('/').pop());
                        }
                    } else {
                        for (const draw of drawings) {
                            const blob = await zip.files[draw.mediaPath].async('blob');
                            const originalUrl = URL.createObjectURL(blob);

                            if (draw.crop.l > 0 || draw.crop.t > 0 || draw.crop.r > 0 || draw.crop.b > 0) {
                                const croppedBlob = await applyExcelCrop(originalUrl, draw.crop);
                                const finalUrl = URL.createObjectURL(croppedBlob);
                                URL.revokeObjectURL(originalUrl);
                                addToList(finalUrl, croppedBlob, draw.mediaPath.split('/').pop());
                            } else {
                                addToList(originalUrl, blob, draw.mediaPath.split('/').pop());
                            }
                        }
                    }

                    renderGallery();
                } catch (e) {
                    console.error(e);
                    $('#imageContainer').html(
                        '<div class="empty-state text-danger"><p>Gagal memproses file Excel.</p></div>');
                }
            }

            function addToList(url, blob, name) {
                const id = 'img-' + Math.random().toString(36).substr(2, 9);
                extractedImages.push({
                    id,
                    url,
                    blob,
                    name
                });
            }

            function renderGallery() {
                let html = '';
                if (extractedImages.length === 0) {
                    $('#imageContainer').html(
                        '<div class="empty-state text-warning"><p>Tidak ada gambar ditemukan.</p></div>');
                    $('#action_buttons, #image-count').hide();
                } else {
                    extractedImages.forEach(img => {
                        html += `
                            <div class="image-card" id="${img.id}">
                                <button type="button" class="delete-btn" data-id="${img.id}"><i class="fas fa-trash-alt"></i></button>
                                <button type="button" class="crop-btn" data-id="${img.id}"><i class="fas fa-crop"></i> CROP</button>
                                <img src="${img.url}" />
                                <div class="image-card-body"><p class="image-card-title">${img.name}</p></div>
                            </div>`;
                    });
                    $('#imageContainer').html(html);
                    $('#image-count').text(extractedImages.length + ' Gambar').show();
                    $('#action_buttons').fadeIn();
                }
            }

            async function applyExcelCrop(url, crop) {
                return new Promise((resolve) => {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        const cropLeft = img.width * crop.l;
                        const cropTop = img.height * crop.t;
                        const cropRight = img.width * crop.r;
                        const cropBottom = img.height * crop.b;

                        const targetWidth = Math.max(1, img.width - cropLeft - cropRight);
                        const targetHeight = Math.max(1, img.height - cropTop - cropBottom);

                        canvas.width = targetWidth;
                        canvas.height = targetHeight;

                        ctx.drawImage(img, cropLeft, cropTop, targetWidth, targetHeight, 0, 0,
                            targetWidth, targetHeight);
                        canvas.toBlob(blob => resolve(blob), 'image/png');
                    };
                    img.src = url;
                });
            }

            // Modal Cropper Initialization Logic
            $(document).on('click', '.crop-btn', function(e) {
                e.preventDefault();
                currentCropId = $(this).data('id');
                const imgData = extractedImages.find(i => i.id === currentCropId);
                if (imgData) {
                    $('#cropperImage').attr('src', imgData.url);
                    $('#cropModal').modal({
                        backdrop: 'static',
                        show: true
                    });
                }
            });

            $('#cropModal').on('shown.bs.modal', function() {
                const img = document.getElementById('cropperImage');
                if (cropper) cropper.destroy();
                cropper = new Cropper(img, {
                    viewMode: 2,
                    autoCropArea: 1,
                    responsive: true,
                    checkOrientation: false
                });
            }).on('hidden.bs.modal', function() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                $('#cropperImage').attr('src', '');
            });

            $('#btn-save-crop').on('click', function() {
                if (!cropper) return;
                const canvas = cropper.getCroppedCanvas({
                    imageSmoothingQuality: 'high'
                });
                canvas.toBlob(blob => {
                    const newUrl = URL.createObjectURL(blob);
                    const index = extractedImages.findIndex(i => i.id === currentCropId);
                    if (index > -1) {
                        URL.revokeObjectURL(extractedImages[index].url);
                        extractedImages[index].url = newUrl;
                        extractedImages[index].blob = blob;
                        $(`#${currentCropId} img`).attr('src', newUrl);
                    }
                    $('#cropModal').modal('hide');
                }, 'image/png');
            });

            // Action Print & Delete
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                const idx = extractedImages.findIndex(i => i.id === id);
                if (idx > -1) {
                    URL.revokeObjectURL(extractedImages[idx].url);
                    extractedImages.splice(idx, 1);
                    $('#' + id).fadeOut(300, function() {
                        $(this).remove();
                        $('#image-count').text(extractedImages.length + ' Gambar');
                    });
                }
            });

            $('#btn-reset').on('click', function() {
                extractedImages.forEach(i => URL.revokeObjectURL(i.url));
                extractedImages = [];
                myDropzone.removeAllFiles();
                $('#imageContainer').html('<div class="empty-state"><p>Gambar kosong.</p></div>');
                $('#action_buttons, #image-count').hide();
            });

            $('.btn-print-all').on('click', function() {
                if (extractedImages.length === 0) return;
                const win = window.open('', '_blank');
                win.document.write(
                    '<html><head><title>Print</title><style>img{max-width:100%;margin-bottom:20px;display:block;page-break-after:always;}</style></head><body>'
                    );
                extractedImages.forEach(i => win.document.write(`<img src="${i.url}">`));
                win.document.write('</body></html>');
                win.document.close();
                win.print();
            });
        });
    </script>
@endpush
