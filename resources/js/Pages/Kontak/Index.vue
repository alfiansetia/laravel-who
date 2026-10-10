<script setup>
import { computed, ref } from 'vue';
import { ArrowDownUp, Copy, RefreshCw, Share } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Kontak' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery((p) => api.get('/kontaks', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});

const searchBox = ref(props.filters.search ?? '');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}
function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
}
const activeCount = computed(() => (query.search.value ? 1 : 0));

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'street', label: 'Alamat' },
    { key: 'phone', label: 'Telepon' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const exportRows = computed(() =>
    query.rows.value.map((r) => ({ name: r.name ?? '', street: r.street ?? '', phone: r.phone ?? '' })),
);

async function syncFromOdoo() {
    const ok = await confirm({
        title: 'Sinkron dari Odoo?',
        message: 'Tarik 10.000 partner Odoo dan simpan ke kontak lokal.',
        confirmText: 'Ya, sinkron',
    });
    if (!ok) {
        return;
    }
    try {
        await api.post('/kontaks', {}, { block: true });
        toast.success('Sinkron kontak selesai.');
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal sinkron kontak.');
    }
}

async function sendToVendor(row) {
    const ok = await confirm({
        title: `Tambah ${row.name} ke vendor?`,
        message: 'Kontak akan disalin sebagai vendor baru.',
        confirmText: 'Ya, tambah',
    });
    if (!ok) {
        return;
    }
    try {
        await api.post('/vendors', { name: row.name }, { block: true });
        toast.success('Kontak ditambah ke vendor.');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menambah vendor.');
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['name', 'street', 'phone'], 'kontak')"><Copy /> Salin</Button>
                <Button variant="destructive" size="sm" @click="syncFromOdoo"><ArrowDownUp /> Sync from Odoo</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <FilterPanel title="Filter Kontak" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari nama, alamat, phone..." @input="onSearchInput" />
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
                empty-title="Kontak tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
            >
                <template #cell-name="{ row }"><b>{{ row.name }}</b></template>
                <template #cell-street="{ row }">{{ row.street || '-' }}</template>
                <template #cell-phone="{ row }">{{ row.phone || '-' }}</template>
                <template #actions="{ row }">
                    <Button variant="outline" size="sm" title="Kirim ke vendor" @click="sendToVendor(row)"><Share /></Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
