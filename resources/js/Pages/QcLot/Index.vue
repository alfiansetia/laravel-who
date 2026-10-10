<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Pencil, Plus, RefreshCw, Save, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'QC Lot' },
    filters: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery((p) => api.get('/qc-lots', { params: p }), {
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
    { key: 'product', label: 'Product' },
    { key: 'lot', label: 'Lot / ED', mono: true },
    { key: 'qc_date', label: 'Tanggal', mono: true },
    { key: 'qc_by', label: 'QC By' },
    { key: 'qc_note', label: 'QC Note', wrap: true, maxWidth: '260px' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const duplicateLots = computed(() => {
    const counts = {};
    query.rows.value.forEach((r) => {
        counts[r.lot_number] = (counts[r.lot_number] ?? 0) + 1;
    });
    return new Set(Object.keys(counts).filter((k) => counts[k] > 1));
});

const exportRows = computed(() =>
    query.rows.value.map((r) => ({
        product: r.product?.code ?? '',
        lot: r.lot_number ?? '',
        ed: r.lot_expiry ?? '',
        date: r.qc_date ?? '',
        qc_by: r.qc_by ?? '',
        note: r.qc_note ?? '',
    })),
);

const productOptions = computed(() =>
    props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })),
);

// Add / Edit modal
const modalOpen = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const form = ref({ product_id: '', lot_number: '', lot_expiry: '', qc_date: '', qc_by: '', qc_note: '' });
const errors = ref({});

function openAdd() {
    isEdit.value = false;
    editId.value = null;
    form.value = { product_id: '', lot_number: '', lot_expiry: '', qc_date: '', qc_by: '', qc_note: '' };
    errors.value = {};
    modalOpen.value = true;
}
function openEdit(row) {
    isEdit.value = true;
    editId.value = row.id;
    form.value = {
        product_id: row.product_id ?? row.product?.id ?? '',
        lot_number: row.lot_number ?? '',
        lot_expiry: row.lot_expiry ?? '',
        qc_date: row.qc_date ?? '',
        qc_by: row.qc_by ?? '',
        qc_note: row.qc_note ?? '',
    };
    errors.value = {};
    modalOpen.value = true;
}

async function save() {
    errors.value = {};
    try {
        if (isEdit.value) {
            await api.put(`/qc-lots/${editId.value}`, { ...form.value }, { block: true });
            toast.success('QC Lot diperbarui.');
        } else {
            await api.post('/qc-lots', { ...form.value }, { block: true });
            toast.success('QC Lot ditambah.');
        }
        modalOpen.value = false;
        query.fetch();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan QC Lot.');
    }
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk dihapus.');
        return;
    }
    const ok = await confirm({
        title: `Hapus ${selected.value.length} QC Lot?`,
        message: 'Data yang dihapus tidak dapat dikembalikan.',
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/qc-lots', { data: { ids: selected.value }, block: true });
        toast.success('QC Lot dihapus.');
        selected.value = [];
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus QC Lot.');
    }
}

function goImport() {
    router.visit('/qc-lots/import');
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['product', 'lot', 'ed', 'date', 'qc_by', 'note'], 'qc-lot')"><Copy /> Salin</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <Button variant="outline" size="sm" @click="goImport">Import</Button>
                <Button size="sm" @click="openAdd"><Plus /> Tambah</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <FilterPanel title="Filter QC Lot" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari product, lot, qc by..." @input="onSearchInput" />
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
                empty-title="QC Lot tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @update:selected="selected = $event"
                @row-click="openEdit"
            >
                <template #cell-product="{ row }"><b>{{ row.product?.code ?? '-' }}</b></template>
                <template #cell-lot="{ row }">
                    <span :class="duplicateLots.has(row.lot_number) && 'rounded bg-yellow-200 px-1'">{{ row.lot_number }}<span v-if="row.lot_expiry"> / {{ row.lot_expiry }}</span></span>
                </template>
                <template #cell-qc_date="{ row }">{{ row.qc_date ?? '-' }}</template>
                <template #cell-qc_by="{ row }">{{ row.qc_by ?? '-' }}</template>
                <template #cell-qc_note="{ row }">{{ row.qc_note ?? '-' }}</template>
                <template #actions="{ row }">
                    <Button variant="ghost" size="sm" @click="openEdit(row)"><Pencil /></Button>
                </template>
            </DataTable>
        </div>
        <AppModal v-model:open="modalOpen" :title="isEdit ? 'Edit QC Lot' : 'Tambah QC Lot'" size="md">
            <div class="grid gap-3 sm:grid-cols-2">
                <FormField label="Product" required :error="errors.product_id?.[0]" class="sm:col-span-2">
                    <SearchableSelect v-model="form.product_id" :options="productOptions" placeholder="Pilih product..." />
                </FormField>
                <FormField label="Lot Number" required :error="errors.lot_number?.[0]">
                    <Input v-model="form.lot_number" placeholder="Nomor lot..." />
                </FormField>
                <FormField label="Lot Expiry" :error="errors.lot_expiry?.[0]">
                    <Input v-model="form.lot_expiry" placeholder="ED (opsional)..." />
                </FormField>
                <FormField label="QC Date" required :error="errors.qc_date?.[0]">
                    <Input v-model="form.qc_date" type="date" />
                </FormField>
                <FormField label="QC By" required :error="errors.qc_by?.[0]">
                    <Input v-model="form.qc_by" placeholder="Nama pemeriksa..." />
                </FormField>
                <FormField label="QC Note" :error="errors.qc_note?.[0]" class="sm:col-span-2">
                    <Textarea v-model="form.qc_note" rows="2" maxlength="200" placeholder="Catatan (maks 200)..." />
                </FormField>
            </div>
            <template #footer>
                <Button variant="ghost" @click="modalOpen = false">Batal</Button>
                <Button @click="save"><Save /> Simpan</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
