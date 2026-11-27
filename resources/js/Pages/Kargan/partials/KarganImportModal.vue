<script setup>
import { computed, ref, watch } from 'vue';
import { Download, RefreshCw, Search } from '@lucide/vue';
import AppModal from '@/components/AppModal.vue';
import ModalTabs from '@/components/ModalTabs.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import DataTable from '@/components/DataTable.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';
import { getCode, getDesc, odooName, formatExpDate } from '@/lib/odoo';
import { useClientTable } from '@/composables/useClientTable';

const props = defineProps({
    products: { type: Array, default: () => [] },
    picOptions: { type: Array, default: () => [] },
    defaultDate: { type: String, default: '' },
});

const open = defineModel('open', { type: Boolean, default: false });
const emit = defineEmits(['imported']);

const toast = useToast();
const sourceTab = ref('do');
const sourceTabs = computed(() => [
    { value: 'do', label: 'DO', count: null },
    { value: 'it', label: 'IT', count: null },
]);

const keyword = ref('CENT/OUT/');
const docOptions = ref([]);
const docLoading = ref(false);
const selectedDocId = ref('');
const docDetail = ref(null);
const docLoadingDetail = ref(false);
const doGudang = ref('2');
const itGudang = ref('5');

const doGudangOptions = [
    { label: 'CENTER', value: '2' },
    { label: 'BAD STOCK', value: '757' },
    { label: 'DEMO', value: '762' },
    { label: 'MARKETING', value: '872' },
    { label: 'SERVICE', value: '882' },
    { label: 'SPAREPART', value: '887' },
];
const itGudangOptions = [
    { label: 'CENTER', value: '5' },
    { label: 'BADSTOCK', value: '760' },
    { label: 'KARANTINA', value: '990' },
    { label: 'CIBUBUR', value: '1310' },
];
const gudangOptions = computed(() => (sourceTab.value === 'do' ? doGudangOptions : itGudangOptions));
const gudang = computed({
    get: () => (sourceTab.value === 'do' ? doGudang.value : itGudang.value),
    set: (v) => {
        if (sourceTab.value === 'do') {
            doGudang.value = v;
        } else {
            itGudang.value = v;
        }
    },
});

const pic = ref('');
const date = ref(props.defaultDate || new Date().toISOString().slice(0, 10));
const selected = ref([]);
const previewNumbers = ref([]);
let previewTimer = null;

watch(
    () => props.picOptions,
    (list) => {
        if (!pic.value) {
            pic.value = list.find((p) => String(p).toLowerCase().includes('karim')) ?? list[0] ?? '';
        }
    },
    { immediate: true },
);

const productMap = computed(() => {
    const map = {};
    props.products.forEach((p) => {
        map[String(p.code).trim().toUpperCase()] = p.id;
    });
    return map;
});
const picSelectOptions = computed(() => props.picOptions.map((p) => ({ value: p, label: p })));

function docLabel(d) {
    let name = d.name ?? d.id;
    if (d.origin) {
        name += ` (${d.origin})`;
    }
    const partner = odooName(d.partner_id);
    if (partner && partner !== '-') {
        name += ` ${partner}`.slice(0, 60);
    }
    return name;
}

function endpoint() {
    return sourceTab.value === 'do' ? '/do' : '/it';
}

async function searchDoc(val, overlay = false) {
    if (val !== undefined && val !== null) {
        keyword.value = val;
    }
    if (!keyword.value || keyword.value.length < 1) {
        return;
    }
    docLoading.value = true;
    try {
        const res = await api.get(endpoint(), {
            params: { search: keyword.value, per_page: 20, gudang: gudang.value },
            silent: !overlay,
            ...(overlay ? { block: true } : {}),
        });
        const body = res.data?.data ?? res.data ?? [];
        const list = Array.isArray(body) ? body : (body.data ?? []);
        docOptions.value = list.map((d) => ({ label: docLabel(d), value: String(d.id), raw: d }));
    } catch {
        docOptions.value = [];
        toast.error('Gagal memuat dokumen.');
    } finally {
        docLoading.value = false;
    }
}

function lotLabel(row) {
    const lot = odooName(row.lot_id) !== '-' ? odooName(row.lot_id) : row.lot_name || '';
    const exp = formatExpDate(sourceTab.value === 'do' ? row.expired_date_do : row.expired_date);
    if (!lot || lot === '-') {
        return exp || '';
    }
    return exp ? `${lot}/${exp}` : lot;
}

const docLocation = computed(() => {
    if (!docDetail.value) {
        return '';
    }
    const from = odooName(docDetail.value.location_id);
    const to = odooName(docDetail.value.location_dest_id);
    if (from !== '-' && to !== '-') {
        return `${from} → ${to}`;
    }
    return from !== '-' ? from : to !== '-' ? to : '';
});

function localProductId(row) {
    const code = getCode(odooName(row.product_id)).trim().toUpperCase();
    return productMap.value[code] ?? null;
}

