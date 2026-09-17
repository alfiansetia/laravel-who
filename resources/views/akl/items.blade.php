@extends('template', ['title' => 'Item AKL ' . $akl->reg_no])

@push('css')
    <style>
        .akl-search-wrap { position: relative; }
        .akl-search-list {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 1050;
            background: #fff; border: 1px solid #e2e8f0; border-top: none;
            border-radius: 0 0 8px 8px; max-height: 260px; overflow-y: auto;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
        }
        .akl-search-item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
        .akl-search-item:hover, .akl-search-item.active { background: #eef2ff; }
        .akl-search-item:last-child { border-bottom: none; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-certificate mr-2 text-info"></i>Data AKL</h6>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless mb-0" style="font-size:.85rem">
                            <tr><td class="text-muted" style="width:90px">Reg No</td><td><strong>{{ $akl->reg_no }}</strong></td></tr>
                            <tr><td class="text-muted">Nama</td><td>{{ $akl->reg_name ?: '-' }}</td></tr>
                            <tr><td class="text-muted">Vendor</td><td>{{ $akl->vendor ?: '-' }}</td></tr>
                            <tr><td class="text-muted">Expired</td><td>{{ $akl->date_expired ? $akl->date_expired->format('d-m-Y') : '-' }}</td></tr>
                            <tr><td class="text-muted">Jumlah</td><td><span class="badge badge-info" id="itemCountBadge">{{ $akl->items_count }} item</span></td></tr>
                        </table>
                        <hr>
                        <a href="{{ route('akls.index') }}" class="btn btn-secondary btn-sm btn-block">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke List AKL
                        </a>
                        @if ($akl->file)
                            <a href="{{ route('akls.show', $akl->id) }}" target="_blank" class="btn btn-outline-info btn-sm btn-block mt-2">
                                <i class="fas fa-file mr-1"></i> Lihat Lampiran
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card card-sm mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-plus mr-2 text-success"></i>Tambah Item</h6>
                    </div>
                    <div class="card-body p-3">
                        <form id="formAddItem" autocomplete="off">
                            <div class="form-group">
                                <label class="small font-weight-bold">Product Code / Custom <span class="text-danger">*</span></label>
                                <div class="akl-search-wrap">
                                    <input type="text" id="inputCode" class="form-control form-control-sm"
                                        placeholder="Ketik code... mis. WH-001 atau custom" required maxlength="100">
                                    <div id="searchList" class="akl-search-list d-none"></div>
                                </div>
                                <small class="form-text text-muted">
                                    Cari berdasarkan <strong>code</strong> product. Klik saran untuk pakai master
                                    (name ikut tersimpan), atau langsung ketik bebas lalu <strong>Tambah</strong>
                                    untuk item custom di luar master.
                                </small>
                                <div id="codeHint" class="small mt-1"></div>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm btn-block" id="btnAdd">
                                <i class="fas fa-plus mr-1"></i> Tambah ke AKL ini
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-file-alt mr-2 text-info"></i>Preview Dokumen</h6>
                        @if ($akl->file)
                            <a href="{{ route('akls.show', $akl->id) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka tab baru
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-2">
                        @if ($akl->file)
                            @if ($akl->is_pdf)
                                <div id="docPdfWrap" class="text-left"
                                    style="max-height: 480px; overflow-y: auto; background: #525659; border-radius: 4px;">
                                    <div class="text-center text-white py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 mb-0">Memuat PDF...</p></div>
                                </div>
                                <embed id="docPdfFallback" type="application/pdf" class="d-none w-100" style="height: 480px;">
                            @else
                                <div class="text-center">
                                    <img src="{{ route('akls.show', $akl->id) }}" class="img-fluid" style="max-height: 480px;" alt="Dokumen AKL">
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Dokumen tidak ada.</strong> AKL ini belum punya lampiran, jadi tidak ada yang bisa dibaca di sini.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list mr-2 text-primary"></i>Item dalam AKL ini</h6>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadItems()">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size:.85rem">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Code</th>
                                        <th>Nama</th>
                                        <th style="width:130px">Status</th>
                                        <th style="width:110px" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cek/Sync ke tabel product -->
    <div class="modal fade" id="itemSyncModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="itemSyncTitle">Cek Product</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3" id="itemSyncBody" style="font-size:.85rem">
                    <div class="text-center text-muted py-4">Memuat...</div>
                </div>
                <div class="modal-footer py-2 d-flex justify-content-between">
                    <small class="text-muted align-self-center" id="itemSyncInfo"></small>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary btn-sm" id="itemSyncSave" disabled>
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
        const AKL_ID = {{ $akl->id }};
        const DOC_URL = @json($akl->file ? route('akls.show', $akl->id) : null);
        const DOC_IS_PDF = {{ $akl->is_pdf ? 'true' : 'false' }};
        const DOC_PDF_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const DOC_PDF_MAX_PAGES = 10;

        function renderDocPdf(url) {
            var wrap = $('#docPdfWrap');
            var fallback = $('#docPdfFallback');
            if (!wrap.length) return;
            if (typeof pdfjsLib === 'undefined') {
                wrap.addClass('d-none');
                fallback.removeClass('d-none').attr('src', url);
                return;
            }
            pdfjsLib.GlobalWorkerOptions.workerSrc = DOC_PDF_WORKER;
            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                var total = pdf.numPages;
                var show = Math.min(total, DOC_PDF_MAX_PAGES);
                wrap.empty();
                var chain = Promise.resolve();
                for (var n = 1; n <= show; n++) {
                    (function(pageNum) {
                        chain = chain.then(function() {
                            return pdf.getPage(pageNum).then(function(page) {
                                var scale = (wrap.width() - 20) / page.getViewport({ scale: 1 }).width;
                                var viewport = page.getViewport({ scale: scale });
                                var canvas = document.createElement('canvas');
                                canvas.style.display = 'block';
                                canvas.style.margin = '10px auto';
                                canvas.style.maxWidth = '100%';
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;
                                wrap.append(canvas);
                                return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;
                            });
                        });
                    })(n);
                }
                chain.then(function() {
                    if (total > show) {
                        wrap.append('<p class="text-center text-white py-2 mb-0">... + ' + (total - show) + ' halaman lagi. <a href="' + url + '" target="_blank" class="text-warning">Buka penuh di tab baru</a>.</p>');
                    }
                });
            }).catch(function(err) {
                console.error('PDF.js gagal:', err);
                wrap.addClass('d-none');
                fallback.removeClass('d-none').attr('src', url);
            });
        }
        const URL_ITEMS_API = "{{ route('api.akl_items.index') }}";
        const URL_ITEM_STORE = "{{ route('api.akl_items.store') }}";
        const URL_ITEM_BASE = "{{ url('api/akl-items') }}";
        const URL_PRODUCT_SEARCH = "{{ route('api.products.search') }}";

        let searchTimeout = null;
        let activeIdx = -1;
        let selectedName = null; // name dari saran product yang diklik (ikut tersimpan)

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        function loadItems() {
            const $tbody = $('#itemsBody');
            $tbody.html(`<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat...</td></tr>`);
            $.ajax({
                url: URL_ITEMS_API,
                type: 'GET',
                data: { akl_id: AKL_ID },
                success: function(res) {
                    const data = res.data || [];
                    $('#itemCountBadge').text(data.length + ' item');
                    if (!data.length) {
                        $tbody.html(`<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Belum ada item. Tambahkan via form di kiri.</td></tr>`);
                        return;
                    }
                    let html = '';
                    data.forEach((row, i) => {
                        const isCustom = row.is_custom || !row.product;
                        const badge = isCustom
                            ? `<span class="badge badge-warning">Custom</span>`
                            : `<span class="badge badge-success">Terdaftar</span>`;
                        const storedName = row.name || (row.product ? row.product.name : null);
                        const pname = storedName ? escapeHtml(storedName) : '<span class="text-muted">-</span>';
                        html += `<tr>
                            <td class="text-muted">${i + 1}</td>
                            <td><strong>${escapeHtml(row.code)}</strong></td>
                            <td>${pname}</td>
                            <td>${badge}</td>
                            <td class="text-center" style="white-space:nowrap">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-item-sync" data-id="${row.id}" data-code="${escapeHtml(row.code)}" title="Cek/sync ke tabel product">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteItem(${row.id}, '${escapeHtml(row.code)}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                    $tbody.html(html);
                },
                error: function() {
                    $tbody.html(`<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data</td></tr>`);
                }
            });
        }

        function deleteItem(id, code) {
            confirmation('Hapus item "' + code + '" dari AKL ini?', function(confirm) {
                if (!confirm) return;
                $.ajax({
                    url: URL_ITEM_BASE + '/' + id,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        show_message(res.message || 'Item dihapus!', 'success');
                        loadItems();
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON?.message || 'Gagal menghapus!');
                    }
                });
            });
        }

        // ── Autocomplete product code (boleh custom) ──
        $('#inputCode').on('keyup', function(e) {
            if (['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(e.key)) return;
            clearTimeout(searchTimeout);
            const q = $(this).val().trim();
            $('#codeHint').html('');
            selectedName = null; // ketikan baru = anggap custom sampai saran diklik
            if (q.length < 1) {
                $('#searchList').addClass('d-none').empty();
                return;
            }
            searchTimeout = setTimeout(() => searchProduct(q), 300);
        });

        $('#inputCode').on('keydown', function(e) {
            const $items = $('#searchList .akl-search-item');
            if ($('#searchList').hasClass('d-none') || !$items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, $items.length - 1);
                highlightSearch($items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
                highlightSearch($items);
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                $items.eq(activeIdx).click();
            } else if (e.key === 'Escape') {
                $('#searchList').addClass('d-none');
            }
        });

        function highlightSearch($items) {
            $items.removeClass('active');
            $items.eq(activeIdx).addClass('active');
        }

        function searchProduct(q) {
            $.ajax({
                url: URL_PRODUCT_SEARCH,
                type: 'GET',
                data: { q: q },
                success: function(res) {
                    const data = res.data || [];
                    const $list = $('#searchList');
                    activeIdx = -1;
                    if (!data.length) {
                        $list.html(`<div class="p-2 small text-muted">Tidak ada di master — akan disimpan sebagai <strong>Custom</strong> bila ditambah.</div>`).removeClass('d-none');
                        $('#codeHint').html(`<span class="badge badge-warning">Custom</span> <span class="text-muted">code baru di luar master</span>`);
                        return;
                    }
                    let html = '';
                    data.forEach(p => {
                        html += `<div class="akl-search-item" data-code="${escapeHtml(p.code)}" data-name="${escapeHtml(p.name || '')}">
                            <strong>${escapeHtml(p.code)}</strong><br><small class="text-muted">${escapeHtml(p.name || '-')}</small>
                        </div>`;
                    });
                    html += `<div class="p-2 small text-muted border-top">Tidak ketemu? Biarkan ketikanmu — akan disimpan sebagai <strong>Custom</strong>.</div>`;
                    $list.html(html).removeClass('d-none');
                }
            });
        }

        $(document).on('click', '#searchList .akl-search-item', function() {
            const code = $(this).data('code');
            const name = $(this).data('name');
            $('#inputCode').val(code);
            selectedName = name || null;
            $('#searchList').addClass('d-none').empty();
            $('#codeHint').html(`<span class="badge badge-success">Terdaftar</span> <span class="text-muted">${escapeHtml(name || '')}</span>`);
            $('#inputCode').focus();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.akl-search-wrap').length) {
                $('#searchList').addClass('d-none');
            }
        });

        $('#inputCode').on('focus', function() {
            const q = $(this).val().trim();
            if (q.length >= 1) searchProduct(q);
        });

        // ── Submit tambah item ──
        $('#formAddItem').on('submit', function(e) {
            e.preventDefault();
            const code = $('#inputCode').val().trim();
            if (!code) {
                show_message('Code wajib diisi!');
                return;
            }
            const $btn = $('#btnAdd');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            $.ajax({
                url: URL_ITEM_STORE,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { akl_id: AKL_ID, code: code, name: selectedName },
                success: function(res) {
                    const isCustom = res.data && (res.data.is_custom || !res.data.product);
                    show_message((res.message || 'Ditambahkan!') + (isCustom ? ' (Custom)' : ''), 'success');
                    $('#inputCode').val('');
                    selectedName = null;
                    $('#codeHint').html('');
                    $('#searchList').addClass('d-none').empty();
                    loadItems();
                },
                error: function(xhr) {
                    show_message(xhr.responseJSON?.message || 'Gagal menambah item!');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-plus mr-1"></i> Tambah ke AKL ini');
                }
            });
        });

        // ── Cek/Sync item ke tabel product (pilih kandidat → save code/name) ──

        let syncItemId = null;

        function syncBadge(sama) {
            return sama
                ? `<span class="badge badge-success">Sama</span>`
                : `<span class="badge badge-danger">Beda</span>`;
        }

        $(document).on('click', '.btn-item-sync', function() {
            syncItemId = $(this).data('id');
            const code = $(this).data('code') || ('#' + syncItemId);
            $('#itemSyncTitle').text('Cek ' + code + ' → Product');
            $('#itemSyncInfo').text('');
            $('#itemSyncSave').prop('disabled', true);
            $('#itemSyncBody').html(
                `<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Mencocokkan code ke tabel product...</div>`
            );
            $('#itemSyncModal').modal('show');

            $.ajax({
                url: URL_ITEM_BASE + '/' + syncItemId + '/check-product',
                type: 'GET',
                success: function(res) {
                    renderItemSyncModal(res.data);
                },
                error: function(xhr) {
                    $('#itemSyncBody').html(
                        `<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>${escapeHtml(xhr.responseJSON?.message || 'Gagal memuat data product')}</div>`
                    );
                }
            });
        });

        function renderItemSyncModal(data) {
            const item = data.item || {};
            const matches = data.matches || [];
            const bisaDipilih = matches.filter(m => !m.is_synced).length;
            $('#itemSyncInfo').text(matches.length
                ? matches.length + ' kandidat (' + bisaDipilih + ' bisa dipilih)'
                : 'Tidak ada yang cocok');

            let html = `<div class="alert alert-light border py-2 mb-3">
                <strong>Item saat ini:</strong><br>
                <span class="text-muted">Code:</span> <strong>${escapeHtml(item.code)}</strong> &nbsp;
                <span class="text-muted">Nama:</span> ${escapeHtml(item.name) || '<span class="text-muted">-</span>'}
            </div>`;

            if (!matches.length) {
                html += `<div class="text-center text-muted py-3"><i class="fas fa-inbox mr-2"></i>Tidak ada yang cocok di tabel product untuk code <strong>${escapeHtml(item.code)}</strong> — item ini Custom.</div>`;
                $('#itemSyncBody').html(html);
                return;
            }

            html += `<div class="table-responsive"><table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:35px"></th>
                        <th>Code</th>
                        <th>Nama</th>
                        <th>Cek</th>
                    </tr>
                </thead><tbody>`;

            matches.forEach((m) => {
                const synced = !!m.is_synced;
                html += `<tr class="${synced ? 'table-success' : ''}">
                    <td class="text-center align-middle">
                        ${synced
                            ? `<input type="radio" disabled title="Sudah sama — tidak bisa dipilih">`
                            : `<input type="radio" name="item_sync_pick" value="${m.id}">`}
                    </td>
                    <td><strong>${escapeHtml(m.code)}</strong><br>${syncBadge(m.diff.code_sama)}</td>
                    <td>${escapeHtml(m.name) || '<span class="text-muted">-</span>'}<br>${syncBadge(m.diff.nama_sama)}</td>
                    <td>${synced
                        ? `<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sudah sama</span>`
                        : `<small class="text-muted">Pilih lalu Save untuk pakai code/name ini</small>`}</td>
                </tr>`;
            });

            html += `</tbody></table></div>
                <small class="form-text text-muted mt-2">Baris hijau <strong>Sudah sama</strong> tidak bisa dipilih. Pilih kandidat lain lalu klik <strong>Save yang Dipilih</strong>.</small>`;

            $('#itemSyncBody').html(html);
        }

        $(document).on('change', 'input[name="item_sync_pick"]', function() {
            $('#itemSyncSave').prop('disabled', !$('input[name="item_sync_pick"]:checked').length);
        });

        $('#itemSyncSave').on('click', function() {
            const picked = $('input[name="item_sync_pick"]:checked').val();
            if (!picked || !syncItemId) return;
            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            $.ajax({
                url: URL_ITEM_BASE + '/' + syncItemId + '/apply-product',
                type: 'PUT',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { product_id: picked },
                success: function(res) {
                    $('#itemSyncModal').modal('hide');
                    show_message(res.message || 'Item disinkron!', 'success');
                    loadItems();
                },
                error: function(xhr) {
                    show_message(xhr.responseJSON?.message || 'Gagal menyimpan!');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save yang Dipilih');
                }
            });
        });

        $(document).ready(function() {
            loadItems();
            if (DOC_URL && DOC_IS_PDF) renderDocPdf(DOC_URL);
        });
    </script>
@endpush
