<script setup>
import { computed, ref } from 'vue';
import { ClipboardCopy, CopyPlus, Download, Plus, RotateCcw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import DataTable from '@/components/DataTable.vue';
import EditableCell from '@/components/EditableCell.vue';
import FormField from '@/components/FormField.vue';
import LabelPrint from '@/components/LabelPrint.vue';
import LabelToolTabs from '@/components/LabelToolTabs.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, copyText, downloadCsv } from '@/lib/export';
import { getCode, getDesc, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Print Label' },
    products: { type: Array, default: () => [] },
});

const toast = useToast();

// ---- Katalog produk: preload sekali, filter lokal (instan) ----
const catalog = computed(() => (props.products ?? []).map((p) => ({
    label: `[${p.code}] ${p.name ?? ''}`,
    value: p.code,
    code: p.code,
    name: p.name ?? '',
})));

const OPTION_LIMIT = 50;

function matchCatalog(kw) {
    const q = String(kw ?? '').trim().toLowerCase();
    if (!q) {
        return catalog.value.slice(0, OPTION_LIMIT);
    }
    return catalog.value.filter((o) => o.code.toLowerCase().includes(q) || o.name.toLowerCase().includes(q)).slice(0, OPTION_LIMIT);
}

function clampQty(v) {
    return Math.min(1000, Math.max(1, Math.floor(Number(v) || 1)));
}

// ---- Form tambah baris: produk + qty ----
function blankForm() {
    return { kode: '', qty: 1 };
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

// Produk yang sama digabung qty-nya supaya tabel tetap ringkas.
function submitForm() {
    const kode = String(form.value.kode ?? '').trim();
    if (!kode) {
        toast.warning('Pilih dulu produk dari database.');
        return;
    }
    const qty = clampQty(form.value.qty);
    const existing = entries.value.find((e) => e.kode.toLowerCase() === kode.toLowerCase());
    if (existing) {
        existing.copies = clampQty(existing.copies + qty);
        resetForm();
        toast.success('Produk sudah ada di tabel — qty digabung.');
        return;
    }
    const found = catalog.value.find((o) => o.code === kode) ?? null;
    seq += 1;
    entries.value.push({
        id: seq,
        kode,
        nama: found?.name ?? '',
        copies: qty,
    });
    resetForm();
    toast.success('Baris ditambahkan.');
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

// ---- Import dari DO / SO / PO / RI / IT / ITR (per produk, qty dokumen) ----
// Odoo mengembalikan `false` untuk field kosong — normalkan jadi string kosong.
function odooStr(value) {
    if (value === false || value === null || value === undefined) {
        return '';
    }
    const s = String(value).trim();
    return s === '-' ? '' : s;
}

const SOURCES = {
    do: { label: 'DO', endpoint: '/do', kind: 'picking', moves: 'move_ids_detail' },
    so: { label: 'SO', endpoint: '/so', kind: 'so' },
    po: { label: 'PO', endpoint: '/po', kind: 'po' },
    ri: { label: 'RI', endpoint: '/ri', kind: 'picking', moves: 'move_without_package_detail' },
    it: { label: 'IT', endpoint: '/it', kind: 'picking', moves: 'move_ids_detail' },
    itr: { label: 'ITR', endpoint: '/itr', kind: 'itr' },
};

const sourceList = Object.entries(SOURCES).map(([value, s]) => ({ value, label: s.label }));

const importOpen = ref(false);
const importSource = ref('ri');
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
                checked: true,
            }))
            .filter((l) => l.code);
    }
    if (kind === 'po') {
        return (body.order_line_detail ?? [])
            .map((m, i) => ({
                key: `${i}`,
                code: getCode(odooName(m.product_id)),
                name: getDesc(odooName(m.product_id)) || m.name || '',
                qty: odooStr(m.product_qty),
                checked: true,
            }))
            .filter((l) => l.code);
    }
    if (kind === 'itr') {
        return (body.detail_lines ?? [])
            .map((m, i) => ({
                key: `${i}`,
                code: getCode(odooName(m.nama_produk)),
                name: getDesc(odooName(m.nama_produk)),
                qty: odooStr(m.qty),
                checked: true,
            }))
            .filter((l) => l.code);
    }
    const moves = body[SOURCES[source]?.moves] ?? [];
    // Gabung per kode produk: qty dijumlah agar tabel tetap ringkas.
    const seen = new Map();
    moves.forEach((m, i) => {
        const code = getCode(odooName(m.product_id));
        if (!code) {
            return;
        }
        const name = getDesc(odooName(m.product_id)) || m.name || '';
        const q = Math.floor(Number(m.quantity_done ?? m.product_uom_qty ?? 1)) || 1;
        if (!seen.has(code)) {
            seen.set(code, { key: `${i}`, code, name, qty: 0, checked: true });
        }
        seen.get(code).qty += q;
    });
    return [...seen.values()].map((g) => ({ ...g, qty: String(g.qty) }));
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
    } catch (e) {
        docOptions.value = [];
        toast.error(e.response?.data?.message ?? `Gagal mencari dokumen ${sourceLabel.value}.`);
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
    picked.forEach((l) => {
        const qty = clampQty(l.qty);
        const existing = entries.value.find((e) => e.kode.toLowerCase() === String(l.code).toLowerCase());
        if (existing) {
            existing.copies = clampQty(existing.copies + qty);
            return;
        }
        seq += 1;
        entries.value.push({
            id: seq,
            kode: l.code,
            nama: l.name,
            copies: qty,
        });
    });
    importOpen.value = false;
    toast.success(`${picked.length} baris diimport dari ${sourceLabel.value}.`);
}

