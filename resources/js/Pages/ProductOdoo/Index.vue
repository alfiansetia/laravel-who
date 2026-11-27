<script setup>
import { computed, ref } from 'vue';
import { Copy, Eye, Move, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import FormField from '@/components/FormField.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyText } from '@/lib/export';
import { cleanLotName, formatExpDate, formatOdooDate, formatQtyUS, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Product Odoo' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/product-odoo', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    filters: { type: initialTypes() },
});
const onhandOpen = ref(false);
const onhandRows = ref([]);
const onhandLoading = ref(false);
const onhandTitle = ref('');
const stockMode = ref('onhand');
const stockLocationFilter = ref('');

const stockLocationOptions = computed(() => {
    const locs = [...new Set(onhandRows.value.map((r) => odooName(r.location_id)).filter((v) => v && v !== '-'))].sort();
    return [{ label: 'Semua Lokasi', value: '' }, ...locs.map((l) => ({ label: l, value: l }))];
});

const filteredStockRows = computed(() => {
    if (stockMode.value !== 'onhand' || !stockLocationFilter.value) {
        return onhandRows.value;
    }
    return onhandRows.value.filter((r) => odooName(r.location_id) === stockLocationFilter.value);
});

const searchBox = ref(props.filters.search ?? '');

function initialTypes() {
    const raw = props.filters.type;
    const list = Array.isArray(raw) ? raw : String(raw ?? '').split(',').map((s) => s.trim()).filter(Boolean);
    const valid = list.filter((v) => ['consu', 'product'].includes(v));
    return valid.length > 0 ? valid : ['consu', 'product'];
}

const typeOptions = [
    { label: 'Consumable', value: 'consu' },
    { label: 'Storable Product', value: 'product' },
];

const activeCount = computed(() => query.filters.value.type.length);

function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
    query.setFilters({ type: ['consu', 'product'] });
}

const columns = [
    { key: 'default_code', label: 'Code', mono: true },
    { key: 'name', label: 'Name' },
    { key: 'akl_id', label: 'AKL' },
    { key: 'x_studio_valid_to_akl', label: 'AKL To', align: 'center' },
    { key: 'qty_available', label: 'On Hand', align: 'center' },
    { key: 'aksi', label: 'Aksi', align: 'center' },
];
const onhandColumns = [
    { key: 'product_id', label: 'Product' },
    { key: 'location_id', label: 'Location' },
    { key: 'lot_id', label: 'Lot' },
    { key: 'itds_expired', label: 'Ed', align: 'center' },
    { key: 'quantity', label: 'Qty', align: 'center' },
];
const moveColumns = [
    { key: 'date', label: 'Date', mono: true },
    { key: 'reference', label: 'Reference', mono: true },
    { key: 'lot', label: 'Lot/SN' },
    { key: 'source', label: 'Source' },
    { key: 'dest', label: 'Dest' },
    { key: 'qty', label: 'Qty', align: 'center' },
    { key: 'state', label: 'State', align: 'center' },
];

const perPageOptions = [10, 25, 50, 100, 500, 1000];

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

let stockSeq = 0;

