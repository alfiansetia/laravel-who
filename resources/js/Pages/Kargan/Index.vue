<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Plus, RefreshCw, Trash2 } from '@lucide/vue';
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
    title: { type: String, default: 'List Kargan' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery((p) => api.get('/kargans', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const selected = ref([]);

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
    { key: 'number', label: 'No', mono: true },
    { key: 'date', label: 'Tgl', mono: true },
    { key: 'sn', label: 'SN', mono: true },
    { key: 'product', label: 'Product' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const exportRows = computed(() =>
    query.rows.value.map((r) => ({
        number: r.number ?? '',
        date: r.date ?? '',
        sn: r.sn ?? '',
        product: r.product ? `[${r.product.code}] ${r.product.name}` : (r.product_id ?? ''),
    })),
);

function goCreate() {
    router.visit('/kargans/create');
}
function goEdit(row) {
    router.visit(`/kargans/${row.id}/edit`);
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk dihapus.');
        return;
    }
    const ok = await confirm({
        title: `Hapus ${selected.value.length} kargan?`,
        message: 'Data yang dihapus tidak dapat dikembalikan.',
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/kargans', { data: { ids: selected.value }, block: true });
        toast.success('Data kargan dihapus.');
        selected.value = [];
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus kargan.');
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Daftar kartu garansi (kargan)">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['number', 'date', 'sn', 'product'], 'kargan')"><Copy /> Salin</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah Data</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <FilterPanel title="Filter Kargan" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari nomor, SN, product..." @input="onSearchInput" />
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
                :selected="selected"
                selectable
                clickable
                empty-title="Kargan tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @update:selected="selected = $event"
                @row-click="goEdit"
            >
                <template #cell-number="{ row }"><b>{{ row.number }}</b></template>
                <template #cell-date="{ row }">{{ row.date ?? '-' }}</template>
                <template #cell-sn="{ row }">{{ row.sn ?? '-' }}</template>
                <template #cell-product="{ row }">{{ row.product ? `[${row.product.code}] ${row.product.name}` : (row.product_id ?? '-') }}</template>
            </DataTable>
        </div>
    </AppLayout>
</template>
