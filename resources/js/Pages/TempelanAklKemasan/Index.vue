<script setup>
import { computed, ref, watch } from 'vue';
import { ChevronDown, Copy, Download, Plus, Printer, RotateCcw, Settings2, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import DataTable from '@/components/DataTable.vue';
import EditableCell from '@/components/EditableCell.vue';
import FormField from '@/components/FormField.vue';
import LabelToolTabs from '@/components/LabelToolTabs.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { printAklKemasanLabels } from '@/lib/labelPrint';
import { formatExpDate, getCode, getDesc, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Tempelan AKL Kemasan' },
    products: { type: Array, default: () => [] },
});

const toast = useToast();

// Odoo mengembalikan `false` untuk field kosong — normalkan jadi string
// kosong agar tidak bocor tampil/tercetak sebagai "false".
function odooStr(value) {
    if (value === false || value === null || value === undefined) {
        return '';
    }
    const s = String(value).trim();
    return s === '-' ? '' : s;
}

// SN = nomor lot digabung expired jadi satu: `aaa/2028.01.31`.
// Exp kosong/false → cukup nomor lot saja (sama seperti Tempelan Dus).
function snLabel(lotId, lotName, expired) {
    let lot = odooStr(odooName(lotId));
    if (!lot) {
        lot = odooStr(lotName);
    }
    if (!lot) {
        return '';
    }
    const exp = formatExpDate(expired);
    return exp ? `${lot}/${exp}` : lot;
}

// ---- Katalog produk: preload sekali, filter lokal (instan) ----
const catalog = computed(() => (props.products ?? []).map((p) => ({
    label: `[${p.code}] ${p.name ?? ''}`,
    value: p.code,
    code: p.code,
    name: p.name ?? '',
    akl: p.akl ?? '',
})));

const OPTION_LIMIT = 50;

function matchCatalog(kw) {
    const q = String(kw ?? '').trim().toLowerCase();
    if (!q) {
        return catalog.value.slice(0, OPTION_LIMIT);
    }
    return catalog.value.filter((o) => o.code.toLowerCase().includes(q) || o.name.toLowerCase().includes(q)).slice(0, OPTION_LIMIT);
}

// ---- Pengaturan global (berlaku untuk semua label) ----
const importer = ref('Diimpor dan didistribusikan oleh :');
const company = ref('PT MITRA ASA PRATAMA');
const address = ref('Jakarta Timur 13330');
const qtyMin = ref(1);

// ---- Opsi cetak ala print label (kontrol inline di atas tabel) ----
const cols = ref(4);
const fillRows = ref(false);
const fontSel = ref('0');

// ---- Pengaturan distributor (jarang berubah, default tersembunyi) ----
const showSettings = ref(false);

const fontOptions = [
    { label: 'Auto', value: '0' },
    { label: 'Kecil', value: '14' },
    { label: 'Sedang', value: '18' },
    { label: 'Besar', value: '24' },
];

const fontOptionList = computed(() => fontOptions);

function clampCols(v) {
    return Math.min(10, Math.max(1, Math.floor(Number(v) || 4)));
}

function clampQty(v) {
    const floor = Math.max(1, Math.floor(Number(qtyMin.value) || 1));
    return Math.min(1000, Math.max(floor, Math.floor(Number(v) || floor)));
}

// ---- Satu form tambah baris (pilih produk saja) ----
function blankForm() {
    return { kode: '' };
}

const form = ref(blankForm());
const formOptions = ref([]);
const formPicked = ref(null);

formOptions.value = matchCatalog('');

const formOptionList = computed(() => {
    if (formPicked.value && !formOptions.value.some((o) => o.value === formPicked.value.value)) {
        return [formPicked.value, ...formOptions.value];
    }
    return formOptions.value;
});

function searchProduct(kw) {
    formOptions.value = matchCatalog(kw);
}

