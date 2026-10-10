<script setup>
import { computed, ref } from 'vue';
import { Copy, RefreshCw, Route } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows } from '@/lib/export';
import { formatQtyUS, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Data Lot' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/lot', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    filters: { product: props.filters.product ?? '' },
});
const productOptions = ref([]);
const searchingProduct = ref(false);
const traceOpen = ref(false);
const traceHtml = ref('');
const traceTable = useClientTable(null, {
    searchKeys: ['ref', 'product', 'date', 'lot', 'from', 'to', 'qty'],
});

const activeCount = computed(() => (query.search.value ? 1 : 0) + (query.filters.value.product ? 1 : 0));

const searchBox = ref(props.filters.search ?? '');

function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}

function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
    query.setFilters({ product: '' });
}

const columns = [
    { key: 'trace', label: '#' },
    { key: 'name', label: 'Lot' },
    { key: 'product_qty1', label: 'Qty', align: 'center' },
    { key: 'product', label: 'Product' },
];

const traceColumns = [
    { key: 'ref', label: 'Reference' },
    { key: 'product', label: 'Product' },
    { key: 'date', label: 'Date' },
    { key: 'lot', label: 'Lot/Serial' },
    { key: 'from', label: 'From' },
    { key: 'to', label: 'To' },
    { key: 'qty', label: 'Quantity', align: 'right' },
];

const traceSearchBox = ref('');

function onTraceSearchInput(e) {
    traceSearchBox.value = e.target.value;
    traceTable.setSearch(e.target.value);
}

function parseTraceHtml(html) {
    const rows = [];
    if (!html) {
        return rows;
    }
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const table = doc.querySelector('table');
    if (!table) {
        return rows;
    }
    const keys = ['ref', 'product', 'date', 'lot', 'from', 'to', 'qty'];
    table.querySelectorAll('tbody tr').forEach((tr) => {
        if (tr.closest('table') !== table) {
            return;
        }
        const cells = [...tr.children].filter((el) => el.tagName === 'TD');
        if (cells.length === 0) {
            return;
        }
        const obj = {};
        cells.forEach((td, i) => {
            obj[keys[i] ?? `c${i}`] = (td.textContent ?? '').replace(/\s+/g, ' ').trim();
        });
        obj._html = cells.map((td) => td.innerHTML);
        rows.push(obj);
    });
    return rows;
}

const perPageOptions = [10, 25, 50, 100, 500, 1000];

async function searchProduct(keyword) {
    const q = (keyword ?? '').trim();
    if (q.length < 1) {
        productOptions.value = [];
        return;
    }
    searchingProduct.value = true;
    try {
        const res = await api.get('/products/search', { params: { q }, silent: true });
        const body = res.data?.data ?? [];
        productOptions.value = body.map((p) => ({ label: `[${p.code}] ${p.name}`, value: p.code ?? p.id }));
    } catch (e) {
        productOptions.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal mencari product.');
    } finally {
        searchingProduct.value = false;
    }
}

async function openTrace(row) {
    traceOpen.value = true;
    traceHtml.value = '';
    traceSearchBox.value = '';
    traceTable.setSearch('', 0);
    traceTable.setRows([]);
    traceTable.loading.value = true;
    try {
        const res = await api.get(`/lot/${encodeURIComponent(row.id)}/trace`, { silent: true, block: true });
        const body = res.data?.data ?? res.data;
        traceHtml.value = body?.html ?? (typeof body === 'string' ? body : '');
        traceTable.setRows(parseTraceHtml(traceHtml.value));
        traceTable.sortKey.value = 'date';
        traceTable.sortDir.value = 'desc';
        if (traceTable.total.value === 0 && !traceHtml.value) {
            toast.info('Trace tidak tersedia untuk lot ini.');
        }
    } catch (e) {
        traceHtml.value = '';
        traceTable.setRows([]);
        const message = e.response?.data?.message ?? 'Gagal memuat trace.';
        traceTable.error.value = message;
        toast.error(message);
    } finally {
        traceTable.loading.value = false;
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(query.rows.value, ['name', 'product_qty1'])"><Copy /> Salin</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Lot" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari lot..." @input="onSearchInput" />
            </FormField>
            <FormField label="Produk">
                <SearchableSelect :model-value="query.filters.value.product" :options="productOptions" :loading="searchingProduct" placeholder="Semua Product" @update:model-value="query.setFilters({ product: $event })" @search="searchProduct" />
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
            empty-title="Lot tidak ditemukan"
            empty-message="Ubah kata kunci atau filter produk."
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
        >
            <template #cell-trace="{ row }">
                <div class="flex justify-center gap-1">
                    <Button variant="outline" size="sm" title="Trace" @click="openTrace(row)"><Route /></Button>
                </div>
            </template>
            <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
            <template #cell-product_qty1="{ row }">{{ formatQtyUS(row.product_qty1) }}</template>
            <template #cell-product="{ row }">{{ odooName(row.product_id) }}</template>
        </DataTable>
        <AppModal v-model:open="traceOpen" title="Lot Traceability" size="xl">
            <div v-if="traceTable.loading.value || traceTable.total.value > 0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="traceSearchBox" type="search" placeholder="Cari reference / produk / lot..." class="max-w-xs" @input="onTraceSearchInput" />
                </div>
                <DataTable
                    :columns="traceColumns"
                    :rows="traceTable.rows.value"
                    :loading="traceTable.loading.value"
                    :error="traceTable.error.value"
                    :page="traceTable.page.value"
                    :total-pages="traceTable.totalPages.value"
                    :total="traceTable.total.value"
                    :per-page="traceTable.perPage.value"
                    :per-page-options="perPageOptions"
                    sortable
                    empty-title="Trace tidak ditemukan"
                    empty-message="Ubah kata kunci pencarian."
                    @update:page="traceTable.setPage"
                    @update:per-page="traceTable.setPerPage"
                    @sort="traceTable.toggleSort"
                >
                    <template #cell-ref="{ row }"><span v-html="row._html?.[0] ?? row.ref" /></template>
                    <template #cell-product="{ row }"><span v-html="row._html?.[1] ?? row.product" /></template>
                    <template #cell-date="{ row }"><span v-html="row._html?.[2] ?? row.date" /></template>
                    <template #cell-lot="{ row }"><span v-html="row._html?.[3] ?? row.lot" /></template>
                    <template #cell-from="{ row }"><span v-html="row._html?.[4] ?? row.from" /></template>
                    <template #cell-to="{ row }"><span v-html="row._html?.[5] ?? row.to" /></template>
                    <template #cell-qty="{ row }"><span v-html="row._html?.[6] ?? row.qty" /></template>
                </DataTable>
            </div>
            <div v-else-if="traceHtml" class="max-h-[60vh] overflow-auto rounded border p-3 text-sm" v-html="traceHtml" />
            <p v-else class="text-sm text-muted-foreground">Tidak ada data trace.</p>
        </AppModal>
    </AppLayout>
</template>
