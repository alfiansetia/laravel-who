<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import CopyButton from '@/components/CopyButton.vue';
import LabelPrint from '@/components/LabelPrint.vue';
import TableToolbar from '@/components/TableToolbar.vue';
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
const selectedItems = ref([]);
const itemTable = useClientTable(null, {
    searchKeys: ['product_id', 'akl', 'product_qty', 'qty_received'],
});

const searchBox = ref(props.filters.search ?? '');

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

const labelItems = computed(() => {
    const picked = new Set((selectedItems.value ?? []).map(String));
    return itemTable.filtered.value
        .filter((row) => picked.size === 0 || picked.has(String(row.id ?? '')))
        .map((row) => ({
            code: getCode(odooName(row.product_id)),
            name: getDesc(odooName(row.product_id)),
            qty: Number(row.product_qty ?? 0),
        }));
});

const labelTitle = computed(() => `Label PO ${detail?.value?.name ?? activeRow?.value?.name ?? ''}`);

const perPageOptions = [10, 25, 50, 100, 500, 1000];

let detailSeq = 0;

async function openDetail(row) {
    const seq = ++detailSeq;
    activeRow.value = row;
    itemSearchBox.value = '';
    selectedItems.value = [];
    itemTable.setSearch('', 0);
    itemTable.setRows([]);
    itemTable.error.value = '';
    try {
        const res = await api.get(`/po/${encodeURIComponent(row.id)}`, { silent: true, block: true });
        if (seq !== detailSeq) {
            return;
        }
        const body = res.data?.data ?? res.data;
        detail.value = body;
        itemTable.setRows(body.order_line_detail ?? []);
        detailOpen.value = true;
    } catch (e) {
        if (seq !== detailSeq) {
            return;
        }
        const message = e.response?.data?.message ?? 'Gagal memuat detail PO.';
        itemTable.error.value = message;
        toast.error(message);
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari nomor PO..." @search="query.setSearch">
            <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
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
        <AppModal v-model:open="detailOpen" size="xl">
            <template #title>
                {{ `List Item PO No : ${detail?.name ?? activeRow?.name ?? ''}` }}
                <CopyButton :text="detail?.name ?? activeRow?.name ?? ''" label="No PO" />
            </template>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <Input :model-value="itemSearchBox" type="search" placeholder="Cari code / desc..." class="max-w-xs" @input="onItemSearchInput" />
                <Button variant="outline" size="sm" @click="copyRows(itemExportRows, ['code', 'desc', 'origin', 'akl', 'qty', 'qty_received', 'qty_sisa'], 'item PO')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv(`po-${detail?.name ?? activeRow?.name ?? 'detail'}`, itemExportRows, ['code', 'desc', 'origin', 'akl', 'qty', 'qty_received', 'qty_sisa'])"><Download /> CSV</Button>
                <LabelPrint :items="labelItems" :title="labelTitle" />
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
                :selected="selectedItems"
                sortable
                selectable
                empty-title="Item tidak ditemukan"
                empty-message="Ubah kata kunci pencarian. Centang baris bila hanya sebagian item yang mau dicetak."
                @update:page="itemTable.setPage"
                @update:per-page="itemTable.setPerPage"
                @update:selected="selectedItems = $event"
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
            <p class="mt-2 border-t pt-2 text-sm"><b>Notes:</b> {{ detail?.notes ?? '-' }} <CopyButton :text="detail?.notes ?? ''" label="Notes" /></p>
        </AppModal>
    </AppLayout>
</template>
