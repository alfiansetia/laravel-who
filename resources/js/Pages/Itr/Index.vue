<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import CopyButton from '@/components/CopyButton.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import LabelPrint from '@/components/LabelPrint.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { getCode, getDesc, odooName, truncate } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'ITR' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/itr', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const detailOpen = ref(false);
const detail = ref(null);
const activeRow = ref(null);
const selectedDetails = ref([]);
const itemTable = useClientTable(null, {
    searchKeys: ['nama_produk', 'qty', 'satuan'],
});

const searchBox = ref(props.filters.search ?? '');

const columns = [
    { key: 'name', label: 'No ITR', mono: true },
    { key: 'tanggal', label: 'Tanggal' },
    { key: 'ref', label: 'Ref' },
    { key: 'state_picking', label: 'Picking', align: 'center' },
    { key: 'state_picking_terima', label: 'Picking Terima', align: 'center' },
    { key: 'warehouse', label: 'Warehouse' },
    { key: 'state', label: 'Status', align: 'center' },
    { key: 'user_id', label: 'User' },
];

const perPageOptions = [10, 25, 50, 100, 500, 1000];

const itemColumns = [
    { key: 'code', label: 'Code', mono: true },
    { key: 'desc', label: 'Desc' },
    { key: 'qty', label: 'Qty', align: 'center' },
    { key: 'satuan', label: 'Satuan', align: 'center' },
];

const itemSearchBox = ref('');

function onItemSearchInput(e) {
    itemSearchBox.value = e.target.value;
    itemTable.setSearch(e.target.value);
}

const itemExportRows = computed(() => itemTable.filtered.value.map((row) => ({
    code: getCode(odooName(row.nama_produk)),
    desc: getDesc(odooName(row.nama_produk)),
    qty: row.qty ?? 0,
    satuan: odooName(row.satuan),
})));

const labelItems = computed(() => {
    const picked = new Set((selectedDetails.value ?? []).map(String));
    return itemTable.filtered.value
        .filter((row) => picked.size === 0 || picked.has(String(row.id ?? '')))
        .map((row) => ({
            code: getCode(odooName(row.nama_produk)),
            name: getDesc(odooName(row.nama_produk)),
            qty: Number(row.qty ?? 0),
        }));
});

const labelTitle = computed(() => `Label ITR ${detail?.value?.name ?? activeRow?.value?.name ?? ''}`);

let detailSeq = 0;

async function openDetail(row) {
    const seq = ++detailSeq;
    activeRow.value = row;
    itemSearchBox.value = '';
    selectedDetails.value = [];
    itemTable.setSearch('', 0);
    itemTable.setRows([]);
    itemTable.error.value = '';
    try {
        const res = await api.get(`/itr/${encodeURIComponent(row.id)}`, { silent: true, block: true });
        if (seq !== detailSeq) {
            return;
        }
        const body = res.data?.data ?? res.data;
        detail.value = body;
        itemTable.setRows(body.detail_lines ?? []);
        detailOpen.value = true;
    } catch (e) {
        if (seq !== detailSeq) {
            return;
        }
        const message = e.response?.data?.message ?? 'Gagal memuat detail ITR.';
        itemTable.error.value = message;
        toast.error(message);
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari nomor ITR..." @search="query.setSearch">
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
            empty-title="ITR tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-name="{ row }"><b>{{ row.name ?? '-' }}</b></template>
            <template #cell-tanggal="{ row }">{{ row.tanggal ?? '-' }}</template>
            <template #cell-ref="{ row }">{{ truncate(row.ref, 40) }}</template>
            <template #cell-state_picking="{ row }">{{ row.state_picking ?? '-' }}</template>
            <template #cell-state_picking_terima="{ row }">{{ row.state_picking_terima ?? '-' }}</template>
            <template #cell-warehouse="{ row }">{{ odooName(row.warehouse) }}</template>
            <template #cell-state="{ row }">{{ row.state ?? '-' }}</template>
            <template #cell-user_id="{ row }">{{ odooName(row.user_id) }}</template>
        </DataTable>
        <AppModal v-model:open="detailOpen" size="xl">
            <template #title>
                {{ `List Item ITR No : ${detail?.name ?? activeRow?.name ?? ''}` }}
                <CopyButton :text="detail?.name ?? activeRow?.name ?? ''" label="No ITR" />
            </template>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <Input :model-value="itemSearchBox" type="search" placeholder="Cari code / desc..." class="max-w-xs" @input="onItemSearchInput" />
                <Button variant="outline" size="sm" @click="copyRows(itemExportRows, ['code', 'desc', 'qty', 'satuan'], 'item ITR')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv(`itr-${detail?.name ?? activeRow?.name ?? 'detail'}`, itemExportRows, ['code', 'desc', 'qty', 'satuan'])"><Download /> CSV</Button>
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
                :selected="selectedDetails"
                sortable
                selectable
                empty-title="Item tidak ditemukan"
                empty-message="Ubah kata kunci pencarian. Centang baris bila hanya sebagian item yang mau dicetak."
                @update:page="itemTable.setPage"
                @update:per-page="itemTable.setPerPage"
                @update:selected="selectedDetails = $event"
                @sort="itemTable.toggleSort"
            >
                <template #cell-code="{ row }">{{ getCode(odooName(row.nama_produk)) }}</template>
                <template #cell-desc="{ row }">{{ getDesc(odooName(row.nama_produk)) }}</template>
                <template #cell-qty="{ row }">{{ row.qty ?? 0 }}</template>
                <template #cell-satuan="{ row }">{{ odooName(row.satuan) }}</template>
            </DataTable>
            <div class="mt-2 space-y-1 border-t pt-2 text-sm">
                <p><b>Asal:</b> {{ odooName(detail?.warehouse_src) }} / {{ odooName(detail?.locs_src) }} → <b>Tujuan:</b> {{ odooName(detail?.warehouse) }} / {{ odooName(detail?.locs) }}</p>
                <p><b>Picking:</b> {{ odooName(detail?.picking) }} ({{ detail?.state_picking ?? '-' }}) → <b>Terima:</b> {{ odooName(detail?.picking_terima) }} ({{ detail?.state_picking_terima ?? '-' }})</p>
                <p><b>Tanggal:</b> {{ detail?.tanggal ?? '-' }} | <b>User:</b> {{ odooName(detail?.user_id) }} | <b>Status:</b> {{ detail?.state ?? '-' }}</p>
                <p class="whitespace-pre-wrap"><b>Keterangan:</b> {{ detail?.ket || detail?.ref || '-' }} <CopyButton :text="detail?.ket || detail?.ref || ''" label="Keterangan" /></p>
            </div>
        </AppModal>
    </AppLayout>
</template>
