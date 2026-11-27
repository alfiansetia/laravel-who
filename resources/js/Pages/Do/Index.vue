<script setup>
import { computed, ref, watch } from 'vue';
import { Copy, Download, Printer, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import DateTimePicker from '@/components/DateTimePicker.vue';
import AppModal from '@/components/AppModal.vue';
import ModalTabs from '@/components/ModalTabs.vue';
import CopyButton from '@/components/CopyButton.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import FormField from '@/components/FormField.vue';
import LabelPrint from '@/components/LabelPrint.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { cleanLotName, formatExpDate, formatOdooDate, getCode, getDesc, odooId, odooName, truncate } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'DO' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/do', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    filters: {
        gudang: props.filters.gudang ?? '2',
        note_search: props.filters.note_search ?? '',
        product_search: props.filters.product_search ?? '',
        date_from: props.filters.date_from ?? '',
        date_to: props.filters.date_to ?? '',
    },
});
const detailOpen = ref(false);
const detail = ref(null);
const productLines = ref([]);
const lotLines = ref([]);
const selectedProducts = ref([]);
const activeRow = ref(null);
const detailTab = ref('product');
const lotCodeFilter = ref('');
const productTable = useClientTable(null, {
    searchKeys: ['product_id', 'akl_id', 'product_uom_qty', 'quantity_done'],
});
const lotTable = useClientTable(null, {
    searchKeys: ['product_id', 'akl_id', 'lot_id', 'lot_name', 'expired_date_do', 'qty_done'],
});
const detailTabs = computed(() => [
    { value: 'product', label: 'Product', count: productTable.total.value },
    { value: 'lot', label: 'Product Lot', count: lotTable.total.value },
]);

const gudangOptions = [
    { label: 'CENTER', value: '2' },
    { label: 'BAD STOCK', value: '757' },
    { label: 'DEMO', value: '762' },
    { label: 'MARKETING', value: '872' },
    { label: 'SERVICE', value: '882' },
    { label: 'SPAREPART', value: '887' },
];

const activeCount = computed(
    () =>
        (query.filters.value.gudang ? 1 : 0) +
        (query.filters.value.note_search ? 1 : 0) +
        (query.filters.value.product_search ? 1 : 0) +
        (query.filters.value.date_from ? 1 : 0) +
        (query.filters.value.date_to ? 1 : 0),
);

const searchBox = ref(props.filters.search ?? '');
const noteBox = ref(props.filters.note_search ?? '');
const productBox = ref(props.filters.product_search ?? '');
const dateFromBox = ref(toInputDatetime(props.filters.date_from ?? ''));
const dateToBox = ref(toInputDatetime(props.filters.date_to ?? ''));

function pad2(n) {
    return String(n).padStart(2, '0');
}

// Input datetime-local dibaca sebagai WIB (+07:00) lalu dikirim sebagai UTC
// (Odoo menyimpan naive UTC): `2026-11-15T16:00` -> `2026-11-15 09:00:00`.
function toApiDatetime(value) {
    const s = String(value ?? '').trim();
    if (!s) {
        return '';
    }
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/);
    if (!m) {
        return '';
    }
    const d = new Date(`${m[1]}-${m[2]}-${m[3]}T${m[4]}:${m[5]}:${m[6] ?? '00'}+07:00`);
    if (Number.isNaN(d.getTime())) {
        return '';
    }
    return `${d.getUTCFullYear()}-${pad2(d.getUTCMonth() + 1)}-${pad2(d.getUTCDate())} ${pad2(d.getUTCHours())}:${pad2(d.getUTCMinutes())}:${pad2(d.getUTCSeconds())}`;
}

// Nilai UTC dari API/URL ditampilkan kembali sebagai WIB di input.
function toInputDatetime(value) {
    const s = String(value ?? '').trim().replace('T', ' ');
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(?::(\d{2}))?$/);
    if (!m) {
        return '';
    }
    const wib = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +(m[6] ?? '00')) + 7 * 3600 * 1000);
    return `${wib.getUTCFullYear()}-${pad2(wib.getUTCMonth() + 1)}-${pad2(wib.getUTCDate())}T${pad2(wib.getUTCHours())}:${pad2(wib.getUTCMinutes())}`;
}