async function openStock(row, mode) {
    const seq = ++stockSeq;
    stockMode.value = mode;
    stockLocationFilter.value = '';
    onhandLoading.value = true;
    try {
        const res = await api.get(`/product-odoo/${row.id}/${variantOf(row)}/${mode === 'move' ? 'move' : 'on-hand'}`, { silent: true, block: true });
        if (seq !== stockSeq) {
            return;
        }
        const body = res.data?.data ?? res.data;
        onhandRows.value = Array.isArray(body) ? body : (body.data ?? body.records ?? []);
        onhandTitle.value = `${mode === 'move' ? 'Move' : 'On Hand'}: ${row.name ?? ''}`;
        onhandOpen.value = true;
    } catch (e) {
        if (seq !== stockSeq) {
            return;
        }
        onhandRows.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat data stock.');
    } finally {
        if (seq === stockSeq) {
            onhandLoading.value = false;
        }
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari code / nama..." :active-count="activeCount" @search="query.setSearch" @reset="resetFilter">
            <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
            <template #filters>
                <FormField label="Type">
                    <MultiSelect :model-value="query.filters.value.type" :options="typeOptions" placeholder="Pilih type..." @update:model-value="query.setFilters({ type: $event })" />
                </FormField>
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
            empty-title="Produk tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
        >
            <template #cell-aksi="{ row }">
                <div class="flex justify-center gap-1">
                    <Button variant="outline" size="sm" title="On Hand" @click.stop="openStock(row, 'onhand')"><Eye /></Button>
                    <Button variant="outline" size="sm" title="Move" @click.stop="openStock(row, 'move')"><Move /></Button>
                    <Button variant="outline" size="sm" title="Salin kode + nama" @click.stop="copyText(`${row.default_code ?? ''}\t${row.name ?? ''}`)"><Copy /></Button>
                </div>
            </template>
            <template #cell-default_code="{ row }">{{ row.default_code ?? '-' }}</template>
            <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
            <template #cell-akl_id="{ row }">{{ odooName(row.akl_id) }}</template>
            <template #cell-x_studio_valid_to_akl="{ row }">{{ row.x_studio_valid_to_akl ?? '-' }}</template>
            <template #cell-qty_available="{ row }">{{ formatQtyUS(row.qty_available) }}</template>
        </DataTable>
        <AppModal v-model:open="onhandOpen" :title="onhandTitle" size="2xl">
            <template v-if="stockMode === 'move'">
                <DataTable :columns="moveColumns" :rows="onhandRows" :loading="onhandLoading" :total="onhandRows.length" :total-pages="1" empty-title="Riwayat move tidak ditemukan">
                    <template #cell-date="{ row }">{{ formatOdooDate(row.date) }}</template>
                    <template #cell-reference="{ row }">{{ row.reference ?? '-' }}</template>
                    <template #cell-lot="{ row }">{{ cleanLotName(odooName(row.lot_id) !== '-' ? odooName(row.lot_id) : (row.lot_name || '-')) }}</template>
                    <template #cell-source="{ row }">{{ odooName(row.location_id) }}</template>
                    <template #cell-dest="{ row }">{{ odooName(row.location_dest_id) }}</template>
                    <template #cell-qty="{ row }">{{ formatQtyUS(row.qty_done) }}</template>
                    <template #cell-state="{ row }">{{ row.state ?? '-' }}</template>
                </DataTable>
            </template>
            <template v-else>
                <div class="mb-2 grid max-w-xl gap-2 sm:grid-cols-2">
                    <SearchableSelect v-model="stockLocationFilter" :options="stockLocationOptions" placeholder="Filter Lokasi" />
                    <Button variant="outline" size="sm" @click="stockLocationFilter = ''">Reset</Button>
                </div>
                <DataTable :columns="onhandColumns" :rows="filteredStockRows" :loading="onhandLoading" :total="filteredStockRows.length" :total-pages="1" empty-title="Data tidak ditemukan">
                    <template #cell-product_id="{ row }">{{ odooName(row.product_id) }}</template>
                    <template #cell-location_id="{ row }">{{ odooName(row.location_id) }}</template>
                    <template #cell-lot_id="{ row }">{{ odooName(row.lot_id) }}</template>
                    <template #cell-itds_expired="{ row }">{{ formatExpDate(row.itds_expired) }}</template>
                    <template #cell-quantity="{ row }">{{ formatQtyUS(row.quantity) }}</template>
                </DataTable>
                <div class="mt-2 border-t pt-2 text-sm">
                    <b>Summary (klik untuk filter):</b>
                    <div v-if="onhandSummary.length > 0" class="mt-1 space-y-1">
                        <div v-for="[loc, qty] in onhandSummary" :key="loc">
                            → <Button variant="ghost" size="sm" class="h-auto px-1 py-0 font-bold" :class="stockLocationFilter === loc ? 'bg-accent' : ''" :title="`Filter lokasi ${loc}`" @click="stockLocationFilter = stockLocationFilter === loc ? '' : loc">{{ loc }}</Button>:
                            <StatusBadge status="secondary">{{ formatQtyUS(qty) }}</StatusBadge>
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground">-</p>
                </div>
            </template>
        </AppModal>
    </AppLayout>
</template>
