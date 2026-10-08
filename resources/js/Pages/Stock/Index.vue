<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import BlockOverlay from '@/components/BlockOverlay.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, copyText, downloadCsv } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Data Stock' },
    filters: { type: Object, default: () => ({}) },
});

const { withBlock } = useBlock();
const toast = useToast();
const table = useClientTable((p) => api.get('/stock', { params: p }), { searchKeys: ['code', 'name', 'akl'] });

const locationOptions = [
    { label: 'CENTER', value: 'center' },
    { label: 'CIBUBUR', value: 'cbb' },
    { label: 'KARANTINA', value: 'krtn' },
    { label: 'BADSTOCK', value: 'badstock' },
    { label: 'DEMO', value: 'demo' },
];

function initialLocations() {
    const raw = props.filters.location;
    if (Array.isArray(raw) && raw.length > 0) {
        return raw.map(String);
    }
    if (typeof raw === 'string' && raw !== '') {
        return raw.split(',').map((s) => s.trim()).filter(Boolean);
    }
    return ['center'];
}

const locations = ref(initialLocations());
const lotOpen = ref(false);
const activeProduct = ref(null);
const lotTable = useClientTable(null, { searchKeys: ['location', 'lot', 'expired'] });

const activeCount = computed(() => locations.value.length + (table.search.value ? 1 : 0));

const searchBox = ref('');

function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

const lotSearchBox = ref('');

function onLotSearchInput(e) {
    lotSearchBox.value = e.target.value;
    lotTable.setSearch(e.target.value);
}

const lotSummary = computed(() => {
    const lotParts = [];
    const snParts = [];
    let total = 0;
    lotTable.filtered.value.forEach((item) => {
        const lotLabel = item.lot || 'Tanpa Lot/Sn';
        const expired = item.expired && item.expired !== 'False' ? `/${item.expired}` : '';
        const qty = Number(item.quantity ?? 0);
        lotParts.push(`${lotLabel}${expired} = ${qty} ea`);
        snParts.push(lotLabel);
        total += qty;
    });
    return {
        lot: lotParts.join(', '),
        sn: lotTable.filtered.value.length > 0 ? `${total} ea, SN : ${snParts.join(', ')}` : '',
    };
});

const columns = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama Produk' },
    { key: 'quantity', label: 'Qty', align: 'right' },
    { key: 'akl', label: 'AKL', mono: true },
];
const lotColumns = [
    { key: 'location', label: 'Lokasi' },
    { key: 'lot', label: 'Lot/SN/ED', align: 'center' },
    { key: 'quantity', label: 'Qty', align: 'right' },
];

function locationParams() {
    return locations.value.length > 0 ? { location: locations.value } : {};
}

async function load() {
    await withBlock(() => table.fetch(locationParams()));
}

function resetFilter() {
    locations.value = ['center'];
    searchBox.value = '';
    table.setSearch('', 0);
    table.setPage(1);
    load();
}

function copyRow(row) {
    copyText(`${row.code ?? ''}\t${row.name ?? ''}`);
}

async function openLot(row) {
    activeProduct.value = row;
    lotOpen.value = true;
    lotSearchBox.value = '';
    lotTable.setSearch('', 0);
    lotTable.setRows([]);
    lotTable.loading.value = true;
    try {
        const res = await api.get(`/stock/${row.id}`, { params: { ...locationParams(), limit: row.quantity ?? 10 }, silent: true });
        const body = res.data?.data ?? res.data;
        lotTable.setRows(Array.isArray(body) ? body : (body.data ?? []));
    } catch (e) {
        const message = e.response?.data?.message ?? 'Gagal memuat lot.';
        lotTable.setRows([]);
        lotTable.error.value = message;
        toast.error(message);
    } finally {
        lotTable.loading.value = false;
    }
}

function lotLabel(row) {
    const lot = row.lot || '-';
    if (row.expired && row.expired !== 'False') {
        return `${lot} / ${row.expired}`;
    }
    return lot;
}