// Odoo mengirim `false` (bukan null) untuk field kosong; `?? ''` tidak menangkapnya
// sehingga `String(false)` = "false" bocor ke tabel/Excel. Helper ini menormalkannya.
function cellText(value, empty = '') {
    if (value === false || value === null || value === undefined || value === '') {
        return empty;
    }
    return String(value);
}

// Tanggal Odoo untuk modal laporan: kosong (false/null) tampil kosong, bukan `-`.
function fmt(value) {
    if (!value || value === false) {
        return '';
    }
    return formatOdooDate(value);
}

function note40(value) {
    if (!value || value === false) {
        return '';
    }
    return truncate(value, 40);
}

function onDateInput() {
    query.setFilters({ date_from: toApiDatetime(dateFromBox.value), date_to: toApiDatetime(dateToBox.value) });
}

function atNine(daysAgo) {
    const d = new Date();
    d.setDate(d.getDate() - daysAgo);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T09:00`;
}

function presetKemarinLusa() {
    dateFromBox.value = atNine(2);
    dateToBox.value = atNine(1);
    onDateInput();
}

function presetKemarin() {
    dateFromBox.value = atNine(1);
    dateToBox.value = atNine(0);
    onDateInput();
}

function onNoteInput(e) {
    noteBox.value = e.target.value;
    query.setFilters({ note_search: e.target.value }, 1000);
}

function onProductInput(e) {
    productBox.value = e.target.value;
    query.setFilters({ product_search: e.target.value }, 1000);
}

function resetFilter() {
    searchBox.value = '';
    noteBox.value = '';
    productBox.value = '';
    dateFromBox.value = '';
    dateToBox.value = '';
    query.setSearch('', 0);
    query.setFilters({ gudang: '2', note_search: '', product_search: '', date_from: '', date_to: '' });
}

const columns = [
    { key: 'name', label: 'No DO', mono: true },
    { key: 'x_studio_field_0rSR5', label: 'Tgl SO' },
    { key: 'force_date', label: 'FDate' },
    { key: 'partner_id', label: 'Partner' },
    { key: 'origin', label: 'SO', align: 'center' },
    { key: 'state', label: 'Status', align: 'center' },
    { key: 'note_to_wh', label: 'Notes' },
    { key: 'aksi', label: 'Action', align: 'center' },
];
const productColumns = [
    { key: 'code', label: 'Code', mono: true },
    { key: 'desc', label: 'Desc' },
    { key: 'akl', label: 'AKL', align: 'center' },
    { key: 'qty_so', label: 'Qty SO', align: 'center' },
    { key: 'qty_done', label: 'Qty Done', align: 'center' },
];
const lotColumns = [
    { key: 'code', label: 'Code', mono: true },
    { key: 'desc', label: 'Desc' },
    { key: 'product', label: 'Product' },
    { key: 'akl', label: 'AKL' },
    { key: 'lot', label: 'Lot/SN/ED' },
    { key: 'qty', label: 'Qty', align: 'center' },
];

const perPageOptions = [10, 25, 50, 100, 500, 1000];

const aklMap = computed(() => {
    const map = {};
    productLines.value.forEach((l) => {
        const pid = Array.isArray(l.product_id) ? l.product_id[0] : null;
        if (pid) {
            map[pid] = odooName(l.akl_id);
        }
    });
    return map;
});

const lotCodeOptions = computed(() => {
    const codes = [...new Set(lotLines.value.map((l) => getCode(odooName(l.product_id))).filter(Boolean))].sort();
    return [{ label: 'All Products', value: '' }, ...codes.map((c) => ({ label: c, value: c }))];
});

const filteredLots = computed(() => {
    if (!lotCodeFilter.value) {
        return lotLines.value;
    }
    return lotLines.value.filter((l) => getCode(odooName(l.product_id)) === lotCodeFilter.value);
});

function applyLotCodeFilter() {
    lotTable.setRows(filteredLots.value);
}

watch(lotCodeFilter, applyLotCodeFilter);

const productSearchBox = ref('');
const lotSearchBox = ref('');

function onProductSearchInput(e) {
    productSearchBox.value = e.target.value;
    productTable.setSearch(e.target.value);
}

function onLotSearchInput(e) {
    lotSearchBox.value = e.target.value;
    lotTable.setSearch(e.target.value);
}

const productExportRows = computed(() => productTable.filtered.value.map((row) => ({
    code: getCode(odooName(row.product_id)),
    desc: getDesc(odooName(row.product_id)),
    akl: odooName(row.akl_id),
    qty_so: row.product_uom_qty ?? 0,
    qty_done: row.quantity_done ?? 0,
})));

const mainExportRows = computed(() => query.rows.value.map((row) => ({
    no_do: cellText(row.name, '-'),
    tgl_so: fmt(row.x_studio_field_0rSR5) || '-',
    fdate: formatOdooDate(row.force_date),
    partner: String(odooName(row.partner_id)).substring(0, 30),
    so: cellText(row.origin, '-'),
    status: cellText(row.state, '-'),
    note: truncate(cellText(row.note_to_wh), 40),
})));
const mainExportCols = ['no_do', 'tgl_so', 'fdate', 'partner', 'so', 'status', 'note'];

async function exportAllCsv() {
    try {
        const params = {
            export: 'csv',
            search: query.search.value,
            gudang: query.filters.value.gudang ?? '2',
            note_search: query.filters.value.note_search ?? '',
            product_search: query.filters.value.product_search ?? '',
            date_from: query.filters.value.date_from ?? '',
            date_to: query.filters.value.date_to ?? '',
        };
        const res = await api.get('/do', { params, responseType: 'blob', block: true });
        const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `do-${Date.now()}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        toast.success('File CSV diunduh.');
    } catch {
        // toast error sudah ditangani interceptor axios
    }
}

