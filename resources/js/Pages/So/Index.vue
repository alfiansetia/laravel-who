<script setup>
import { computed, ref } from 'vue';
import { Check, Copy, Download, Printer, RefreshCw, X } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import CopyButton from '@/components/CopyButton.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useTableQuery } from '@/composables/useTableQuery';
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { formatOdooDate, isPrinted, odooName, truncate } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'SO' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();
const query = useTableQuery((p) => api.get('/so', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    filters: { note_search: props.filters.note_search ?? '', filter: props.filters.filter ?? '' },
});
const detailOpen = ref(false);
const detail = ref(null);
const activeRow = ref(null);
const itemTable = useClientTable(null, {
    searchKeys: ['default_code', 'name', 'product_id', 'unit_price1', 'product_uom_qty', 'qty_delivered'],
});

const printOptions = [
    { label: 'Semua', value: '' },
    { label: 'Blm Print OK', value: 'print_ok' },
];

const activeCount = computed(() => (query.search.value ? 1 : 0) + (query.filters.value.note_search ? 1 : 0) + (query.filters.value.filter ? 1 : 0));

const searchBox = ref(props.filters.search ?? '');
const noteBox = ref(props.filters.note_search ?? '');

function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}

function onNoteInput(e) {
    noteBox.value = e.target.value;
    query.setFilters({ note_search: e.target.value }, 1000);
}

function resetFilter() {
    searchBox.value = '';
    noteBox.value = '';
    query.setSearch('', 0);
    query.setFilters({ note_search: '', filter: '' });
}

const columns = [
    { key: 'name', label: 'No SO', mono: true },
    { key: 'date_order', label: 'Date' },
    { key: 'partner_id', label: 'Customer' },
    { key: 'note_to_wh', label: 'Notes' },
    { key: 'delivery_count', label: 'DO', align: 'center' },
    { key: 'aksi', label: 'Action', align: 'center' },
];
const itemColumns = [
    { key: 'default_code', label: 'Code', mono: true },
    { key: 'name', label: 'Desc' },
    { key: 'product_id', label: 'Origin' },
    { key: 'unit_price1', label: 'Price', align: 'right' },
    { key: 'product_uom_qty', label: 'Qty Order', align: 'center' },
    { key: 'qty_delivered', label: 'Qty Delivered', align: 'center' },
];

const perPageOptions = [10, 25, 50, 100, 500, 1000];

const itemSearchBox = ref('');

function onItemSearchInput(e) {
    itemSearchBox.value = e.target.value;
    itemTable.setSearch(e.target.value);
}

const itemExportRows = computed(() => itemTable.filtered.value.map((row) => ({
    code: row.default_code ?? '',
    desc: row.name ?? '',
    origin: odooName(row.product_id),
    price: row.unit_price1 ?? '',
    qty_order: row.product_uom_qty ?? 0,
    qty_delivered: row.qty_delivered ?? 0,
})));

function sistemOf(row) {
    const s = String(row.sistem ?? '').trim().toUpperCase();
    return s && s !== '-' ? s : '';
}

function customerOf(row) {
    return String(odooName(row.partner_id)).substring(0, 30);
}

async function openDetail(row) {
    activeRow.value = row;
    detailOpen.value = true;
    itemSearchBox.value = '';
    itemTable.setSearch('', 0);
    itemTable.setRows([]);
    itemTable.loading.value = true;
    try {
        const res = await api.get(`/so/${row.id}`, { silent: true, block: true });
        const body = res.data?.data ?? res.data;
        detail.value = body;
        itemTable.setRows(body.order_line_detail ?? body.order_line ?? []);
        if (itemTable.total.value === 0) {
            toast.info('SO tidak memiliki item order.');
        }
    } catch (e) {
        detail.value = row;
        itemTable.setRows([]);
        const message = e.response?.data?.message ?? 'Gagal memuat detail SO.';
        itemTable.error.value = message;
        toast.error(message);
    } finally {
        itemTable.loading.value = false;
    }
}