function pickProduct(val) {
    form.value.kode = val ?? '';
    formPicked.value = formOptionList.value.find((o) => o.value === val) ?? null;
}

function resetForm() {
    form.value = blankForm();
    formPicked.value = null;
    formOptions.value = matchCatalog('');
}

// ---- Entri (di-CRUD lewat form + tabel) ----
let seq = 0;
const entries = ref([]);

function submitForm() {
    if (!String(form.value.kode ?? '').trim()) {
        toast.warning('Pilih dulu produk dari database.');
        return;
    }
    const found = catalog.value.find((o) => o.code === form.value.kode.trim()) ?? null;
    seq += 1;
    entries.value.push({
        id: seq,
        kode: form.value.kode.trim(),
        nama: found?.name ?? '',
        akl: found?.akl ?? '',
        sn: '',
        copies: clampQty(qtyMin.value),
    });
    resetForm();
    toast.success('Baris ditambahkan. Lengkapi SN lewat tabel bila perlu.');
}

function removeEntry(id) {
    entries.value = entries.value.filter((e) => e.id !== id);
}

function duplicateEntry(id) {
    const idx = entries.value.findIndex((e) => e.id === id);
    if (idx < 0) {
        return;
    }
    seq += 1;
    entries.value.splice(idx + 1, 0, { ...entries.value[idx], id: seq });
    toast.success('Baris diduplikat.');
}

function clearEntries() {
    entries.value = [];
    resetForm();
}

// ---- Import dari DO / SO / PO / RI / IT / ITR (seperti Tempelan Dus) ----
// Bedanya: qty dokumen langsung dipakai sebagai qty baris (batas bawah = qty minimal).
const SOURCES = {
    do: { label: 'DO', endpoint: '/do', kind: 'picking', moves: 'move_ids_detail', lots: 'move_line_detail' },
    so: { label: 'SO', endpoint: '/so', kind: 'so' },
    po: { label: 'PO', endpoint: '/po', kind: 'po' },
    ri: { label: 'RI', endpoint: '/ri', kind: 'picking', moves: 'move_without_package_detail', lots: 'move_line_ids_without_package_detail' },
    it: { label: 'IT', endpoint: '/it', kind: 'picking', moves: 'move_ids_detail', lots: 'move_line_detail' },
    itr: { label: 'ITR', endpoint: '/itr', kind: 'itr' },
};

const sourceList = Object.entries(SOURCES).map(([value, s]) => ({ value, label: s.label }));

const importOpen = ref(false);
const importSource = ref('ri');
const importSn = ref(false);
const importGudang = ref('5');
const gudangOptions = [
    { label: 'CENTER', value: '5' },
    { label: 'BADSTOCK', value: '760' },
    { label: 'KARANTINA', value: '990' },
    { label: 'CIBUBUR', value: '1310' },
];
const docOptions = ref([]);
const docLoading = ref(false);
const docId = ref('');
const docDetail = ref(null);
const importLines = ref([]);
const importBusy = ref(false);

const sourceLabel = computed(() => SOURCES[importSource.value]?.label ?? '');

function docLabel(d) {
    const second = d.origin
        || (Array.isArray(d.partner_id) ? d.partner_id[1] : '')
        || d.ref
        || '';
    return `${d.name ?? ''}${second ? ` — ${second}` : ''}`;
}

