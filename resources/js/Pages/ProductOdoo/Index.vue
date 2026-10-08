<script setup>
import { computed, ref } from 'vue';
import { Eye, Move, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import BlockOverlay from '@/components/BlockOverlay.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { formatExpDate, formatQtyUS, getCode, getDesc, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Product Odoo' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/product-odoo', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const detailOpen = ref(false);
const onhandOpen = ref(false);
const detail = ref(null);
const productLines = ref([]);
const lotLines = ref([]);
const onhandRows = ref([]);
const detailLoading = ref(false);
const onhandLoading = ref(false);
const onhandTitle = ref('');
const activeRow = ref(null);
const detailTab = ref('product');
const lotCodeFilter = ref('');

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
    { key: 'aksi', label: '#' },
    { key: 'default_code', label: 'Code', mono: true },
    { key: 'name', label: 'Name' },
    { key: 'akl_id', label: 'AKL' },
    { key: 'x_studio_valid_to_akl', label: 'AKL To', align: 'center' },
    { key: 'qty_available', label: 'On Hand', align: 'center' },
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
    { key: 'lot', label: 'Lot/SN' },
    { key: 'qty', label: 'Qty', align: 'center' },
    { key: 'exp', label: 'Exp Date', align: 'center' },
];
const onhandColumns = [
    { key: 'product_id', label: 'Product' },
    { key: 'location_id', label: 'Location' },
    { key: 'lot_id', label: 'Lot' },
    { key: 'itds_expired', label: 'Ed', align: 'center' },
    { key: 'quantity', label: 'Qty', align: 'center' },
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

const onhandSummary = computed(() => {
    const map = {};
    onhandRows.value.forEach((r) => {
        const loc = odooName(r.location_id);
        map[loc] = (map[loc] ?? 0) + Number(r.quantity ?? 0);
    });
    return Object.entries(map);
});

function variantOf(row) {
    return Array.isArray(row.product_variant_id) ? row.product_variant_id[0] : (row.product_variant_id ?? 0);
}

async function openDetail(row) {
    activeRow.value = row;
    detailOpen.value = true;
    detailTab.value = 'product';
    lotCodeFilter.value = '';
    detailLoading.value = true;
    try {
        const res = await api.get(`/product-odoo/${row.id}`, { silent: true });
        const body = res.data?.data ?? res.data;
        detail.value = body;
        productLines.value = body.move_ids_detail ?? [];
        lotLines.value = body.move_line_detail ?? [];
    } catch (e) {
        detail.value = row;
        productLines.value = [];
        lotLines.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail produk.');
    } finally {
        detailLoading.value = false;
    }
}

async function openStock(row, mode) {
    activeRow.value = row;
    onhandOpen.value = true;
    onhandLoading.value = true;
    onhandRows.value = [];
    onhandTitle.value = `${mode === 'move' ? 'Move' : 'On Hand'}: ${row.name ?? ''}`;
    try {
        const res = await api.get(`/product-odoo/${row.id}/${variantOf(row)}/${mode === 'move' ? 'move' : 'on-hand'}`, { silent: true });
        const body = res.data?.data ?? res.data;
        onhandRows.value = body.data ?? body.records ?? [];
    } catch (e) {
        onhandRows.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat data stock.');
    } finally {
        onhandLoading.value = false;
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <BlockOverlay>
            <PageHeader :title="title" description="Daftar Product Odoo">
                <template #actions>
                    <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                </template>
            </PageHeader>
            <FilterPanel title="Filter Produk" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari code / nama..." @input="onSearchInput" />
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
                empty-title="Produk tidak ditemukan"
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @row-click="openDetail"
            >
                <template #cell-aksi="{ row }">
                    <div class="flex justify-center gap-1">
                        <Button variant="outline" size="sm" title="On Hand" @click="openStock(row, 'onhand')"><Eye /></Button>
                        <Button variant="outline" size="sm" title="Move" @click="openStock(row, 'move')"><Move /></Button>
                    </div>
                </template>
                <template #cell-default_code="{ row }">{{ row.default_code ?? '-' }}</template>
                <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
                <template #cell-akl_id="{ row }">{{ odooName(row.akl_id) }}</template>
                <template #cell-x_studio_valid_to_akl="{ row }">{{ row.x_studio_valid_to_akl ?? '-' }}</template>
                <template #cell-qty_available="{ row }">{{ formatQtyUS(row.qty_available) }}</template>
            </DataTable>
            <AppModal v-model:open="detailOpen" :title="`List Item RI No : ${detail?.name ?? activeRow?.name ?? ''}`" size="xl">
                <div class="mb-2 flex gap-1 border-b">
                    <Button :variant="detailTab === 'product' ? 'default' : 'ghost'" size="sm" @click="detailTab = 'product'">Product</Button>
                    <Button :variant="detailTab === 'lot' ? 'default' : 'ghost'" size="sm" @click="detailTab = 'lot'">Product Lot</Button>
                </div>
                <div v-if="detailTab === 'product'">
                    <DataTable :columns="productColumns" :rows="productLines" :loading="detailLoading" :total="productLines.length" :total-pages="1" empty-title="Item tidak ditemukan">
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
                    <DataTable :columns="lotColumns" :rows="filteredLots" :loading="detailLoading" :total="filteredLots.length" :total-pages="1" empty-title="Lot tidak ditemukan">
                        <template #cell-code="{ row }">{{ getCode(odooName(row.product_id)) }}</template>
                        <template #cell-desc="{ row }">{{ getDesc(odooName(row.product_id)) }}</template>
                        <template #cell-product="{ row }">{{ odooName(row.product_id) }}</template>
                        <template #cell-akl="{ row }">{{ aklMap[Array.isArray(row.product_id) ? row.product_id[0] : -1] ?? '-' }}</template>
                        <template #cell-lot="{ row }">{{ odooName(row.lot_id) !== '-' ? odooName(row.lot_id) : (row.lot_name ?? '') }}</template>
                        <template #cell-qty="{ row }">{{ row.qty_done ?? 0 }}</template>
                        <template #cell-exp="{ row }">{{ formatExpDate(row.expired_date) }}</template>
                    </DataTable>
                </div>
                <div class="mt-2 space-y-1 border-t pt-2 text-sm">
                    <p><b>Origin/PO:</b> {{ detail?.origin ?? '-' }}</p>
                    <p class="whitespace-pre-wrap"><b>Notes:</b> {{ detail?.note_to_wh ?? '-' }}</p>
                </div>
            </AppModal>
            <AppModal v-model:open="onhandOpen" :title="onhandTitle" size="xl">
                <DataTable :columns="onhandColumns" :rows="onhandRows" :loading="onhandLoading" :total="onhandRows.length" :total-pages="1" empty-title="Data tidak ditemukan">
                    <template #cell-product_id="{ row }">{{ odooName(row.product_id) }}</template>
                    <template #cell-location_id="{ row }">{{ odooName(row.location_id) }}</template>
                    <template #cell-lot_id="{ row }">{{ odooName(row.lot_id) }}</template>
                    <template #cell-itds_expired="{ row }">{{ formatExpDate(row.itds_expired) }}</template>
                    <template #cell-quantity="{ row }">{{ formatQtyUS(row.quantity) }}</template>
                </DataTable>
                <div class="mt-2 border-t pt-2 text-sm">
                    <b>Summary:</b>
                    <div v-if="onhandSummary.length > 0" class="mt-1 space-y-1">
                        <div v-for="[loc, qty] in onhandSummary" :key="loc">→ <b>{{ loc }}</b>: <StatusBadge status="secondary">{{ formatQtyUS(qty) }}</StatusBadge></div>
                    </div>
                    <p v-else class="text-muted-foreground">-</p>
                </div>
            </AppModal>
        </BlockOverlay>
    </AppLayout>
</template>
