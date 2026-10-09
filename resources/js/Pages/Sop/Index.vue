<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Download, FileDown, Pencil, Printer, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'SOP QC' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { withBlock } = useBlock();
const query = useTableQuery((p) => api.get('/sops', { params: p }), {
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
    { key: 'product_code', label: 'Kode Product', mono: true },
    { key: 'product_name', label: 'Nama Product' },
    { key: 'target', label: 'Target' },
];
const perPageOptions = [10, 25, 50, 100, 200];

const mainExportRows = computed(() =>
    query.rows.value.map((r) => ({
        code: r.product?.code ?? r.product_code ?? '',
        name: r.product?.name ?? r.product_name ?? '',
        target: r.target ?? '',
    })),
);

// Detail + edit modal
const modalOpen = ref(false);
const detail = ref(null);
const detailLoading = ref(false);
const activeRow = ref(null);
const activeTab = ref('view');
const editTarget = ref('');
const editItems = ref([]);
const errors = ref({});

async function openDetail(row) {
    activeRow.value = row;
    modalOpen.value = true;
    activeTab.value = 'view';
    detailLoading.value = true;
    errors.value = {};
    try {
        const res = await api.get(`/sops/${row.id}`, { silent: true });
        detail.value = res.data?.data ?? res.data;
        editTarget.value = detail.value?.target ?? '';
        editItems.value = (detail.value?.items ?? []).map((i) => ({ item: i.item ?? '' }));
    } catch (e) {
        detail.value = row;
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail SOP.');
    } finally {
        detailLoading.value = false;
    }
}

function addEditRow(content = '') {
    editItems.value.push({ item: content });
}
function removeEditRow(i) {
    editItems.value.splice(i, 1);
}

async function saveSop() {
    errors.value = {};
    const productId = detail.value?.product_id ?? detail.value?.product?.id ?? activeRow.value?.product_id;
    if (!productId) {
        toast.error('Product tidak ditemukan.');
        return;
    }
    const items = editItems.value.map((r) => ({ item: r.item ?? '' })).filter((r) => String(r.item).trim() !== '');
    if (!editTarget.value.trim() || items.length === 0) {
        toast.error('Target dan minimal 1 item wajib diisi.');
        return;
    }
    await withBlock(async () => {
        try {
            await api.post('/sops', { product_id: productId, target: editTarget.value, items }, { block: true });
            toast.success('SOP disimpan.');
            query.fetch();
            activeTab.value = 'view';
            const res = await api.get(`/sops/${activeRow.value.id}`, { silent: true });
            detail.value = res.data?.data ?? res.data;
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data?.errors ?? {};
            }
        }
    });
}

function downloadSop(row) {
    const id = row?.id ?? detail.value?.id;
    if (id) {
        window.open(`/api/sops/${id}/download`, '_blank');
    }
}
function printSop(row) {
    const id = row?.id ?? detail.value?.id;
    if (id) {
        window.open(`/sops/${id}/print`, '_blank');
    }
}
function exportAll() {
    const q = query.search.value ? `?search=${encodeURIComponent(query.search.value)}` : '';
    window.open(`/api/sops/export${q}`, '_blank');
}
function goCreate() {
    router.visit('/sops/create');
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="SOP QC per product (terbaru)">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(mainExportRows, ['code', 'name', 'target'], 'sop')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="exportAll"><FileDown /> Export</Button>
                <Button size="sm" @click="goCreate"><Pencil /> Manage SOP QC</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter SOP" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari product, target..." @input="onSearchInput" />
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
            empty-title="SOP tidak ditemukan"
            empty-message="Ubah kata kunci pencarian."
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-product_code="{ row }"><b>{{ row.product?.code ?? '-' }}</b></template>
            <template #cell-product_name="{ row }">{{ row.product?.name ?? '-' }}</template>
            <template #cell-target="{ row }">{{ row.target ?? '-' }}</template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Button variant="outline" size="sm" title="Unduh Excel" @click="downloadSop(row)"><Download /></Button>
                    <Button variant="outline" size="sm" title="Print" @click="printSop(row)"><Printer /></Button>
                </div>
            </template>
        </DataTable>

        <AppModal v-model:open="modalOpen" :title="`[${detail?.product?.code ?? activeRow?.product?.code ?? ''}] ${detail?.product?.name ?? activeRow?.product?.name ?? 'Detail SOP'}`" size="lg">
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 5" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="space-y-3">
                <p class="text-sm"><b>Target:</b> {{ detail?.target ?? '-' }}</p>
                <div class="flex gap-2 border-b text-sm">
                    <button class="px-3 py-2" :class="activeTab === 'view' ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="activeTab = 'view'">Lihat Detail</button>
                    <button class="px-3 py-2" :class="activeTab === 'edit' ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="activeTab = 'edit'">Edit SOP</button>
                </div>
                <div v-if="activeTab === 'view'">
                    <ol v-if="detail?.items?.length" class="list-decimal space-y-1 pl-5 text-sm">
                        <li v-for="(it, i) in detail.items" :key="i">{{ it.item }}</li>
                    </ol>
                    <p v-else class="text-sm text-slate-500">Tidak ada item SOP.</p>
                </div>
                <div v-else class="space-y-3">
                    <FormField label="Target" required :error="errors.target?.[0]">
                        <Input v-model="editTarget" placeholder="Target pemeriksaan..." />
                    </FormField>
                    <div class="space-y-2">
                        <div v-for="(r, i) in editItems" :key="i" class="flex gap-2">
                            <Textarea v-model="r.item" rows="2" class="flex-1" :placeholder="`Item ${i + 1}...`" />
                            <Button variant="outline" size="sm" title="Hapus baris" @click="removeEditRow(i)"><Trash2 /></Button>
                        </div>
                    </div>
                    <Button variant="outline" size="sm" @click="addEditRow()">Tambah Item</Button>
                    <p v-if="errors.items?.[0]" class="text-xs text-destructive">{{ errors.items[0] }}</p>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="modalOpen = false">Tutup</Button>
                <Button variant="outline" @click="printSop()"><Printer /> Print</Button>
                <Button variant="outline" @click="downloadSop()"><Download /> Excel</Button>
                <Button v-if="activeTab === 'edit'" @click="saveSop">Simpan SOP</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