function mapDocDetail(body, source) {
    const kind = SOURCES[source]?.kind ?? 'picking';
    if (kind === 'so') {
        return (body.order_line_detail ?? [])
            .map((m, i) => ({
                key: `${i}`,
                code: m.default_code || getCode(odooName(m.product_id)),
                name: m.name || getDesc(odooName(m.product_id)),
                qty: odooStr(m.product_uom_qty),
                akl: '',
                checked: true,
            }))
            .filter((l) => l.code);
    }
    if (kind === 'po') {
        return (body.order_line_detail ?? [])
            .map((m, i) => {
                const akl = odooStr(odooName(m.akl_id));
                return {
                    key: `${i}`,
                    code: getCode(odooName(m.product_id)),
                    name: getDesc(odooName(m.product_id)) || m.name || '',
                    qty: odooStr(m.product_qty),
                    akl,
                    checked: true,
                };
            })
            .filter((l) => l.code);
    }
    if (kind === 'itr') {
        return (body.detail_lines ?? [])
            .map((m, i) => ({
                key: `${i}`,
                code: getCode(odooName(m.nama_produk)),
                name: getDesc(odooName(m.nama_produk)),
                qty: odooStr(m.qty),
                akl: '',
                checked: true,
            }))
            .filter((l) => l.code);
    }
    const moves = body[SOURCES[source]?.moves] ?? [];
    const lotByCode = lotNamesOf(body, source);
    return moves
        .map((m, i) => {
            const akl = odooStr(odooName(m.akl_id));
            const code = getCode(odooName(m.product_id));
            return {
                key: `${i}`,
                code,
                name: getDesc(odooName(m.product_id)) || m.name || '',
                qty: odooStr(m.quantity_done ?? m.product_uom_qty),
                akl,
                lots: lotByCode[code] ?? [],
                checked: true,
            };
        })
        .filter((l) => l.code);
}

// Daftar nomor lot/serial per kode produk untuk ditampilkan di review modal.
function lotNamesOf(body, source) {
    const lines = body[SOURCES[source]?.lots] ?? [];
    const byCode = {};
    lines.forEach((l) => {
        const code = getCode(odooName(l.product_id));
        if (!code) {
            return;
        }
        const sn = snLabel(l.lot_id, l.lot_name, l.expired_date ?? l.expired_date_do);
        if (!sn) {
            return;
        }
        if (!byCode[code]) {
            byCode[code] = [];
        }
        if (!byCode[code].includes(sn)) {
            byCode[code].push(sn);
        }
    });
    return byCode;
}

// Baris SN / Lot per dokumen picking (DO / RI / IT): satu baris per
// kombinasi produk + nomor lot/serial, qty = total qty baris lot tersebut.
function mapLotDetail(body, source) {
    const lines = body[SOURCES[source]?.lots] ?? [];
    const seen = new Map();
    lines.forEach((l) => {
        const code = getCode(odooName(l.product_id));
        if (!code) {
            return;
        }
        const sn = snLabel(l.lot_id, l.lot_name, l.expired_date ?? l.expired_date_do);
        if (!sn) {
            return;
        }
        const name = getDesc(odooName(l.product_id));
        const q = Math.floor(Number(l.qty_done ?? l.product_uom_qty ?? 1)) || 1;
        const k = `${code}||${sn}`;
        if (!seen.has(k)) {
            seen.set(k, { key: k, code, name, qty: 0, akl: '', sn, checked: true });
        }
        seen.get(k).qty += q;
    });
    return [...seen.values()].map((g) => ({ ...g, qty: String(g.qty) }));
}

function refillImportLines() {
    if (!docDetail.value) {
        importLines.value = [];
        return;
    }
    const picking = SOURCES[importSource.value]?.kind === 'picking';
    if (importSn.value && picking) {
        importLines.value = mapLotDetail(docDetail.value, importSource.value);
    } else {
        importLines.value = mapDocDetail(docDetail.value, importSource.value);
    }
    if (importLines.value.length === 0) {
        toast.warning(importSn.value && picking ? 'Dokumen tidak punya SN / Lot.' : 'Dokumen tidak punya baris produk.');
    }
}

watch(importSn, () => {
    refillImportLines();
});

