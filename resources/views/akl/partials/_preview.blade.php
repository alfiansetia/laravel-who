{{-- Preview dokumen AKL (gambar via <img>, PDF halaman pertama via PDF.js).
     Dipakai di create (file terpilih) & edit (file saat ini + file pengganti).
     Variabel opsional: $currentUrl (string|null), $isPdf (bool). --}}
<div class="form-group d-none" id="aklPreviewGroup">
    <label>Preview Dokumen</label>
    <div class="border rounded p-2 text-center" style="background:#f8fafc;">
        <img id="aklPreviewImg" class="img-fluid d-none" style="max-height:320px;" alt="Preview dokumen">
        <div id="aklPreviewPdf" class="d-none text-left mx-auto"
            style="max-width:100%;max-height:320px;overflow-y:auto;background:#525659;border-radius:4px;"></div>
        <p id="aklPreviewNote" class="text-muted small mb-0 mt-1 d-none"></p>
    </div>
</div>

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        var AKL_FORM_PDF_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        var aklObjectUrl = null;

        function aklPreviewShowImg(src) {
            $('#aklPreviewPdf').addClass('d-none').empty();
            $('#aklPreviewNote').addClass('d-none').text('');
            $('#aklPreviewImg').removeClass('d-none').attr('src', src);
            $('#aklPreviewGroup').removeClass('d-none');
        }

        function aklPreviewHide() {
            if (aklObjectUrl) {
                URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = null;
            }
            $('#aklPreviewGroup').addClass('d-none');
            $('#aklPreviewImg').addClass('d-none').attr('src', '');
            $('#aklPreviewPdf').addClass('d-none').empty();
            $('#aklPreviewNote').addClass('d-none').text('');
        }

        function aklPreviewRenderPdf(url, done) {
            var wrap = $('#aklPreviewPdf');
            wrap.html(
                '<div class="text-center text-white py-4"><i class="fas fa-spinner fa-spin"></i><p class="mt-2 mb-0 small">Memuat PDF...</p></div>'
            );

            if (typeof pdfjsLib === 'undefined') {
                wrap.html(
                    '<p class="text-center text-white py-4 mb-0 small">Preview tidak tersedia, file tetap bisa diupload.</p>'
                );
                if (done) done();
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = AKL_FORM_PDF_WORKER;
            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                wrap.empty();
                return pdf.getPage(1).then(function(page) {
                    var scale = (wrap.width() - 20) / page.getViewport({
                        scale: 1
                    }).width;
                    var viewport = page.getViewport({
                        scale: scale
                    });
                    var canvas = document.createElement('canvas');
                    canvas.style.display = 'block';
                    canvas.style.margin = '0 auto';
                    canvas.style.maxWidth = '100%';
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    wrap.append(canvas);
                    return page.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport
                    }).promise.then(function() {
                        if (pdf.numPages > 1) {
                            $('#aklPreviewNote').removeClass('d-none')
                                .text('Halaman 1 dari ' + pdf.numPages + ' — preview penuh ada di tabel.');
                        }
                    });
                });
            }).catch(function(err) {
                console.error('PDF.js gagal:', err);
                wrap.html(
                    '<p class="text-center text-white py-4 mb-0 small">Gagal preview PDF, file tetap bisa diupload.</p>'
                );
            }).finally(function() {
                if (done) done();
            });
        }

        function aklPreviewShowPdf(url, revokeAfter) {
            $('#aklPreviewImg').addClass('d-none').attr('src', '');
            $('#aklPreviewNote').addClass('d-none').text('');
            $('#aklPreviewPdf').removeClass('d-none');
            $('#aklPreviewGroup').removeClass('d-none');
            aklPreviewRenderPdf(url, function() {
                if (revokeAfter && aklObjectUrl) {
                    URL.revokeObjectURL(aklObjectUrl);
                    aklObjectUrl = null;
                }
            });
        }

        // Preview file yang baru dipilih (belum diupload): baca lokal via browser.
        window.aklPreviewSelected = function(file) {
            aklPreviewHide();
            if (!file) return;
            var ext = (file.name.split('.').pop() || '').toLowerCase();
            if (ext === 'pdf') {
                if (aklObjectUrl) URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = URL.createObjectURL(file);
                aklPreviewShowPdf(aklObjectUrl, true);
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
                aklPreviewShowPdf(url, false);
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