function printSo(row) {
    window.open(`/so/${row.id}/print`, '_blank');
}

async function markPrint(row, unprint = false) {
    const ok = await confirm({ title: unprint ? 'Batalkan tanda print?' : 'Mark as print?', message: `SO ${row.name ?? ''}`, confirmText: 'Ya, lanjutkan' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            await api.post(`/so/${row.id}/mark-as-${unprint ? 'unprint' : 'print'}`, { note: row.note_to_wh ?? '' });
            toast.success(unprint ? 'Tanda print dihapus.' : 'SO ditandai sudah print.');
            query.fetch();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menandai print.');
        }
    });
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
        <FilterPanel title="Filter SO" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Nomor SO">
                <Input :model-value="searchBox" type="search" placeholder="Cari nomor SO..." @input="onSearchInput" />
            </FormField>
            <FormField label="Notes">
                <Input :model-value="noteBox" placeholder="Cari Notes..." @input="onNoteInput" />
            </FormField>
            <FormField label="Status Print">
                <SearchableSelect :model-value="query.filters.value.filter" :options="printOptions" placeholder="Semua" @update:model-value="query.setFilters({ filter: $event })" />
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
            empty-title="SO tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-name="{ row }"><b>{{ row.name ?? '-' }}</b> <span v-if="sistemOf(row)" class="ml-1 rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700">{{ sistemOf(row) }}</span></template>
            <template #cell-date_order="{ row }">{{ formatOdooDate(row.date_order) }}</template>
            <template #cell-partner_id="{ row }">{{ customerOf(row) }}</template>
            <template #cell-note_to_wh="{ row }">{{ truncate(row.note_to_wh, 40) }}</template>
            <template #cell-delivery_count="{ row }">{{ row.delivery_count ?? 0 }}</template>
            <template #cell-aksi="{ row }">
                <div class="flex justify-center gap-1">
                    <Button variant="outline" size="sm" title="Print SO" @click="printSo(row)"><Printer /></Button>
                    <Button variant="outline" size="sm" title="Mark As Print" :disabled="isPrinted(row.note_to_wh)" @click="markPrint(row, false)"><Check /></Button>
                    <Button variant="outline" size="sm" title="Mark As Unprint" :disabled="!isPrinted(row.note_to_wh)" @click="markPrint(row, true)"><X /></Button>
                </div>
            </template>
        </DataTable>
        <AppModal v-model:open="detailOpen" size="xl">
            <template #title>
                {{ `Item SO: ${detail?.name ?? activeRow?.name ?? ''}` }}
                <CopyButton :text="detail?.name ?? activeRow?.name ?? ''" label="No SO" />
            </template>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <Button size="sm" @click="printSo(activeRow)"><Printer /> Print</Button>
                <Input :model-value="itemSearchBox" type="search" placeholder="Cari code / desc..." class="max-w-xs" @input="onItemSearchInput" />
                <Button variant="outline" size="sm" @click="copyRows(itemExportRows, ['code', 'desc', 'origin', 'price', 'qty_order', 'qty_delivered'], 'item SO')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv(`so-${detail?.name ?? activeRow?.name ?? 'detail'}`, itemExportRows, ['code', 'desc', 'origin', 'price', 'qty_order', 'qty_delivered'])"><Download /> CSV</Button>
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
                <template #cell-default_code="{ row }">{{ row.default_code ?? '-' }}</template>
                <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
                <template #cell-product_id="{ row }">{{ odooName(row.product_id) }}</template>
                <template #cell-unit_price1="{ row }">{{ row.unit_price1 ?? '-' }}</template>
            </DataTable>
            <p class="mt-2 border-t pt-2 text-sm"><b>Notes:</b> {{ detail?.note_to_wh ?? detail?.note ?? '-' }} <CopyButton :text="detail?.note_to_wh ?? detail?.note ?? ''" label="Notes" /></p>
        </AppModal>
    </AppLayout>
</template>