async function searchDocs(kw) {
    if (!kw || String(kw).length < 3) {
        docOptions.value = [];
        return;
    }
    docLoading.value = true;
    try {
        const params = { search: kw, per_page: 20 };
        if (importSource.value === 'it') {
            params.gudang = importGudang.value;
        }
        const res = await api.get(SOURCES[importSource.value].endpoint, { params, silent: true });
        const body = res.data?.data ?? res.data ?? [];
        const list = Array.isArray(body) ? body : (body.data ?? []);
        docOptions.value = list.map((d) => ({
            label: docLabel(d),
            value: String(d.id),
        }));
    } catch {
        docOptions.value = [];
    } finally {
        docLoading.value = false;
    }
}

async function pickDoc(val) {
    docId.value = val ?? '';
    docDetail.value = null;
    importLines.value = [];
    if (!val) {
        return;
    }
    importBusy.value = true;
    try {
        const res = await api.get(`${SOURCES[importSource.value].endpoint}/${encodeURIComponent(val)}`, { silent: true, block: true });
        const body = res.data?.data ?? res.data;
        docDetail.value = body;
        refillImportLines();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail dokumen.');
    } finally {
        importBusy.value = false;
    }
}

const selectedImportLines = computed(() => importLines.value.filter((l) => l.checked));
const allImportChecked = computed(() => importLines.value.length > 0 && importLines.value.every((l) => l.checked));

function toggleAllImport(v) {
    importLines.value.forEach((l) => { l.checked = v; });
}

function openImport(source) {
    importSource.value = SOURCES[source] ? source : 'ri';
    // Dokumen picking (DO/RI/IT) langsung mode per-SN: tiap SN beda jadi
    // baris sendiri dengan qty sesuai jumlahnya di dokumen.
    importSn.value = SOURCES[importSource.value]?.kind === 'picking';
    docId.value = '';
    docOptions.value = [];
    docDetail.value = null;
    importLines.value = [];
    importOpen.value = true;
}

function changeGudang(val) {
    importGudang.value = val ?? '5';
    docId.value = '';
    docOptions.value = [];
    docDetail.value = null;
    importLines.value = [];
}

// Cocokkan kode hasil import ke DB lokal untuk mengisi AKL yang kosong.
function fillAklFromDb(entry) {
    if (!entry.kode || entry.akl) {
        return;
    }
    const hit = catalog.value.find((o) => o.code.toLowerCase() === String(entry.kode).toLowerCase());
    if (hit) {
        entry.akl = hit.akl;
    }
}

function importCopies(qty) {
    const n = Math.floor(Number(qty));
    return clampQty(n > 0 ? n : qtyMin.value);
}

function doImport() {
    const picked = selectedImportLines.value;
    if (!picked.length) {
        toast.warning('Centang dulu baris yang mau diimport.');
        return;
    }
    picked.forEach((l) => {
        seq += 1;
        const entry = {
            id: seq,
            kode: l.code,
            nama: l.name,
            akl: l.akl,
            sn: l.sn || '',
            copies: importCopies(l.qty),
        };
        fillAklFromDb(entry);
        entries.value.push(entry);
    });
    importOpen.value = false;
    toast.success(`${picked.length} baris diimport dari ${sourceLabel.value}.`);
}

function printCount(copies) {
    const qty = clampQty(copies);
    const c = clampCols(cols.value);
    return fillRows.value ? Math.ceil(qty / c) * c : qty;
}

// ---- Label hasil (tampil semua, tanpa paging/search) ----
const resultColumns = [
    { key: 'no', label: '#', width: '44px', align: 'center' },
    { key: 'kode', label: 'Kode', mono: true },
    { key: 'nama', label: 'Nama', wrap: true, maxWidth: '280px' },
    { key: 'akl', label: 'AKL', wrap: true, maxWidth: '180px' },
    { key: 'sn', label: 'SN / ID', mono: true },
    { key: 'copies', label: 'Qty', align: 'center' },
];

// ---- Baris tabel: 1 baris per produk (qty hanya mengatur jumlah label cetak) ----
const tableRows = computed(() => entries.value.map((e, i) => ({ ...e, no: i + 1 })));

