<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';

const props = defineProps({
    title: { type: String, default: 'Shipping Estimate' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const table = useClientTable((params) => api.get('/shipping-estimate', { params }), {
    searchKeys: ['no_so', 'customer_name', 'shipping_address'],
});
const selected = ref([]);
const searchBox = ref('');

const columns = [
    { key: 'no_so', label: 'No SO', mono: true },
    { key: 'customer_name', label: 'Customer' },
    { key: 'total_items', label: 'Total Item', align: 'center' },
    { key: 'total_packages', label: 'Total Koli', align: 'center' },
    { key: 'total_invoice', label: 'Nilai Faktur', align: 'right' },
    { key: 'total_weight', label: 'Total Berat', align: 'right' },
    { key: 'created_at', label: 'Tanggal', mono: true },
];

function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

function resetFilter() {
    searchBox.value = '';
    table.setSearch('', 0);
}

function goCreate() {
    router.visit('/shipping-estimate/create');
}

function goShow(row) {
    router.visit(`/shipping-estimate/${row.id}`);
}

function goEdit(row) {
    router.visit(`/shipping-estimate/${row.id}/edit`);
}

async function deleteRow(row) {
    const ok = await confirm({ title: `Hapus ${row.no_so}?`, message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/shipping-estimate/${row.id}`, { block: true });
        toast.success('Data dihapus.');
        table.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus data.');
    }
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk dihapus.');
        return;
    }
    const ok = await confirm({ title: `Hapus ${selected.value.length} data?`, message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/shipping-estimate', { data: { ids: selected.value }, block: true });
        toast.success('Data dihapus.');
        selected.value = [];
        table.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus data.');
    }
}

table.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Estimasi ongkos kirim per SO">
            <template #actions>
                <Button variant="outline" size="sm" @click="table.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah Data</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Shipping Estimate" :active-count="searchBox ? 1 : 0" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari no SO, customer..." @input="onSearchInput" />
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
            :selected="selected"
            selectable
            clickable
            empty-title="Data tidak ditemukan"
            empty-message="Ubah kata kunci pencarian atau tambah data baru."
            @update:page="table.setPage"
            @update:per-page="table.setPerPage"
            @update:selected="selected = $event"
            @row-click="goShow"
        >
            <template #cell-no_so="{ row }"><b>{{ row.no_so }}</b></template>
            <template #cell-customer_name="{ row }">{{ row.customer_name }}</template>
            <template #cell-total_items="{ row }">{{ row.total_items }}</template>
            <template #cell-total_packages="{ row }">{{ row.total_packages }}</template>
            <template #cell-total_invoice="{ row }">Rp {{ row.total_invoice }}</template>
            <template #cell-total_weight="{ row }">{{ row.total_weight }} kg</template>
            <template #cell-created_at="{ row }">{{ row.created_at }}</template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1" @click.stop>
                    <Button variant="ghost" size="sm" title="Lihat" @click="goShow(row)"><Eye /></Button>
                    <Button variant="ghost" size="sm" title="Edit" @click="goEdit(row)"><Pencil /></Button>
                    <Button variant="ghost" size="sm" title="Hapus" @click="deleteRow(row)"><Trash2 /></Button>
                </div>
            </template>
        </DataTable>
    </AppLayout>
</template>