const lotRows = computed(() => {
    const lines = docDetail.value?.move_line_detail ?? [];
    return lines.map((l, i) => {
        const code = getCode(odooName(l.product_id));
        const pid = localProductId(l);
        return {
            _key: `${sourceTab.value}-${selectedDocId.value}-${i}`,
            code,
            desc: getDesc(odooName(l.product_id)),
            lot: lotLabel(l),
            qty: l.qty_done ?? l.product_uom_qty ?? 0,
            product_id: pid,
            match: !!pid,
        };
    });
});

const table = useClientTable(null, {
    searchKeys: ['number', 'code', 'desc', 'lot'],
});

// Qty dipecah 1 unit = 1 kartu garansi, masing-masing dapat nomor berurutan.
// Contoh: produk A qty 5 → 5 baris unit dengan SN sama + nomor 062..066.
const unitRows = computed(() => {
    const out = [];
    for (const r of lotRows.value) {
        if (!r.match) {
            continue;
        }
        const qty = Math.max(1, Math.min(200, Math.floor(Number(r.qty) || 1)));
        for (let n = 0; n < qty; n++) {
            out.push({ ...r, _key: `${r._key}#${n + 1}`, unit: n + 1, units: qty });
            if (out.length >= 200) {
                break;
            }
        }
        if (out.length >= 200) {
            break;
        }
    }
    return out;
});

const skippedCount = computed(() => lotRows.value.filter((r) => !r.match).length);

// Unit yang sudah di-generate sesi ini disembunyikan agar tidak dobel.
// Modal TIDAK auto-close; preview nomor di-refresh untuk sisa unit.
const doneKeys = ref(new Set());
const createdNumbers = ref([]);
const remainingUnits = computed(() => unitRows.value.filter((r) => !doneKeys.value.has(String(r._key))));

const displayRows = computed(() => remainingUnits.value.map((r, i) => ({ ...r, number: previewNumbers.value[i] ?? '…' })));
watch([remainingUnits, date], () => {
    table.setRows(displayRows.value);
    queuePreview();
});
watch(displayRows, (rows) => table.setRows(rows));

function queuePreview() {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(fetchPreview, 400);
}

async function fetchPreview() {
    if (!remainingUnits.value.length || !date.value) {
        previewNumbers.value = [];
        return;
    }
    try {
        const res = await web.get('/kargans/next-numbers', {
            params: { count: remainingUnits.value.length, date: date.value },
            silent: true,
        });
        const body = res.data?.data ?? res.data ?? [];
        previewNumbers.value = Array.isArray(body) ? body : (body.data ?? []);
    } catch {
        previewNumbers.value = [];
    }
}

const selectedMatch = computed(() => {
    const byKey = new Map(remainingUnits.value.map((r) => [String(r._key), r]));
    return selected.value.map((k) => byKey.get(String(k))).filter(Boolean);
});

function resetSession() {
    selected.value = [];
    previewNumbers.value = [];
    doneKeys.value = new Set();
    createdNumbers.value = [];
}

async function pickDoc(val) {
    selectedDocId.value = val;
    resetSession();
    docDetail.value = null;
    if (!val) {
        return;
    }
    docLoadingDetail.value = true;
    try {
        const res = await api.get(`${endpoint()}/${val}`, { block: true, silent: true });
        docDetail.value = res.data?.data ?? res.data;
        if (!lotRows.value.length) {
            toast.warning('Dokumen tidak punya lot/SN.');
        }
    } catch {
        toast.error('Gagal memuat detail dokumen.');
    } finally {
        docLoadingDetail.value = false;
    }
}

function switchTab(v) {
    sourceTab.value = v;
    keyword.value = v === 'do' ? 'CENT/OUT/' : 'CENT/INT/';
    docOptions.value = [];
    selectedDocId.value = '';
    docDetail.value = null;
    resetSession();
}

function onGudangChange(v) {
    gudang.value = v;
    searchDoc(keyword.value, false);
}

async function generate() {
    if (!selectedMatch.value.length) {
        toast.warning('Pilih minimal 1 lot yang produknya cocok.');
        return;
    }
    if (!date.value) {
        toast.warning('Tanggal wajib diisi.');
        return;
    }
    if (!pic.value) {
        toast.warning('PIC wajib diisi.');
        return;
    }
    try {
        const items = selectedMatch.value.map((r) => ({
            product_id: r.product_id,
            sn: r.lot || null,
            date: date.value,
            pic: pic.value,
        }));
        const res = await web.post(
            '/kargans/import',
            { items, source: sourceTab.value.toUpperCase(), source_no: docDetail.value?.name ?? '' },
            { block: true },
        );
        const created = res.data?.data ?? [];
        const numbers = (Array.isArray(created) ? created : []).map((c) => c.number ?? c).filter(Boolean);
        createdNumbers.value = [...createdNumbers.value, ...numbers];
        doneKeys.value = new Set([...doneKeys.value, ...selectedMatch.value.map((r) => String(r._key))]);
        selected.value = [];
        previewNumbers.value = [];
        toast.success(`${numbers.length || items.length} kargan dibuat. Sisa unit dapat nomor baru otomatis.`);
        emit('imported');
        queuePreview();
    } catch (e) {
        if (e.response?.status !== 422) {
            toast.error(e.response?.data?.message ?? 'Gagal generate kargan.');
        }
    }
}

