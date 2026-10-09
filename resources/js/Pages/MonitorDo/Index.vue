<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { formatOdooDate, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Monitor DO' },
});

const toast = useToast();
const table = useClientTable(null, {
    searchKeys: ['no_do', 'no_so', 'item', 'note'],
});
const doCount = ref(0);
const searchBox = ref('');

const columns = [
    { key: 'no', label: 'No', align: 'center' },
    { key: 'tgl', label: 'TGL', mono: true },
    { key: 'no_do', label: 'NO DO', mono: true },
    { key: 'no_so', label: 'NO SO', align: 'center' },
    { key: 'item', label: 'ITEM' },
    { key: 'qty', label: 'QTY', align: 'center' },
    { key: 'note', label: 'NOTE' },
];
const perPageOptions = [10, 50, 100, 500, 1000];

const exportRows = computed(() =>
    table.filtered.value.map((r) => ({ no: r.no, tgl: r.tgl, no_do: r.no_do, no_so: r.no_so, item: r.item, qty: r.qty, note: r.note })),
);

function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

function resetFilter() {
    searchBox.value = '';
    table.setSearch('', 0);
}

async function reload() {
    table.loading.value = true;
    try {
        const res = await api.get('/monitor-do', { silent: true });
        const list = res.data?.data ?? [];
        const rows = [];
        let i = 1;
        list.forEach((item) => {
            (item.move_ids_without_package_detail ?? []).forEach((detail) => {
                rows.push({
                    no: i++,
                    tgl: formatOdooDate(item.confirmation_date_so),
                    no_do: item.name ?? '-',
                    no_so: item.origin ?? '-',
                    item: odooName(detail.product_id),
                    qty: detail.product_uom_qty ?? 0,
                    note: item.note_to_wh ?? '',
                });
            });
        });
        doCount.value = Array.isArray(list) ? list.length : 0;
        table.setRows(rows);
    } catch (e) {
        table.setRows([]);
        table.error.value = e.response?.data?.message ?? 'Gagal memuat monitor DO.';
        toast.error(table.error.value);
    } finally {
        table.loading.value = false;
    }
}

reload();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" :description="`Total : ${doCount} DO`">
            <template #actions>
                <Button variant="outline" size="sm" @click="reload"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['no', 'tgl', 'no_do', 'no_so', 'item', 'qty', 'note'], 'monitor DO')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv('monitor-do', exportRows, ['no', 'tgl', 'no_do', 'no_so', 'item', 'qty', 'note'])"><Download /> CSV</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Monitor DO" :active-count="searchBox ? 1 : 0" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari NO DO, NO SO, item..." @input="onSearchInput" />
            </FormField>
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
            :per-page-options="perPageOptions"
            empty-title="DO tidak ditemukan"
            empty-message="Belum ada DO berjalan atau ubah kata kunci pencarian."
            @update:page="table.setPage"
            @update:per-page="table.setPerPage"
        >
            <template #cell-no="{ row }">{{ row.no }}</template>
            <template #cell-tgl="{ row }">{{ row.tgl }}</template>
            <template #cell-no_do="{ row }">{{ row.no_do }}</template>
            <template #cell-no_so="{ row }">{{ row.no_so }}</template>
            <template #cell-item="{ row }">{{ row.item }}</template>
            <template #cell-qty="{ row }">{{ row.qty }}</template>
            <template #cell-note="{ row }">{{ row.note }}</template>
        </DataTable>
    </AppLayout>
</template>
