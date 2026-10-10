<script setup>
import { computed, ref, watch } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import ModalTabs from '@/components/ModalTabs.vue';
import CopyButton from '@/components/CopyButton.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { formatExpDate, getCode, getDesc, odooName, truncate } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'RI' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/ri', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const detailOpen = ref(false);
const detail = ref(null);
const productLines = ref([]);
const lotLines = ref([]);
const activeRow = ref(null);
const detailTab = ref('product');
const lotCodeFilter = ref('');
const productTable = useClientTable(null, {
    searchKeys: ['product_id', 'akl_id', 'name', 'product_uom_qty', 'quantity_done'],
    perPage: 50,
});
const lotTable = useClientTable(null, {
    searchKeys: ['product_id', 'akl_id', 'lot_id', 'lot_name', 'expired_date', 'qty_done'],
});
const detailTabs = computed(() => [
    { value: 'product', label: 'Product', count: productTable.total.value },
    { value: 'lot', label: 'Product Lot', count: lotTable.total.value },
]);

const activeCount = computed(() => (query.search.value ? 1 : 0));

const searchBox = ref(props.filters.search ?? '');

function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}

function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
}

const columns = [
    { key: 'name', label: 'No', mono: true },
    { key: 'partner_id', label: 'Vendor' },
    { key: 'origin', label: 'PO' },
    { key: 'note_to_wh', label: 'Notes' },
    { key: 'state', label: 'Status', align: 'center' },
];
const productColumns = [
    { key: 'code', label: 'Code', mono: true },
    { key: 'desc', label: 'Desc' },
    { key: 'name', label: 'Name' },
    { key: 'akl', label: 'AKL' },
    { key: 'qty_total', label: 'Qty Total', align: 'center' },
    { key: 'qty_done', label: 'Qty Done', align: 'center' },
    { key: 'qty_sisa', label: 'Qty Sisa', align: 'center' },
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
    name: row.name ?? '',
    akl: odooName(row.akl_id),
    qty_total: row.product_uom_qty ?? 0,
    qty_done: row.quantity_done ?? 0,
    qty_sisa: Number(row.product_uom_qty ?? 0) - Number(row.quantity_done ?? 0),
})));

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

function lotLabel(row) {
    const lot = odooName(row.lot_id) !== '-' ? odooName(row.lot_id) : (row.lot_name ?? '');
    const exp = formatExpDate(row.expired_date);
    return exp ? `${lot}/${exp}` : (lot || '');
}

async function openDetail(row) {
    activeRow.value = row;
    detailOpen.value = true;
    detailTab.value = 'product';
    lotCodeFilter.value = '';
    productSearchBox.value = '';
    lotSearchBox.value = '';
    productTable.setSearch('', 0);
    lotTable.setSearch('', 0);
    productTable.setRows([]);
    lotTable.setRows([]);
    productTable.loading.value = true;
    lotTable.loading.value = true;
    try {
        const res = await api.get(`/ri/${encodeURIComponent(row.id)}`, { silent: true, block: true });
        const body = res.data?.data ?? res.data;
        detail.value = body;
        productLines.value = body.move_without_package_detail ?? [];
        lotLines.value = body.move_line_ids_without_package_detail ?? [];
        productTable.setRows(productLines.value);
        applyLotCodeFilter();
    } catch (e) {
        detail.value = row;
        productLines.value = [];
        lotLines.value = [];
        productTable.setRows([]);
        lotTable.setRows([]);
        const message = e.response?.data?.message ?? 'Gagal memuat detail RI.';
        productTable.error.value = message;
        lotTable.error.value = message;
        toast.error(message);
    } finally {
        productTable.loading.value = false;
        lotTable.loading.value = false;
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter RI" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari nomor RI..." @input="onSearchInput" />
            </FormField>
        </FilterPanel>
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
            empty-title="RI tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-name="{ row }"><b>{{ row.name ?? '-' }}</b></template>
            <template #cell-partner_id="{ row }">{{ Array.isArray(row.partner_id) ? odooName(row.partner_id) : '-' }}</template>
            <template #cell-origin="{ row }">{{ row.origin ?? '-' }}</template>
            <template #cell-note_to_wh="{ row }">{{ truncate(row.note_to_wh, 50) }}</template>
            <template #cell-state="{ row }">{{ row.state ?? '-' }}</template>
        </DataTable>
        <AppModal v-model:open="detailOpen" size="xl">
            <template #title>
                {{ `List Item RI No : ${detail?.name ?? activeRow?.name ?? ''}` }}
                <CopyButton :text="detail?.name ?? activeRow?.name ?? ''" label="No RI" />
            </template>
            <ModalTabs v-model="detailTab" :tabs="detailTabs" class="mb-2" />
            <div v-if="detailTab === 'product'">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="productSearchBox" type="search" placeholder="Cari code / nama..." class="max-w-xs" @input="onProductSearchInput" />
                    <Button variant="outline" size="sm" @click="copyRows(productExportRows, ['code', 'desc', 'name', 'akl', 'qty_total', 'qty_done', 'qty_sisa'], 'item RI')"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadCsv(`ri-${detail?.name ?? activeRow?.name ?? 'detail'}-product`, productExportRows, ['code', 'desc', 'name', 'akl', 'qty_total', 'qty_done', 'qty_sisa'])"><Download /> CSV</Button>
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
                    sortable
                    empty-title="Item tidak ditemukan"
                    empty-message="Ubah kata kunci pencarian."
                    @update:page="productTable.setPage"
                    @update:per-page="productTable.setPerPage"
                    @sort="productTable.toggleSort"
                >
                    <template #cell-code="{ row }">{{ getCode(odooName(row.product_id)) }}</template>
                    <template #cell-desc="{ row }">{{ getDesc(odooName(row.product_id)) }}</template>
                    <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
                    <template #cell-akl="{ row }">{{ odooName(row.akl_id) }}</template>
                    <template #cell-qty_total="{ row }">{{ row.product_uom_qty ?? 0 }}</template>
                    <template #cell-qty_done="{ row }">{{ row.quantity_done ?? 0 }}</template>
                    <template #cell-qty_sisa="{ row }">{{ Number(row.product_uom_qty ?? 0) - Number(row.quantity_done ?? 0) }}</template>
                </DataTable>
            </div>
            <div v-else>
                <div class="mb-2 grid max-w-xl gap-2 sm:grid-cols-2">
                    <SearchableSelect v-model="lotCodeFilter" :options="lotCodeOptions" placeholder="Select Product Code" />
                    <Button variant="outline" size="sm" @click="lotCodeFilter = ''">Reset</Button>
                </div>
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="lotSearchBox" type="search" placeholder="Cari lot..." class="max-w-xs" @input="onLotSearchInput" />
                    <Button variant="outline" size="sm" @click="copyRows(lotExportRows, ['code', 'desc', 'product', 'akl', 'lot', 'qty'], 'lot RI')"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadCsv(`ri-${detail?.name ?? activeRow?.name ?? 'detail'}-lot`, lotExportRows, ['code', 'desc', 'product', 'akl', 'lot', 'qty'])"><Download /> CSV</Button>
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
                <p><b>Origin/PO:</b> {{ detail?.origin ?? '-' }} <CopyButton :text="detail?.origin ?? ''" label="Origin/PO" /></p>
                <p class="whitespace-pre-wrap"><b>Notes:</b> {{ detail?.note_to_wh ?? '-' }} <CopyButton :text="detail?.note_to_wh ?? ''" label="Notes" /></p>
            </div>
        </AppModal>
    </AppLayout>
</template>
