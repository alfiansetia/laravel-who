<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Download, FileDown, Pencil, Plus, Printer, RefreshCw, Trash2 } from '@lucide/vue';
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
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Packing List' },
    vendors: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();
const query = useTableQuery((p) => api.get('/packs', { params: p }), {
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
    { key: 'product_code', label: 'Kode', mono: true },
    { key: 'product_name', label: 'Product' },
    { key: 'name', label: 'PL Name' },
    { key: 'desc', label: 'PL Desc' },
    { key: 'vendor', label: 'Vendor' },
    { key: 'vendor_desc', label: 'Vendor Desc' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const mainExportRows = computed(() =>
    query.rows.value.map((r) => ({
        code: r.product?.code ?? '',
        name: r.product?.name ?? '',
        pl: r.name ?? '',
        desc: r.desc ?? '',
        vendor: r.vendor?.name ?? '',
        vendor_desc: r.vendor_desc ?? '',
    })),
);

function goCreate() {
    router.visit('/packs/create');
}
function goEdit(row) {
    router.visit(`/packs/${row.id}/edit`);
}

async function deleteBatch(ids = null) {
    const list = ids ?? selected.value;
    if (list.length === 0) {
        toast.warning('Pilih data yang akan dihapus.');
        return;
    }
    const ok = await confirm({ title: `Hapus ${list.length} PL?`, message: 'Data yang dihapus tidak bisa dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            await api.delete('/packs', { data: { ids: list.map(Number) }, block: true });
            toast.success('Packing list dihapus.');
            selected.value = [];
            query.fetch();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghapus packing list.');
        }
    });
}

// Change vendor modal
const changeOpen = ref(false);
const changeVendor = ref('');
const vendorOptions = computed(() => props.vendors.map((v) => ({ label: v.name, value: String(v.id) })));
function openChange() {
    if (selected.value.length === 0) {
        toast.warning('Pilih data dulu.');
        return;
    }
    changeVendor.value = '';
    changeOpen.value = true;
}
async function saveChange() {
    if (!changeVendor.value) {
        toast.error('Pilih vendor.');
        return;
    }
    await withBlock(async () => {
        try {
            await api.post('/packs-change', { vendor_id: Number(changeVendor.value), ids: selected.value.map(Number) }, { block: true });
            toast.success('Vendor diganti.');
            changeOpen.value = false;
            selected.value = [];
            query.fetch();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal mengganti vendor.');
        }
    });
}

function exportAll() {
    const q = query.search.value ? `?search=${encodeURIComponent(query.search.value)}` : '';
    window.open(`/packs/export${q}`, '_blank');
}
function downloadRow(row) {
    window.open(`/packs/${row.id}/download`, '_blank');
}
function printRow(row) {
    window.open(`/packs/${row.id}/print`, '_blank');
}
function printCombined(row) {
    window.open(`/packs/${row.id}/print-combined`, '_blank');
}

// View/edit modal (flat simple edit)
const modalOpen = ref(false);
const detail = ref(null);
const detailLoading = ref(false);
const activeRow = ref(null);
const activeTab = ref('view');
const editForm = ref({ name: '', desc: '', vendor_desc: '', items: [] });

async function openDetail(row) {
    activeRow.value = row;
    modalOpen.value = true;
    activeTab.value = 'view';
    detailLoading.value = true;
    await withBlock(async () => {
        try {
            const res = await api.get(`/packs/${row.id}`, { block: true, silent: true });
            detail.value = res.data?.data ?? res.data;
            editForm.value = {
                name: detail.value?.name ?? '',
                desc: detail.value?.desc ?? '',
                vendor_desc: detail.value?.vendor_desc ?? '',
                items: flattenItems(detail.value?.items ?? []).map((i) => ({ item: i.item ?? '', qty: i.qty ?? '' })),
            };
        } catch (e) {
            detail.value = row;
            toast.error(e.response?.data?.message ?? 'Gagal memuat detail PL.');
        } finally {
            detailLoading.value = false;
        }
    });
}

function flattenItems(items) {
    const out = [];
    (items ?? []).forEach((t) => {
        out.push({ item: t.item, qty: t.qty });
        (t.children ?? []).forEach((c) => out.push({ item: `  ${c.item}`, qty: c.qty }));
    });
    return out;
}

function addFlatRow() {
    editForm.value.items.push({ item: '', qty: '' });
}
function removeFlatRow(i) {
    editForm.value.items.splice(i, 1);
}

async function saveFlat() {
    const items = editForm.value.items.map((r) => ({ item: r.item.trim(), qty: String(r.qty ?? '') })).filter((r) => r.item !== '');
    if (!editForm.value.name.trim() || items.length === 0) {
        toast.error('Nama PL dan minimal 1 item wajib diisi.');
        return;
    }
    await withBlock(async () => {
        try {
            await api.put(`/packs/${activeRow.value.id}`, {
                name: editForm.value.name,
                desc: editForm.value.desc,
                vendor_desc: editForm.value.vendor_desc,
                product_id: detail.value?.product_id ?? detail.value?.product?.id,
                vendor_id: detail.value?.vendor_id ?? detail.value?.vendor?.id,
                items,
            }, { block: true });
            toast.success('Packing list disimpan.');
            query.fetch();
            activeTab.value = 'view';
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menyimpan packing list.');
        }
    });
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Packing list per product + vendor">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(mainExportRows, ['code', 'name', 'pl', 'desc', 'vendor', 'vendor_desc'], 'packing list')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="exportAll"><FileDown /> Export</Button>
                <Button variant="outline" size="sm" :disabled="selected.length === 0" @click="openChange">Ganti Vendor ({{ selected.length }})</Button>
                <Button variant="outline" size="sm" :disabled="selected.length === 0" @click="deleteBatch()"><Trash2 /> Hapus ({{ selected.length }})</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah PL</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Packing List" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari PL, product, vendor..." @input="onSearchInput" />
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
            empty-title="Packing list tidak ditemukan"
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @update:selected="selected = $event"
            @row-click="openDetail"
        >
            <template #cell-product_code="{ row }"><b>{{ row.product?.code ?? '-' }}</b></template>
            <template #cell-product_name="{ row }">{{ row.product?.name ?? '-' }}</template>
            <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
            <template #cell-desc="{ row }">{{ row.desc ?? '-' }}</template>
            <template #cell-vendor="{ row }">{{ row.vendor?.name ?? '-' }}</template>
            <template #cell-vendor_desc="{ row }">{{ row.vendor_desc ?? '-' }}</template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Button variant="outline" size="sm" title="Edit halaman" @click="goEdit(row)"><Pencil /></Button>
                    <Button variant="outline" size="sm" title="Unduh Excel" @click="downloadRow(row)"><Download /></Button>
                    <Button variant="outline" size="sm" title="Print" @click="printRow(row)"><Printer /></Button>
                </div>
            </template>
        </DataTable>

        <AppModal v-model:open="modalOpen" :title="`PL: ${detail?.name ?? activeRow?.name ?? ''}`" size="lg">
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 5" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="space-y-3">
                <p class="text-sm"><b>Product:</b> [{{ detail?.product?.code ?? '-' }}] {{ detail?.product?.name ?? '-' }} — <b>Vendor:</b> {{ detail?.vendor?.name ?? '-' }}</p>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="downloadRow(activeRow)"><Download /> Excel</Button>
                    <Button variant="outline" size="sm" @click="printRow(activeRow)"><Printer /> Print</Button>
                    <Button variant="outline" size="sm" @click="printCombined(activeRow)"><Printer /> Combined</Button>
                </div>
                <div class="flex gap-2 border-b text-sm">
                    <button class="px-3 py-2" :class="activeTab === 'view' ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="activeTab = 'view'">Lihat</button>
                    <button class="px-3 py-2" :class="activeTab === 'edit' ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="activeTab = 'edit'">Edit Cepat</button>
                </div>
                <div v-if="activeTab === 'view'">
                    <ol class="list-decimal space-y-1 pl-5 text-sm">
                        <li v-for="(it, i) in flattenItems(detail?.items ?? [])" :key="i">{{ it.item }} — {{ it.qty }}</li>
                    </ol>
                    <p v-if="flattenItems(detail?.items ?? []).length === 0" class="text-sm text-slate-500">Tidak ada item.</p>
                </div>
                <div v-else class="space-y-3">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <FormField label="PL Name" required><Input v-model="editForm.name" /></FormField>
                        <FormField label="PL Desc"><Input v-model="editForm.desc" /></FormField>
                        <FormField label="Vendor Desc"><Input v-model="editForm.vendor_desc" /></FormField>
                    </div>
                    <div class="space-y-2">
                        <div v-for="(r, i) in editForm.items" :key="i" class="flex gap-2">
                            <Input v-model="r.item" class="flex-1" :placeholder="`Item ${i + 1}`" />
                            <Input v-model="r.qty" class="w-24" placeholder="Qty" />
                            <Button variant="outline" size="sm" @click="removeFlatRow(i)"><Trash2 /></Button>
                        </div>
                    </div>
                    <Button variant="outline" size="sm" @click="addFlatRow"><Plus /> Tambah Baris</Button>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="modalOpen = false">Tutup</Button>
                <Button v-if="activeTab === 'edit'" @click="saveFlat">Simpan</Button>
                <Button v-else @click="goEdit(activeRow)">Edit Lengkap</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="changeOpen" title="Ganti Vendor" size="md">
            <FormField label="Vendor" required>
                <SearchableSelect v-model="changeVendor" :options="vendorOptions" placeholder="Pilih vendor..." />
            </FormField>
            <template #footer>
                <Button variant="ghost" @click="changeOpen = false">Batal</Button>
                <Button @click="saveChange">Simpan</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
