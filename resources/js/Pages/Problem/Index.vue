<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, Clock, Copy, Pencil, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Data Problem' },
    filters: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    picOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    stockOptions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery(
    (p) => api.get('/problem', { params: { ...p, type: typeBox.value || undefined, year: yearBox.value || undefined, product_id: productBox.value || undefined, status: statusBox.value || undefined } }),
    { search: props.filters.search ?? '', page: Number(props.filters.page ?? 1) },
);

const searchBox = ref(props.filters.search ?? '');
const typeBox = ref('');
const yearBox = ref(String(new Date().getFullYear()));
const productBox = ref('');
const statusBox = ref('');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}
function onFilter() {
    query.fetch();
}
function resetFilter() {
    searchBox.value = '';
    typeBox.value = '';
    yearBox.value = String(new Date().getFullYear());
    productBox.value = '';
    statusBox.value = '';
    query.setSearch('', 0);
}
const activeCount = computed(
    () => (query.search.value ? 1 : 0) + (typeBox.value ? 1 : 0) + (productBox.value ? 1 : 0) + (statusBox.value ? 1 : 0),
);

const columns = [
    { key: 'number', label: 'Number', mono: true },
    { key: 'date', label: 'Date', mono: true },
    { key: 'type', label: 'Type', align: 'center' },
    { key: 'stock', label: 'Stock', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'pic', label: 'PIC' },
];
const perPageOptions = [10, 25, 50, 100];

const productOptions = computed(() => props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })));
const yearOptions = computed(() => {
    const now = new Date().getFullYear();
    const out = [];
    for (let y = 2020; y <= now; y++) {
        out.push({ value: String(y), label: String(y) });
    }
    return out.reverse();
});

const exportRows = computed(() =>
    query.rows.value.map((r) => ({ number: r.number ?? '', date: r.date ?? '', type: r.type ?? '', stock: r.stock ?? '', status: r.status ?? '', pic: r.pic ?? '' })),
);

function goCreate() {
    router.visit('/problems/create');
}
function goDetail(row) {
    router.visit(`/problems/${row.id}`);
}
function goEdit(row) {
    router.visit(`/problems/${row.id}/edit`);
}

async function setStatus(row, status) {
    try {
        await api.post(`/problem/${row.id}/status`, { status }, { block: true });
        toast.success(`Status diubah ke ${status}.`);
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal ubah status.');
    }
}
async function duplicateRow(row) {
    const ok = await confirm({ title: 'Duplikasi problem?', message: 'Buat salinan dengan nomor baru.', confirmText: 'Ya, duplikasi' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/problem/${row.id}/duplicate`, {}, { block: true });
        const id = res.data?.data?.id ?? res.data?.id;
        toast.success('Problem diduplikasi.');
        query.fetch();
        if (id) {
            window.open(`/problems/${id}/edit`, '_blank');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal duplikasi.');
    }
}
async function deleteRow(row) {
    const ok = await confirm({ title: 'Hapus problem?', message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/problem/${row.id}`, { block: true });
        toast.success('Problem dihapus.');
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus.');
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Manajemen masalah product + log aktivitas">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['number', 'date', 'type', 'stock', 'status', 'pic'], 'problem')"><Copy /> Salin</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah Problem</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <FilterPanel title="Filter Problem" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari nomor, PIC, RI/PO..." @input="onSearchInput" />
                </FormField>
                <FormField label="Tipe">
                    <SearchableSelect :model-value="typeBox" :options="typeOptions.map((t) => ({ value: t, label: t }))" placeholder="Semua tipe" @update:model-value="(v) => { typeBox = v; onFilter(); }" />
                </FormField>
                <FormField label="Tahun">
                    <SearchableSelect :model-value="yearBox" :options="yearOptions" placeholder="Semua tahun" @update:model-value="(v) => { yearBox = v; onFilter(); }" />
                </FormField>
                <FormField label="Product">
                    <SearchableSelect v-model="productBox" :options="productOptions" placeholder="Semua product" @update:model-value="onFilter" />
                </FormField>
                <FormField label="Status">
                    <SearchableSelect :model-value="statusBox" :options="statusOptions.map((s) => ({ value: s, label: s }))" placeholder="Semua status" @update:model-value="(v) => { statusBox = v; onFilter(); }" />
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
                empty-title="Problem tidak ditemukan"
                empty-message="Ubah filter atau kata kunci."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @row-click="goDetail"
            >
                <template #cell-number="{ row }"><b>{{ row.number }}</b></template>
                <template #cell-date="{ row }">{{ row.date ?? '-' }}</template>
                <template #cell-type="{ row }"><StatusBadge :status="row.type" /></template>
                <template #cell-stock="{ row }"><StatusBadge :status="row.stock" /></template>
                <template #cell-status="{ row }"><StatusBadge :status="row.status" /></template>
                <template #cell-pic="{ row }">{{ row.pic ?? '-' }}</template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1" @click.stop>
                        <Button v-if="row.status === 'pending'" variant="outline" size="sm" title="Tandai selesai" @click="setStatus(row, 'done')"><Check /></Button>
                        <Button v-else variant="outline" size="sm" title="Kembalikan pending" @click="setStatus(row, 'pending')"><Clock /></Button>
                        <Button variant="ghost" size="sm" title="Edit" @click="goEdit(row)"><Pencil /></Button>
                        <Button variant="ghost" size="sm" title="Duplikasi" @click="duplicateRow(row)"><Copy /></Button>
                        <Button variant="ghost" size="sm" title="Hapus" @click="deleteRow(row)"><Trash2 /></Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
