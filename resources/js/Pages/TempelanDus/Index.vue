<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, Plus, Printer, RotateCcw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import EditableCell from '@/components/EditableCell.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { printDusLabels } from '@/lib/labelPrint';
import { formatExpDate, getCode, getDesc, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Tempelan Dus' },
    products: { type: Array, default: () => [] },
});

const toast = useToast();

function isoDate(value) {
    const m = /^(\d{4}-\d{2}-\d{2})/.exec(String(value ?? ''));
    return m ? m[1] : '';
}

function formatDocDate(iso) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso ?? ''));
    if (!m) {
        return String(iso ?? '');
    }
    return `${m[3]}/${m[2]}/${m[1]}`;
}

const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// ISO → "20 September 2026" khusus hasil print (tabel tetap dd/mm/yyyy).
function formatDocDateLong(iso) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso ?? ''));
    if (!m) {
        return String(iso ?? '');
    }
    const name = MONTH_NAMES[Number(m[2]) - 1] ?? m[2];
    return `${Number(m[3])} ${name} ${m[1]}`;
}

const MONTHS = {
    jan: 1, january: 1, feb: 2, februari: 2, february: 2, mar: 3, maret: 3, march: 3,
    apr: 4, april: 4, mei: 5, may: 5, jun: 6, juni: 6, june: 6, jul: 7, juli: 7, july: 7,
    agu: 8, agustus: 8, aug: 8, august: 8, sep: 9, sept: 9, september: 9, okt: 10,
    oktober: 10, oct: 10, october: 10, nov: 11, nop: 11, november: 11, des: 12,
    desember: 12, dec: 12, december: 12,
};

function toIso(y, mo, d) {
    if (!Number.isInteger(y) || mo < 1 || mo > 12 || d < 1 || d > 31) {
        return null;
    }
    const dt = new Date(y, mo - 1, d);
    if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) {
        return null;
    }
    const p = (n) => String(n).padStart(2, '0');
    return `${y}-${p(mo)}-${p(d)}`;
}

// Terima: 2025-10-31, 31/10/2025, 31-10-25, 31 Okt 2025, 31 October 2025.
// Kosong = boleh (kolom dibiarkan untuk tulis tangan). Selain itu = null.
function parseFlexibleDate(input) {
    const s = String(input ?? '').trim();
    if (!s) {
        return '';
    }
    let m;
    if ((m = /^(\d{4})-(\d{1,2})-(\d{1,2})$/.exec(s))) {
        return toIso(Number(m[1]), Number(m[2]), Number(m[3]));
    }
    if ((m = /^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{2,4})$/.exec(s))) {
        const y = Number(m[3]) < 100 ? Number(m[3]) + 2000 : Number(m[3]);
        return toIso(y, Number(m[2]), Number(m[1]));
    }
    if ((m = /^(\d{1,2})\s+([A-Za-z]+)\s+(\d{2,4})$/.exec(s))) {
        const mo = MONTHS[m[2].toLowerCase()];
        if (!mo) {
            return null;
        }
        const y = Number(m[3]) < 100 ? Number(m[3]) + 2000 : Number(m[3]);
        return toIso(y, mo, Number(m[1]));
    }
    return null;
}

// ---- Sumber import ----
const SOURCES = {
    do: { label: 'DO', endpoint: '/do', kind: 'picking', moves: 'move_ids_detail', lots: 'move_line_detail', fillDoc: true },
    so: { label: 'SO', endpoint: '/so', kind: 'so', fillDoc: false },
    po: { label: 'PO', endpoint: '/po', kind: 'po', fillDoc: false, poFromName: true },
    ri: { label: 'RI', endpoint: '/ri', kind: 'picking', moves: 'move_without_package_detail', lots: 'move_line_ids_without_package_detail', fillDoc: true },
    it: { label: 'IT', endpoint: '/it', kind: 'picking', moves: 'move_ids_detail', lots: 'move_line_detail', fillDoc: true, poFromLocations: true },
    itr: { label: 'ITR', endpoint: '/itr', kind: 'itr', fillDoc: false },
};

const sourceList = Object.entries(SOURCES).map(([value, s]) => ({ value, label: s.label }));

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

// ---- Satu form tambah baris (2 kolom) ----
function blankForm() {
    return {
        kode: '',
        nama: '',
        akl: '',
    };
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
    const found = formOptionList.value.find((o) => o.value === val) ?? null;
    formPicked.value = found;
    if (found) {
        form.value.nama = found.name;
        form.value.akl = found.akl;
    }
}

