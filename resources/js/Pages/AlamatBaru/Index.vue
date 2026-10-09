<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Pencil, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'List Alamat Baru' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();
const query = useTableQuery((p) => api.get('/alamat-baru', { params: p }), {
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
const selected = ref([]);
const columns = [
    { key: 'do', label: 'No DO', mono: true },
    { key: 'tujuan', label: 'Tujuan' },
    { key: 'ekspedisi', label: 'Ekspedisi' },
    { key: 'koli', label: 'Koli', align: 'center' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const mainExportRows = computed(() =>
    query.rows.value.map((r) => ({ do: r.do ?? '', tujuan: r.tujuan ?? '', ekspedisi: r.ekspedisi ?? '', koli: r.total_koli ?? r.kolis_count ?? '' })),
);

function goCreate() {
    router.visit('/alamat-baru/create');
}
function goEdit(row) {
    router.visit(`/alamat-baru/${row.id}/edit`);
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih data yang akan dihapus.');
        return;
    }
    const ok = await confirm({ title: `Hapus ${selected.value.length} alamat?`, message: 'Data yang dihapus tidak bisa dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            await api.delete('/alamat-baru', { data: { ids: selected.value.map(Number) }, block: true });
            toast.success('Alamat dihapus.');
            selected.value = [];
            query.fetch();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghapus alamat.');
        }
    });
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Alamat pengiriman + koli">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(mainExportRows, ['do', 'tujuan', 'ekspedisi', 'koli'], 'alamat')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus ({{ selected.length }})</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah Data</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Alamat Baru" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari DO, tujuan, ekspedisi..." @input="onSearchInput" />
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
            selectable
            :selected="selected"
            clickable
            empty-title="Alamat tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @update:selected="selected = $event"
            @row-click="goEdit"
        >
            <template #cell-do="{ row }"><b>{{ row.do ?? '-' }}</b></template>
            <template #cell-tujuan="{ row }">{{ row.tujuan ?? '-' }}</template>
            <template #cell-ekspedisi="{ row }">{{ row.ekspedisi ?? '-' }}</template>
            <template #cell-koli="{ row }">{{ row.total_koli ?? '-' }} Koli</template>
            <template #actions="{ row }">
                <Button variant="outline" size="sm" title="Edit" @click="goEdit(row)"><Pencil /></Button>
            </template>
        </DataTable>
    </AppLayout>
</template>