// ---- Modal Tarik Data (laporan range tanggal, default kemarin lusa 15:30 -> kemarin 15:30) ----
const reportOpen = ref(false);
const reportFrom = ref('');
const reportTo = ref('');
const reportSearchBox = ref('');
const reportTable = useClientTable(null, {
    perPage: 25,
    searchKeys: ['name', 'x_studio_no_do_manual', 'partner_id', 'origin', 'note_to_wh', 'note_itr', 'state'],
});
const reportTotal = ref(0);

function atHalfPastThree(daysAgo) {
    const d = new Date();
    d.setDate(d.getDate() - daysAgo);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T15:30`;
}

async function openReport() {
    reportFrom.value = atHalfPastThree(2);
    reportTo.value = atHalfPastThree(1);
    reportSearchBox.value = '';
    reportTable.setSearch('', 0);
    reportTable.setRows([]);
    reportTable.error.value = '';
    await fetchReport();
    reportOpen.value = true;
}

async function fetchReport() {
    try {
        const res = await api.get(
            '/do',
            {
                params: {
                    search: '',
                    page: 1,
                    per_page: 500,
                    date_from: toApiDatetime(reportFrom.value),
                    date_to: toApiDatetime(reportTo.value),
                },
                block: true,
            },
        );
        const body = res.data ?? {};
        reportTable.setRows(body.data ?? []);
        reportTotal.value = Number(body.total ?? (body.data ?? []).length);
        if (reportTotal.value > (body.data ?? []).length) {
            toast.warning(`Menampilkan ${(body.data ?? []).length} dari ${reportTotal.value} baris. Persempit tanggal atau pakai CSV Semua.`);
        }
    } catch {
        // toast error sudah ditangani interceptor axios
    }
}

function onReportSearchInput(e) {
    reportSearchBox.value = e.target.value;
    reportTable.setSearch(e.target.value, 1000);
}

// Nilai relasi/many2one Odoo bisa `[id, nama]`, string, atau `false`.
// Versi laporan: kosong tampil kosong (bukan `-`) agar rapi di Excel.
function textOf(value) {
    if (value === false || value === null || value === undefined || value === '') {
        return '';
    }
    if (Array.isArray(value)) {
        if (value.length > 0 && Array.isArray(value[0])) {
            const names = value.map((v) => v?.[1]).filter(Boolean);
            return names.length > 0 ? names.join(', ') : '';
        }
        return odooName(value);
    }
    return String(value);
}

function priorityOf(row) {
    const map = { 0: 'Low', 1: 'Medium', 2: 'High', 3: 'Very High' };
    const v = row.priority_so ?? row.priority;
    if (v === false || v === null || v === undefined || v === '') {
        return '';
    }
    const key = String(Array.isArray(v) ? (v[0] ?? '') : v);
    return map[key] ?? textOf(v);
}

// Label bawaan Odoo: confirmed tampil "Waiting", assigned "Ready", dst.
function stateLabel(state) {
    const map = {
        draft: 'Draft',
        waiting: 'Waiting Another Operation',
        confirmed: 'Waiting',
        assigned: 'Ready',
        done: 'Done',
        cancel: 'Cancelled',
    };
    return map[String(state ?? '')] ?? cellText(state);
}

// Tanggal saja `YYYY-MM-DD` tampil `DD/MM/YYYY`; kosong tampil kosong.
function dateOnly(value) {
    if (!value || value === false) {
        return '';
    }
    const m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) {
        return `${m[3]}/${m[2]}/${m[1]}`;
    }
    return formatOdooDate(value);
}

const reportColumns = [
    { key: 'name', label: 'Reference', mono: true },
    { key: 'x_studio_no_do_manual', label: 'No DO Manual', mono: true },
    { key: 'scheduled_date', label: 'Scheduled Date' },
    { key: 'force_date', label: 'Force Date' },
    { key: 'partner_id', label: 'Partner' },
    { key: 'location_dest_id', label: 'Destination' },
    { key: 'location_id', label: 'Source' },
    { key: 'origin', label: 'Source Doc', align: 'center' },
    { key: 'tags', label: 'Tags', align: 'center' },
    { key: 'x_studio_field_0rSR5', label: 'Tgl SO' },
    { key: 'priority', label: 'Priority', align: 'center' },
    { key: 'note_to_wh', label: 'Note WH' },
    { key: 'note_itr', label: 'Note ITR' },
    { key: 'backorder_id', label: 'Back Order' },
    { key: 'state', label: 'Status', align: 'center' },
    { key: 'ekspedisi_id', label: 'Expedition' },
    { key: 'shipping_date', label: 'Ship Date' },
    { key: 'tanggal_sampai', label: 'Arrival' },
];

const reportExportRows = computed(() => reportTable.filtered.value.map((row) => ({
    reference: cellText(row.name),
    no_do_manual: cellText(row.x_studio_no_do_manual),
    scheduled: fmt(row.scheduled_date),
    force_date: fmt(row.force_date),
    partner: textOf(row.partner_id),
    destination: textOf(row.location_dest_id),
    source: textOf(row.location_id),
    source_doc: cellText(row.origin),
    tags: textOf(row.x_studio_tags),
    tgl_so: fmt(row.x_studio_field_0rSR5),
    priority: priorityOf(row),
    note_wh: cellText(row.note_to_wh),
    note_itr: cellText(row.note_itr),
    backorder: textOf(row.backorder_id),
    status: stateLabel(row.state),
    expedition: textOf(row.ekspedisi_id),
    ship_date: dateOnly(row.shipping_date),
    arrival: dateOnly(row.tanggal_sampai),
})));
const reportExportCols = [
    'reference',
    'no_do_manual',
    'scheduled',
    'force_date',
    'partner',
    'destination',
    'source',
    'source_doc',
    'tags',
    'tgl_so',
    'priority',
    'note_wh',
    'note_itr',
    'backorder',
    'status',
    'expedition',
    'ship_date',
    'arrival',
];
const labelItems = computed(() => {
    const picked = new Set((selectedProducts.value ?? []).map(String));
    return productTable.filtered.value
        .filter((row) => picked.size === 0 || picked.has(String(row.id ?? '')))
        .map((row) => ({
            code: getCode(odooName(row.product_id)),
            name: getDesc(odooName(row.product_id)),
            qty: Number(row.product_uom_qty ?? 0),
        }));
});

const labelTitle = computed(() => `Label DO ${detail?.value?.name ?? activeRow?.value?.name ?? ''}`);

const lotExportRows = computed(() => lotTable.filtered.value.map((row) => {
    const pid = Array.isArray(row.product_id) ? row.product_id[0] : -1;
    return {
        code: getCode(odooName(row.product_id)),
        desc: getDesc(odooName(row.product_id)),
        product: odooName(row.product_id),
        akl: aklMap.value[pid] ?? '-',
        lot: lotLabel(row),
        qty: row.qty_done ?? 0,
    };
}));

// Lot/SN digabung dengan ED DO: `LOT/2028.01.31`.
// Sumber expired khusus DO adalah `expired_date_do`; bila kosong/false cukup lot saja.
function lotLabel(row) {
    const lot = cleanLotName(odooName(row.lot_id) !== '-' ? odooName(row.lot_id) : (row.lot_name || ''));
    const exp = formatExpDate(row.expired_date_do);
    if (!lot) {
        return exp || '';
    }
    return exp ? `${lot}/${exp}` : lot;
}

function sistemOf(row) {
    const s = String(row.sistem ?? '').trim().toUpperCase();
    return s && s !== '-' ? s : '';
}

let detailSeq = 0;

async function openDetail(row) {
    const seq = ++detailSeq;
    activeRow.value = row;
    detailTab.value = 'product';
    lotCodeFilter.value = '';
    productSearchBox.value = '';
    lotSearchBox.value = '';
    selectedProducts.value = [];
    productTable.setSearch('', 0);
    lotTable.setSearch('', 0);
    productTable.setRows([]);
    lotTable.setRows([]);
    productTable.error.value = '';
    lotTable.error.value = '';
    try {
        const res = await api.get(`/do/${row.id}`, { silent: true, block: true });
        if (seq !== detailSeq) {
            return;
        }
        const body = res.data?.data ?? res.data;
        detail.value = body;
        productLines.value = body.move_ids_detail ?? [];
        lotLines.value = body.move_line_detail ?? [];
        productTable.setRows(productLines.value);
        applyLotCodeFilter();
        detailOpen.value = true;
    } catch (e) {
        if (seq !== detailSeq) {
            return;
        }
        const message = e.response?.data?.message ?? 'Gagal memuat detail DO.';
        productTable.error.value = message;
        lotTable.error.value = message;
        toast.error(message);
    }
}

function printDo(row, withLot = false) {
    window.open(`/do/${row.id}/print${withLot ? '?with_lot=true' : ''}`, '_blank');
}

function soIdOf(row) {
    return odooId(row.sale_id);
}

function printSo(row) {
    const soId = soIdOf(row);
    if (soId <= 0) {
        toast.warning('DO ini tidak terikat SO.');
        return;
    }
    window.open(`/so/${soId}/print`, '_blank');
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari nomor DO..." :active-count="activeCount" @search="query.setSearch" @reset="resetFilter">
            <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
            <Button variant="outline" size="sm" @click="copyRows(mainExportRows, mainExportCols, 'DO')"><Copy /> Salin</Button>
            <Button variant="outline" size="sm" @click="downloadCsv('do', mainExportRows, mainExportCols)"><Download /> CSV</Button>
            <Button variant="outline" size="sm" title="Unduh seluruh data sesuai filter (maks 2000 baris)" @click="exportAllCsv()"><Download /> CSV Semua</Button>
            <Button variant="outline" size="sm" title="Buka laporan tarik data per tanggal" @click="openReport()"><Download /> Tarik Data</Button>
            <template #filters>
                <FormField label="Gudang">
                    <SearchableSelect :model-value="query.filters.value.gudang" :options="gudangOptions" placeholder="Pilih gudang..." @update:model-value="query.setFilters({ gudang: $event })" />
                </FormField>
                <FormField label="Note">
                    <Input :model-value="noteBox" placeholder="Cari Note..." @input="onNoteInput" />
                </FormField>
                <FormField label="Kode Produk">
                    <Input :model-value="productBox" placeholder="Cari kode produk..." @input="onProductInput" />
                </FormField>
                <FormField label="Tgl SO Dari (WIB)">
                    <DateTimePicker v-model="dateFromBox" placeholder="Dari..." @change="onDateInput" />
                </FormField>
                <FormField label="Tgl SO Sampai (WIB)">
                    <DateTimePicker v-model="dateToBox" placeholder="Sampai..." @change="onDateInput" />
                </FormField>
            </template>
            <template #filterActions>
                <Button variant="outline" size="sm" @click="presetKemarinLusa()">Kemarin lusa - Kemarin 09:00 WIB</Button>
                <Button variant="outline" size="sm" @click="presetKemarin()">Kemarin - Hari ini 09:00 WIB</Button>
            </template>
        </TableToolbar>
        <DataTable
            :columns="columns"
            :rows="query.rows.value"
            :loading="query.loading.value"
            :error="query.error.value"
            :page="query.page.value"
            :total-pages="query.totalPages.value"
            :total="query.total.value"
            :per-page="query.perPage.value"
            :per-page-options="perPageOptions"
            clickable
            empty-title="DO tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-name="{ row }"><b>{{ row.name ?? '-' }}</b> <span v-if="sistemOf(row)" class="ml-1 rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700">{{ sistemOf(row) }}</span></template>
            <template #cell-x_studio_field_0rSR5="{ row }">{{ formatOdooDate(row.x_studio_field_0rSR5) }}</template>
            <template #cell-force_date="{ row }">{{ formatOdooDate(row.force_date) }}</template>
            <template #cell-partner_id="{ row }">{{ String(odooName(row.partner_id)).substring(0, 30) }}</template>
            <template #cell-origin="{ row }">{{ cellText(row.origin, '-') }}</template>
            <template #cell-state="{ row }">{{ cellText(row.state, '-') }}</template>
            <template #cell-note_to_wh="{ row }">{{ truncate(cellText(row.note_to_wh), 40) }}</template>
            <template #cell-aksi="{ row }">
                <div class="flex justify-center gap-1" @click.stop>
                    <Button variant="outline" size="sm" title="Print DO" @click.stop="printDo(row, false)"><Printer /></Button>
                    <Button variant="outline" size="sm" title="Print Lot" @click.stop="printDo(row, true)">Lot</Button>
                    <Button v-if="soIdOf(row) > 0" variant="outline" size="sm" title="Print SO" @click.stop="printSo(row)">SO</Button>
                </div>
            </template>
        </DataTable>
        <AppModal v-model:open="detailOpen" size="2xl">
            <template #title>
                {{ `Item DO: ${detail?.name ?? activeRow?.name ?? ''}` }}
                <CopyButton :text="detail?.name ?? activeRow?.name ?? ''" label="No DO" />
            </template>
            <ModalTabs v-model="detailTab" :tabs="detailTabs" class="mb-2" />
            <div v-if="detailTab === 'product'">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="productSearchBox" type="search" placeholder="Cari code / desc..." class="max-w-xs" @input="onProductSearchInput" />
                    <Button variant="outline" size="sm" @click="copyRows(productExportRows, ['code', 'desc', 'akl', 'qty_so', 'qty_done'], 'item DO')"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadCsv(`do-${detail?.name ?? activeRow?.name ?? 'detail'}-product`, productExportRows, ['code', 'desc', 'akl', 'qty_so', 'qty_done'])"><Download /> CSV</Button>
                    <LabelPrint :items="labelItems" :title="labelTitle" />
                </div>
                <DataTable
                    :columns="productColumns"
                    :rows="productTable.rows.value"
                    :loading="productTable.loading.value"
                    :error="productTable.error.value"
                    :page="productTable.page.value"
                    :total-pages="productTable.totalPages.value"
                    :total="productTable.total.value"
                    :per-page="productTable.perPage.value"
                    :selected="selectedProducts"
                    sortable
                    selectable
                    empty-title="Item tidak ditemukan"
                    empty-message="Ubah kata kunci pencarian. Centang baris bila hanya sebagian item yang mau dicetak."
                    @update:page="productTable.setPage"
                    @update:per-page="productTable.setPerPage"
                    @update:selected="selectedProducts = $event"
                    @sort="productTable.toggleSort"
                >
                    <template #cell-code="{ row }">{{ getCode(odooName(row.product_id)) }}</template>
                    <template #cell-desc="{ row }">{{ getDesc(odooName(row.product_id)) }}</template>
                    <template #cell-akl="{ row }">{{ odooName(row.akl_id) }}</template>
                    <template #cell-qty_so="{ row }">{{ row.product_uom_qty ?? 0 }}</template>
                    <template #cell-qty_done="{ row }">{{ row.quantity_done ?? 0 }}</template>
                </DataTable>
            </div>
            <div v-else>
                <div class="mb-2 grid max-w-xl gap-2 sm:grid-cols-2">
                    <SearchableSelect v-model="lotCodeFilter" :options="lotCodeOptions" placeholder="Select Product Code" />
                    <Button variant="outline" size="sm" @click="lotCodeFilter = ''">Reset</Button>
                </div>
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="lotSearchBox" type="search" placeholder="Cari lot..." class="max-w-xs" @input="onLotSearchInput" />
                    <Button variant="outline" size="sm" @click="copyRows(lotExportRows, ['code', 'desc', 'product', 'akl', 'lot', 'qty'], 'lot DO')"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadCsv(`do-${detail?.name ?? activeRow?.name ?? 'detail'}-lot`, lotExportRows, ['code', 'desc', 'product', 'akl', 'lot', 'qty'])"><Download /> CSV</Button>
                </div>
                <DataTable
                    :columns="lotColumns"
                    :rows="lotTable.rows.value"
                    :loading="lotTable.loading.value"
                    :error="lotTable.error.value"
                    :page="lotTable.page.value"
                    :total-pages="lotTable.totalPages.value"
                    :total="lotTable.total.value"
                    :per-page="lotTable.perPage.value"
                    sortable
                    empty-title="Lot tidak ditemukan"
                    empty-message="Ubah kata kunci pencarian."
                    @update:page="lotTable.setPage"
                    @update:per-page="lotTable.setPerPage"
                    @sort="lotTable.toggleSort"
                >
                    <template #cell-code="{ row }">{{ getCode(odooName(row.product_id)) }}</template>
                    <template #cell-desc="{ row }">{{ getDesc(odooName(row.product_id)) }}</template>
                    <template #cell-product="{ row }">{{ odooName(row.product_id) }}</template>
                    <template #cell-akl="{ row }">{{ aklMap[Array.isArray(row.product_id) ? row.product_id[0] : -1] ?? '-' }}</template>
                    <template #cell-lot="{ row }">{{ lotLabel(row) }}</template>
                    <template #cell-qty="{ row }">{{ row.qty_done ?? 0 }}</template>
                </DataTable>
            </div>
            <div class="mt-2 space-y-1 border-t pt-2 text-sm">
                <p><b>Status:</b> <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800">{{ cellText(detail?.state, '-') }}</span></p>
                <p class="whitespace-pre-wrap"><b>Notes:</b> {{ cellText(detail?.note_to_wh, '-') }} <CopyButton :text="cellText(detail?.note_to_wh)" label="Notes" /></p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
                <Button @click="printDo(activeRow, false)"><Printer /> Print</Button>
                <Button variant="outline" @click="printDo(activeRow, true)">Print Lot</Button>
            </template>
        </AppModal>
        <AppModal v-model:open="reportOpen" size="2xl">
            <template #title>
                Tarik Data DO ({{ reportTotal }} baris)
            </template>
            <div class="mb-2 grid gap-2 sm:grid-cols-2">
                <FormField label="Dari (WIB)">
                    <DateTimePicker v-model="reportFrom" placeholder="Dari..." />
                </FormField>
                <FormField label="Sampai (WIB)">
                    <DateTimePicker v-model="reportTo" placeholder="Sampai..." />
                </FormField>
            </div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <Button size="sm" @click="fetchReport()"><RefreshCw /> Terapkan</Button>
                <Button
                    variant="outline"
                    size="sm"
                    @click="reportFrom = atHalfPastThree(2); reportTo = atHalfPastThree(1); fetchReport();"
                >
                    Kemarin lusa - Kemarin 15:30 WIB
                </Button>
                <Input :model-value="reportSearchBox" type="search" placeholder="Cari di hasil..." class="max-w-xs" @input="onReportSearchInput" />
                <Button variant="outline" size="sm" @click="copyRows(reportExportRows, reportExportCols, 'DO')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv('do-tarik-data', reportExportRows, reportExportCols)"><Download /> CSV</Button>
            </div>
            <DataTable
                :columns="reportColumns"
                :rows="reportTable.rows.value"
                :loading="reportTable.loading.value"
                :error="reportTable.error.value"
                :page="reportTable.page.value"
                :total-pages="reportTable.totalPages.value"
                :total="reportTable.total.value"
                :per-page="reportTable.perPage.value"
                :per-page-options="[10, 25, 50, 100, 500]"
                sortable
                empty-title="Tidak ada data"
                empty-message="Ubah rentang tanggal lalu klik Terapkan."
                @update:page="reportTable.setPage"
                @update:per-page="reportTable.setPerPage"
                @sort="reportTable.toggleSort"
            >
                <template #cell-name="{ row }"><b>{{ cellText(row.name) }}</b></template>
                <template #cell-x_studio_no_do_manual="{ row }">{{ cellText(row.x_studio_no_do_manual) }}</template>
                <template #cell-scheduled_date="{ row }">{{ fmt(row.scheduled_date) }}</template>
                <template #cell-force_date="{ row }">{{ fmt(row.force_date) }}</template>
                <template #cell-partner_id="{ row }">{{ textOf(row.partner_id) }}</template>
                <template #cell-location_dest_id="{ row }">{{ textOf(row.location_dest_id) }}</template>
                <template #cell-location_id="{ row }">{{ textOf(row.location_id) }}</template>
                <template #cell-origin="{ row }">{{ cellText(row.origin) }}</template>
                <template #cell-tags="{ row }">{{ textOf(row.x_studio_tags) }}</template>
                <template #cell-x_studio_field_0rSR5="{ row }">{{ fmt(row.x_studio_field_0rSR5) }}</template>
                <template #cell-priority="{ row }">{{ priorityOf(row) }}</template>
                <template #cell-note_to_wh="{ row }">{{ note40(row.note_to_wh) }}</template>
                <template #cell-note_itr="{ row }">{{ note40(row.note_itr) }}</template>
                <template #cell-backorder_id="{ row }">{{ textOf(row.backorder_id) }}</template>
                <template #cell-state="{ row }">{{ stateLabel(row.state) }}</template>
                <template #cell-ekspedisi_id="{ row }">{{ textOf(row.ekspedisi_id) }}</template>
                <template #cell-shipping_date="{ row }">{{ dateOnly(row.shipping_date) }}</template>
                <template #cell-tanggal_sampai="{ row }">{{ dateOnly(row.tanggal_sampai) }}</template>
            </DataTable>
            <template #footer>
                <Button variant="ghost" @click="reportOpen = false">Tutup</Button>
                <Button variant="outline" @click="downloadCsv('do-tarik-data', reportExportRows, reportExportCols)"><Download /> CSV</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
