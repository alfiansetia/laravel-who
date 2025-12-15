@extends('template', ['title' => 'Product Images'])

@push('css')
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .input-group>.select2-container--bootstrap {
            width: auto;
            flex: 1 1 auto;
        }

        .input-group>.select2-container--bootstrap .select2-selection--single {
            height: 100%;
            line-height: inherit;
            padding: 0.5rem 1rem;
        }

        /* Product Image Gallery Styles */
        .product-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .product-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .product-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-card-header h6 {
            margin: 0;
            font-weight: 600;
        }

        .product-card-header .badge {
            background: rgba(255, 255, 255, 0.2);
            font-size: 0.75rem;
        }

        .product-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px;
            background: #f8f9fa;
            min-height: 100px;
        }

        .gallery-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .gallery-item:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-item .delete-overlay {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(220, 53, 69, 0.9);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-item:hover .delete-overlay {
            opacity: 1;
        }

        .no-images {
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            text-align: center;
            width: 100%;
        }

        /* Search & Filter Bar */
        .filter-bar {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .product-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 767.98px) {
            .filter-bar .row>div {
                margin-bottom: 10px;
            }

            .filter-bar .row>div:last-child {
                margin-bottom: 0;
                text-align: center !important;
            }

            .filter-bar .btn {
                width: 48%;
                margin-bottom: 8px;
            }

            .product-count {
                display: block;
                width: 100%;
                text-align: center;
                margin-top: 8px;
                margin-left: 0 !important;
            }

            .product-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .product-card-header h6 {
                font-size: 0.9rem;
                word-break: break-word;
            }

            .product-card-header .badge {
                align-self: flex-end;
            }

            .gallery-item {
                width: 80px;
                height: 80px;
            }

            .gallery-item .delete-overlay {
                opacity: 1;
                /* Always show on mobile since no hover */
            }

            .product-gallery {
                gap: 6px;
                padding: 10px;
                justify-content: center;
            }
        }

        @media (max-width: 575.98px) {
            .filter-bar {
                padding: 12px;
            }

            .filter-bar .btn {
                width: 100%;
                margin-bottom: 8px;
            }

            .gallery-item {
                width: 70px;
                height: 70px;
            }

            .product-card-header h6 {
                font-size: 0.85rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row align-items-center">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari produk...">
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
                <div class="col-12 col-md-4 text-center text-md-right">
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-end align-items-center">
                        <button type="button" class="btn btn-secondary btn-sm mr-1 mb-1" id="btnResetFilter">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-info btn-sm mr-2 mb-1" id="btnAddImage">
                            <i class="fas fa-plus mr-1"></i> Tambah Gambar
                        </button>
                        <span class="product-count mb-1" id="productCount">0 Produk</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Gallery Container -->
        <div id="productGalleryContainer">
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Memuat data...</p>
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

            function filterAndRender() {
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

                renderGallery(filteredGroups);
            }

            function renderGallery(groups) {
                const container = $('#productGalleryContainer');
                const productIds = Object.keys(groups);

                // Update product count
                $('#productCount').text(`${productIds.length} Produk`);

                if (productIds.length === 0) {
                    container.html(`
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data gambar produk.</p>
                        </div>
                    `);
                    return;
                }

                let html = '';
                productIds.forEach(productId => {
                    const group = groups[productId];
                    const product = group.product;
                    const images = group.images;

                    html += `
                        <div class="product-card" data-product-id="${productId}">
                            <div class="product-card-header">
                                <h6><i class="fas fa-box mr-2"></i>[${product.code || '-'}] ${product.name}</h6>
                                <span class="badge">${images.length} gambar</span>
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
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                });

                container.html(html);

                // Bind delete event
                $('.btn-delete-image').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const imageId = $(this).data('id');
                    deleteImage(imageId);
                });
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
