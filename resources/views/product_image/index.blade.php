@extends('template', ['title' => 'Product Images'])

@push('css')
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        :root {
            --pi-primary: #4b6cb7;
            --pi-primary-light: #6b8cd7;
            --pi-danger: #e74c3c;
            --pi-bg: #f8f9fa;
            --pi-card-radius: 12px;
            --pi-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --pi-shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        /* Skeleton Loading Animation */
        .skeleton {
            background: linear-gradient(90deg, #e2e8f0 25%, #edf2f7 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-card {
            background: #fff;
            border-radius: var(--pi-card-radius);
            padding: 16px;
            margin-bottom: 1rem;
            box-shadow: var(--pi-shadow);
        }

        .skeleton-header {
            height: 20px;
            width: 60%;
            margin-bottom: 16px;
        }

        .skeleton-badge {
            height: 24px;
            width: 80px;
            border-radius: 12px;
        }

        .skeleton-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        .skeleton-img {
            aspect-ratio: 1/1;
            border-radius: 8px;
        }

        /* Filter Bar */
        .pi-filter-bar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 16px;
            border-radius: var(--pi-card-radius);
            margin-bottom: 1.25rem;
            box-shadow: var(--pi-shadow);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: sticky;
            top: 10px;
            z-index: 100;
        }

        .pi-filter-bar .form-control {
            border-radius: 8px;
            font-size: 0.875rem;
        }

        /* Product Card */
        .pi-product-card {
            background: #fff;
            border-radius: var(--pi-card-radius);
            box-shadow: var(--pi-shadow);
            margin-bottom: 1.25rem;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
            animation: fadeInUp 0.4s ease backwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pi-product-card:hover {
            box-shadow: var(--pi-shadow-hover);
        }

        .pi-card-header {
            background: #fafbfc;
            border-bottom: 1px solid #f0f0f0;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pi-card-header h6 {
            margin: 0;
            font-weight: 700;
            font-size: 0.9rem;
            color: #2d3436;
        }

        .pi-card-header h6 i {
            color: var(--pi-primary);
            margin-right: 6px;
        }

        .pi-card-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pi-badge-count {
            background: #eef2ff;
            color: var(--pi-primary);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Gallery Grid */
        .pi-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            padding: 14px;
        }

        .pi-gallery-item {
            position: relative;
            aspect-ratio: 1/1;
            border-radius: 8px;
            overflow: hidden;
            background: #f1f3f5;
            border: 1px solid #edf2f7;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .pi-gallery-item:hover {
            transform: scale(1.04);
            z-index: 2;
        }

        .pi-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .pi-delete-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(231, 76, 60, 0.9);
            color: #fff;
            border: none;
            border-radius: 8px;
            width: 28px;
            height: 28px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
        }

        .pi-gallery-item:hover .pi-delete-btn {
            opacity: 1;
            transform: scale(1);
        }

        /* Image Preview Modal */
        .pi-preview-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
            cursor: zoom-out;
        }

        .pi-preview-overlay.active {
            display: flex;
        }

        .pi-preview-overlay img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .pi-preview-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pi-preview-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .pi-preview-nav:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .pi-preview-nav.prev { left: 16px; }
        .pi-preview-nav.next { right: 16px; }

        /* Empty State */
        .pi-empty {
            text-align: center;
            padding: 3rem 1rem;
        }

        .pi-empty i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .pi-empty p {
            color: #a0aec0;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .pi-filter-bar {
                position: static;
                padding: 12px;
                border-radius: 0 0 var(--pi-card-radius) var(--pi-card-radius);
                margin-top: -8px;
            }

            .pi-gallery {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 8px;
                padding: 10px;
            }

            .pi-delete-btn {
                opacity: 0.85;
                transform: scale(1);
            }

            .pi-card-header {
                padding: 10px 12px;
            }

            .pi-card-header h6 {
                font-size: 0.82rem;
            }

            .pi-preview-nav {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .skeleton-grid {
                grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .pi-gallery {
                grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">
        {{-- Filter Bar --}}
        <div class="pi-filter-bar">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-5 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;">
                                <i class="fas fa-search text-muted" style="font-size: 0.85rem;"></i>
                            </span>
                        </div>
                        <input type="text" id="searchInput" class="form-control border-left-0"
                            placeholder="Cari nama / kode produk..." autocomplete="off" autofocus>
                        <div class="input-group-append" id="btnClearSearch" style="display: none;">
                            <button class="btn btn-outline-secondary" type="button" title="Hapus pencarian">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 6px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefresh" title="Refresh data">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#uploadModal">
                            <i class="fas fa-upload mr-1"></i> Upload
                        </button>
                        <span class="pi-badge-count" id="productCount">0 Produk</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Skeleton Loading --}}
        <div id="skeletonLoader">
            @for ($i = 0; $i < 3; $i++)
                <div class="skeleton-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="skeleton skeleton-header"></div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                    <div class="skeleton-grid">
                        @for ($j = 0; $j < 6; $j++)
                            <div class="skeleton skeleton-img"></div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>

        {{-- Product Gallery --}}
        <div id="productGallery" style="display: none;"></div>
    </div>

    {{-- Upload Modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header" style="background: #fafbfc; border-bottom: 1px solid #f0f0f0;">
                        <h6 class="modal-title font-weight-bold" id="uploadModalLabel">
                            <i class="fas fa-cloud-upload-alt text-primary mr-2"></i>Upload Gambar Produk
                        </h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-semibold">Pilih Produk <span class="text-danger">*</span></label>
                            <select name="product_id" id="uploadProductId" class="form-control" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($allProducts as $p)
                                    <option value="{{ $p->id }}">[{{ $p->code ?? '-' }}] {{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-semibold">Pilih Gambar <span class="text-danger">*</span></label>
                            <input type="file" id="filepondInput" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                            <small class="form-text text-muted">Format: JPG, PNG, WebP. Maks 5MB per file. Bisa pilih lebih dari 1.</small>
                        </div>
                    </div>
                    <div class="modal-footer" style="background: #fafbfc; border-top: 1px solid #f0f0f0;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Batal
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="btnUpload">
                            <i class="fas fa-upload mr-1"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Image Preview Overlay --}}
    <div class="pi-preview-overlay" id="previewOverlay">
        <button type="button" class="pi-preview-close" id="previewClose">
            <i class="fas fa-times"></i>
        </button>
        <button type="button" class="pi-preview-nav prev" id="previewPrev">
            <i class="fas fa-chevron-left"></i>
        </button>
        <img src="" alt="Preview" id="previewImage">
        <button type="button" class="pi-preview-nav next" id="previewNext">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
@endsection

@push('js')
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.product_images.index') }}";
        const URL_STORE_API = "{{ route('api.product_images.store') }}";
        const URL_COLLAGE = "{{ route('product_images.collage', ':id') }}";
        const URL_DOWNLOAD = "{{ route('product_images.download', ':id') }}";
        const URL_DESTROY_BATCH = "{{ route('product_images.destroy_batch') }}";

        let allImages = [];
        let groupedData = {};
        let currentPage = 1;
        let itemsPerPage = 5;
        let currentFilteredGroups = {};

        document.addEventListener('DOMContentLoaded', function () {
            // --- FilePond ---
            FilePond.registerPlugin(
                FilePondPluginImagePreview,
                FilePondPluginImageExifOrientation,
                FilePondPluginFileValidateType
            );
            const pond = FilePond.create(document.getElementById('filepondInput'), {
                allowMultiple: true,
                acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'],
                instantUpload: false,
                credits: false,
                labelIdle: 'Seret & lepas gambar atau <span class="filepond--label-action">Pilih file</span>',
            });

            // --- Select2 ---
            $('#uploadProductId').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#uploadModal'),
                placeholder: '-- Pilih Produk --',
                allowClear: true,
            });

            // Reset on modal close
            $('#uploadModal').on('hidden.bs.modal', function () {
                pond.removeFiles();
                $('#uploadProductId').val('').trigger('change');
            });

            // --- Refresh button ---
            $('#btnRefresh').on('click', function () {
                const btn = $(this);
                btn.prop('disabled', true).find('i').addClass('fa-spin');
                loadGalleryData(function () {
                    btn.prop('disabled', false).find('i').removeClass('fa-spin');
                });
            });

            // --- Load data ---
            loadGalleryData();

            // --- Search ---
            let searchTimer;
            $('#searchInput').on('keyup', function () {
                const val = $(this).val();
                $('#btnClearSearch').toggle(val.length > 0);
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    filterAndRender();
                }, 300);
            });

            $('#btnClearSearch').on('click', function () {
                $('#searchInput').val('');
                $(this).hide();
                filterAndRender();
            });

            // --- Upload via AJAX ---
            $('#btnUpload').on('click', function (e) {
                e.preventDefault();
                const productId = $('#uploadProductId').val();
                if (!productId) {
                    show_message('Pilih produk terlebih dahulu!');
                    return;
                }
                if (pond.getFiles().length === 0) {
                    show_message('Pilih minimal 1 file gambar!');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Uploading...');

                const formData = new FormData();
                formData.append('product_id', productId);
                pond.getFiles().forEach(function (fileItem) {
                    formData.append('images[]', fileItem.file);
                });

                $.ajax({
                    url: URL_STORE_API,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        $('#uploadModal').modal('hide');
                        show_message(res.message || 'Upload berhasil!', 'success');
                        loadGalleryData();
                    },
                    error: function (xhr) {
                        let msg = 'Upload gagal!';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            if (xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                if (errors.length) msg = errors.join(', ');
                            }
                        }
                        show_message(msg);
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload');
                    }
                });
            });

            // --- Image Preview (lightbox) ---
            const overlay = document.getElementById('previewOverlay');
            const previewImg = document.getElementById('previewImage');
            let previewItems = [];
            let previewIdx = 0;

            $(document).on('click', '.pi-gallery-item', function () {
                const card = $(this).closest('.pi-product-card');
                previewItems = card.find('.pi-gallery-item').toArray();
                previewIdx = previewItems.indexOf(this);
                showPreview();
            });

            function showPreview() {
                previewImg.src = previewItems[previewIdx].getAttribute('data-preview');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function hidePreview() {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            document.getElementById('previewClose').addEventListener('click', hidePreview);
            overlay.addEventListener('click', function (e) { if (e.target === overlay) hidePreview(); });
            document.getElementById('previewPrev').addEventListener('click', function (e) {
                e.stopPropagation();
                if (previewIdx > 0) { previewIdx--; showPreview(); }
            });
            document.getElementById('previewNext').addEventListener('click', function (e) {
                e.stopPropagation();
                if (previewIdx < previewItems.length - 1) { previewIdx++; showPreview(); }
            });
            document.addEventListener('keydown', function (e) {
                if (!overlay.classList.contains('active')) return;
                if (e.key === 'Escape') hidePreview();
                if (e.key === 'ArrowLeft' && previewIdx > 0) { previewIdx--; showPreview(); }
                if (e.key === 'ArrowRight' && previewIdx < previewItems.length - 1) { previewIdx++; showPreview(); }
            });

            // --- Delete single image via AJAX ---
            $(document).on('click', '.btn-delete-image', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const imageId = $(this).data('id');
                confirmation('Hapus gambar ini?', function (confirm) {
                    if (!confirm) return;
                    $.ajax({
                        url: URL_INDEX_API + '/' + imageId,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function (res) {
                            show_message(res.message || 'Gambar dihapus!', 'success');
                            allImages = allImages.filter(function (img) { return img.id !== imageId; });
                            groupDataByProduct();
                            filterAndRender(false);
                        },
                        error: function (xhr) {
                            show_message(xhr.responseJSON?.message || 'Gagal menghapus!');
                        }
                    });
                });
            });

            // --- Delete all images for a product via AJAX ---
            $(document).on('click', '.btn-delete-product-images', function (e) {
                e.preventDefault();
                const productId = $(this).data('product-id');
                const productName = $(this).data('product-name');
                confirmation('Hapus semua gambar untuk "' + productName + '"?', function (confirm) {
                    if (!confirm) return;
                    $.ajax({
                        url: URL_DESTROY_BATCH,
                        type: 'DELETE',
                        data: { product_id: productId },
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function (res) {
                            show_message(res.message || 'Gambar dihapus!', 'success');
                            allImages = allImages.filter(function (img) { return parseInt(img.product_id) !== parseInt(productId); });
                            groupDataByProduct();
                            filterAndRender(false);
                        },
                        error: function (xhr) {
                            show_message(xhr.responseJSON?.message || 'Gagal menghapus!');
                        }
                    });
                });
            });

            // --- Print collage ---
            $(document).on('click', '.btn-print-product', function () {
                const productId = $(this).data('product-id');
                window.open(URL_COLLAGE.replace(':id', productId), '_blank');
            });

            // --- Pagination ---
            $(document).on('click', '#btnPrevPage', function () {
                if (currentPage > 1) {
                    currentPage--;
                    renderGallery(currentFilteredGroups);
                    scrollToGallery();
                }
            });
            $(document).on('click', '#btnNextPage', function () {
                const totalPages = Math.ceil(Object.keys(currentFilteredGroups).length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderGallery(currentFilteredGroups);
                    scrollToGallery();
                }
            });
            $(document).on('change', '#pageSizeSelect', function () {
                itemsPerPage = parseInt($(this).val());
                currentPage = 1;
                renderGallery(currentFilteredGroups);
            });
        });

        // ========================
        // Core Functions
        // ========================

        function loadGalleryData(callback) {
            $('#skeletonLoader').show();
            $('#productGallery').hide();

            $.ajax({
                url: URL_INDEX_API,
                type: 'GET',
                success: function (res) {
                    allImages = res.data || [];
                    groupDataByProduct();
                    filterAndRender();
                    $('#skeletonLoader').hide();
                    $('#productGallery').show();
                    if (typeof callback === 'function') callback();
                },
                error: function (xhr) {
                    $('#skeletonLoader').hide();
                    $('#productGallery').html(
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>' +
                        'Gagal memuat data: ' + (xhr.responseJSON?.message || 'Error!') + '</div>'
                    ).show();
                    if (typeof callback === 'function') callback();
                }
            });
        }

        function groupDataByProduct() {
            groupedData = {};
            allImages.forEach(function (img) {
                const pid = img.product_id;
                if (!groupedData[pid]) {
                    groupedData[pid] = { product: img.product, images: [] };
                }
                groupedData[pid].images.push(img);
            });
        }

        function filterAndRender(resetPage) {
            if (resetPage !== false) currentPage = 1;
            const search = $('#searchInput').val().toLowerCase().trim();

            let filtered = {};
            Object.keys(groupedData).forEach(function (pid) {
                const group = groupedData[pid];
                const p = group.product;
                const label = ('[' + (p.code || '-') + '] ' + p.name).toLowerCase();
                if (search && !label.includes(search)) return;
                filtered[pid] = group;
            });

            currentFilteredGroups = filtered;
            renderGallery(filtered);
        }

        function renderGallery(groups) {
            const container = $('#productGallery');
            const productIds = Object.keys(groups);
            const totalProducts = productIds.length;
            const totalPages = Math.ceil(totalProducts / itemsPerPage) || 1;

            $('#productCount').text(totalProducts + ' Produk');

            if (totalProducts === 0) {
                container.html(
                    '<div class="pi-empty">' +
                    '<i class="fas fa-images d-block"></i>' +
                    '<h5 class="text-muted">Tidak ada gambar ditemukan</h5>' +
                    '<p>Coba gunakan kata kunci lain atau upload gambar baru.</p>' +
                    '</div>'
                );
                return;
            }

            const start = (currentPage - 1) * itemsPerPage;
            const end = Math.min(start + itemsPerPage, totalProducts);
            const pageIds = productIds.slice(start, end);

            let html = '';
            pageIds.forEach(function (pid, idx) {
                const group = groups[pid];
                const p = group.product;
                const imgs = group.images;
                const collageUrl = URL_COLLAGE.replace(':id', pid);

                html += '<div class="pi-product-card" style="animation-delay:' + (idx * 0.08) + 's">';
                html += '<div class="pi-card-header">';
                html += '<h6><i class="fas fa-box-open"></i>[' + esc(p.code || '-') + '] ' + esc(p.name) + '</h6>';
                html += '<div class="pi-card-actions">';
                html += '<a href="' + URL_DOWNLOAD.replace(':id', pid) + '" class="btn btn-outline-success btn-sm" title="Download ZIP"><i class="fas fa-download"></i></a>';
                html += '<button type="button" class="btn btn-outline-primary btn-sm btn-print-product" data-product-id="' + pid + '" title="Cetak Kolase"><i class="fas fa-print"></i></button>';
                html += '<button type="button" class="btn btn-outline-danger btn-sm btn-delete-product-images" data-product-id="' + pid + '" data-product-name="' + esc(p.name) + '" title="Hapus Semua"><i class="fas fa-trash-alt"></i></button>';
                html += '<span class="pi-badge-count">' + imgs.length + ' Gambar</span>';
                html += '</div></div>';
                html += '<div class="pi-gallery">';

                imgs.forEach(function (img) {
                    html += '<div class="pi-gallery-item" data-preview="' + esc(img.url) + '">';
                    html += '<img src="' + esc(img.url) + '" alt="' + esc(img.name) + '" loading="lazy">';
                    html += '<button type="button" class="pi-delete-btn btn-delete-image" data-id="' + img.id + '" title="Hapus"><i class="fas fa-trash-alt"></i></button>';
                    html += '</div>';
                });

                html += '</div></div>';
            });

            // Pagination
            html += '<div class="d-flex justify-content-center align-items-center flex-wrap mt-3" style="gap:10px; padding:12px; background:rgba(255,255,255,0.9); border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08);">';
            html += '<button class="btn btn-sm btn-outline-primary" id="btnPrevPage" ' + (currentPage <= 1 ? 'disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';
            html += '<span style="font-weight:600; font-size:0.85rem;">Halaman ' + currentPage + ' / ' + totalPages + '</span>';
            html += '<button class="btn btn-sm btn-outline-primary" id="btnNextPage" ' + (currentPage >= totalPages ? 'disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';
            html += '<select class="form-control form-control-sm border-0 bg-light shadow-none" id="pageSizeSelect" style="width:80px; border-radius:10px; font-size:0.8rem;">';
            [5, 10, 20, 50].forEach(function (n) {
                html += '<option value="' + n + '"' + (itemsPerPage === n ? ' selected' : '') + '>' + n + '</option>';
            });
            html += '</select></div>';

            container.html(html);
        }

        function scrollToGallery() {
            $('html, body').animate({ scrollTop: $('#productGallery').offset().top - 20 }, 300);
        }

        function esc(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
@endpush
