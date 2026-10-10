<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Eye, Pencil, Printer, Plus, RefreshCw, Trash2 } from '@lucide/vue';
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
import web from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'List Alamat' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery(
    (p) => web.get('/alamats', { params: p }),
    { search: props.filters.search ?? '', page: Number(props.filters.page ?? 1) },
);
const selected = ref([]);
const searchBox = ref(props.filters.search ?? '');

const columns = [
    { key: 'do', label: 'No DO', mono: true },
    { key: 'tujuan', label: 'Tujuan' },
    { key: 'ekspedisi', label: 'Ekspedisi' },
    { key: 'koli', label: 'Koli', align: 'center' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const rows = computed(() =>
    query.rows.value.map((r, i) => ({ ...r, no: (query.page.value - 1) * query.perPage.value + i + 1 })),
);

function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}

function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
}

function goCreate() {
    router.visit('/alamats/create');
}

function goEdit(row) {
    router.visit(`/alamats/${row.id}/edit`);
}

function printRow(row) {
    window.open(`/alamats/${row.id}`, '_blank');
}

async function deleteRow(row) {
    const ok = await confirm({ title: `Hapus ${row.do}?`, message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await web.delete(`/alamats/${row.id}`, { block: true });
        toast.success('Alamat dihapus.');
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus alamat.');
    }
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk dihapus.');
        return;
    }
    const ok = await confirm({ title: `Hapus ${selected.value.length} alamat?`, message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await web.delete('/alamats', { data: { ids: selected.value }, block: true });
        toast.success('Alamat dihapus.');
        selected.value = [];
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus alamat.');
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Alamat" :active-count="searchBox ? 1 : 0" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari DO, tujuan, ekspedisi..." @input="onSearchInput" />
            </FormField>
        </FilterPanel>
        <DataTable
            :columns="columns"
            :rows="rows"
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
            empty-title="Alamat tidak ditemukan"
            empty-message="Ubah kata kunci pencarian atau tambah data baru."
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @update:selected="selected = $event"
            @row-click="goEdit"
        >
            <template #cell-do="{ row }"><b>{{ row.do }}</b></template>
            <template #cell-tujuan="{ row }">{{ row.tujuan }}</template>
            <template #cell-ekspedisi="{ row }">{{ row.ekspedisi ?? '-' }}</template>
            <template #cell-koli="{ row }">{{ row.koli }} Koli</template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1" @click.stop>
                    <Button variant="ghost" size="sm" title="Detail" @click="goEdit(row)"><Eye /></Button>
                    <Button variant="ghost" size="sm" title="Edit" @click="goEdit(row)"><Pencil /></Button>
                    <Button variant="ghost" size="sm" title="Print" @click="printRow(row)"><Printer /></Button>
                    <Button variant="ghost" size="sm" title="Hapus" @click="deleteRow(row)"><Trash2 /></Button>
                </div>
            </template>
        </DataTable>
    </AppLayout>
</template>
