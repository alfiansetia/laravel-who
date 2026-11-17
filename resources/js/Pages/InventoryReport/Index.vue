<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import CopyButton from '@/components/CopyButton.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, copyText, downloadCsv } from '@/lib/export';
import { formatQtyID } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Inventory Report' },
    filters: { type: Object, default: () => ({}) },
});

const { withBlock } = useBlock();
const toast = useToast();
const table = useClientTable((p) => api.get('/inventory-report', { params: p }), {
    searchKeys: ['code', 'name'],
});

const excludeGaStock = ref(props.filters.exclude_ga_stock ?? true);
const activeProduct = ref(null);
const locOpen = ref(false);
const locTable = useClientTable(null, { searchKeys: ['location'] });

const searchBox = ref(props.filters.search ?? '');
const locSearchBox = ref('');
const locSelect = ref('');

const locOptions = computed(() => {
    const locs = [...new Set(locTable.allRows.value.map((r) => r.location).filter(Boolean))].sort();
    return [{ label: 'Semua Lokasi', value: '' }, ...locs.map((l) => ({ label: l, value: l }))];
});

const activeCount = computed(() => (excludeGaStock.value ? 1 : 0));

function locationParams() {
    return { exclude_ga_stock: excludeGaStock.value ? 1 : 0 };
}

async function load() {
    await withBlock(() => table.fetch(locationParams()));
}

function resetFilter() {
    excludeGaStock.value = true;
    searchBox.value = '';
    table.setSearch('', 0);
    table.setPage(1);
    load();
}

function toggleExclude() {
    excludeGaStock.value = !excludeGaStock.value;
    table.setPage(1);
    load();
}

function copyRow(row) {
    copyText(`${row.code ?? ''}\t${row.name ?? ''}`);
}

function downloadExport() {
    const exclude = excludeGaStock.value ? 1 : 0;
    window.open(`/api/inventory-report/export?exclude_ga_stock=${exclude}`, '_blank');
}

async function openLocations(row) {
    activeProduct.value = row;
    locOpen.value = false;
    locSearchBox.value = '';
    locSelect.value = '';
    locTable.setSearch('', 0);
    locTable.setRows([]);
    try {
        const res = await api.get(`/inventory-report/${row.id}/locations`, {
            params: locationParams(),
            silent: true,
            block: true,
        });
        const body = res.data?.data ?? res.data;
        locTable.setRows(Array.isArray(body) ? body : (body.data ?? []));
        locOpen.value = true;
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat rincian lokasi.');
    }
}

function onLocSearchInput(e) {
    locSearchBox.value = e.target.value;
    locSelect.value = '';
    locTable.setSearch(e.target.value);
}

function onLocSelect(v) {
    locSelect.value = v ?? '';
    locSearchBox.value = locSelect.value;
    locTable.setSearch(locSelect.value, 0);
}

function resetLocFilter() {
    locSelect.value = '';
    locSearchBox.value = '';
    locTable.setSearch('', 0);
    locTable.setPage(1);
}

const locSummary = computed(() => {
    let total = 0;
    let reserved = 0;
    locTable.filtered.value.forEach((item) => {
        total += Number(item.quantity ?? 0);
        reserved += Number(item.reserved ?? 0);
    });
    return { total, reserved, available: total - reserved };
});

const tableSummary = computed(() => {
    let total = 0;
    let reserved = 0;
    table.filtered.value.forEach((item) => {
        total += Number(item.quantity ?? 0);
        reserved += Number(item.reserved ?? 0);
    });
    return { count: table.filtered.value.length, total, reserved, available: total - reserved };
});

const columns = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama Produk' },
    { key: 'quantity', label: 'Qty', align: 'right' },
    { key: 'reserved', label: 'Reserved', align: 'right' },
    { key: 'available', label: 'Available', align: 'right' },
];
const locColumns = [
    { key: 'location', label: 'Lokasi' },
    { key: 'quantity', label: 'Qty', align: 'right' },
    { key: 'reserved', label: 'Reserved', align: 'right' },
    { key: 'available', label: 'Available', align: 'right' },
];