const totalPrint = computed(() => entries.value.reduce((sum, e) => sum + printCount(e.copies), 0));

// ---- Edit langsung di sel tabel (commit → entri induk, semua copy ikut) ----
const editing = ref({ id: null, field: '' });
const cellOptions = ref([]);

function entryOf(row) {
    return entries.value.find((e) => e.id === row.id);
}

function isEditing(row, field) {
    return editing.value.id === row.id && editing.value.field === field;
}

function startEdit(row, field) {
    if (field === 'kode') {
        const entry = entryOf(row);
        const cur = entry ? catalog.value.find((o) => o.code === entry.kode) : null;
        cellOptions.value = matchCatalog('');
        if (cur && !cellOptions.value.some((o) => o.value === cur.value)) {
            cellOptions.value = [cur, ...cellOptions.value];
        }
    }
    editing.value = { id: row.id, field };
}

function cancelEdit() {
    editing.value = { id: null, field: '' };
}

function commitCell(row, field, value) {
    const entry = entryOf(row);
    editing.value = { id: null, field: '' };
    if (!entry) {
        return;
    }
    if (field === 'copies') {
        entry.copies = clampQty(value);
        return;
    }
    entry[field] = String(value ?? '').trim();
}

function searchCellProduct(kw) {
    cellOptions.value = matchCatalog(kw);
}

function commitKode(row, val) {
    const entry = entryOf(row);
    editing.value = { id: null, field: '' };
    if (!entry) {
        return;
    }
    entry.kode = val ?? '';
    const found = catalog.value.find((o) => o.code === val) ?? null;
    if (found) {
        entry.nama = found.name;
        entry.akl = found.akl;
    }
}