const columns = [
    { key: 'number', label: 'No. Ekspektasi', mono: true },
    { key: 'code', label: 'Kode', mono: true },
    { key: 'desc', label: 'Produk' },
    { key: 'lot', label: 'Lot / SN', mono: true },
    { key: 'unit', label: 'Unit', align: 'center' },
];
</script>

<template>
    <AppModal v-model:open="open" title="Import Kargan dari DO / IT" size="2xl" persistent>
        <ModalTabs v-model="sourceTab" :tabs="sourceTabs" class="mb-3" @update:model-value="switchTab" />
        <div class="grid gap-3 sm:grid-cols-2">
            <FormField :label="sourceTab === 'do' ? 'Gudang DO' : 'Lokasi IT'">
                <SearchableSelect :model-value="gudang" :options="gudangOptions" placeholder="Pilih lokasi..." @update:model-value="onGudangChange" />
            </FormField>
            <FormField v-if="docLocation" label="Lokasi Dokumen">
                <Input :model-value="docLocation" readonly />
            </FormField>
            <FormField label="Cari Nomor Dokumen">
                <div class="flex gap-2">
                    <Input v-model="keyword" placeholder="CENT/OUT/..." class="flex-1" @keyup.enter="searchDoc(keyword, true)" />
                    <Button variant="outline" size="sm" @click="searchDoc(keyword, true)"><Search /> GET</Button>
                </div>
            </FormField>
            <FormField label="Pilih Hasil Pencarian">
                <SearchableSelect
                    :model-value="selectedDocId"
                    :options="docOptions"
                    :loading="docLoading"
                    placeholder="Ketik lalu pilih dokumen..."
                    @update:model-value="pickDoc"
                    @search="searchDoc"
                />
            </FormField>
            <FormField label="Tanggal Kargan">
                <DatePicker v-model="date" />
            </FormField>
            <FormField label="PIC">
                <SearchableSelect v-model="pic" :options="picSelectOptions" placeholder="Pilih PIC..." />
            </FormField>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <b class="text-sm">Unit Kartu ({{ remainingUnits.length }}{{ doneKeys.size ? `, ${doneKeys.size} sudah dibuat` : '' }})</b>
            <span class="text-xs text-muted-foreground">Qty dipecah 1 unit = 1 kartu, nomor berurutan otomatis. Modal tidak auto-close; unit terbuat disembunyikan + sisa dapat nomor baru.{{ skippedCount ? ` ${skippedCount} baris lot dilewati (kode tak cocok).` : '' }}{{ unitRows.length >= 200 ? ' Maks 200 per import.' : '' }}</span>
            <span class="ml-auto flex gap-2">
                <Button variant="outline" size="sm" @click="pickDoc(selectedDocId)"><RefreshCw /> Muat Ulang</Button>
                <Button size="sm" :disabled="!selectedMatch.length" @click="generate"><Download /> Generate {{ selectedMatch.length ? `(${selectedMatch.length})` : '' }}</Button>
            </span>
        </div>
        <div class="mt-2">
            <DataTable
                :columns="columns"
                :rows="table.rows.value"
                :loading="docLoadingDetail"
                :page="table.page.value"
                :total-pages="table.totalPages.value"
                :total="table.total.value"
                :per-page="table.perPage.value"
                :selected="selected"
                selectable
                row-key="_key"
                empty-title="Belum ada unit"
                empty-message="Pilih dokumen DO/IT dulu, qty otomatis dipecah per kartu."
                @update:page="table.setPage"
                @update:per-page="table.setPerPage"
                @update:selected="selected = $event"
            >
                <template #cell-number="{ row }"><b>{{ row.number }}</b></template>
                <template #cell-code="{ row }">{{ row.code || '-' }}</template>
                <template #cell-desc="{ row }">{{ row.desc || '-' }}</template>
                <template #cell-lot="{ row }">{{ row.lot || '-' }}</template>
                <template #cell-unit="{ row }">{{ row.unit }}/{{ row.units }}</template>
            </DataTable>
        </div>
        <div v-if="createdNumbers.length" class="mt-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm">
            <b>Sudah dibuat ({{ createdNumbers.length }}):</b>
            <span class="font-mono">{{ createdNumbers.join(', ') }}</span>
        </div>
        <template #footer>
            <Button variant="ghost" @click="open = false">Tutup</Button>
            <Button :disabled="!selectedMatch.length" @click="generate"><Download /> Generate Kargan</Button>
        </template>
    </AppModal>
</template>
