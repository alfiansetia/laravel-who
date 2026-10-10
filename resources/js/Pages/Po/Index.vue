<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { getCode, getDesc, odooName, truncate } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'PO' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/po', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const detailOpen = ref(false);
const detail = ref(null);
const activeRow = ref(null);
const itemTable = useClientTable(null, {
    searchKeys: ['product_id', 'akl', 'product_qty', 'qty_received'],
});

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
    { key: 'name', label: 'No PO', mono: true },
    { key: 'partner_id', label: 'Vendor' },
    { key: 'user_id', label: 'User' },
    { key: 'notes', label: 'Notes' },
    { key: 'picking_count', label: 'RI', align: 'center' },
];
const itemColumns = [
    { key: 'code', label: 'Code', mono: true },
    { key: 'desc', label: 'Desc' },
    { key: 'origin', label: 'Origin' },
    { key: 'akl', label: 'AKL' },
    { key: 'qty', label: 'Qty', align: 'center' },
    { key: 'qty_received', label: 'Qty RI', align: 'center' },
    { key: 'qty_sisa', label: 'Qty Sisa', align: 'center' },
];

const itemSearchBox = ref('');

function onItemSearchInput(e) {
    itemSearchBox.value = e.target.value;
    itemTable.setSearch(e.target.value);
}

const itemExportRows = computed(() => itemTable.filtered.value.map((row) => ({
    code: getCode(odooName(row.product_id)),
    desc: getDesc(odooName(row.product_id)),
    origin: odooName(row.product_id),
    akl: odooName(row.akl),
    qty: row.product_qty ?? 0,
    qty_received: row.qty_received ?? 0,
    qty_sisa: Number(row.product_qty ?? 0) - Number(row.qty_received ?? 0),
})));

const perPageOptions = [10, 25, 50, 100, 500, 1000];

async function openDetail(row) {
    activeRow.value = row;
    detailOpen.value = true;
    itemSearchBox.value = '';
    itemTable.setSearch('', 0);
    itemTable.setRows([]);
    itemTable.loading.value = true;
    try {
        const res = await api.get(`/po/${encodeURIComponent(row.id)}`, { silent: true, block: true });
        const body = res.data?.data ?? res.data;
        detail.value = body;
        itemTable.setRows(body.order_line_detail ?? []);
    } catch (e) {
        detail.value = row;
        itemTable.setRows([]);
        const message = e.response?.data?.message ?? 'Gagal memuat detail PO.';
        itemTable.error.value = message;
        toast.error(message);
    } finally {
        itemTable.loading.value = false;
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
        <FilterPanel title="Filter PO" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari nomor PO..." @input="onSearchInput" />
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
            empty-title="PO tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-name="{ row }"><b>{{ row.name ?? '-' }}</b></template>
            <template #cell-partner_id="{ row }">{{ odooName(row.partner_id) }}</template>
            <template #cell-user_id="{ row }">{{ odooName(row.user_id) }}</template>
            <template #cell-notes="{ row }">{{ truncate(row.notes, 40) }}</template>
            <template #cell-picking_count="{ row }">{{ row.picking_count ?? 0 }}</template>
        </DataTable>
        <AppModal v-model:open="detailOpen" :title="`List Item PO No : ${detail?.name ?? activeRow?.name ?? ''}`" size="xl">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <Input :model-value="itemSearchBox" type="search" placeholder="Cari code / desc..." class="max-w-xs" @input="onItemSearchInput" />
                <Button variant="outline" size="sm" @click="copyRows(itemExportRows, ['code', 'desc', 'origin', 'akl', 'qty', 'qty_received', 'qty_sisa'], 'item PO')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv(`po-${detail?.name ?? activeRow?.name ?? 'detail'}`, itemExportRows, ['code', 'desc', 'origin', 'akl', 'qty', 'qty_received', 'qty_sisa'])"><Download /> CSV</Button>
            </div>
            <DataTable
                :columns="itemColumns"
                :rows="itemTable.rows.value"
                :loading="itemTable.loading.value"
                :error="itemTable.error.value"
                :page="itemTable.page.value"
                :total-pages="itemTable.totalPages.value"
                :total="itemTable.total.value"
                :per-page="itemTable.perPage.value"
                sortable
                empty-title="Item tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="itemTable.setPage"
                @update:per-page="itemTable.setPerPage"
                @sort="itemTable.toggleSort"
            >
                <template #cell-code="{ row }">{{ getCode(odooName(row.product_id)) }}</template>
                <template #cell-desc="{ row }">{{ getDesc(odooName(row.product_id)) }}</template>
                <template #cell-origin="{ row }">{{ odooName(row.product_id) }}</template>
                <template #cell-akl="{ row }">{{ odooName(row.akl) }}</template>
                <template #cell-qty="{ row }">{{ row.product_qty ?? 0 }}</template>
                <template #cell-qty_received="{ row }">{{ row.qty_received ?? 0 }}</template>
                <template #cell-qty_sisa="{ row }">{{ Number(row.product_qty ?? 0) - Number(row.qty_received ?? 0) }}</template>
            </DataTable>
            <p class="mt-2 border-t pt-2 text-sm"><b>Notes:</b> {{ detail?.notes ?? '-' }}</p>
        </AppModal>
    </AppLayout>
</template>
