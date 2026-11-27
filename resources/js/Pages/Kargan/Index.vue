<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronDown, Copy, Download, FileDown, Files, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import Button from '@/components/ui/Button.vue';
import KarganImportModal from './partials/KarganImportModal.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'List Kargan' },
    filters: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    picOptions: { type: Array, default: () => [] },
    defaultDate: { type: String, default: '' },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery((p) => api.get('/kargans', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const selected = ref([]);
const importOpen = ref(false);

const searchBox = ref(props.filters.search ?? '');
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

function downloadZip() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk diunduh.');
        return;
    }
    const qs = selected.value.map((id) => `ids[]=${encodeURIComponent(id)}`).join('&');
    window.open(`/kargans/download-zip?${qs}`, '_blank');
    dlMenuOpen.value = false;
}

function downloadMerged() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk diunduh.');
        return;
    }
    const qs = selected.value.map((id) => `ids[]=${encodeURIComponent(id)}`).join('&');
    window.open(`/kargans/download-docx?${qs}`, '_blank');
    dlMenuOpen.value = false;
}

const dlMenuOpen = ref(false);

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
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['number', 'date', 'sn', 'product'], 'kargan')"><Copy /> Salin</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <span class="relative">
                    <Button variant="outline" size="sm" :disabled="selected.length === 0" @click="dlMenuOpen = !dlMenuOpen"><Download /> Unduh{{ selected.length ? ` (${selected.length})` : '' }} <ChevronDown /></Button>
                    <span v-if="dlMenuOpen" class="fixed inset-0 z-40" @click="dlMenuOpen = false" />
                    <span v-if="dlMenuOpen" class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white p-1 text-left shadow-lg">
                        <p class="px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Batch download</p>
                        <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="downloadZip"><FileDown class="size-4 text-slate-500" /> ZIP — file terpisah</button>
                        <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="downloadMerged"><Files class="size-4 text-slate-500" /> DOCX gabungan — 1 file, {{ selected.length }} halaman</button>
                    </span>
                </span>
                <Button variant="outline" size="sm" @click="importOpen = true"><Download /> Import DO/IT</Button>
                <Button size="sm" @click="goCreate"><Plus /> Tambah Data</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <TableToolbar v-model="searchBox" placeholder="Cari nomor, SN, product..." @search="query.setSearch">
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
            </TableToolbar>
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
        <KarganImportModal
            v-model:open="importOpen"
            :products="props.products"
            :pic-options="props.picOptions"
            :default-date="props.defaultDate"
            @imported="query.fetch()"
        />
    </AppLayout>
</template>