// ---- Tabel hasil ----
const resultColumns = [
    { key: 'no', label: '#', width: '44px', align: 'center' },
    { key: 'kode', label: 'Kode', mono: true },
    { key: 'nama', label: 'Nama', wrap: true, maxWidth: '280px' },
    { key: 'copies', label: 'Qty', align: 'center' },
];

const tableRows = computed(() => entries.value.map((e, i) => ({ ...e, no: i + 1 })));

const totalQty = computed(() => entries.value.reduce((sum, e) => sum + clampQty(e.copies), 0));

const labelItems = computed(() => entries.value.map((e) => ({
    code: e.kode,
    name: e.nama,
    qty: clampQty(e.copies),
})));

// ---- Export / salin tabel ----
const EXPORT_HEADERS = ['Kode', 'Nama', 'Qty'];

// copyRows menulis header apa adanya (nama key), jadi bentuk ulang baris
// agar clipboard berisi header yang enak dibaca.
function exportRows() {
    return entries.value.map((e) => ({
        Kode: e.kode,
        Nama: e.nama,
        Qty: clampQty(e.copies),
    }));
}

function onCopy() {
    copyRows(exportRows(), EXPORT_HEADERS, 'label');
}

function onDownloadCsv() {
    downloadCsv('print-label', exportRows(), EXPORT_HEADERS);
}

// ---- Edit langsung di sel tabel ----
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
    }
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
            <p class="mt-2 text-xs text-muted-foreground">Ambil baris produk dari dokumen sumber. Qty baris mengikuti qty dokumen dan produk yang sama digabung.</p>
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
                <FormField label="Qty" required class="w-full sm:w-28">
                    <Input v-model="form.qty" type="number" min="1" max="1000" />
                </FormField>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="resetForm"><RotateCcw /> Reset</Button>
                    <Button size="sm" @click="submitForm"><Plus /> Tambah ke Tabel</Button>
                </div>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Pilih produk lalu tambah — kode dan nama terisi otomatis dari DB. Produk yang sama akan digabung qty-nya. Qty juga bisa diubah langsung di tabel.</p>
        </Card>

        <Card class="p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">Daftar Label ({{ totalQty }} label / {{ entries.length }} baris)</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm" :disabled="entries.length === 0" title="Salin kode, nama, qty ke clipboard (tempel ke Excel)" @click="onCopy"><ClipboardCopy /> Salin</Button>
                    <Button variant="outline" size="sm" :disabled="entries.length === 0" title="Unduh tabel sebagai CSV" @click="onDownloadCsv"><Download /> CSV</Button>
                    <Button variant="outline" size="sm" :disabled="entries.length === 0" @click="clearEntries"><Trash2 /> Hapus Semua</Button>
                    <LabelPrint :items="labelItems" :title="props.title" />
                </div>
            </div>
            <DataTable
                :columns="resultColumns"
                :rows="tableRows"
                :loading="false"
                row-key="id"
                :show-footer="false"
                empty-title="Belum ada label"
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
                <template #cell-copies="{ row }">
                    <EditableCell :value="row.copies" type="number" :active="isEditing(row, 'copies')" @start="startEdit(row, 'copies')" @commit="(v) => commitCell(row, 'copies', v)" @cancel="cancelEdit" />
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="outline" size="sm" title="Salin kode + nama baris ini" @click="copyText(`${row.kode}\t${row.nama ?? ''}`)"><ClipboardCopy /></Button>
                        <Button variant="outline" size="sm" title="Duplikat baris ini" @click="duplicateEntry(row.id)"><CopyPlus /></Button>
                        <Button variant="destructive" size="sm" title="Hapus baris ini" @click="removeEntry(row.id)"><Trash2 /></Button>
                    </div>
                </template>
            </DataTable>
            <p class="mt-2 text-xs text-muted-foreground">Klik sel mana pun untuk ubah langsung. Qty menentukan jumlah label yang tercetak. Pengaturan kolom, font, genapkan baris, dan kode saja ada di tombol cetak. Hasil cetak terbuka di tab baru.</p>
        </Card>

        <AppModal v-model:open="importOpen" :title="`Import dari ${sourceLabel}`" size="xl" persistent>
            <div class="space-y-3">
                <p class="text-xs text-muted-foreground">
                    Centang baris yang mau dijadikan label. Qty mengikuti qty dokumen.
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
                            <span class="block text-xs text-muted-foreground">QTY: {{ l.qty || '-' }}</span>
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