load();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="copyRows(table.filtered.value, ['code', 'name', 'quantity', 'reserved', 'available'])"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv('inventory_report', table.filtered.value, ['code', 'name', 'quantity', 'reserved', 'available'])"><Download /> CSV</Button>
                <Button size="sm" @click="downloadExport"><Download /> Export</Button>
            </template>
        </PageHeader>
        <TableToolbar v-model="searchBox" placeholder="Cari kode / nama..." :active-count="activeCount" @search="table.setSearch" @reset="resetFilter">
            <Button variant="outline" size="sm" @click="load"><RefreshCw /> Segarkan</Button>
            <template #filters>
                <FormField label="Filter lokasi">
                    <Button :variant="excludeGaStock ? 'secondary' : 'outline'" size="sm" @click="toggleExclude">
                        {{ excludeGaStock ? 'Exclude GA/Stock: ON' : 'Exclude GA/Stock: OFF' }}
                    </Button>
                </FormField>
            </template>
            <template #filterActions>
                <Button size="sm" @click="load">Terapkan</Button>
            </template>
        </TableToolbar>
        <DataTable
            :columns="columns"
            :rows="table.rows.value"
            :loading="table.loading.value"
            :error="table.error.value"
            :page="table.page.value"
            :total-pages="table.totalPages.value"
            :total="table.total.value"
            :per-page="table.perPage.value"
            sortable
            clickable
            empty-title="Stok tidak ditemukan"
            empty-message="Ubah kata kunci pencarian atau matikan filter Exclude GA/Stock."
            @update:page="table.setPage"
            @update:per-page="table.setPerPage"
            @sort="table.toggleSort"
            @row-click="openLocations"
        >
            <template #cell-code="{ row }"><b>{{ row.code ?? '-' }}</b></template>
            <template #cell-quantity="{ row }"><span class="inline-flex min-w-12 items-center justify-center rounded-md px-2 py-0.5 text-xs font-semibold" :class="Number(row.quantity ?? 0) === 0 ? 'bg-slate-100 text-slate-400' : 'bg-slate-900 text-white'">{{ formatQtyID(row.quantity) }}</span></template>
            <template #cell-reserved="{ row }">{{ formatQtyID(row.reserved) }}</template>
            <template #cell-available="{ row }">{{ formatQtyID(row.available) }}</template>
            <template #actions="{ row }">
                <Button variant="outline" size="sm" title="Salin kode + nama" @click="copyRow(row)"><Copy /></Button>
            </template>
        </DataTable>
        <p class="mt-2 text-xs text-muted-foreground">
            {{ tableSummary.count }} produk · Qty {{ formatQtyID(tableSummary.total) }} · Reserved {{ formatQtyID(tableSummary.reserved) }} · Available {{ formatQtyID(tableSummary.available) }}
        </p>
        <AppModal v-model:open="locOpen" size="2xl">
            <template #title>
                {{ activeProduct?.code ?? '' }} — {{ activeProduct?.name ?? 'Rincian Lokasi' }}
                <CopyButton :text="activeProduct?.code ?? ''" label="Kode Barang" />
                <CopyButton :text="activeProduct?.name ?? ''" label="Deskripsi" />
            </template>
            <p class="mb-2 text-sm text-muted-foreground">
                Total: <b>{{ formatQtyID(locSummary.total) }}</b>
                &nbsp;· Reserved: <b>{{ formatQtyID(locSummary.reserved) }}</b>
                &nbsp;· Available: <b>{{ formatQtyID(locSummary.available) }}</b>
            </p>
            <div class="mb-2 flex max-w-xl flex-wrap items-center gap-2">
                <Input :model-value="locSearchBox" type="search" placeholder="Cari lokasi..." class="max-w-xs flex-1" @input="onLocSearchInput" />
                <SearchableSelect :model-value="locSelect" :options="locOptions" placeholder="Pilih lokasi" @update:model-value="onLocSelect" />
                <Button variant="outline" size="sm" @click="resetLocFilter">Reset</Button>
            </div>
            <DataTable
                :columns="locColumns"
                :rows="locTable.rows.value"
                :loading="locTable.loading.value"
                :error="locTable.error.value"
                :page="locTable.page.value"
                :total-pages="locTable.totalPages.value"
                :total="locTable.total.value"
                :per-page="locTable.perPage.value"
                sortable
                empty-title="Lokasi tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="locTable.setPage"
                @update:per-page="locTable.setPerPage"
                @sort="locTable.toggleSort"
            >
                <template #cell-quantity="{ row }">{{ formatQtyID(row.quantity) }}</template>
                <template #cell-reserved="{ row }">{{ formatQtyID(row.reserved) }}</template>
                <template #cell-available="{ row }">{{ formatQtyID(row.available) }}</template>
            </DataTable>
        </AppModal>
    </AppLayout>
</template>