function resetForm() {
    form.value = blankForm();
    formPicked.value = null;
    formOptions.value = matchCatalog('');
}

// ---- Entri (di-CRUD lewat form + tabel) ----
let seq = 0;
const entries = ref([]);

function clampCopies(v) {
    return Math.min(60, Math.max(1, Math.floor(Number(v) || 1)));
}

function submitForm() {
    if (!form.value.kode.trim()) {
        toast.warning('Pilih dulu kode barang dari database.');
        return;
    }
    const data = {
        kode: form.value.kode.trim(),
        nama: form.value.nama.trim(),
        po: '',
        docType: 'RI',
        docNo: '',
        docDate: '',
        qty: '',
        akl: form.value.akl.trim(),
        lotEd: '',
        noCtn: '',
        copies: 1,
    };
    seq += 1;
    entries.value.push({ id: seq, ...data });
    resetForm();
    toast.success('Baris ditambahkan. Lengkapi lewat tabel.');
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

// Cocokkan kode Odoo hasil import ke DB lokal untuk mengisi AKL.
function fillAklFromDb(entry) {
    if (!entry.kode || entry.akl) {
        return;
    }
    const hit = catalog.value.find((o) => o.code.toLowerCase() === String(entry.kode).toLowerCase());
    if (hit) {
        entry.akl = hit.akl;
    }
}

// ---- Import dari DO / SO / PO / RI / IT / ITR ----
const importOpen = ref(false);
const importSource = ref('ri');
const importLot = ref(true);
const importLokasiPo = ref(true);
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

function docDateOf(body) {
    return isoDate(body?.force_date ?? body?.date ?? body?.x_studio_date ?? body?.date_done ?? body?.date_order ?? body?.tanggal);
}

// IT: PO diisi rute lokasi "ASAL > TUJUAN" (mis. CENTER > KARANTINA).
function locationRoute(body) {
    const from = odooName(body?.location_id);
    const to = odooName(body?.location_dest_id);
    if (from && from !== '-' && to && to !== '-') {
        return `${from} > ${to}`;
    }
    return body?.origin ?? '';
}

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

function lotLabel(lotId, lotName, expired) {
    let lot = odooName(lotId);
    if (lot === '-') {
        lot = lotName ?? '';
    }
    const exp = formatExpDate(expired);
    if (!lot) {
        return '';
    }
    return exp ? `${lot}/${exp}` : lot;
}

function mapDocDetail(body, source) {
    const kind = SOURCES[source]?.kind ?? 'picking';
    if (kind === 'so') {
        return (body.order_line_detail ?? [])
            .map((m, i) => {
                const code = m.default_code || getCode(odooName(m.product_id));
                return {
                    key: `${i}`,
                    code,
                    name: m.name || getDesc(odooName(m.product_id)),
                    qty: String(m.product_uom_qty ?? ''),
                    akl: '',
                    lotEd: '',
                    checked: true,
                };
            })
            .filter((l) => l.code);
    }
    if (kind === 'po') {
        return (body.order_line_detail ?? [])
            .map((m, i) => {
                const code = getCode(odooName(m.product_id));
                const akl = odooName(m.akl);
                return {
                    key: `${i}`,
                    code,
                    name: getDesc(odooName(m.product_id)) || m.name || '',
                    qty: String(m.product_qty ?? ''),
                    akl: akl === '-' ? '' : akl,
                    lotEd: '',
                    checked: true,
                };
            })
            .filter((l) => l.code);
    }
    if (kind === 'itr') {
        return (body.detail_lines ?? [])
            .map((m, i) => {
                const code = getCode(odooName(m.nama_produk));
                return {
                    key: `${i}`,
                    code,
                    name: getDesc(odooName(m.nama_produk)),
                    qty: String(m.qty ?? ''),
                    akl: '',
                    lotEd: '',
                    checked: true,
                };
            })
            .filter((l) => l.code);
    }
    const moves = body[SOURCES[source]?.moves] ?? [];
    const lotLines = body[SOURCES[source]?.lots] ?? [];
    const lotByCode = {};
    lotLines.forEach((l) => {
        const code = getCode(odooName(l.product_id));
        if (!code) {
            return;
        }
        const label = lotLabel(l.lot_id, l.lot_name, l.expired_date ?? l.expired_date_do);
        if (!label) {
            return;
        }
        if (!lotByCode[code]) {
            lotByCode[code] = [];
        }
        if (!lotByCode[code].includes(label)) {
            lotByCode[code].push(label);
        }
    });
    return moves
        .map((m, i) => {
            const code = getCode(odooName(m.product_id));
            const akl = odooName(m.akl_id);
            return {
                key: `${i}`,
                code,
                name: getDesc(odooName(m.product_id)) || m.name || '',
                qty: String(m.quantity_done ?? m.product_uom_qty ?? ''),
                akl: akl === '-' ? '' : akl,
                lotEd: (lotByCode[code] ?? []).join(', '),
                checked: true,
            };
        })
        .filter((l) => l.code);
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
        importLines.value = mapDocDetail(body, importSource.value);
        if (importLines.value.length === 0) {
            toast.warning('Dokumen tidak punya baris produk.');
        }
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

function doImport() {
    const picked = selectedImportLines.value;
    if (!picked.length) {
        toast.warning('Centang dulu baris yang mau diimport.');
        return;
    }
    const cfg = SOURCES[importSource.value];
    const po = cfg.poFromLocations
        ? (importLokasiPo.value ? locationRoute(docDetail.value) : '')
        : cfg.poFromName
            ? (docDetail.value?.name ?? '')
            : (docDetail.value?.origin ?? '');
    const d = docDateOf(docDetail.value);
    // RI/IT/DO: No. dokumen + tanggal ikut terisi. SO/PO/ITR: dikosongkan.
    picked.forEach((l) => {
        seq += 1;
        const entry = {
            id: seq,
            kode: l.code,
            nama: l.name,
            po,
            docType: 'RI',
            docNo: cfg.fillDoc ? (docDetail.value?.name ?? '') : '',
            docDate: cfg.fillDoc ? (d || '') : '',
            qty: '',
            akl: l.akl,
            lotEd: importLot.value ? l.lotEd : '',
            noCtn: '',
            copies: 1,
        };
        fillAklFromDb(entry);
        entries.value.push(entry);
    });
    importOpen.value = false;
    toast.success(`${picked.length} baris diimport dari ${sourceLabel.value}.`);
}

// ---- Label + tabel hasil (tampil semua, tanpa paging/search) ----
const resultColumns = [
    { key: 'no', label: '#', width: '44px', align: 'center' },
    { key: 'noCtn', label: 'NO CTN' },
    { key: 'kode', label: 'Kode', mono: true },
    { key: 'nama', label: 'Nama', wrap: true, maxWidth: '280px' },
    { key: 'po', label: 'PO' },
    { key: 'docNo', label: 'No. RI', wrap: true, maxWidth: '170px' },
    { key: 'docDate', label: 'Tgl RI', wrap: true, maxWidth: '150px' },
    { key: 'qty', label: 'QTY', align: 'center' },
    { key: 'akl', label: 'AKL / SUKET', wrap: true, maxWidth: '180px' },
    { key: 'lotEd', label: 'LOT & ED', wrap: true, maxWidth: '180px' },
    { key: 'copies', label: 'Jml', align: 'center' },
];

const labels = computed(() => {
    return entries.value.flatMap((e) => {
        const base = {
            noCtn: e.noCtn,
            kode: e.kode,
            nama: e.nama,
            po: e.po,
            docType: 'RI',
            docNo: e.docNo,
            docDate: formatDocDate(e.docDate),
            docDateIso: e.docDate,
            docDatePrint: formatDocDateLong(e.docDate),
            qty: e.qty,
            akl: e.akl,
            lotEd: e.lotEd,
        };
        return Array.from({ length: clampCopies(e.copies) }, () => ({ ...base }));
    });
});

const sheets = computed(() => Math.max(0, Math.ceil(labels.value.length / 3)));

// ---- Baris tabel: 1 baris per entri (Jml hanya mengatur jumlah label cetak) ----
const tableRows = computed(() => entries.value.map((e, i) => ({ ...e, no: i + 1 })));

// ---- Edit langsung di sel tabel (commit → entri induk) ----
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
        entry.copies = clampCopies(value);
        return;
    }
    if (field === 'docDate') {
        const parsed = parseFlexibleDate(value);
        if (parsed === null) {
            toast.warning('Format tanggal tidak dikenali. Contoh: 31/10/2025.');
            return;
        }
        entry.docDate = parsed;
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
    if (labels.value.length === 0) {
        toast.warning('Tambahkan dulu minimal 1 baris lewat form di atas.');
        return;
    }
    printDusLabels({ labels: labels.value });
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" />

        <Card class="mb-4 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-1 text-sm font-semibold">Import dari:</span>
                <Button v-for="s in sourceList" :key="s.value" variant="outline" size="sm" @click="openImport(s.value)"><Download /> {{ s.label }}</Button>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Ambil baris produk dari dokumen sumber. No. RI &amp; tanggal per baris tetap bisa diubah di tabel.</p>
        </Card>

        <Card class="mb-4 p-4">
            <h2 class="mb-3 text-sm font-semibold">Tambah Baris</h2>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <FormField label="Kode Barang (dari DB)" required class="min-w-0 flex-1">
                    <SearchableSelect
                        :model-value="form.kode"
                        :options="formOptionList"
                        placeholder="Cari kode barang..."
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
            <p class="mt-2 text-xs text-muted-foreground">Pilih produk lalu tambah — QTY, PO, LOT &amp; ED, NO CTN, No./Tgl RI dilengkapi lewat tabel (klik sel) atau duplikat baris.</p>
        </Card>

        <Card class="p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">Hasil Tempelan ({{ labels.length }} tempelan / {{ sheets }} lbr A4)</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm" :disabled="entries.length === 0" @click="clearEntries"><Trash2 /> Hapus Semua</Button>
                    <Button size="sm" :disabled="labels.length === 0" @click="onPrint"><Printer /> Print</Button>
                </div>
            </div>
            <DataTable
                :columns="resultColumns"
                :rows="tableRows"
                :loading="false"
                row-key="id"
                :show-footer="false"
                empty-title="Belum ada tempelan"
                empty-message="Tambahkan baris lewat form di atas atau import dari dokumen."
            >
                <template #cell-docNo="{ row }">
                    <EditableCell :value="row.docNo" :active="isEditing(row, 'docNo')" @start="startEdit(row, 'docNo')" @commit="(v) => commitCell(row, 'docNo', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-docDate="{ row }">
                    <EditableCell :value="row.docDateIso" :display="row.docDate" :active="isEditing(row, 'docDate')" @start="startEdit(row, 'docDate')" @commit="(v) => commitCell(row, 'docDate', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-noCtn="{ row }">
                    <EditableCell :value="row.noCtn" :active="isEditing(row, 'noCtn')" @start="startEdit(row, 'noCtn')" @commit="(v) => commitCell(row, 'noCtn', v)" @cancel="cancelEdit" />
                </template>
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
                <template #cell-po="{ row }">
                    <EditableCell :value="row.po" :active="isEditing(row, 'po')" @start="startEdit(row, 'po')" @commit="(v) => commitCell(row, 'po', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-qty="{ row }">
                    <EditableCell :value="row.qty" :active="isEditing(row, 'qty')" @start="startEdit(row, 'qty')" @commit="(v) => commitCell(row, 'qty', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-akl="{ row }">
                    <EditableCell :value="row.akl" :active="isEditing(row, 'akl')" @start="startEdit(row, 'akl')" @commit="(v) => commitCell(row, 'akl', v)" @cancel="cancelEdit" />
                </template>
                <template #cell-lotEd="{ row }">
                    <EditableCell :value="row.lotEd" :active="isEditing(row, 'lotEd')" @start="startEdit(row, 'lotEd')" @commit="(v) => commitCell(row, 'lotEd', v)" @cancel="cancelEdit" />
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
            <p class="mt-2 text-xs text-muted-foreground">Klik sel mana pun untuk ubah langsung. Kolom Jml menentukan jumlah tempelan tercetak. Hapus lewat kolom aksi. Hasil cetak terbuka di tab baru (3 tempelan per lembar).</p>
        </Card>

        <AppModal v-model:open="importOpen" :title="`Import dari ${sourceLabel}`" size="xl" persistent>
            <div class="space-y-3">
                <p class="text-xs text-muted-foreground">
                    Centang baris yang mau dijadikan tempelan.
                </p>
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
                    <div class="flex flex-wrap gap-2">
                        <label v-if="SOURCES[importSource]?.kind === 'picking'" class="flex w-fit cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium">
                            <input v-model="importLot" type="checkbox" class="size-4 accent-primary" />
                            Sertakan LOT &amp; ED
                        </label>
                        <label v-if="importSource === 'it'" class="flex w-fit cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium">
                            <input v-model="importLokasiPo" type="checkbox" class="size-4 accent-primary" />
                            Isi PO dari asal lokasi
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
                            <span class="block text-xs text-muted-foreground">QTY: {{ l.qty || '-' }}{{ l.akl ? ` — AKL: ${l.akl}` : '' }} — LOT &amp; ED: {{ l.lotEd || '-' }}</span>
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
