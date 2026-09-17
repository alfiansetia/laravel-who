@extends('template')

@push('css')
    <style>
        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 0.35rem 0.5rem;
            white-space: nowrap;
        }

        .table-modern tbody td {
            padding: 0.3rem 0.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.8rem;
        }

        .table-modern tbody tr {
            transition: background 0.15s;
            cursor: pointer;
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
        }

        /* Pagination */
        .pagination-modern {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .pagination-modern .page-btn {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            padding: 0 8px;
        }

        .pagination-modern .page-btn:hover:not(:disabled):not(.active) {
            background: #eef2ff;
            border-color: #6366f1;
            color: #6366f1;
        }

        .pagination-modern .page-btn.active {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: #fff;
            border-color: transparent;
        }

        .pagination-modern .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .per-page-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }

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

@section('content')
    <div class="container-fluid">
        <div class="card card-sm">
            <div class="card-header d-flex align-items-center flex-wrap" style="gap: 8px;">
                {{-- Left: Search + Refresh --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px; flex: 1;">
                    <div class="input-group input-group-sm" style="max-width: 400px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="search" id="searchInput" class="form-control form-control-sm"
                            placeholder="Cari reg_no, nama, vendor...">
                    </div>
                    <button type="button" id="btnRefresh" class="btn btn-outline-secondary btn-sm" title="Refresh"
                        onclick="loadData()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                {{-- Right: Action Buttons --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <button type="button" class="btn btn-info btn-sm" onclick="window.location.href='{{ route('akls.create') }}'">
                        <i class="fas fa-upload mr-1"></i> Upload Lampiran
                    </button>
                    <button type="button" id="btnDeleteSelected" class="btn btn-danger btn-sm" onclick="deleteBatch()">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern" id="tableAkl" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="chkAll" class="new-control-input">
                                </th>
                                <th>Reg No</th>
                                <th>Nama</th>
                                <th>Vendor</th>
                                <th>Berlaku</th>
                                <th>Expired</th>
                                <th>Status</th>
                                <th>File</th>
                                <th>Item</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="text-muted small">Tampilkan</span>
                    <select id="perPageSelect" class="per-page-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-muted small">data</span>
                </div>
                <small class="text-muted" id="pageInfo"></small>
                <div class="pagination-modern" id="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal Preview (image & PDF) -->
    <div class="modal fade" id="aklPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="aklPreviewTitle">Preview</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-2" style="min-height: 200px;">
                    <p class="text-muted small mb-1"><i class="fas fa-info-circle mr-1"></i>Teks PDF bisa diseleksi &amp; dicopy langsung dari preview.</p>
                    <img id="aklPreviewImg" class="img-fluid d-none" alt="Preview lampiran">
                    <div id="aklPreviewPdfWrap" class="d-none text-left"
                        style="max-height: 70vh; overflow-y: auto; background: #525659; border-radius: 4px;">
                    </div>
                    <embed id="aklPreviewPdfFallback" type="application/pdf" class="d-none w-100"
                        style="height: 70vh;">
                </div>
                <div class="modal-footer py-2">
                    <a id="aklPreviewOpen" href="#" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka tab baru
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview List Item -->
    <div class="modal fade" id="aklItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="aklItemsTitle">Item AKL</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size:.85rem">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:35px">#</th>
                                    <th>Code</th>
                                    <th>Nama</th>
                                    <th style="width:110px">Status</th>
                                </tr>
                            </thead>
                            <tbody id="aklItemsBody">
                                <tr><td colspan="4" class="text-center text-muted py-4">Memuat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2 d-flex justify-content-between">
                    <small class="text-muted align-self-center" id="aklItemsInfo"></small>
                    <div>
                        <a id="aklItemsManage" href="#" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i> Kelola / Input Item
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sync Cek Izin Edar -->
    <div class="modal fade" id="aklSyncModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="aklSyncTitle">Sync Cek Izin Edar</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3" id="aklSyncBody" style="font-size:.85rem">
                    <div class="text-center text-muted py-4">Memuat...</div>
                </div>
                <div class="modal-footer py-2 d-flex justify-content-between">
                    <small class="text-muted align-self-center" id="aklSyncInfo"></small>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary btn-sm" id="aklSyncSave" disabled>
                            <i class="fas fa-save mr-1"></i> Save yang Dipilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        const URL_INDEX_API = "{{ route('api.akls.index') }}";
        const URL_DELETE_BATCH_API = "{{ route('api.akls.delete_batch') }}";
        const URL_INDEX = "{{ route('akls.index') }}";
        const URL_ITEMS_API = "{{ route('api.akl_items.index') }}";
        let currentPage = 1;
        let currentPerPage = 25;
        let currentSearch = '';
        let searchTimeout = null;

        $(document).ready(function() {
            loadData();

            // Search input with debounce
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                const val = $(this).val().trim();
                searchTimeout = setTimeout(function() {
                    currentSearch = val;
                    currentPage = 1;
                    loadData();
                }, 400);
            });

            // Per page change
            $('#perPageSelect').on('change', function() {
                currentPerPage = parseInt($(this).val());
                currentPage = 1;
                loadData();
            });

            // Select all checkbox
            $('#chkAll').on('change', function() {
                const checked = $(this).is(':checked');
                $('input[name="id[]"]').prop('checked', checked);
            });

            // Row Click → halaman Edit (abaikan klik di checkbox/tombol/link)
            $(document).on('click', '#tableBody tr', function(e) {
                if ($(e.target).closest('input,button,a').length) return;
                const id = $(this).data('id');
                if (id) window.location.href = URL_INDEX + "/" + id + '/edit';
            });

        });

        function formatDate(iso) {
            if (!iso) return '-';
            const d = new Date(iso);
            if (isNaN(d)) return '-';
            return d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        // ── Data Loading ─────────────────────────────────────

        function loadData() {
            const $tbody = $('#tableBody');
            $tbody.html(
                `<tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
            );

            $.ajax({
                url: URL_INDEX_API,
                type: 'GET',
                data: {
                    page: currentPage,
                    per_page: currentPerPage,
                    search: currentSearch,
                },
                success: function(res) {
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                },
                error: function() {
                    $tbody.html(
                        `<tr><td colspan="10" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                    );
                }
            });
        }

        // ── Table Rendering ──────────────────────────────────

        function renderTable(data) {
            const $tbody = $('#tableBody');

            if (!data || data.length === 0) {
                $tbody.html(
                    `<tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
                );
                return;
            }

            let html = '';
            data.forEach(row => {
                const status = !row.date_expired
                    ? `<span class="badge badge-secondary">-</span>`
                    : (row.is_expired
                        ? `<span class="badge badge-danger">Expired</span>`
                        : `<span class="badge badge-success">Aktif</span>`);
                const fileBtn = row.file
                    ? `<button type="button" class="btn btn-outline-info btn-sm btn-akl-preview"
                           data-reg="${escapeHtml(row.reg_no)}" data-id="${row.id}" data-pdf="${row.is_pdf ? '1' : '0'}">
                            <i class="fas ${row.is_pdf ? 'fa-file-pdf' : 'fa-file-image'} mr-1"></i>Preview
                        </button>`
                    : `<span class="text-muted">-</span>`;
                const itemCount = row.items_count ?? 0;
                const itemBtn = `<button type="button" class="btn btn-outline-success btn-sm btn-akl-items"
                            data-id="${row.id}" data-reg="${escapeHtml(row.reg_no)}" title="Preview list item">
                            <i class="fas fa-boxes mr-1"></i>${itemCount} Item
                        </button>`;
                const aksiBtn = `<div class="btn-group btn-group-sm" role="group">
                            <a href="${URL_INDEX}/${row.id}/items" class="btn btn-success" title="Input item">
                                <i class="fas fa-plus"></i>
                            </a>
                            <button type="button" class="btn btn-outline-primary btn-akl-sync" data-id="${row.id}" data-reg="${escapeHtml(row.reg_no)}" title="Sync cek ke Izin Edar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>`;
                html += `<tr data-id="${row.id}">
                    <td class="text-center">
                        <input type="checkbox" name="id[]" value="${row.id}" class="new-control-input child-chk">
                    </td>
                    <td><strong>${escapeHtml(row.reg_no)}</strong></td>
                    <td>${escapeHtml(row.reg_name)}</td>
                    <td>${escapeHtml(row.vendor)}</td>
                    <td style="white-space:nowrap">${formatDate(row.date_from)}</td>
                    <td style="white-space:nowrap">${formatDate(row.date_expired)}</td>
                    <td>${status}</td>
                    <td>${fileBtn}</td>
                    <td style="white-space:nowrap">${itemBtn}</td>
                    <td>${aksiBtn}</td>
                </tr>`;
            });

            $tbody.html(html);
            $('#chkAll').prop('checked', false);
        }

        // ── Delete Batch ─────────────────────────────────────

        function deleteBatch() {
            const selectedIds = $('input[name="id[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                show_message("No Selected Data!");
                return;
            }

            confirmation('Delete Selected (' + selectedIds.length + ')? File di S3 ikut terhapus.', function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: URL_DELETE_BATCH_API,
                        type: "DELETE",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: { ids: selectedIds },
                        success: function(res) {
                            loadData();
                            show_message(res.message || 'Deleted!', 'success');
                        },
                        error: function(xhr) {
                            show_message(xhr.responseJSON?.message || 'Error!');
                        }
                    });
                }
            });
        }

        // ── Pagination ───────────────────────────────────────

        function renderPagination(page, totalPages, total) {
            const $pag = $('#pagination');
            const start = (page - 1) * currentPerPage + 1;
            const end = Math.min(page * currentPerPage, total);

            $('#pageInfo').text(total > 0 ? `Menampilkan ${start}–${end} dari ${total.toLocaleString('id-ID')} data` :
                'Tidak ada data');

            if (totalPages <= 1) {
                $pag.html('');
                return;
            }

            let html = '';

            html +=
                `<button class="page-btn" ${page <= 1 ? 'disabled' : ''} data-page="${page - 1}"><i class="fas fa-chevron-left"></i></button>`;

            const pages = getPaginationPages(page, totalPages);
            pages.forEach(p => {
                if (p === '...') {
                    html += `<span class="page-btn" style="border:none;cursor:default;">…</span>`;
                } else {
                    html += `<button class="page-btn ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
            });

            html +=
                `<button class="page-btn" ${page >= totalPages ? 'disabled' : ''} data-page="${page + 1}"><i class="fas fa-chevron-right"></i></button>`;

            $pag.html(html);

            $pag.off('click', '.page-btn').on('click', '.page-btn', function() {
                if ($(this).is(':disabled') || $(this).css('cursor') === 'default') return;
                currentPage = parseInt($(this).data('page'));
                loadData();
                $('html, body').animate({
                    scrollTop: $('#tableAkl').offset().top - 80
                }, 200);
            });
        }

        function getPaginationPages(current, total) {
            if (total <= 7) {
                return Array.from({
                    length: total
                }, (_, i) => i + 1);
            }

            const pages = [];
            pages.push(1);

            if (current > 3) pages.push('...');

            const start = Math.max(2, current - 1);
            const end = Math.min(total - 1, current + 1);
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            if (current < total - 2) pages.push('...');

            pages.push(total);
            return pages;
        }

        // ── Preview (image & PDF via PDF.js) ─────────────────

        var AKL_PDF_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        var AKL_PDF_MAX_PAGES = 10;

        function aklShowUrl(id) {
            return URL_INDEX + "/" + id;
        }

        function aklRenderPdf(url) {
            var wrap = $('#aklPreviewPdfWrap');
            var fallback = $('#aklPreviewPdfFallback');
            wrap.html(
                '<div class="text-center text-white py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 mb-0">Memuat PDF...</p></div>'
            );

            if (typeof pdfjsLib === 'undefined') {
                wrap.addClass('d-none');
                fallback.removeClass('d-none').attr('src', url);
                return;
            }
            fallback.addClass('d-none').attr('src', '');

            pdfjsLib.GlobalWorkerOptions.workerSrc = AKL_PDF_WORKER;
            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                var total = pdf.numPages;
                var show = Math.min(total, AKL_PDF_MAX_PAGES);
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
                wrap.addClass('d-none');
                fallback.removeClass('d-none').attr('src', url);
            });
        }

        $(document).on('click', '.btn-akl-preview', function() {
            var url = aklShowUrl($(this).data('id'));
            var isPdf = String($(this).data('pdf')) === '1';
            $('#aklPreviewTitle').text('Preview ' + $(this).data('reg'));
            $('#aklPreviewOpen').attr('href', url);
            if (isPdf) {
                $('#aklPreviewImg').addClass('d-none').attr('src', '');
                $('#aklPreviewPdfWrap').removeClass('d-none');
                aklRenderPdf(url);
            } else {
                $('#aklPreviewPdfWrap').addClass('d-none').empty();
                $('#aklPreviewPdfFallback').addClass('d-none').attr('src', '');
                $('#aklPreviewImg').removeClass('d-none').attr('src', url);
            }
            $('#aklPreviewModal').modal('show');
        });
        $('#aklPreviewModal').on('hidden.bs.modal', function() {
            $('#aklPreviewImg').attr('src', '');
            $('#aklPreviewPdfWrap').empty();
            $('#aklPreviewPdfFallback').attr('src', '');
        });

        // ── Preview List Item (modal tabel) ──────────────

        $(document).on('click', '.btn-akl-items', function() {
            const id = $(this).data('id');
            const reg = $(this).data('reg') || ('#' + id);
            $('#aklItemsTitle').text('Item AKL ' + reg);
            $('#aklItemsManage').attr('href', URL_INDEX + '/' + id + '/items');
            $('#aklItemsInfo').text('');
            $('#aklItemsBody').html(
                `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat item...</td></tr>`
            );
            $('#aklItemsModal').modal('show');

            $.ajax({
                url: URL_ITEMS_API,
                type: 'GET',
                data: { akl_id: id },
                success: function(res) {
                    const data = res.data || [];
                    $('#aklItemsInfo').text(data.length + ' item');
                    if (!data.length) {
                        $('#aklItemsBody').html(
                            `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Belum ada item</td></tr>`
                        );
                        return;
                    }
                    let html = '';
                    data.forEach((row, i) => {
                        const isCustom = row.is_custom || !row.product;
                        const badge = isCustom
                            ? `<span class="badge badge-warning">Custom</span>`
                            : `<span class="badge badge-success">Terdaftar</span>`;
                        const storedName = row.name || (row.product && row.product.name);
                        const pname = storedName
                            ? escapeHtml(storedName)
                            : '<span class="text-muted">-</span>';
                        html += `<tr>
                            <td class="text-muted">${i + 1}</td>
                            <td><strong>${escapeHtml(row.code)}</strong></td>
                            <td>${pname}</td>
                            <td>${badge}</td>
                        </tr>`;
                    });
                    $('#aklItemsBody').html(html);
                },
                error: function() {
                    $('#aklItemsBody').html(
                        `<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat item</td></tr>`
                    );
                }
            });
        });

        // ── Sync Cek Izin Edar (pilih kandidat → save) ──

        let syncAklId = null;

        $(document).on('click', '.btn-akl-sync', function() {
            syncAklId = $(this).data('id');
            const reg = $(this).data('reg') || ('#' + syncAklId);
            $('#aklSyncTitle').text('Sync Cek ' + reg + ' → Izin Edar');
            $('#aklSyncInfo').text('');
            $('#aklSyncSave').prop('disabled', true);
            $('#aklSyncBody').html(
                `<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Mencocokkan reg_no ke Izin Edar...</div>`
            );
            $('#aklSyncModal').modal('show');

            $.ajax({
                url: "{{ url('api/akls') }}/" + syncAklId + "/check-izin",
                type: 'GET',
                success: function(res) {
                    renderSyncModal(res.data);
                },
                error: function(xhr) {
                    $('#aklSyncBody').html(
                        `<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(xhr.responseJSON?.message || 'Gagal memuat data Izin Edar')}</div>`
                    );
                }
            });
        });

        function syncBadge(sama) {
            return sama
                ? `<span class="badge badge-success">Sama</span>`
                : `<span class="badge badge-danger">Beda</span>`;
        }

        function renderSyncModal(data) {
            const akl = data.akl || {};
            const matches = data.matches || [];
            const bisaDipilih = matches.filter(m => !m.is_synced).length;
            $('#aklSyncInfo').text(matches.length
                ? matches.length + ' kandidat cocok (' + bisaDipilih + ' bisa dipilih)'
                : 'Tidak ada yang cocok');

            let html = `<div class="alert alert-light border py-2 mb-3">
                <strong>Data AKL saat ini:</strong><br>
                <span class="text-muted">Reg No:</span> <strong>${escapeHtml(akl.reg_no)}</strong> &nbsp;
                <span class="text-muted">Nama:</span> ${escapeHtml(akl.reg_name)} &nbsp;
                <span class="text-muted">Vendor:</span> ${escapeHtml(akl.vendor)}<br>
                <span class="text-muted">Terbit:</span> ${formatDate(akl.date_from)} &nbsp;
                <span class="text-muted">Expired:</span> ${formatDate(akl.date_expired)}
            </div>`;

            if (!matches.length) {
                html += `<div class="text-center text-muted py-3"><i class="fas fa-inbox mr-2"></i>Tidak ada data yang cocok di Izin Edar untuk reg_no <strong>${escapeHtml(akl.reg_no)}</strong>.</div>`;
                $('#aklSyncBody').html(html);
                return;
            }

            html += `<div class="table-responsive"><table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:35px"></th>
                        <th>Nomor Izin Edar</th>
                        <th>Merk / Produk</th>
                        <th>Pendaftar / Pabrik</th>
                        <th>Terbit</th>
                        <th>Expired</th>
                        <th>Cek</th>
                    </tr>
                </thead><tbody>`;

            matches.forEach((m, i) => {
                const expBadge = m.is_expired ? `<span class="badge badge-danger ml-1">Expired</span>` : `<span class="badge badge-success ml-1">Aktif</span>`;
                const synced = !!m.is_synced;
                html += `<tr class="${synced ? 'table-success' : ''}">
                    <td class="text-center align-middle">
                        ${synced
                            ? `<input type="radio" disabled title="Sudah sama — tidak bisa dipilih">`
                            : `<input type="radio" name="sync_pick" value="${m.id}" data-idx="${i}">`}
                    </td>
                    <td><strong>${escapeHtml(m.nomor_izin_edar)}</strong><br><small class="text-muted">${escapeHtml(m.kategori || '')}</small> ${syncBadge(m.diff.reg_no_sama)}</td>
                    <td>${escapeHtml(m.merk)} ${syncBadge(m.diff.nama_sama)}<br><small class="text-muted">${escapeHtml(m.jenis_produk)}</small></td>
                    <td>${escapeHtml(m.pendaftar)} ${syncBadge(m.diff.vendor_sama)}<br><small class="text-muted">${escapeHtml(m.pabrik)}</small></td>
                    <td style="white-space:nowrap">${formatDate(m.tgl_terbit)}<br>${syncBadge(m.diff.terbit_sama)}</td>
                    <td style="white-space:nowrap">${formatDate(m.tgl_exp)}${expBadge}<br>${syncBadge(m.diff.expired_sama)}</td>
                    <td>${synced
                        ? `<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sudah sama</span><br><small class="text-muted">Tidak bisa dipilih</small>`
                        : `<small class="text-muted">Saran:<br>Nama: ${escapeHtml(m.saran.reg_name || '-')}<br>Vendor: ${escapeHtml(m.saran.vendor || '-')}</small>`}</td>
                </tr>`;
            });

            html += `</tbody></table></div>
                <small class="form-text text-muted mt-2">Baris hijau <strong>Sudah sama</strong> tidak bisa dipilih. Pilih kandidat lain yang masih <strong>Beda</strong>, lalu klik <strong>Save yang Dipilih</strong>.</small>`;

            $('#aklSyncBody').html(html);
        }

        $(document).on('change', 'input[name="sync_pick"]', function() {
            $('#aklSyncSave').prop('disabled', !$('input[name="sync_pick"]:checked').length);
        });

        $('#aklSyncSave').on('click', function() {
            const picked = $('input[name="sync_pick"]:checked').val();
            if (!picked || !syncAklId) return;
            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            $.ajax({
                url: "{{ url('api/akls') }}/" + syncAklId + "/apply-izin",
                type: 'PUT',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { izin_edar_id: picked },
                success: function(res) {
                    $('#aklSyncModal').modal('hide');
                    show_message(res.message || 'AKL disinkron!', 'success');
                    loadData();
                },
                error: function(xhr) {
                    show_message(xhr.responseJSON?.message || 'Gagal menyimpan!');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save yang Dipilih');
                }
            });
        });

        // ── Utility ──────────────────────────────────────────

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>
@endpush
