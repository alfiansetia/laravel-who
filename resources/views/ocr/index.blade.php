@extends('template', ['title' => 'OCR Tool'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-text-width"></i> OCR - Text Recognition</h4>
                    </div>
                    <div class="card-body">
                        <!-- Upload Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                                        <h5>Upload Image / PDF</h5>
                                        <input type="file" id="imageUpload" accept="image/*,application/pdf"
                                            class="form-control-file mb-3" onchange="handleFileSelect()">

                                        <!-- PDF Page Selector (Hidden by default) -->
                                        <div id="pdfPageSelector" style="display: none;" class="mb-3">
                                            <label class="font-weight-bold">Select Page:</label>
                                            <div class="d-flex align-items-center justify-content-center"
                                                style="gap: 10px;">
                                                <button class="btn btn-sm btn-outline-primary" onclick="changePage(-1)">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <span class="mx-3">
                                                    Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                                                </span>
                                                <button class="btn btn-sm btn-outline-primary" onclick="changePage(1)">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </div>
                                            <button class="btn btn-info btn-sm mt-2" onclick="processAllPages()">
                                                <i class="fas fa-layer-group"></i> Process All Pages
                                            </button>
                                        </div>

                                        <button class="btn btn-primary btn-block" onclick="processUploadedImage()">
                                            <i class="fas fa-cog"></i> <span id="processButtonText">Process Image</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-camera fa-3x text-success mb-3"></i>
                                        <h5>Capture from Camera</h5>
                                        <button class="btn btn-success btn-block mb-2" onclick="startCamera()">
                                            <i class="fas fa-video"></i> Start Camera
                                        </button>
                                        <button class="btn btn-warning btn-block" id="captureBtn" onclick="captureImage()"
                                            disabled>
                                            <i class="fas fa-camera"></i> Capture & Process
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Preview (Hidden by default) -->
                        <div id="cameraContainer" class="mb-4" style="display: none;">
                            <div class="card">
                                <div
                                    class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-video"></i> Camera Preview</span>
                                    <button class="btn btn-sm btn-danger" onclick="stopCamera()">
                                        <i class="fas fa-stop"></i> Stop Camera
                                    </button>
                                </div>
                                <div class="card-body text-center">
                                    <video id="video" autoplay playsinline
                                        style="max-width: 100%; border: 2px solid #28a745; border-radius: 8px;"></video>
                                    <canvas id="canvas" style="display: none;"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="mb-4" style="display: none;">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <i class="fas fa-image"></i> Image Preview
                                </div>
                                <div class="card-body text-center">
                                    <img id="previewImg"
                                        style="max-width: 100%; max-height: 400px; border: 2px solid #17a2b8; border-radius: 8px;">
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div id="progressContainer" class="mb-4" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-2">Processing...</h6>
                                    <div class="progress" style="height: 25px;">
                                        <div id="progressBar"
                                            class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                            role="progressbar" style="width: 0%">0%</div>
                                    </div>
                                    <small id="progressStatus" class="text-muted mt-2 d-block">Initializing...</small>
                                </div>
                            </div>
                        </div>

                        <!-- Results -->
                        <div id="resultsContainer" style="display: none;">
                            <div class="card border-success">
                                <div
                                    class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-check-circle"></i> Extracted Text</span>
                                    <button class="btn btn-sm btn-light" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                                <div class="card-body">
                                    <textarea id="resultText" class="form-control" rows="10" style="font-family: monospace; font-size: 14px;"></textarea>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Confidence: <span id="confidence" class="font-weight-bold">-</span>%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script>
        // Set PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

        let stream = null;
        let worker = null;
        let pdfDoc = null;
        let currentPageNum = 1;
        let totalPagesNum = 0;

        // Initialize Tesseract Worker
        async function initWorker() {
            if (!worker) {
                worker = await Tesseract.createWorker('eng', 1, {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const progress = Math.round(m.progress * 100);
                            updateProgress(progress, m.status);
                        }
                    }
                });
            }
            return worker;
        }

        // Handle File Selection
        async function handleFileSelect() {
            const fileInput = document.getElementById('imageUpload');
            const file = fileInput.files[0];

            if (!file) return;

            if (file.type === 'application/pdf') {
                await loadPDF(file);
            } else {
                // Hide PDF controls for images
                document.getElementById('pdfPageSelector').style.display = 'none';
                document.getElementById('processButtonText').textContent = 'Process Image';
            }
        }

        // Load PDF
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
                document.getElementById('pdfPageSelector').style.display = 'block';
                document.getElementById('processButtonText').textContent = 'Process Current Page';

                await renderPDFPage(currentPageNum);
            } catch (error) {
                alert('Error loading PDF: ' + error.message);
            }
        }

        // Render PDF Page
        async function renderPDFPage(pageNum) {
            const page = await pdfDoc.getPage(pageNum);
            const scale = 2;
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
            document.getElementById('imagePreview').style.display = 'block';

            return imageDataUrl;
        }

        // Change PDF Page
        async function changePage(delta) {
            const newPage = currentPageNum + delta;
            if (newPage < 1 || newPage > totalPagesNum) return;

            currentPageNum = newPage;
            document.getElementById('currentPage').textContent = currentPageNum;
            await renderPDFPage(currentPageNum);
        }

        // Process All PDF Pages
        async function processAllPages() {
            if (!pdfDoc) {
                alert('No PDF loaded!');
                return;
            }

            let allText = '';
            document.getElementById('progressContainer').style.display = 'block';
            document.getElementById('resultsContainer').style.display = 'none';

            for (let i = 1; i <= totalPagesNum; i++) {
                updateProgress(Math.round((i / totalPagesNum) * 100), `Processing page ${i} of ${totalPagesNum}...`);

                const imageData = await renderPDFPage(i);
                const tesseractWorker = await initWorker();
                const {
                    data: {
                        text
                    }
                } = await tesseractWorker.recognize(imageData);

                allText += `\n\n=== Page ${i} ===\n\n${text}`;
            }

            document.getElementById('resultText').value = allText.trim();
            document.getElementById('confidence').textContent = 'Multi-page';
            document.getElementById('resultsContainer').style.display = 'block';
            document.getElementById('progressContainer').style.display = 'none';
        }

        // Process Uploaded Image or Current PDF Page
        async function processUploadedImage() {
            const fileInput = document.getElementById('imageUpload');
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select a file first!');
                return;
            }

            if (file.type === 'application/pdf') {
                // Process current PDF page
                const imageData = await renderPDFPage(currentPageNum);
                await recognizeText(imageData);
            } else {
                // Process image file
                const reader = new FileReader();
                reader.onload = async function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    await recognizeText(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }

        // Start Camera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment'
                    }
                });
                const video = document.getElementById('video');
                video.srcObject = stream;
                document.getElementById('cameraContainer').style.display = 'block';
                document.getElementById('captureBtn').disabled = false;
            } catch (err) {
                alert('Error accessing camera: ' + err.message);
            }
        }

        // Stop Camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                document.getElementById('cameraContainer').style.display = 'none';
                document.getElementById('captureBtn').disabled = true;
            }
        }

        // Capture Image from Camera
        async function captureImage() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);

            const imageDataUrl = canvas.toDataURL('image/png');
            document.getElementById('previewImg').src = imageDataUrl;
            document.getElementById('imagePreview').style.display = 'block';

            stopCamera();
            await recognizeText(imageDataUrl);
        }

        // Recognize Text using Tesseract
        async function recognizeText(imageData) {
            try {
                document.getElementById('progressContainer').style.display = 'block';
                document.getElementById('resultsContainer').style.display = 'none';
                updateProgress(0, 'Initializing OCR...');

                const tesseractWorker = await initWorker();

                updateProgress(10, 'Loading image...');
                const {
                    data: {
                        text,
                        confidence
                    }
                } = await tesseractWorker.recognize(imageData);

                document.getElementById('resultText').value = text;
                document.getElementById('confidence').textContent = confidence.toFixed(2);
                document.getElementById('resultsContainer').style.display = 'block';
                document.getElementById('progressContainer').style.display = 'none';

            } catch (error) {
                alert('Error processing image: ' + error.message);
                document.getElementById('progressContainer').style.display = 'none';
            }
        }

        // Update Progress Bar
        function updateProgress(percent, status) {
            const progressBar = document.getElementById('progressBar');
            const progressStatus = document.getElementById('progressStatus');
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
            progressStatus.textContent = status;
        }

        // Copy to Clipboard
        function copyToClipboard() {
            const resultText = document.getElementById('resultText');
            resultText.select();
            document.execCommand('copy');

            // Show feedback
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-light');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-light');
            }, 2000);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopCamera();
            if (worker) {
                worker.terminate();
            }
        });
    </script>
@endpush

@push('css')
    <style>
        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        #video {
            max-height: 400px;
        }
    </style>
@endpush
