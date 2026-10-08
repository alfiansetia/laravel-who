@extends('template', ['title' => 'Edit Packing List'])
@push('css')
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
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">
        <form method="POST" action="{{ route('api.packs.update', $data->id) }}" id="form">
            @csrf
            <div class="card card-sm">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-edit mr-2"></i>EDIT PACKING LIST</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-building mr-1"></i> VENDOR</label>
                            <select name="vendor_id" id="vendor_id" class="custom-select select2" style="width: 100%"
                                required>
                                <option value="">--- Pilih Vendor ---</option>
                                @foreach ($vendors as $item)
                                    <option data-id="{{ $item->id }}" value="{{ $item->id }}"
                                        @selected($data->vendor_id == $item->id)>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-box mr-1"></i> PRODUCT</label>
                            <select name="product_id" id="product_id" class="custom-select select2" style="width: 100%"
                                required>
                                <option value="">--- Pilih Produk ---</option>
                                @foreach ($products as $item)
                                    <option data-id="{{ $item->id }}" data-code="{{ $item->code }}"
                                        data-name="{{ $item->name }}" value="{{ $item->id }}"
                                        @selected($data->product_id == $item->id)>
                                        [{{ $item->code }}] {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-tag mr-1"></i> PACKING LIST NAME</label>
                            <textarea name="name" id="name" class="form-control" rows="1" maxlength="200" required>{{ $data->name }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-comment-dots mr-1"></i> VENDOR DESC</label>
                            <textarea name="vendor_desc" id="vendor_desc" class="form-control" rows="1" maxlength="200">{{ $data->vendor_desc }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-align-left mr-1"></i> PACKING LIST DESC</label>
                            <textarea name="desc" id="desc" class="form-control" rows="1" maxlength="200">{{ $data->desc }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-file-import mr-1"></i> IMPORT FROM TEXT (EXCEL TAB)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" id="btn_import_clear" class="btn btn-outline-danger" title="Bersihkan">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <textarea name="import" id="import" class="form-control" placeholder="Paste data Excel di sini..." rows="1"></textarea>
                                <div class="input-group-append">
                                    <button type="button" id="btn_import" class="btn btn-info font-weight-bold">
                                        <i class="fas fa-download mr-1"></i> IMPORT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light text-center">
                    <button type="submit" id="btn_simpan" class="btn btn-primary px-4 mr-1">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('api.packs.download', $data->id) }}" class="btn btn-success px-4 mr-1" target="_blank">
                        <i class="fas fa-file-excel mr-1"></i> Download Excel
                    </a>
                    <a href="{{ route('packs.edit', $data->id) }}" class="btn btn-outline-warning px-3 mr-1">
                        <i class="fas fa-sync mr-1"></i> Refresh
                    </a>
                    <a href="{{ route('packs.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </form>

        <div class="card card-sm mt-3 shadow-sm">
            <div class="card-header bg-light py-2 d-flex align-items-center">
                <h6 class="font-weight-bold mb-0 text-dark"><i class="fas fa-clipboard-list mr-2"></i>DATA ITEM PACKING LIST</h6>
                <div class="ml-auto">
                    <button type="button" id="btn_add_item" class="btn btn-sm btn-info"><i class="fas fa-plus mr-1"></i>Item</button>
                    <button type="button" id="btn_add_group" class="btn btn-sm btn-primary"><i class="fas fa-folder-plus mr-1"></i>Header / Part</button>
                    <button type="button" id="btn_empty" class="btn btn-sm btn-danger"><i class="fas fa-trash mr-1"></i>Kosongkan</button>
                    <button type="button" id="btn_refresh" class="btn btn-sm btn-warning"><i class="fas fa-sync mr-1"></i>Refresh</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-hover mb-0" style="width: 100%;">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th style="width: 230px" class="text-center">AKSI</th>
                                <th style="width: 60px" class="text-center">NO</th>
                                <th>NAMA ITEM / DESKRIPSI</th>
                                <th style="width: 150px" class="text-center">QUANTITY</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="p-2 small text-muted border-top">
                    <i class="fas fa-info-circle mr-1"></i>
                    Header/Part = baris grup (tanpa qty). <b>→</b> jadikan anak dari baris atasnya (a,b,c), <b>←</b> kembalikan ke level atas (tepat di bawah grupnya). <b>↑↓</b> geser 1 langkah, <b>⤒⤓</b> ke paling atas/bawah dalam grupnya. <b>#</b> tampil/sembunyikan nomor baris itu (kuning = nomor disembunyikan). Nomor tampil otomatis dihitung ulang tiap ada perubahan.
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal_item" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="form_item">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Add Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="ftype">TIPE</label>
                                <select id="ftype" class="form-control">
                                    <option value="item">Item biasa (ada qty)</option>
                                    <option value="group">Header / Grup (tanpa qty, mis. Printer :, Part 1 :)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="item">NAMA</label>
                                <input name="item" type="text" class="form-control" id="item" required>
                            </div>
                            <div class="form-group" id="wrap_qty">
                                <label for="qty">QTY</label>
                                <input name="qty" type="text" class="form-control" id="qty">
                            </div>
                            <div class="form-group" id="wrap_parent" style="display:none;">
                                <label for="fparent">SIMPAN SEBAGAI ANAK DARI</label>
                                <select id="fparent" class="form-control">
                                    <option value="">— Level atas —</option>
                                </select>
                                <small class="text-muted">Anak dari grup bernomor tampil a,b,c. Anak dari grup tanpa nomor (Part) tampil 1,2,3.</small>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="fshow" checked>
                                <label class="form-check-label" for="fshow">Tampilkan nomor baris ini</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Close
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fab fa-telegram-plane mr-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const URL_INDEX_API = "{{ route('api.packs.index') }}"
        const URL_INDEX_ITEM_API = "{{ route('api.pack_items.index') }}"
        $(document).ready(function() {
            $('#product_id').select2({
                theme: 'bootstrap4',
            })

            $('#vendor_id').select2({
                theme: 'bootstrap4',
            })

            // ================= MODEL HIERARKI (max 2 level) =================
            let rows = []; // tops: {uid,item,qty,is_group,show_number,children:[{uid,item,qty,show_number}]}
            let _uid = 1;
            const uid = () => 'u' + (_uid++) + Date.now().toString(36);

            function alphaLabel(i) { // 0->a ... 25->z, 26->aa
                let s = '', n = i;
                do { s = String.fromCharCode(97 + (n % 26)) + s; n = Math.floor(n / 26) - 1; } while (n >= 0);
                return s;
            }

            function computeNumbers() { // return flat [{kind,ti,ci,no}]
                let out = [], counter = 1;
                rows.forEach((t, ti) => {
                    let topNo = t.show_number ? String(counter++) : '';
                    out.push({ kind: 'top', ti, ci: -1, no: topNo });
                    if (t.show_number) {
                        (t.children || []).forEach((c, ci) => {
                            out.push({ kind: 'child', ti, ci, no: c.show_number ? alphaLabel(ci) : '' });
                        });
                    } else {
                        let cc = 1;
                        (t.children || []).forEach((c, ci) => {
                            out.push({ kind: 'child', ti, ci, no: c.show_number ? String(cc++) : '' });
                        });
                    }
                });
                return out;
            }

            function esc(s) {
                return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function render() {
                const tb = $('#table tbody');
                tb.empty();
                const flat = computeNumbers();
                flat.forEach(f => {
                    const isTop = f.kind === 'top';
                    const t = rows[f.ti];
                    const d = isTop ? t : t.children[f.ci];
                    const badge = isTop && t.is_group
                        ? (t.show_number ? ' <span class="badge badge-primary">GRUP</span>' : ' <span class="badge badge-secondary">PART</span>')
                        : (!isTop ? ' <span class="badge badge-light border">sub</span>' : '');
                    const nameHtml = `<span style="display:inline-block;padding-left:${isTop ? 0 : 28}px;${(isTop && t.is_group) ? 'font-weight:bold;' : ''}">${esc(d.item)}${badge}</span>`;
                    const qtyHtml = (isTop && t.is_group) ? '<span class="text-muted">—</span>' : esc(d.qty || '');
                    const btnIndent = isTop ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-indent" title="Jadikan anak dari baris atasnya">→</button>` : '';
                    const btnOutdent = !isTop ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-outdent" title="Kembalikan ke level atas">←</button>` : '';
                    const noHidden = !d.show_number;
                    const btnToggleNo = `<button type="button" class="btn btn-sm ${noHidden ? 'btn-warning' : 'btn-outline-secondary'} btn-toggle-no" title="Tampilkan / sembunyikan nomor baris ini"><i class="fas fa-hashtag"></i></button>`;
                    tb.append(`<tr data-kind="${f.kind}" data-ti="${f.ti}" data-ci="${f.ci}">
                        <td class="text-center"><div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-danger del-item" title="Hapus"><i class="fas fa-trash"></i></button>
                            <button type="button" class="btn btn-sm btn-secondary btn-top" title="Pindah ke paling atas"><i class="fas fa-angle-double-up"></i></button>
                            <button type="button" class="btn btn-sm btn-secondary btn-up" title="Naik"><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-sm btn-secondary btn-down" title="Turun"><i class="fas fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-sm btn-secondary btn-bottom" title="Pindah ke paling bawah"><i class="fas fa-angle-double-down"></i></button>
                            ${btnToggleNo}${btnIndent}${btnOutdent}
                        </div></td>
                        <td class="text-center font-weight-bold">${esc(f.no)}</td>
                        <td class="text-left cell-item" style="cursor:text;">${nameHtml}</td>
                        <td class="text-center cell-qty" style="cursor:text;">${qtyHtml}</td>
                    </tr>`);
                });
                if (!flat.length) {
                    tb.append('<tr><td colspan="4" class="text-center text-muted py-3">Belum ada item. Tambah Item / Header, atau Import dari Excel.</td></tr>');
                }
                refreshParentOptions();
            }

            function refreshParentOptions() {
                const sel = $('#fparent');
                const cur = sel.val();
                sel.empty().append('<option value="">— Level atas —</option>');
                rows.forEach((t, i) => {
                    sel.append(`<option value="${i}">${esc((t.show_number ? (computeTopNo(i) + '. ') : '') + (t.item || '(tanpa nama)'))}</option>`);
                });
                sel.val(cur);
            }

            function computeTopNo(ti) {
                let c = 1;
                for (let i = 0; i <= ti; i++) if (rows[i].show_number) { if (i === ti) return String(c); c++; }
                return '';
            }

            function appendFlatAsNested(flatItems) {
                const items = (flatItems || []).slice().sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || (a.id ?? 0) - (b.id ?? 0));
                const topsById = new Map();
                const tops = [];
                items.forEach(it => {
                    if (it.parent_id == null) {
                        const t = { uid: uid(), item: it.item || '', qty: it.qty || '', is_group: !!it.is_group, show_number: it.show_number !== false, children: [] };
                        topsById.set(it.id, t);
                        tops.push(t);
                    }
                });
                items.forEach(it => {
                    if (it.parent_id != null && topsById.has(it.parent_id)) {
                        topsById.get(it.parent_id).children.push({ uid: uid(), item: it.item || '', qty: it.qty || '', show_number: it.show_number !== false });
                    } else if (it.parent_id != null) {
                        tops.push({ uid: uid(), item: it.item || '', qty: it.qty || '', is_group: false, show_number: true, children: [] });
                    }
                });
                rows = rows.concat(tops);
                render();
            }

            function loadItems() {
                $.ajax({
                    url: URL_INDEX_ITEM_API,
                    type: 'GET',
                    data: { pack_id: "{{ $data->id }}" },
                    success: function(res) {
                        rows = [];
                        appendFlatAsNested(res.data || []);
                    },
                    error: function(xhr) { show_message((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat item!'); }
                });
            }

            function openModal(presetType) {
                $('#ftype').val(presetType || 'item').trigger('change');
                $('#item').val('');
                $('#qty').val('');
                $('#fshow').prop('checked', true);
                refreshParentOptions();
                $('#fparent').val('');
                $('#modal_item').modal('show');
            }

            $('#ftype').on('change', function() {
                const isGroup = $(this).val() === 'group';
                $('#wrap_qty').toggle(!isGroup);
                $('#wrap_parent').toggle(!isGroup);
            });

            $('#btn_add_item').click(() => openModal('item'));
            $('#btn_add_group').click(() => openModal('group'));
            $('#btn_empty').click(() => { rows = []; render(); });

            // Tampilkan / sembunyikan nomor per baris (tanpa hapus barisnya)
            $('#table').on('click', '.btn-toggle-no', function() {
                const { kind, ti, ci } = locate($(this).closest('tr'));
                const d = kind === 'top' ? rows[ti] : rows[ti].children[ci];
                d.show_number = !d.show_number;
                render();
            });
            $('#btn_refresh').click(() => loadItems());

            function locate(tr) {
                return { kind: tr.data('kind'), ti: parseInt(tr.data('ti')), ci: parseInt(tr.data('ci')) };
            }

            $('#table').on('click', '.btn-top', function() {
                const { kind, ti, ci } = locate($(this).closest('tr'));
                if (kind === 'top') {
                    if (ti <= 0) return;
                    const [moved] = rows.splice(ti, 1);
                    rows.unshift(moved);
                } else {
                    if (ci <= 0) return;
                    const ch = rows[ti].children;
                    const [moved] = ch.splice(ci, 1);
                    ch.unshift(moved);
                }
                render();
            });

            $('#table').on('click', '.btn-bottom', function() {
                const { kind, ti, ci } = locate($(this).closest('tr'));
                if (kind === 'top') {
                    if (ti >= rows.length - 1) return;
                    const [moved] = rows.splice(ti, 1);
                    rows.push(moved);
                } else {
                    const ch = rows[ti].children;
                    if (ci >= ch.length - 1) return;
                    const [moved] = ch.splice(ci, 1);
                    ch.push(moved);
                }
                render();
            });

            $('#table').on('click', '.btn-up', function() {
                const { kind, ti, ci } = locate($(this).closest('tr'));
                if (kind === 'top') {
                    if (ti <= 0) return;
                    [rows[ti - 1], rows[ti]] = [rows[ti], rows[ti - 1]];
                } else {
                    if (ci <= 0) return;
                    const ch = rows[ti].children;
                    [ch[ci - 1], ch[ci]] = [ch[ci], ch[ci - 1]];
                }
                render();
            });

            $('#table').on('click', '.btn-down', function() {
                const { kind, ti, ci } = locate($(this).closest('tr'));
                if (kind === 'top') {
                    if (ti >= rows.length - 1) return;
                    [rows[ti + 1], rows[ti]] = [rows[ti], rows[ti + 1]];
                } else {
                    const ch = rows[ti].children;
                    if (ci >= ch.length - 1) return;
                    [ch[ci + 1], ch[ci]] = [ch[ci], ch[ci + 1]];
                }
                render();
            });

            // Jadikan top sebagai anak dari top sebelumnya (max 2 level)
            $('#table').on('click', '.btn-indent', function() {
                const { ti } = locate($(this).closest('tr'));
                if (ti <= 0) { show_message('Baris pertama tidak bisa di-indent!', 'warning'); return; }
                const [moved] = rows.splice(ti, 1);
                if (moved.is_group && (moved.children || []).length) {
                    show_message('Grup yang punya anak tidak bisa di-indent!', 'warning');
                    rows.splice(ti, 0, moved); render(); return;
                }
                rows[ti - 1].children = rows[ti - 1].children || [];
                rows[ti - 1].children.push({ uid: moved.uid, item: moved.item, qty: moved.qty, show_number: moved.show_number });
                render();
            });

            $('#table').on('click', '.btn-outdent', function() {
                const { ti, ci } = locate($(this).closest('tr'));
                const [moved] = rows[ti].children.splice(ci, 1);
                rows.splice(ti + 1, 0, { uid: moved.uid, item: moved.item, qty: moved.qty, is_group: false, show_number: moved.show_number, children: [] });
                render();
            });

            $('#modal_item').on('shown.bs.modal', function() {
                $('#item').focus()
            });

            $('#form_item').submit(function(e) {
                e.preventDefault()
                const type = $('#ftype').val();
                const isGroup = type === 'group';
                let item = $('#item').val() ? $('#item').val().trim() : '';
                let qty = $('#qty').val() ? $('#qty').val().trim() : '';
                const show = $('#fshow').is(':checked');
                if (item == '' || item == null) {
                    show_message('Item empty!')
                    $('#item').focus()
                    return
                }
                if (isGroup) {
                    rows.push({ uid: uid(), item: item, qty: '', is_group: true, show_number: show, children: [] });
                } else {
                    const pIdx = $('#fparent').val();
                    const child = { uid: uid(), item: item, qty: qty, show_number: show };
                    if (pIdx !== '' && rows[parseInt(pIdx)]) {
                        rows[parseInt(pIdx)].children = rows[parseInt(pIdx)].children || [];
                        rows[parseInt(pIdx)].children.push(child);
                    } else {
                        rows.push({ uid: uid(), item: item, qty: qty, is_group: false, show_number: show, children: [] });
                    }
                }
                render();
                $('#item').val('')
                $('#qty').val('')
                $('#modal_item').modal('hide')
            })

            $('#table').on('click', '.del-item', function() {
                const tr = $(this).closest('tr');
                const { kind, ti, ci } = locate(tr);
                if (kind === 'top') rows.splice(ti, 1);
                else rows[ti].children.splice(ci, 1);
                render();

            });

            function payloadItems() {
                return rows.map(t => ({
                    item: t.item,
                    qty: t.is_group ? null : (t.qty || null),
                    is_group: !!t.is_group,
                    show_number: !!t.show_number,
                    children: (t.children || []).map(c => ({ item: c.item, qty: c.qty || null, show_number: !!c.show_number }))
                }));
            }

            function totalCount() {
                return rows.reduce((n, t) => n + 1 + (t.children || []).length, 0);
            }

            $('#form').submit(function(e) {
                e.preventDefault()
                let product = $('#product_id').val()
                let vendor = $('#vendor_id').val()
                let vendor_desc = $('#vendor_desc').val()
                let name = $('#name').val()
                let desc = $('#desc').val()
                let data = payloadItems();
                if (product == '' || product == null) {
                    show_message('Pilih Produk!', 'warning')
                    return
                }
                if (vendor == '' || vendor == null) {
                    show_message('Pilih Vendor!', 'warning')
                    return
                }
                if (totalCount() < 1) {
                    show_message('Item minimal harus ada 1!', 'warning')
                    return
                }

                // Validasi tiap item tidak boleh kosong
                let hasEmpty = false;
                data.forEach(t => {
                    if (!t.item || !t.item.trim()) hasEmpty = true;
                    (t.children || []).forEach(c => { if (!c.item || !c.item.trim()) hasEmpty = true; });
                });
                if (hasEmpty) {
                    show_message('Nama Item tidak boleh ada yang kosong!', 'warning');
                    return;
                }

                $.ajax({
                    url: $('#form').attr('action'),
                    type: 'PUT',
                    data: {
                        product_id: product,
                        vendor_id: vendor,
                        vendor_desc: vendor_desc,
                        name: name,
                        desc: desc,
                        items: data
                    },
                    beforeSend: function() {
                        bloc();
                    },
                    success: function(res) {
                        unbloc();
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        unbloc();
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

            function looksLikePartHeader(text) {
                return /^part\b/i.test(text);
            }

            $('#btn_import').click(function() {
                let imp = $('#import').val();
                if (!imp || !imp.trim()) return;
                // Baris ter-indent (spasi/tab di depan, atau "- ") -> anak dari grup terakhir
                imp.split('\n').forEach((raw) => {
                    if (!raw || !raw.trim()) return;
                    const indented = /^[\s\t]/.test(raw) || /^\s*-\s+/.test(raw);
                    let cols = raw.split('\t'); // Excel tab-delimited
                    let item = (cols[0] || '').replace(/^\s*-\s+/, '').trim();
                    let qty = (cols[1] || '').trim();
                    if (!item) return;

                    const isGroupLine = !indented && (/:$/.test(item) || looksLikePartHeader(item));
                    if (isGroupLine) {
                        const isPart = looksLikePartHeader(item);
                        rows.push({ uid: uid(), item: item, qty: '', is_group: true, show_number: !isPart, children: [] });
                    } else if (indented && rows.length) {
                        const parent = rows[rows.length - 1];
                        parent.children = parent.children || [];
                        parent.children.push({ uid: uid(), item: item, qty: qty, show_number: true });
                    } else {
                        rows.push({ uid: uid(), item: item, qty: qty, is_group: false, show_number: true, children: [] });
                    }
                });
                render();
            });

            $('#btn_import_clear').click(function() {
                let imp = $('#import').val('')
            });


            // Klik sel nama/qty untuk edit inline
            $('#table tbody').on('click', 'td.cell-item, td.cell-qty', function() {
                const tr = $(this).closest('tr');
                if ($(this).find('input').length > 0) return;
                const isQtyCell = $(this).hasClass('cell-qty');
                const { kind, ti, ci } = locate(tr);
                const t = rows[ti];
                if (!t) return;
                if (isQtyCell && kind === 'top' && t.is_group) return; // grup tanpa qty
                const d = kind === 'top' ? t : t.children[ci];
                if (!d) return;
                const oldValue = isQtyCell ? (d.qty || '') : (d.item || '');
                $(this).html(`<input type="text" class="form-control edit-input" value="${esc(oldValue)}" />`);
                const input = $(this).find('input');
                input.focus();
                input.on('blur', function() {
                    const nv = $(this).val().trim();
                    if (isQtyCell) d.qty = nv; else d.item = nv;
                    render();
                });
                input.on('keypress', function(e) { if (e.which === 13) $(this).blur(); });
            });

            loadItems();

        });
    </script>
@endpush
