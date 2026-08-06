<script>
    const API_URL = '{{ route('api.izin_edars.index') }}';
    const IMPORT_BATCH_URL = '{{ route('api.izin_edars.import_batch') }}';
    let currentPage = 1;
    let currentPerPage = 10;
    let currentKategori = '';
    let currentSearch = '';
    let searchTimeout = null;

    $(document).ready(function() {
        loadData();

        // Kategori select filter
        $('#kategoriSelect').on('change', function() {
            currentKategori = $(this).val();
            currentPage = 1;
            loadData();
        });

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

        // Detail button click
        $(document).on('click', '.btn-detail', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const rowData = window.lastRowDataMap && window.lastRowDataMap[id];
            if (rowData) showDetail(rowData);
        });

        // Copy button click
        $(document).on('click', '.btn-copy-row', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const rowData = window.lastRowDataMap && window.lastRowDataMap[id];
            if (rowData) copyRowToClipboard(rowData);
        });
    });

    // ── Data Loading ─────────────────────────────────────

    function loadData() {
        const $tbody = $('#tableBody');
        $tbody.html(
            `<tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>`
        );

        $.ajax({
            url: API_URL,
            type: 'GET',
            data: {
                page: currentPage,
                per_page: currentPerPage,
                kategori: currentKategori,
                search: currentSearch,
            },
            success: function(res) {
                renderTable(res.data);
                renderPagination(res.page, res.total_pages, res.total);
                window.lastRowDataMap = {};
                res.data.forEach(row => {
                    window.lastRowDataMap[row.id] = row;
                });
            },
            error: function() {
                $tbody.html(
                    `<tr><td colspan="9" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat data</td></tr>`
                );
            }
        });
    }

    // ── Table Rendering ──────────────────────────────────

    function renderTable(data) {
        const $tbody = $('#tableBody');

        if (!data || data.length === 0) {
            $tbody.html(
                `<tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i>Tidak ada data</td></tr>`
            );
            return;
        }

        const kelasMap = {
            'AKD': 'badge-akd',
            'AKL': 'badge-akl',
            'PKD': 'badge-pkd',
            'PKL': 'badge-pkl',
        };

        let html = '';
        data.forEach(row => {
            const badgeClass = kelasMap[row.kategori] || 'badge-lainnya';
            const tglTerbit = row.tgl_terbit ? formatDate(row.tgl_terbit) : '-';
            const tglExpHtml = formatExpDate(row.tgl_exp);

            html += `<tr data-id="${row.id}">
                <td>
                    <div class="d-flex" style="gap: 4px;">
                        <button type="button" class="btn btn-action btn-outline-primary btn-detail" data-id="${row.id}" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-action btn-outline-secondary btn-copy-row" data-id="${row.id}" title="Copy">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </td>
                <td><span class="badge-kategori ${badgeClass}">${escapeHtml(row.kategori)}</span></td>
                <td class="font-weight-bold" style="white-space:nowrap">${escapeHtml(row.nomor_izin_edar)}</td>
                <td style="white-space:nowrap">${tglTerbit}</td>
                <td style="white-space:nowrap">${tglExpHtml}</td>
                <td>${escapeHtml(row.merk)}</td>
                <td>${escapeHtml(row.jenis_produk || '-')}</td>
                <td>${escapeHtml(row.pendaftar)}</td>
                <td>${escapeHtml(row.pabrik || '-')}</td>
            </tr>`;
        });

        $tbody.html(html);
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
                scrollTop: $('#tableIzinEdar').offset().top - 80
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

    // ── Detail Modal ─────────────────────────────────────

    function showDetail(d) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><td class="font-weight-bold text-muted" style="width:140px">Kategori</td><td>${escapeHtml(d.kategori)}</td></tr>
                        <tr><td class="font-weight-bold text-muted">No. Izin Edar</td><td>${escapeHtml(d.nomor_izin_edar)}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Tgl Terbit</td><td>${d.tgl_terbit ? formatDate(d.tgl_terbit) : '-'}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Tgl Expired</td><td>${formatExpDate(d.tgl_exp)}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Merk</td><td>${escapeHtml(d.merk)}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Jenis Produk</td><td>${escapeHtml(d.jenis_produk || '-')}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><td class="font-weight-bold text-muted" style="width:140px">Pendaftar</td><td>${escapeHtml(d.pendaftar)}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Alamat Pendaftar</td><td>${escapeHtml(d.alamat_pendaftar || '-')}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Pabrik</td><td>${escapeHtml(d.pabrik || '-')}</td></tr>
                        <tr><td class="font-weight-bold text-muted">Alamat Pabrik</td><td>${escapeHtml(d.alamat_pabrik || '-')}</td></tr>
                        ${d.sub_kategori ? `<tr><td class="font-weight-bold text-muted">Sub Kategori</td><td>${escapeHtml(d.sub_kategori)}</td></tr>` : ''}
                        ${d.kelompok_produk ? `<tr><td class="font-weight-bold text-muted">Kelompok Produk</td><td>${escapeHtml(d.kelompok_produk)}</td></tr>` : ''}
                        ${d.tipe ? `<tr><td class="font-weight-bold text-muted">Tipe</td><td>${escapeHtml(d.tipe)}</td></tr>` : ''}
                        ${d.kelas ? `<tr><td class="font-weight-bold text-muted">Kelas</td><td>${escapeHtml(d.kelas)}</td></tr>` : ''}
                        ${d.kelas_resiko ? `<tr><td class="font-weight-bold text-muted">Kelas Resiko</td><td>${escapeHtml(d.kelas_resiko)}</td></tr>` : ''}
                    </table>
                </div>
            </div>
        `;
        $('#detailBody').html(html);
        $('#modalDetail').modal('show');
    }

    // ── Utility Functions ────────────────────────────────

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function formatExpDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        const now = new Date();
        const diff = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
        let cls = '';
        if (diff < 0) cls = 'expired-text';
        else if (diff <= 30) cls = 'expiring-text';
        return `<span class="${cls}">${formatDate(dateStr)}</span>`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function copyRowToClipboard(row) {
        const fields = [
            ['Kategori', row.kategori],
            ['No. Izin Edar', row.nomor_izin_edar],
            ['Tgl Terbit', row.tgl_terbit ? formatDate(row.tgl_terbit) : '-'],
            ['Tgl Expired', row.tgl_exp ? formatDate(row.tgl_exp) : '-'],
            ['Merk', row.merk],
            ['Jenis Produk', row.jenis_produk || '-'],
            ['Pendaftar', row.pendaftar],
            ['Alamat Pendaftar', row.alamat_pendaftar || '-'],
            ['Pabrik', row.pabrik || '-'],
            ['Alamat Pabrik', row.alamat_pabrik || '-'],
        ];
        if (row.sub_kategori) fields.push(['Sub Kategori', row.sub_kategori]);
        if (row.kelompok_produk) fields.push(['Kelompok Produk', row.kelompok_produk]);
        if (row.tipe) fields.push(['Tipe', row.tipe]);
        if (row.kelas) fields.push(['Kelas', row.kelas]);
        if (row.kelas_resiko) fields.push(['Kelas Resiko', row.kelas_resiko]);

        const text = fields.map(([k, v]) => `${k}: ${v}`).join('\n');

        navigator.clipboard.writeText(text).then(() => {
            showToast('Data berhasil disalin ke clipboard');
        }).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast('Data berhasil disalin ke clipboard');
        });
    }

    function showToast(message) {
        let toast = document.querySelector('.toast-copy');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'toast-copy';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
        toast.classList.add('show');
        clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    }

    function formatFileSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
        pow = Math.min(pow, units.length - 1);
        bytes /= (1 << (10 * pow));
        return bytes.toFixed(1) + ' ' + units[pow];
    }
</script>
