@extends('template', ['title' => 'Product Images'])

@push('css')
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        /* Modern UI Design Tokens */
        :root {
            --primary-gradient: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --danger-gradient: linear-gradient(135deg, #f85032 0%, #e73827 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
        }

        .input-group>.select2-container--bootstrap {
            width: auto;
            flex: 1 1 auto;
        }

        .input-group>.select2-container--bootstrap .select2-selection--single {
            height: 100% !important;
            border-radius: 10px !important;
            border: 1px solid #e0e6ed !important;
        }

        /* Glassmorphism Filter Bar */
        .filter-bar {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 10px;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .filter-bar:hover {
            box-shadow: var(--hover-shadow);
        }

        #searchInput {
            border-radius: 10px;
            border: 1px solid #e0e6ed;
            padding: 10px 15px;
            transition: all 0.2s;
        }

        #searchInput:focus {
            box-shadow: 0 0 0 3px rgba(75, 108, 183, 0.2);
            border-color: #4b6cb7;
        }

        /* Product Card - Premium Look */
        .product-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            animation: fadeInUp 0.5s ease backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .product-card-header {
            background: #fcfcfd;
            border-bottom: 1px solid #f1f3f5;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-card-header h6 {
            margin: 0;
            font-weight: 700;
            color: #2d3436;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .product-card-header h6 i {
            color: #4b6cb7;
            font-size: 1.2rem;
        }

        .product-card-header .badge-count {
            background: #f1f3f5;
            color: #4b6cb7;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Modern Gallery Grid */
        .product-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 12px;
            padding: 20px;
            background: #fff;
        }

        .gallery-item {
            position: relative;
            aspect-ratio: 1/1;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            border: 1px solid #edf2f7;
            transition: all 0.3s ease;
        }

        .gallery-item:hover {
            transform: scale(1.05);
            z-index: 2;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-item .delete-overlay {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--danger-gradient);
            color: #fff;
            border: none;
            border-radius: 10px;
            width: 32px;
            height: 32px;
            font-size: 14px;
            cursor: pointer;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(231, 56, 39, 0.3);
        }

        .gallery-item:hover .delete-overlay {
            opacity: 1;
            transform: scale(1);
        }

        /* Custom Pagination */
        .gallery-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin: 2rem 0;
            padding: 15px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: var(--card-shadow);
        }

        .page-info {
            font-weight: 600;
            color: #2d3436;
        }

        .product-count-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(75, 108, 183, 0.3);
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .filter-bar {
                top: 0;
                border-radius: 0 0 15px 15px;
                margin-bottom: 1.5rem;
            }

            .product-gallery {
                grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
                padding: 15px;
            }

            .gallery-item .delete-overlay {
                opacity: 0.9;
                transform: scale(1);
            }

            .product-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .product-card-header .btn-print-product {
                order: 3;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row align-items-center">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-right-0"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" id="searchInput" class="form-control border-left-0"
                            placeholder="Cari nama atau kode produk...">
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <select id="filterProduct" class="form-control">
                        <option value="">-- Semua Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">[{{ $product->code ?? '-' }}] {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-end align-items-center"
                        style="gap: 10px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilter">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnAddImage">
                            <i class="fas fa-upload mr-1"></i> Upload Gambar
                        </button>
                        <span class="product-count-badge" id="productCount">0 Produk</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Gallery Container -->
        <div id="productGalleryContainer">
            <div class="text-center py-5">
                <div class="spinner-grow text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 text-muted font-weight-bold">Mempersiapkan galeri produk...</p>
            </div>
        </div>
    </div>

    @include('product_image.modal')
@endsection

@push('js')
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js">
    </script>
    <script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.product_images.index') }}"
        const URL_INDEX = "{{ route('product_images.index') }}"

        let allImages = [];
        let groupedData = {};
        let currentPage = 1;
        let itemsPerPage = 5;
        let currentFilteredGroups = {};

        $(document).ready(function() {
            // Initialize Select2 for modal
            $('#product_id').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal_add'),
            }).on('change', function() {
                showExistingImages($(this).val());
            });

            // Initialize Select2 for filter
            $('#filterProduct').select2({
                theme: 'bootstrap4',
                placeholder: '-- Semua Produk --',
                allowClear: true
            });

            // Lightbox options
            lightbox.option({
                resizeDuration: 200,
                wrapAround: true
            });

            // Load data
            loadGalleryData();

            // Event: Add button
            $('#btnAddImage').on('click', function() {
                addData();
            });

            // Event: Print Product Collage
            $(document).on('click', '.btn-print-product', function() {
                const productId = $(this).data('product-id');
                let url = "{{ route('product_images.collage', ':id') }}";
                url = url.replace(':id', productId);
                window.open(url, '_blank');
            });

            // Event: Reset filters
            $('#btnResetFilter').on('click', function() {
                $('#searchInput').val('');
                $('#filterProduct').val('').trigger('change');
                filterAndRender();
            });

            // Event: Search
            $('#searchInput').on('keyup', function() {
                filterAndRender();
            });

            // Event: Filter by product
            $('#filterProduct').on('change', function() {
                filterAndRender();
            });

            // FilePond setup
            FilePond.registerPlugin(
                FilePondPluginImagePreview,
                FilePondPluginImageExifOrientation,
                FilePondPluginImageCrop,
                FilePondPluginFileValidateType
            );

            const pond = FilePond.create(document.querySelector('#images'), {
                allowMultiple: true,
                acceptedFileTypes: ['image/*'],
                instantUpload: false,
                credits: false
            });

            // Form submit
            $('#form_add').on('submit', function(e) {
                e.preventDefault();
                if (pond.getFiles().length === 0) {
                    show_message('⚠️ Minimal 1 file gambar harus diupload!');
                    return;
                }
                let formData = new FormData(this);
                pond.getFiles().forEach(fileItem => {
                    formData.append('images[]', fileItem.file);
                });
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        loadGalleryData();
                        show_message(res.message, 'success');
                        $('#product_id').val('').change();
                        pond.removeFiles();
                        $('#modal_add').modal('hide');
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error!');
                    }
                });
            });

            function addData() {
                pond.removeFiles();
                $('#product_id').val('').change();
                $('#modal_add').modal('show');
            }

            function loadGalleryData() {
                $('#productGalleryContainer').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Memuat data...</p>
                    </div>
                `);

                $.ajax({
                    url: URL_INDEX_API,
                    type: 'GET',
                    success: function(res) {
                        allImages = res.data || [];
                        groupDataByProduct();
                        filterAndRender();
                    },
                    error: function(xhr) {
                        $('#productGalleryContainer').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Gagal memuat data: ${xhr.responseJSON?.message || 'Error!'}
                            </div>
                        `);
                    }
                });
            }

            function groupDataByProduct() {
                groupedData = {};
                allImages.forEach(img => {
                    const productId = img.product_id;
                    if (!groupedData[productId]) {
                        groupedData[productId] = {
                            product: img.product,
                            images: []
                        };
                    }
                    groupedData[productId].images.push(img);
                });
            }

            function filterAndRender(resetPage = true) {
                const searchTerm = $('#searchInput').val().toLowerCase().trim();
                const filterProductId = $('#filterProduct').val();

                let filteredGroups = {};

                Object.keys(groupedData).forEach(productId => {
                    const group = groupedData[productId];
                    const product = group.product;
                    const productName = `[${product.code || '-'}] ${product.name}`.toLowerCase();

                    // Filter by search term
                    if (searchTerm && !productName.includes(searchTerm)) {
                        return;
                    }

                    // Filter by product dropdown
                    if (filterProductId && parseInt(productId) !== parseInt(filterProductId)) {
                        return;
                    }

                    filteredGroups[productId] = group;
                });

                // Reset to page 1 when filtering
                if (resetPage) {
                    currentPage = 1;
                }

                renderGallery(filteredGroups);
            }

            function renderGallery(groups) {
                // Store for later use
                currentFilteredGroups = groups;

                const container = $('#productGalleryContainer');
                const productIds = Object.keys(groups);
                const totalProducts = productIds.length;
                const totalPages = Math.ceil(totalProducts / itemsPerPage);

                // Update product count with animation
                $('#productCount').fadeOut(200, function() {
                    $(this).text(`${totalProducts} Produk`).fadeIn(200);
                });

                if (totalProducts === 0) {
                    container.html(`
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                            <h5 class="text-muted">Tidak ada data ditemukan</h5>
                            <p class="text-muted">Coba gunakan kata kunci pencarian lain.</p>
                        </div>
                    `);
                    return;
                }

                // Calculate pagination
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalProducts);
                const paginatedProductIds = productIds.slice(startIndex, endIndex);

                // Build gallery HTML
                let html = '';
                paginatedProductIds.forEach((productId, idx) => {
                    const group = groups[productId];
                    const product = group.product;
                    const images = group.images;

                    // Small delay for stagger animation
                    const delay = idx * 0.1;

                    html += `
                        <div class="product-card" data-product-id="${productId}" style="animation-delay: ${delay}s">
                            <div class="product-card-header">
                                <h6><i class="fas fa-box-open mr-2"></i>[${product.code || '-'}] ${product.name}</h6>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-xs btn-outline-primary btn-sm mr-2 btn-print-product" data-product-id="${productId}" title="Cetak Kolase">
                                        <i class="fas fa-print mr-1"></i> Cetak
                                    </button>
                                    <span class="badge-count">${images.length} Gambar</span>
                                </div>
                            </div>
                            <div class="product-gallery">
                    `;

                    images.forEach(img => {
                        html += `
                            <div class="gallery-item" data-image-id="${img.id}">
                                <a href="${img.url}" 
                                   data-lightbox="product-${productId}" 
                                   data-title="[${product.code || '-'}] ${product.name} - ${img.name}">
                                    <img src="${img.url}" alt="${img.name}" loading="lazy">
                                </a>
                                <button type="button" class="delete-overlay btn-delete-image" 
                                        data-id="${img.id}" 
                                        title="Hapus gambar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                });

                // Add pagination controls
                html += `
                    <div class="gallery-pagination mt-4">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="btnPrevPage" ${currentPage === 1 ? 'disabled' : ''}>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="page-info mx-2">
                                Halaman <strong>${currentPage}</strong> dari <strong>${totalPages}</strong>
                                <small class="text-muted d-block d-md-inline ml-md-2">(${startIndex + 1}-${endIndex} dari ${totalProducts})</small>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="btnNextPage" ${currentPage >= totalPages ? 'disabled' : ''}>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            
                            <div class="ml-auto d-flex align-items-center">
                                <span class="text-muted small mr-2">Tampilkan:</span>
                                <select class="form-control form-control-sm border-0 bg-light shadow-none" id="pageSizeSelect" style="width: 80px; border-radius: 10px;">
                                    <option value="5" ${itemsPerPage === 5 ? 'selected' : ''}>5</option>
                                    <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
                                    <option value="20" ${itemsPerPage === 20 ? 'selected' : ''}>20</option>
                                    <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;

                container.html(html);

                // Bind pagination events (First/Last removed for cleaner UI, can be added back if needed)
                $('#btnPrevPage').off('click').on('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        renderGallery(currentFilteredGroups);
                        scrollToTop();
                    }
                });
                $('#btnNextPage').off('click').on('click', function() {
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderGallery(currentFilteredGroups);
                        scrollToTop();
                    }
                });
                $('#pageSizeSelect').off('change').on('change', function() {
                    itemsPerPage = parseInt($(this).val());
                    currentPage = 1;
                    renderGallery(currentFilteredGroups);
                });

                // Bind delete event
                $('.btn-delete-image').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const imageId = $(this).data('id');
                    deleteImage(imageId);
                });
            }

            function scrollToTop() {
                $('html, body').animate({
                    scrollTop: $('#productGalleryContainer').offset().top - 20
                }, 300);
            }

            function deleteImage(imageId) {
                confirmation('Hapus gambar ini?', function(confirm) {
                    if (confirm) {
                        $.ajax({
                            url: URL_INDEX_API,
                            type: 'DELETE',
                            data: {
                                ids: [imageId]
                            },
                            success: function(res) {
                                show_message(res.message, 'success');
                                // Remove from local data
                                allImages = allImages.filter(img => img.id !== imageId);
                                groupDataByProduct();
                                filterAndRender();
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON?.message || 'Error!', 'error');
                            }
                        });
                    }
                });
            }

            function showExistingImages(productId) {
                $('#prev_image').html('');
                if (!productId) return;

                productId = parseInt(productId);
                const images = allImages.filter(img => parseInt(img.product_id) === productId);

                if (images.length < 1) {
                    $('#prev_image').html(`
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle mr-2"></i>Belum ada gambar untuk produk ini.
                        </div>
                    `);
                    return;
                }

                images.forEach(item => {
                    if (item.url) {
                        $('#prev_image').append(`
                            <a href="${item.url}" 
                               data-lightbox="modal-product-${item.product_id}" 
                               data-title="[${item.product.code || '-'}] ${item.product.name} (${item.name})">
                                <img src="${item.url}" 
                                     class="img-thumbnail"
                                     style="width:80px;height:80px;object-fit:cover;margin:4px;">
                            </a>
                        `);
                    }
                });
            }
        });
    </script>
@endpush
