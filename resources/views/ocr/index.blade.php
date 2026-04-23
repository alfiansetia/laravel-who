@extends('template', ['title' => 'Advanced OCR'])

@section('content')
    <div class="container-fluid px-4 py-3">
        <!-- Header with Gradient Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between p-4 rounded-lg shadow-sm border-0"
                    style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white;">
                    <div>
                        <h2 class="font-weight-bold mb-1 ml-2"><i class="fas fa-magic mr-2 text-warning"></i> OCR Logic
                            Workbench</h2>
                        <p class="mb-0 opacity-75 ml-2 font-weight-light">Ekstraksi teks dari gambar & PDF harian dengan
                            cerdas.</p>
                    </div>
                    <div class="d-none d-md-block mr-3">
                        <i class="fas fa-brain fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row d-flex align-items-stretch">
            <!-- Left Panel: Input & Preview -->
            <div class="col-xl-7">
                <div class="card card-sm border-0 shadow-lg overflow-hidden h-100 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold m-0 text-primary"><i class="fas fa-file-image mr-2"></i>SOURCE DOCUMENT
                        </h6>
                        <div class="d-flex align-items-center">
                            <div class="btn-group mr-2" id="pdfControls" style="display: none;">
                                <button class="btn btn-xs btn-outline-primary" onclick="changePage(-1)"><i
                                        class="fas fa-chevron-left"></i></button>
                                <span class="btn btn-xs btn-light disabled border-left-0 border-right-0 px-2"
                                    style="font-size: 0.7rem; line-height: 1.5;">
                                    <span id="currentPage">1</span>/<span id="totalPages">1</span>
                                </span>
                                <button class="btn btn-xs btn-outline-primary" onclick="changePage(1)"><i
                                        class="fas fa-chevron-right"></i></button>
                            </div>

                            <button class="btn btn-xs btn-outline-info d-none mr-2" id="processAllBtn"
                                onclick="processAllPages()" title="Process All Pages">
                                <i class="fas fa-layer-group mr-1"></i> All
                            </button>

                            <button class="btn btn-xs btn-success text-white mr-1 px-2" onclick="startCamera()">
                                <i class="fas fa-camera mr-1"></i> Camera
                            </button>

                            <button id="resetSourceBtn" class="btn btn-xs btn-outline-danger d-none px-2"
                                onclick="resetSource()" title="Reset Scan">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0 d-flex flex-column h-100 bg-light">
                        <!-- Drop Zone -->
                        <div id="dropZone"
                            class="flex-grow-1 d-flex flex-column align-items-center justify-content-center p-5 text-center transition-all"
                            style="border: 2px dashed #cbd5e1; margin: 20px; border-radius: 12px; cursor: pointer; background: white;">
                            <div class="icon-box mb-3 p-4 rounded-circle bg-blue-soft transition-all">
                                <i class="fas fa-cloud-upload-alt fa-4x text-primary"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">Drop your files here</h4>
                            <p class="text-secondary mb-4">Support formats: JPG, PNG, PDF</p>
                            <input type="file" id="imageUpload" accept="image/*,application/pdf" class="d-none"
                                onchange="handleFileSelect()">
                            <button class="btn btn-primary px-4 shadow"
                                onclick="document.getElementById('imageUpload').click()">
                                <i class="fas fa-search mr-2"></i> Browse Files
                            </button>
                        </div>

                        <!-- Image Display with Scanner Effect -->
                        <div id="previewWrapper" class="position-relative p-3 text-center" style="display: none;">
                            <div id="scannerLine" class="scan-line" style="display: none;"></div>
                            <img id="previewImg" class="img-fluid rounded shadow-sm border border-white"
                                style="max-height: 550px; background: white;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Extraction Results -->
            <div class="col-xl-5">
                <div class="card card-sm border-0 shadow-lg h-100 mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold m-0 text-success"><i class="fas fa-file-alt mr-2"></i>EXTRACTED
                            CONTENT
                        </h6>
                        <div class="btn-group">
                            <button class="btn btn-xs btn-light" id="copyBtn" title="Copy Text">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-xs btn-light text-danger" onclick="clearResults()" title="Clear Form">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0 d-flex flex-column h-100 bg-white">
                        <!-- Progress Overlay -->
                        <div id="progressContainer" class="p-3 border-bottom bg-light" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-primary" id="progressStatus">Initializing...</span>
                                <span class="small font-weight-bold text-primary" id="progressPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 10px;">
                                <div id="progressBar"
                                    class="progress-bar progress-bar-striped progress-bar-animated shadow-sm"
                                    role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Results Editor -->
                        <div class="flex-grow-1 position-relative">
                            <textarea id="resultText" class="form-control border-0 p-4 h-100"
                                style="resize: none; font-family: 'Consolas', 'Monaco', monospace; line-height: 1.6; font-size: 14px;"
                                placeholder="Extracting text will appear here..."></textarea>

                            <!-- Confidence Badge -->
                            <div id="confidenceBadge" class="position-absolute"
                                style="bottom: 20px; right: 20px; display: none;">
                                <div class="badge badge-pill badge-primary p-2 px-3 shadow-lg">
                                    <i class="fas fa-bullseye mr-1"></i> Confidence: <span id="confidence">-</span>%
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-3 bg-light border-top">
                            <button class="btn btn-success btn-block font-weight-bold" id="processManuallyBtn"
                                style="display:none;" onclick="processUploadedImage()">
                                <i class="fas fa-play mr-2 text-white"></i> Start Recognition
                            </button>
                            <div class="text-center small text-muted font-italic">
                                <i class="fas fa-shield-alt mr-1 text-primary"></i> Local browser processing. Your data
                                stays private.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Modal (Glassmorphism) -->
    <div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl overflow-hidden"
                style="background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(15px); border-radius: 24px;">

                <div class="modal-header border-0 pb-0 position-absolute w-100" style="z-index: 100; top: 0;">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-camera mr-2 text-info"></i>
                        Live Scan</h5>
                    <button type="button" class="close text-white opacity-100" onclick="stopCamera()">
                        <span style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0 position-relative bg-black" style="min-height: 400px;">
                    <video id="video" autoplay playsinline class="w-100 h-100"
                        style="object-fit: contain; max-height: 70vh;"></video>

                    <!-- Camera HUD -->
                    <div class="camera-hud position-absolute w-100 h-100"
                        style="top:0; pointer-events: none; border: 2px solid rgba(255,255,255,0.1);">
                        <div class="scanner-frame"></div>
                    </div>

                    <!-- Bottom Controls Bar -->
                    <div class="position-absolute w-100 py-4 px-5 d-flex justify-content-between align-items-center"
                        style="bottom: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); z-index: 50;">

                        <div class="flex-1"></div>

                        <div class="capture-trigger mr-3">
                            <button
                                class="btn btn-light rounded-circle shadow-lg p-0 d-flex align-items-center justify-content-center"
                                id="captureBtn" onclick="captureImage()"
                                style="width: 70px; height: 70px; border: 5px solid rgba(255,255,255,0.3);">
                                <div class="bg-white rounded-circle" style="width: 50px; height: 50px;"></div>
                            </button>
                        </div>

                        <button class="btn btn-outline-light rounded-circle p-0" id="switchCameraBtn"
                            onclick="switchCamera()" style="width: 45px; height: 45px; border-width: 1px; display: none;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <canvas id="canvas" style="display: none;"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

        let stream = null;
        let worker = null;
        let pdfDoc = null;
        let currentPageNum = 1;
        let totalPagesNum = 0;

        function resetSource() {
            document.getElementById('dropZone').style.display = 'flex';
            document.getElementById('previewWrapper').style.display = 'none';
            document.getElementById('resetSourceBtn').classList.add('d-none');
            document.getElementById('pdfControls').style.display = 'none';
            document.getElementById('imageUpload').value = '';
            document.getElementById('previewImg').src = '';
            pdfDoc = null;
            clearResults();
            show_message('Source cleared', 'info');
        }

        // Paste from Clipboard Handler
        window.addEventListener('paste', e => {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let index in items) {
                const item = items[index];
                if (item.kind === 'file' && item.type.includes('image')) {
                    const blob = item.getAsFile();
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        // Tampilkan Preview
                        document.getElementById('dropZone').style.display = 'none';
                        document.getElementById('previewWrapper').style.display = 'block';
                        document.getElementById('resetSourceBtn').classList.remove('d-none');
                        document.getElementById('previewImg').src = event.target.result;

                        // Auto process
                        recognizeText(event.target.result);
                    };
                    reader.readAsDataURL(blob);
                    show_message('Image pasted from clipboard!', 'info');
                }
            }
        });

        // Auto-initialize Tesseract Worker background
        initWorker();

        // Update handleFileSelect to show reset button
        const originalHandleFileSelect = handleFileSelect;
        handleFileSelect = async function() {
            await originalHandleFileSelect();
            if (document.getElementById('previewWrapper').style.display !== 'none') {
                document.getElementById('resetSourceBtn').classList.remove('d-none');
            }
        };

        // Update captureImage to show reset button
        const originalCaptureImage = captureImage;
        captureImage = async function() {
            await originalCaptureImage();
            document.getElementById('resetSourceBtn').classList.remove('d-none');
        };

        async function initWorker() {
            if (!worker) {
                worker = await Tesseract.createWorker('eng', 1, {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const progress = Math.round(m.progress * 100);
                            updateProgress(progress, 'Reading characters...');
                        } else if (m.status === 'loading tesseract core') {
                            updateProgress(5, 'Loading Core AI...');
                        }
                    }
                });
            }
            return worker;
        }

        // Drop Zone Handler
        const dz = document.getElementById('dropZone');
        dz.addEventListener('dragover', (e) => {
            e.preventDefault();
            dz.style.borderColor = '#3b82f6';
            dz.style.background = '#f8fafc';
        });
        dz.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dz.style.borderColor = '#cbd5e1';
            dz.style.background = 'white';
        });
        dz.addEventListener('drop', (e) => {
            e.preventDefault();
            dz.style.borderColor = '#cbd5e1';
            const files = e.dataTransfer.files;
            if (files.length) {
                document.getElementById('imageUpload').files = files;
                handleFileSelect();
            }
        });

        async function handleFileSelect() {
            const fileInput = document.getElementById('imageUpload');
            const file = fileInput.files[0];
            if (!file) return;

            // Show Preview Layout
            document.getElementById('dropZone').style.display = 'none';
            document.getElementById('previewWrapper').style.display = 'block';

            if (file.type === 'application/pdf') {
                await loadPDF(file);
            } else {
                document.getElementById('pdfControls').style.display = 'none';
                document.getElementById('processAllBtn').classList.add('d-none');

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('previewImg');
                    img.src = e.target.result;
                    // Auto process
                    recognizeText(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }

        async function loadPDF(file) {
            try {
                const arrayBuffer = await file.arrayBuffer();
                pdfDoc = await pdfjsLib.getDocument({
                    data: arrayBuffer
                }).promise;
                totalPagesNum = pdfDoc.numPages;
                currentPageNum = 1;

                document.getElementById('totalPages').textContent = totalPagesNum;
                document.getElementById('currentPage').textContent = currentPageNum;
                document.getElementById('pdfControls').style.display = 'flex';
                document.getElementById('processAllBtn').classList.remove('d-none');

                await renderPDFPage(currentPageNum);
            } catch (error) {
                show_message('Error loading PDF: ' + error.message, 'error');
            }
        }

        async function renderPDFPage(pageNum, autoProcess = true) {
            const page = await pdfDoc.getPage(pageNum);
            const scale = 2; // High resolution
            const viewport = page.getViewport({
                scale
            });
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;
            const imageDataUrl = canvas.toDataURL('image/png');
            document.getElementById('previewImg').src = imageDataUrl;

            if (autoProcess) recognizeText(imageDataUrl);
            return imageDataUrl;
        }

        async function changePage(delta) {
            const newPage = currentPageNum + delta;
            if (newPage < 1 || newPage > totalPagesNum) return;
            currentPageNum = newPage;
            document.getElementById('currentPage').textContent = currentPageNum;
            await renderPDFPage(currentPageNum);
        }

        async function processAllPages() {
            let allText = '';
            clearResults();
            document.getElementById('progressContainer').style.display = 'block';

            for (let i = 1; i <= totalPagesNum; i++) {
                updateProgress(Math.round((i / totalPagesNum) * 100), `Scanning page ${i}/${totalPagesNum}...`);
                const imageData = await renderPDFPage(i, false);
                const tesseractWorker = await initWorker();
                const {
                    data: {
                        text
                    }
                } = await tesseractWorker.recognize(imageData);
                allText += `\n\n[PAGE ${i}]\n${text}`;
            }

            document.getElementById('resultText').value = allText.trim();
            document.getElementById('progressContainer').style.display = 'none';
            document.getElementById('confidenceBadge').style.display = 'none';
        }

        async function recognizeText(imageData) {
            try {
                clearResults();
                document.getElementById('progressContainer').style.display = 'block';
                document.getElementById('scannerLine').style.display = 'block';
                updateProgress(10, 'Waking up AI...');

                const tesseractWorker = await initWorker();
                const {
                    data: {
                        text,
                        confidence
                    }
                } = await tesseractWorker.recognize(imageData);

                document.getElementById('resultText').value = text;
                document.getElementById('confidence').textContent = confidence.toFixed(1);
                document.getElementById('confidenceBadge').style.display = 'block';
                document.getElementById('progressContainer').style.display = 'none';
                document.getElementById('scannerLine').style.display = 'none';

                if (text.trim().length > 0) show_message('Scanning complete!', 'success');
            } catch (error) {
                show_message('Error: ' + error.message, 'error');
                document.getElementById('progressContainer').style.display = 'none';
                document.getElementById('scannerLine').style.display = 'none';
            }
        }

        let currentFacingMode = 'environment';
        let videoDevices = [];
        let currentDeviceIndex = 0;

        async function startCamera() {
            try {
                // Get all video devices
                const devices = await navigator.mediaDevices.enumerateDevices();
                videoDevices = devices.filter(device => device.kind === 'videoinput');

                if (videoDevices.length > 1) {
                    document.getElementById('switchCameraBtn').style.display = 'block';
                }

                const constraints = {
                    video: {
                        facingMode: currentFacingMode
                    }
                };

                stream = await navigator.mediaDevices.getUserMedia(constraints);
                document.getElementById('video').srcObject = stream;
                $('#cameraModal').modal('show');
            } catch (err) {
                show_message('Camera error: ' + err.message, 'error');
            }
        }

        async function switchCamera() {
            if (videoDevices.length < 2) return;

            // Stop current stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Toggle facing mode
            currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';

            // Alternatively use specific device ID if available (optional enhancement)
            // But facingMode is usually better for mobile

            const constraints = {
                video: {
                    facingMode: currentFacingMode
                }
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                document.getElementById('video').srcObject = stream;
                show_message('Camera switched', 'success');
            } catch (err) {
                show_message('Switch failed: ' + err.message, 'error');
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                $('#cameraModal').modal('hide');
            }
        }

        async function captureImage() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            const data = canvas.toDataURL('image/png');
            document.getElementById('previewImg').src = data;
            document.getElementById('dropZone').style.display = 'none';
            document.getElementById('previewWrapper').style.display = 'block';

            stopCamera();
            recognizeText(data);
        }

        function updateProgress(percent, status) {
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressPercent').textContent = percent + '%';
            document.getElementById('progressStatus').textContent = status;
        }

        function clearResults() {
            document.getElementById('resultText').value = '';
            document.getElementById('confidenceBadge').style.display = 'none';
        }

        $('#copyBtn').click(function() {
            let txt = document.getElementById('resultText').value;
            copyToClipboard(txt);
        });
    </script>
@endpush

@push('css')
    <style>
        .bg-blue-soft {
            background-color: rgba(59, 130, 246, 0.08);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .rounded-lg {
            border-radius: 1rem !important;
        }

        /* Scanner Laser Animation */
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, transparent, #3b82f6, transparent);
            box-shadow: 0 0 15px #3b82f6;
            z-index: 10;
            animation: scan 2s linear infinite;
        }

        @keyframes scan {
            0% {
                top: 10%;
            }

            50% {
                top: 90%;
            }

            100% {
                top: 10%;
            }
        }

        #dropZone:hover {
            transform: scale(1.01);
            background-color: #f1f5f9 !important;
            border-color: #3b82f6 !important;
        }

        #dropZone:hover i {
            transform: translateY(-5px);
            color: #1d4ed8;
        }

        .camera-hud {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scanner-frame {
            width: 80%;
            height: 60%;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            position: relative;
            box-shadow: 0 0 0 5000px rgba(0, 0, 0, 0.5);
        }

        .scanner-frame::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            width: 40px;
            height: 40px;
            border-top: 4px solid #3b82f6;
            border-left: 4px solid #3b82f6;
            border-top-left-radius: 15px;
        }

        .scanner-frame::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 40px;
            height: 40px;
            border-top: 4px solid #3b82f6;
            border-right: 4px solid #3b82f6;
            border-top-right-radius: 15px;
        }

        #switchCameraBtn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(180deg);
            transition: all 0.4s ease;
        }

        .bg-black {
            background-color: #000 !important;
        }

        .btn-xs {
            padding: 0.15rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>
@endpush
