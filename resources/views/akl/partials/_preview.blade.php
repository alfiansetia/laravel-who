{{-- Preview dokumen AKL via PDF.js (tidak memicu IDM) + TextLayer
     supaya teks PDF bisa diseleksi & dicopy. Gambar via <img>.
     Dipakai di create (file terpilih) & edit (file saat ini + file pengganti).
     Variabel opsional: $currentUrl (string|null), $isPdf (bool). --}}
<div class="border rounded" id="aklPreviewBox" style="background:#f8fafc; min-height:200px; max-height:70vh; overflow-y:auto;">
    <img id="aklPreviewImg" class="img-fluid d-none mx-auto" style="max-height:70vh;" alt="Preview dokumen">
    <div id="aklPreviewPdf"></div>
    <div id="aklPreviewEmpty" class="text-muted small py-5 text-center">
        <i class="fas fa-file-alt fa-2x mb-2"></i><br>Belum ada file dipilih.
    </div>
</div>
<p class="text-muted small mt-1 mb-0">
    <i class="fas fa-info-circle mr-1"></i>Untuk PDF berisi teks, seleksi &amp; copy langsung dari preview di atas sambil mengisi form.
</p>

@push('css')
    <style>
        /* PDF.js TextLayer — lapisan teks transparan di atas canvas
           supaya teks PDF bisa diseleksi & dicopy */
        .akl-pdf-page {
            position: relative;
            margin: 10px auto;
        }
        .akl-pdf-page canvas {
            display: block;
        }
        .textLayer {
            position: absolute;
            left: 0; top: 0; right: 0; bottom: 0;
            overflow: hidden;
            opacity: 1;
            line-height: 1;
        }
        .textLayer span, .textLayer br {
            color: transparent;
            position: absolute;
            white-space: pre;
            cursor: text;
            transform-origin: 0% 0%;
        }
        .textLayer .highlight {
            margin: -1px; padding: 1px;
            background-color: rgb(180 0 170 / 30%);
            border-radius: 4px;
        }
        .textLayer .highlight.selected {
            background-color: rgb(0 100 0 / 30%);
        }
        .textLayer ::selection { background: rgb(0 0 255 / 30%); }
        .textLayer .endOfContent {
            display: block; position: absolute;
            left: 0; top: 100%; right: 0; bottom: 0;
            z-index: -1; cursor: default; user-select: none;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        var AKL_PREVIEW_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        var AKL_PREVIEW_MAX_PAGES = 20;
        var aklObjectUrl = null;

        function aklPreviewBoxMode(pdf) {
            $('#aklPreviewBox').css('background', pdf ? '#525659' : '#f8fafc');
        }

        function aklPreviewHide() {
            if (aklObjectUrl) {
                URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = null;
            }
            $('#aklPreviewImg').addClass('d-none').attr('src', '');
            $('#aklPreviewPdf').empty();
            $('#aklPreviewEmpty').removeClass('d-none');
            aklPreviewBoxMode(false);
        }

        function aklPreviewShowImg(src) {
            if (aklObjectUrl) {
                URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = null;
            }
            $('#aklPreviewPdf').empty();
            $('#aklPreviewEmpty').addClass('d-none');
            $('#aklPreviewImg').removeClass('d-none').attr('src', src);
            aklPreviewBoxMode(false);
        }

        function aklPreviewShowPdf(url) {
            var wrap = $('#aklPreviewPdf');
            $('#aklPreviewImg').addClass('d-none').attr('src', '');
            $('#aklPreviewEmpty').addClass('d-none');
            aklPreviewBoxMode(true);
            wrap.html(
                '<div class="text-center text-white py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 mb-0">Memuat PDF...</p></div>'
            );

            if (typeof pdfjsLib === 'undefined') {
                wrap.html(
                    '<p class="text-center text-white py-5 mb-0 small">Preview tidak tersedia. File tetap bisa disimpan.</p>'
                );
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = AKL_PREVIEW_WORKER;
            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                var total = pdf.numPages;
                var show = Math.min(total, AKL_PREVIEW_MAX_PAGES);
                wrap.empty();

                var chain = Promise.resolve();
                for (var n = 1; n <= show; n++) {
                    (function(pageNum) {
                        chain = chain.then(function() {
                            return pdf.getPage(pageNum).then(function(page) {
                                var scale = (wrap.width() - 20) / page.getViewport({
                                    scale: 1
                                }).width;
                                var viewport = page.getViewport({
                                    scale: scale
                                });
                                var pageDiv = document.createElement('div');
                                pageDiv.className = 'akl-pdf-page';
                                pageDiv.style.width = viewport.width + 'px';
                                var canvas = document.createElement('canvas');
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;
                                pageDiv.appendChild(canvas);
                                var textLayerDiv = document.createElement('div');
                                textLayerDiv.className = 'textLayer';
                                pageDiv.appendChild(textLayerDiv);
                                wrap.append(pageDiv);
                                return page.render({
                                    canvasContext: canvas.getContext('2d'),
                                    viewport: viewport
                                }).promise.then(function() {
                                    return pdfjsLib.renderTextLayer({
                                        textContentSource: page.streamTextContent(),
                                        container: textLayerDiv,
                                        viewport: viewport,
                                        enhanceTextSelection: true
                                    }).promise.catch(function(e) {
                                        console.warn('TextLayer gagal:', e);
                                    });
                                });
                            });
                        });
                    })(n);
                }
                chain.then(function() {
                    if (total > show) {
                        wrap.append(
                            '<p class="text-center text-white py-2 mb-0">... + ' + (total - show) +
                            ' halaman lagi. <a href="' + url +
                            '" target="_blank" class="text-warning">Buka penuh di tab baru</a>.</p>'
                        );
                    }
                });
            }).catch(function(err) {
                console.error('PDF.js gagal:', err);
                wrap.html(
                    '<p class="text-center text-white py-5 mb-0 small">Gagal preview PDF. File tetap bisa disimpan.</p>'
                );
            });
        }

        // Preview file yang baru dipilih (belum diupload): baca lokal via browser.
        // Pakai object URL + pdf.js (XHR), jadi tidak memicu IDM.
        window.aklPreviewSelected = function(file) {
            aklPreviewHide();
            if (!file) return;
            var ext = (file.name.split('.').pop() || '').toLowerCase();
            if (ext === 'pdf') {
                if (aklObjectUrl) URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = URL.createObjectURL(file);
                aklPreviewShowPdf(aklObjectUrl);
            } else {
                var reader = new FileReader();
                reader.onload = function(e) {
                    aklPreviewShowImg(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        };

        // Preview file yang sudah tersimpan (dipakai halaman edit).
        window.aklPreviewRemote = function(url, isPdf) {
            aklPreviewHide();
            if (!url) return;
            if (isPdf) {
                aklPreviewShowPdf(url);
            } else {
                aklPreviewShowImg(url);
            }
        };
    </script>
    @if (!empty($currentUrl ?? null))
        <script>
            $(function() {
                aklPreviewRemote(@json($currentUrl), {{ !empty($isPdf) ? 'true' : 'false' }});
            });
        </script>
    @endif
@endpush
