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
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'List BAST' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();
const query = useTableQuery((p) => api.get('/basts', { params: p }), {
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
    { key: 'name', label: 'Kepada' },
    { key: 'city', label: 'Kota' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const mainExportRows = computed(() =>
    query.rows.value.map((r) => ({ do: r.do ?? '', name: r.name ?? '', city: r.city ?? '' })),
);

function goCreate() {
    router.visit('/basts/create');
}
function goEdit(row) {
    router.visit(`/basts/${row.id}/edit`);
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih data yang akan dihapus.');
        return;
    }
    const ok = await confirm({
        title: `Hapus ${selected.value.length} BAST?`,
        message: 'Data yang dihapus tidak bisa dikembalikan.',
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            await api.delete('/basts', { data: { ids: selected.value.map(Number) }, block: true });
            toast.success('BAST dihapus.');
            selected.value = [];
            query.fetch();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghapus BAST.');
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
                <Button variant="outline" size="sm" @click="copyRows(mainExportRows, ['do', 'name', 'city'], 'bast')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus ({{ selected.length }})</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter BAST" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari DO, nama, kota..." @input="onSearchInput" />
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
            empty-title="BAST tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @update:selected="selected = $event"
            @row-click="goEdit"
        >
            <template #cell-do="{ row }"><b>{{ row.do ?? '-' }}</b></template>
            <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
            <template #cell-city="{ row }">{{ row.city ?? '-' }}</template>
        </DataTable>
    </AppLayout>
</template>