function downloadOpname() {
    if (locations.value.length === 0) {
        window.open('/api/stock-opname', '_blank');
        return;
    }
    window.open(`/api/stock-opname?location=${encodeURIComponent(locations.value.join(','))}`, '_blank');
}

load();
</script>

<template>
    <AppLayout>
        <BlockOverlay>
            <PageHeader :title="title" description="Pantau stok barang (snapshot Odoo)">
                <template #actions>
                    <Button variant="outline" size="sm" @click="load"><RefreshCw /> Segarkan</Button>
                    <Button variant="outline" size="sm" @click="copyRows(table.filtered.value, ['code', 'name', 'quantity'])"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadCsv('stock', table.filtered.value, ['code', 'name', 'quantity', 'akl'])"><Download /> CSV</Button>
                    <Button size="sm" @click="downloadOpname"><Download /> Opname</Button>
                </template>
            </PageHeader>
            <FilterPanel title="Filter Stock" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Lokasi">
                    <MultiSelect v-model="locations" :options="locationOptions" placeholder="Pilih lokasi..." />
                </FormField>
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari kode / nama / AKL..." @input="onSearchInput" />
                </FormField>
                <template #actions>
                    <Button size="sm" @click="load">Terapkan</Button>
                </template>
            </FilterPanel>
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
                empty-message="Ubah filter lokasi atau kata kunci pencarian."
                @update:page="table.setPage"
                @update:per-page="table.setPerPage"
                @sort="table.toggleSort"
                @row-click="openLot"
            >
                <template #cell-code="{ row }"><b>{{ row.code ?? '-' }}</b></template>
                <template #cell-quantity="{ row }"><span class="inline-flex min-w-12 items-center justify-center rounded-md px-2 py-0.5 text-xs font-semibold" :class="Number(row.quantity ?? 0) === 0 ? 'bg-slate-100 text-slate-400' : 'bg-slate-900 text-white'">{{ Number(row.quantity ?? 0).toLocaleString('id-ID') }}</span></template>
                <template #cell-akl="{ row }"><span v-if="row.akl" :title="row.akl" class="inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-mono text-xs text-emerald-700">{{ row.akl }}</span><span v-else class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-400">-</span></template>
                <template #actions="{ row }">
                    <Button variant="outline" size="sm" title="Salin kode + nama" @click="copyRow(row)"><Copy /></Button>
                </template>
            </DataTable>
            <AppModal v-model:open="lotOpen" :title="`${activeProduct?.code ?? ''} — ${activeProduct?.name ?? 'Detail Lot/SN'}`" size="xl">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="lotSearchBox" type="search" placeholder="Cari lokasi / lot..." class="max-w-xs" @input="onLotSearchInput" />
                    <Button variant="outline" size="sm" @click="copyRows(lotTable.filtered.value, ['location', 'lot', 'expired', 'quantity'], 'lot')"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadCsv(`lot-${activeProduct?.code ?? 'stock'}`, lotTable.filtered.value, ['location', 'lot', 'expired', 'quantity'])"><Download /> CSV</Button>
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
                    <template #cell-lot="{ row }">{{ lotLabel(row) }}</template>
                    <template #cell-quantity="{ row }">{{ Number(row.quantity ?? 0).toLocaleString('id-ID') }}</template>
                </DataTable>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <FormField label="Lot/SN">
                        <div class="flex gap-1">
                            <Button variant="outline" size="sm" title="Salin Lot/SN" @click="copyText(lotSummary.lot)"><Copy /></Button>
                            <Textarea :model-value="lotSummary.lot" rows="3" readonly placeholder="Ringkasan lot per qty..." />
                        </div>
                    </FormField>
                    <FormField label="Serial Number">
                        <div class="flex gap-1">
                            <Button variant="outline" size="sm" title="Salin Serial Number" @click="copyText(lotSummary.sn)"><Copy /></Button>
                            <Textarea :model-value="lotSummary.sn" rows="3" readonly placeholder="Ringkasan SN..." />
                        </div>
                    </FormField>
                </div>
            </AppModal>
        </BlockOverlay>
    </AppLayout>
</template>