function onPrint() {
    if (entries.value.length === 0) {
        toast.warning('Tambahkan dulu minimal 1 produk lewat form di atas.');
        return;
    }
    printAklKemasanLabels({
        items: entries.value.map((e) => ({
            kode: e.kode,
            nama: e.nama,
            akl: e.akl,
            sn: e.sn,
            qty: clampQty(e.copies),
        })),
        cols: clampCols(cols.value),
        title: props.title,
        fillRows: fillRows.value,
        fontPt: Number(fontSel.value),
        company: company.value,
        address: address.value,
        importer: importer.value,
    });
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" />
        <LabelToolTabs class="mb-4" />

        <Card class="mb-4 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-1 text-sm font-semibold">Import dari:</span>
                <Button v-for="s in sourceList" :key="s.value" variant="outline" size="sm" @click="openImport(s.value)"><Download /> {{ s.label }}</Button>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Ambil baris produk dari dokumen sumber. Qty baris mengikuti qty dokumen (minimal {{ qtyMin }}). SN dilengkapi lewat tabel.</p>
        </Card>

        <Card class="mb-4 p-4">
            <Button variant="ghost" size="sm" class="px-0" @click="showSettings = !showSettings">
                <Settings2 /> Pengaturan Distributor
                <ChevronDown :class="['transition-transform', showSettings && 'rotate-180']" />
            </Button>
            <div v-show="showSettings" class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <FormField label="Baris importir" hint="Tercetak di semua label">
                    <Input v-model="importer" placeholder="Diimpor dan didistribusikan oleh :" />
                </FormField>
                <FormField label="Nama distributor" hint="Tercetak di semua label">
                    <Input v-model="company" placeholder="PT MITRA ASA PRATAMA" />
                </FormField>
                <FormField label="Alamat distributor" hint="Tercetak di semua label">
                    <Input v-model="address" placeholder="Jakarta Timur 13330" />
                </FormField>
            </div>
            <p v-show="!showSettings" class="mt-1 text-xs text-muted-foreground">{{ company }}{{ address ? ` — ${address}` : '' }}</p>
        </Card>

        <Card class="mb-4 p-4">
            <h2 class="mb-3 text-sm font-semibold">Tambah Baris</h2>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <FormField label="Produk (dari DB)" required class="min-w-0 flex-1">
                    <SearchableSelect
                        :model-value="form.kode"
                        :options="formOptionList"
                        placeholder="Cari kode / nama produk..."
                        search-placeholder="Ketik kode atau nama..."
                        @update:model-value="pickProduct"
                        @search="searchProduct"
                    />
                </FormField>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="resetForm"><RotateCcw /> Reset</Button>
                    <Button size="sm" @click="submitForm"><Plus /> Tambah ke Tabel</Button>
                </div>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Pilih produk lalu tambah — AKL terisi otomatis dari DB (kosong = tercetak "-"). SN/Qty dilengkapi lewat tabel (klik sel) atau duplikat baris.</p>
        </Card>

        <Card class="p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">Hasil Tempelan ({{ totalPrint }} label)</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs text-muted-foreground" title="Qty bawaan tiap baris baru">Min</label>
                    <Input v-model="qtyMin" type="number" min="1" max="1000" class="h-8 w-16 px-2 text-xs" />
                    <label class="text-xs text-muted-foreground" title="Makin sedikit, makin besar">Kolom</label>
                    <Input v-model="cols" type="number" min="1" max="10" class="h-8 w-16 px-2 text-xs" />
                    <label class="flex cursor-pointer items-center gap-1 text-xs text-muted-foreground" title="Kunci ukuran font label (Auto = mengikuti jumlah kolom)">
                        Font
                        <select v-model="fontSel" class="h-8 rounded-md border border-input bg-background px-1 text-xs shadow-sm">
                            <option v-for="o in fontOptionList" :key="o.value" :value="o.value">{{ o.label }}</option>
                        </select>
                    </label>
                    <label class="flex cursor-pointer items-center gap-1 text-xs text-muted-foreground" title="Genapkan tiap produk menjadi baris penuh (kekurangan sel diisi label yang sama)">
                        <input v-model="fillRows" type="checkbox" class="size-3.5 accent-primary" />
                        Genapkan
                    </label>
                    <Button variant="outline" size="sm" :disabled="entries.length === 0" @click="clearEntries"><Trash2 /> Hapus Semua</Button>
                    <Button size="sm" :disabled="entries.length === 0" @click="onPrint"><Printer /> Print</Button>
                </div>
            </div>
            <DataTable
                :columns="resultColumns"
                :rows="tableRows"
                :loading="false"
                row-key="id"
                :show-footer="false"
                empty-title="Belum ada tempelan"
                empty-message="Tambahkan produk lewat form di atas."
            >
                <template #cell-kode="{ row }">
                    <SearchableSelect
                        v-if="isEditing(row, 'kode')"
                        :model-value="entryOf(row)?.kode ?? ''"
                        :options="cellOptions"
                        placeholder="Cari..."
                        search-placeholder="Ketik kode/nama..."
                        @update:model-value="(v) => commitKode(row, v)"
                        @search="searchCellProduct"
                    />
                    <span
                        v-else
                        role="button"
                        tabindex="0"
                        title="Klik untuk ubah"
                        class="-mx-1 block min-h-5 cursor-text rounded px-1 font-mono text-xs hover:bg-slate-100"
                        @click="startEdit(row, 'kode')"
                        @keydown.enter="startEdit(row, 'kode')"
                    >{{ row.kode }}</span>
                </template>
                <template #cell-nama="{ row }">
                    <EditableCell :value="row.nama" :active="isEditing(row, 'nama')" @start="startEdit(row, 'nama')" @commit="(v) => commitCell(row, 'nama', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-akl="{ row }">
                    <EditableCell :value="row.akl" :active="isEditing(row, 'akl')" @start="startEdit(row, 'akl')" @commit="(v) => commitCell(row, 'akl', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-sn="{ row }">
                    <EditableCell :value="row.sn" :active="isEditing(row, 'sn')" @start="startEdit(row, 'sn')" @commit="(v) => commitCell(row, 'sn', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-copies="{ row }">
                    <EditableCell :value="row.copies" type="number" :active="isEditing(row, 'copies')" @start="startEdit(row, 'copies')" @commit="(v) => commitCell(row, 'copies', v)" @cancel="cancelEdit" />
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="outline" size="sm" title="Duplikat baris ini" @click="duplicateEntry(row.id)"><Copy /></Button>
                        <Button variant="destructive" size="sm" title="Hapus baris ini" @click="removeEntry(row.id)"><Trash2 /></Button>
                    </div>
                </template>
            </DataTable>
            <p class="mt-2 text-xs text-muted-foreground">Klik sel mana pun untuk ubah langsung. Qty menentukan jumlah label yang tercetak. AKL / SN yang dikosongkan tercetak "-". Hasil cetak terbuka di tab baru, tiap produk diawali note merah.</p>
        </Card>

        <AppModal v-model:open="importOpen" :title="`Import dari ${sourceLabel}`" size="xl" persistent>
            <div class="space-y-3">
                <p class="text-xs text-muted-foreground">
                    Centang baris yang mau dijadikan tempelan. Qty mengikuti qty dokumen.
                </p>
                <label v-if="SOURCES[importSource]?.kind === 'picking'" class="flex w-fit cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium">
                    <input v-model="importSn" type="checkbox" class="size-4 accent-primary" />
                    Sertakan SN / Lot
                </label>
                <FormField v-if="importSource === 'it'" label="Gudang">
                    <SearchableSelect
                        :model-value="importGudang"
                        :options="gudangOptions"
                        :clearable="false"
                        placeholder="Pilih gudang..."
                        @update:model-value="changeGudang"
                    />
                </FormField>
                <FormField :label="`Cari No. ${sourceLabel}`">
                    <SearchableSelect
                        :model-value="docId"
                        :options="docOptions"
                        :loading="docLoading"
                        :placeholder="`Ketik nomor ${sourceLabel}...`"
                        :debounce="600"
                        :min-length="3"
                        @update:model-value="pickDoc"
                        @search="searchDocs"
                    />
                </FormField>
                <div v-if="importBusy" class="rounded-lg border p-6 text-center text-sm text-muted-foreground">
                    Memuat detail dokumen...
                </div>
                <div v-else-if="docDetail" class="space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm">
                        <span><b>{{ docDetail.name }}</b> — PO: {{ docDetail.origin ?? '-' }}</span>
                        <label class="flex cursor-pointer items-center gap-1.5 text-xs font-medium">
                            <input :checked="allImportChecked" type="checkbox" class="size-4 accent-primary" @change="toggleAllImport($event.target.checked)" />
                            Pilih semua
                        </label>
                    </div>
                    <div v-if="importLines.length === 0" class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                        Tidak ada baris produk pada dokumen ini.
                    </div>
                    <label
                        v-for="l in importLines"
                        :key="l.key"
                        class="flex cursor-pointer items-start gap-2 rounded-lg border px-3 py-2 hover:border-primary/50"
                    >
                        <input v-model="l.checked" type="checkbox" class="mt-1 size-4 shrink-0 accent-primary" />
                        <span class="min-w-0 flex-1 text-sm">
                            <span class="font-bold">[{{ l.code }}]</span> {{ l.name }}
                            <span class="block text-xs text-muted-foreground">QTY: {{ l.qty || '-' }}{{ l.sn ? ` — SN: ${l.sn}` : '' }}{{ (l.lots ?? []).length ? ` — LOT: ${l.lots.join(', ')}` : '' }}{{ l.akl ? ` — AKL: ${l.akl}` : '' }}</span>
                        </span>
                    </label>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="importOpen = false">Batal</Button>
                <Button :disabled="selectedImportLines.length === 0" @click="doImport"><Download /> Import {{ selectedImportLines.length }} Baris</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
