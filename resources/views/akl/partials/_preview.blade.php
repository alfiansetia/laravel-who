{{-- Preview dokumen AKL.
     PDF tampil penuh (semua halaman, bisa scroll) via viewer bawaan browser,
     jadi teksnya bisa diseleksi & dicopy langsung. Gambar via <img>.
     Dipakai di create (file terpilih) & edit (file saat ini + file pengganti).
     Variabel opsional: $currentUrl (string|null), $isPdf (bool). --}}
<div class="border rounded text-center" id="aklPreviewBox" style="background:#f8fafc; min-height:200px;">
    <img id="aklPreviewImg" class="img-fluid d-none" style="max-height:70vh;" alt="Preview dokumen">
    <embed id="aklPreviewPdf" type="application/pdf" class="d-none w-100" style="height:70vh;">
    <div id="aklPreviewEmpty" class="text-muted small py-5">
        <i class="fas fa-file-alt fa-2x mb-2"></i><br>Belum ada file dipilih.
    </div>
</div>
<p class="text-muted small mt-1 mb-0">
    <i class="fas fa-info-circle mr-1"></i>Untuk PDF berisi teks, seleksi &amp; copy langsung dari preview di atas sambil mengisi form.
</p>

@push('js')
    <script>
        var aklObjectUrl = null;

        function aklPreviewHide() {
            if (aklObjectUrl) {
                URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = null;
            }
            $('#aklPreviewImg').addClass('d-none').attr('src', '');
            $('#aklPreviewPdf').addClass('d-none').attr('src', '');
            $('#aklPreviewEmpty').removeClass('d-none');
        }

        function aklPreviewShowImg(src) {
            if (aklObjectUrl) {
                URL.revokeObjectURL(aklObjectUrl);
                aklObjectUrl = null;
            }
            $('#aklPreviewPdf').addClass('d-none').attr('src', '');
            $('#aklPreviewEmpty').addClass('d-none');
            $('#aklPreviewImg').removeClass('d-none').attr('src', src);
        }

        function aklPreviewShowPdf(url, revokeAfter) {
            $('#aklPreviewImg').addClass('d-none').attr('src', '');
            $('#aklPreviewEmpty').addClass('d-none');
            var $embed = $('#aklPreviewPdf');
            $embed.removeClass('d-none').attr('src', url);
            if (revokeAfter) {
                $embed.one('load', function() {
                    if (aklObjectUrl) {
                        URL.revokeObjectURL(aklObjectUrl);
                        aklObjectUrl = null;
                    }
                });
            }
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
