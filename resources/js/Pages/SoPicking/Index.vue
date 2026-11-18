<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import DatePicker from '@/components/DatePicker.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { formatExpDate, formatOdooDate, odooName, truncate } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'SO Picking' },
    filters: { type: Object, default: () => ({}) },
});

const stateOptions = [
    { label: 'Semua', value: '' },
    { label: 'Waiting Another Operation', value: 'waiting' },
    { label: 'Waiting', value: 'confirmed' },
    { label: 'Available', value: 'assigned' },
    { label: 'Done', value: 'done' },
];

const gudangOptions = [
    { label: 'Semua', value: '' },
    { label: 'CENTER/Stock', value: '12' },
    { label: 'GA/Stock', value: '1742' },
];

function pad2(n) {
    return String(n).padStart(2, '0');
}

// Tanggal lokal browser (zona user = WIB) format Y-m-d untuk <input type="date">.
function todayYmd(daysAgo = 0) {
    const d = new Date();
    d.setDate(d.getDate() - daysAgo);
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function validYmd(value) {
    return /^\d{4}-\d{2}-\d{2}$/.test(String(value ?? '').trim()) ? String(value).trim() : '';
}

// Default: kemarin + gudang CENTER/Stock,
// kecuali URL sudah membawa filter tanggal/gudang sendiri.
const hasDateParam = validYmd(props.filters.date) !== '';
const hasLocationParam = String(props.filters.location_id ?? '') !== '';

const query = useTableQuery((p) => api.get('/so-picking', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    perPage: 80,
    filters: {
        state: props.filters.state ?? '',
        product_search: props.filters.product_search ?? '',
        date: hasDateParam ? validYmd(props.filters.date) : todayYmd(1),
        location_id: hasLocationParam ? String(props.filters.location_id) : '12',
    },
});

const searchBox = ref(props.filters.search ?? '');
const productBox = ref(props.filters.product_search ?? '');
const dateBox = ref(hasDateParam ? validYmd(props.filters.date) : todayYmd(1));

// Search utama jalan murni di frontend (tanpa request): data sudah dibatasi
// filter tanggal/gudang di server, jadi tinggal saring baris yang ke-load.
// Kolom yang dicari: picking, order, produk, customer, deskripsi, lokasi.
const feKeyword = computed(() => String(searchBox.value ?? '').trim().toLowerCase());

const filteredRows = computed(() => {
    const kw = feKeyword.value;
    if (kw === '') {
        return query.rows.value;
    }
    return query.rows.value.filter((row) => {
        const hay = [
            odooName(row.picking_id),
            cellText(row.origin, ''),
            odooName(row.product_id),
            odooName(row.partner_id),
            cellText(row.name, ''),
            odooName(row.location_id),
        ].join(' ').toLowerCase();

        return hay.includes(kw);
    });
});

const activeCount = computed(
    () =>
        (query.filters.value.state ? 1 : 0) +
        (query.filters.value.product_search ? 1 : 0) +
        (query.filters.value.date ? 1 : 0) +
        (query.filters.value.location_id ? 1 : 0),
);

function onProductInput(e) {
    productBox.value = e.target.value;
    query.setFilters({ product_search: e.target.value }, 1000);
}

function onDateInput() {
    query.setFilters({ date: validYmd(dateBox.value) });
}

function presetHariIni() {
    dateBox.value = todayYmd(0);
    onDateInput();
}

function presetKemarin() {
    dateBox.value = todayYmd(1);
    onDateInput();
}

function resetFilter() {
    searchBox.value = '';
    productBox.value = '';
    dateBox.value = todayYmd(1);
    query.setSearch('', 0);
    query.setFilters({ state: '', product_search: '', date: dateBox.value, location_id: '12' });
}

function stateLabel(state) {
    const map = {
        draft: 'Draft',
        waiting: 'Waiting Another Operation',
        confirmed: 'Waiting',
        assigned: 'Available',
        done: 'Done',
        cancel: 'Cancelled',
    };
    return map[String(state ?? '')] ?? String(state ?? '-');
}

function cellText(value, empty = '-') {
    if (value === false || value === null || value === undefined || value === '') {
        return empty;
    }
    return String(value);
}

const columns = [
    { key: 'confirmation_date_so', label: 'Confirmation Date SO' },
    { key: 'picking_id', label: 'Picking Reference', mono: true },
    { key: 'origin', label: 'Order Reference', mono: true },
    { key: 'partner_id', label: 'Customer' },
    { key: 'location_id', label: 'Source Location' },
    { key: 'product_id', label: 'Product' },
    { key: 'name', label: 'Description' },
    { key: 'exp_date', label: 'Expire Date', align: 'center' },
    { key: 'product_uom_qty', label: 'Quantity', align: 'right' },
    { key: 'quantity_done', label: 'Done', align: 'right' },
    { key: 'product_uom', label: 'UoM', align: 'center' },
    { key: 'state', label: 'Status', align: 'center' },
];

const perPageOptions = [10, 25, 50, 80, 100, 500];

const exportRows = computed(() => filteredRows.value.map((row) => ({
    confirmation_date_so: row.confirmation_date_so && row.confirmation_date_so !== false ? formatOdooDate(row.confirmation_date_so) : '',
    picking_reference: odooName(row.picking_id),
    order_reference: cellText(row.origin, ''),
    customer: String(odooName(row.partner_id)).substring(0, 30),
    source_location: odooName(row.location_id),
    product: odooName(row.product_id),
    description: cellText(row.name, ''),
    expire_date: formatExpDate(row.exp_date),
    quantity: row.product_uom_qty ?? 0,
    done: row.quantity_done ?? 0,
    uom: odooName(row.product_uom),
    status: stateLabel(row.state),
})));
const exportCols = ['confirmation_date_so', 'picking_reference', 'order_reference', 'customer', 'source_location', 'product', 'description', 'expire_date', 'quantity', 'done', 'uom', 'status'];

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari di data ini..." :active-count="activeCount" @reset="resetFilter">
            <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
            <Button variant="outline" size="sm" @click="copyRows(exportRows, exportCols, 'SO Picking')"><Copy /> Salin</Button>
            <Button variant="outline" size="sm" @click="downloadCsv('so-picking', exportRows, exportCols)"><Download /> CSV</Button>
            <template #filters>
                <FormField label="Status">
                    <SearchableSelect :model-value="query.filters.value.state" :options="stateOptions" placeholder="Semua" @update:model-value="query.setFilters({ state: $event })" />
                </FormField>
                <FormField label="Gudang">
                    <SearchableSelect :model-value="query.filters.value.location_id" :options="gudangOptions" placeholder="Semua" @update:model-value="query.setFilters({ location_id: $event })" />
                </FormField>
                <FormField label="Tanggal (WIB)">
                    <DatePicker v-model="dateBox" placeholder="Pilih tanggal..." @change="onDateInput" />
                </FormField>
                <FormField label="Kode Produk">
                    <Input :model-value="productBox" placeholder="Cari kode produk..." @input="onProductInput" />
                </FormField>
            </template>
            <template #filterActions>
                <Button variant="outline" size="sm" @click="presetHariIni()">Hari ini</Button>
                <Button variant="outline" size="sm" @click="presetKemarin()">Kemarin</Button>
            </template>
        </TableToolbar>
        <DataTable
            :columns="columns"
            :rows="filteredRows"
            :loading="query.loading.value"
            :error="query.error.value"
            :page="query.page.value"
            :total-pages="query.totalPages.value"
            :total="query.total.value"
            :per-page="query.perPage.value"
            :per-page-options="perPageOptions"
            empty-title="SO Picking tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
        >
            <template #cell-confirmation_date_so="{ row }">{{ row.confirmation_date_so && row.confirmation_date_so !== false ? formatOdooDate(row.confirmation_date_so) : '-' }}</template>
            <template #cell-picking_id="{ row }"><b>{{ odooName(row.picking_id) }}</b></template>
            <template #cell-origin="{ row }">{{ cellText(row.origin) }}</template>
            <template #cell-partner_id="{ row }">{{ String(odooName(row.partner_id)).substring(0, 30) }}</template>
            <template #cell-location_id="{ row }">{{ odooName(row.location_id) }}</template>
            <template #cell-product_id="{ row }">{{ odooName(row.product_id) }}</template>
            <template #cell-name="{ row }">{{ truncate(cellText(row.name, ''), 60) }}</template>
            <template #cell-exp_date="{ row }">{{ formatExpDate(row.exp_date) || '-' }}</template>
            <template #cell-product_uom_qty="{ row }">{{ row.product_uom_qty ?? 0 }}</template>
            <template #cell-quantity_done="{ row }">{{ row.quantity_done ?? 0 }}</template>
            <template #cell-product_uom="{ row }">{{ odooName(row.product_uom) }}</template>
            <template #cell-state="{ row }">{{ stateLabel(row.state) }}</template>
        </DataTable>
    </AppLayout>
</template>
