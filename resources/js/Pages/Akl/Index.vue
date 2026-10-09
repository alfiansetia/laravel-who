<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Eye, ListOrdered, Pencil, Plus, RefreshCw, RefreshCw as Sync, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
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
    title: { type: String, default: 'AKL' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery((p) => api.get('/akls', { params: p }), {
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
    { key: 'reg_no', label: 'Reg No', mono: true },
    { key: 'reg_name', label: 'Nama', wrap: true, maxWidth: '220px' },
    { key: 'vendor', label: 'Vendor', wrap: true, maxWidth: '180px' },
    { key: 'date_from', label: 'Berlaku', mono: true },
    { key: 'date_expired', label: 'Expired', mono: true },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'items', label: 'Item', align: 'center', mono: true },
];
const perPageOptions = [10, 25, 50, 100];

const exportRows = computed(() =>
    query.rows.value.map((r) => ({
        reg_no: r.reg_no ?? '', name: r.reg_name ?? '', vendor: r.vendor ?? '',
        berlaku: r.date_from ?? '', expired: r.date_expired ?? '',
    })),
);

function statusOf(row) {
    if (!row.date_expired) {
        return '-';
    }
    return row.is_expired ? 'Expired' : 'Aktif';
}
function goCreate() {
    router.visit('/akls/create');
}
function goEdit(row) {
    router.visit(`/akls/${row.id}/edit`);
}
function goItems(row) {
    router.visit(`/akls/${row.id}/items`);
}
function openFile(row) {
    if (!row.file) {
        toast.warning('Tidak ada lampiran.');
        return;
    }
    window.open(`/akls/${row.id}`, '_blank');
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk dihapus.');
        return;
    }
    const ok = await confirm({ title: `Hapus ${selected.value.length} AKL?`, message: 'Lampiran di S3 ikut dihapus.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/akls', { data: { ids: selected.value }, block: true });
        toast.success('AKL dihapus.');
        selected.value = [];
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus AKL.');
    }
}

// Preview modal (gambar langsung, PDF via embed + buka tab baru)
const previewOpen = ref(false);
const previewRow = ref(null);
function openPreview(row) {
    if (!row.file) {
        toast.warning('Tidak ada lampiran.');
        return;
    }
    previewRow.value = row;
    previewOpen.value = true;
}

// Sync Izin Edar modal (check + apply, samakan akl/index.blade.php)
const syncOpen = ref(false);
const syncRow = ref(null);
const syncAkl = ref(null);
const syncMatches = ref([]);
const syncLoading = ref(false);
const syncPicked = ref('');
const syncSaving = ref(false);
const syncColumns = [
    { key: 'pick', label: '', },
    { key: 'nomor', label: 'Nomor Izin Edar' },
    { key: 'merk', label: 'Merk / Produk' },
    { key: 'pendaftar', label: 'Pendaftar / Pabrik' },
    { key: 'terbit', label: 'Terbit', mono: true },
    { key: 'expired', label: 'Expired', mono: true },
    { key: 'cek', label: 'Cek' },
];
const syncPickable = computed(() => syncMatches.value.filter((m) => !m.is_synced).length);
function diffBadge(sama) {
    return sama
        ? 'inline-flex items-center rounded-md border border-green-200 bg-green-50 px-1.5 py-0.5 text-[11px] font-medium text-green-700'
        : 'inline-flex items-center rounded-md border border-red-200 bg-red-50 px-1.5 py-0.5 text-[11px] font-medium text-red-700';
}
async function openSync(row) {
    syncRow.value = row;
    syncAkl.value = null;
    syncMatches.value = [];
    syncPicked.value = '';
    syncOpen.value = true;
    syncLoading.value = true;
    try {
        const res = await api.get(`/akls/${row.id}/check-izin`, { silent: true, block: true });
        syncAkl.value = res.data?.data?.akl ?? row;
        syncMatches.value = res.data?.data?.matches ?? [];
        if (syncMatches.value.length === 0) {
            toast.warning(res.data?.message ?? 'Tidak ada data cocok di Izin Edar.');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal cek Izin Edar.');
    } finally {
        syncLoading.value = false;
    }
}
async function applySync() {
    if (!syncPicked.value) {
        toast.warning('Pilih kandidat dulu.');
        return;
    }
    syncSaving.value = true;
    try {
        const res = await api.put(`/akls/${syncRow.value.id}/apply-izin`, { izin_edar_id: syncPicked.value }, { silent: true, block: true });
        toast.success(res.data?.message ?? 'AKL disinkron dari Izin Edar.');
        syncOpen.value = false;
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal sinkron.');
    } finally {
        syncSaving.value = false;
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Arsip lampiran AKL + sinkron Izin Edar">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['reg_no', 'name', 'vendor', 'berlaku', 'expired'], 'akl')"><Copy /> Salin</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <Button size="sm" @click="goCreate"><Plus /> Upload Lampiran</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <FilterPanel title="Filter AKL" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari reg no, nama, vendor..." @input="onSearchInput" />
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
                empty-title="AKL tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @update:selected="selected = $event"
                @row-click="goEdit"
            >
                <template #cell-reg_no="{ row }"><b>{{ row.reg_no }}</b></template>
                <template #cell-reg_name="{ row }">{{ row.reg_name ?? '-' }}</template>
                <template #cell-vendor="{ row }">{{ row.vendor ?? '-' }}</template>
                <template #cell-date_from="{ row }">{{ row.date_from ?? '-' }}</template>
                <template #cell-date_expired="{ row }"><span :class="row.is_expired && 'font-semibold text-red-600'">{{ row.date_expired ?? '-' }}</span></template>
                <template #cell-status="{ row }">{{ statusOf(row) }}</template>
                <template #cell-items="{ row }">{{ row.items_count ?? 0 }}</template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1" @click.stop>
                        <Button variant="ghost" size="sm" title="Preview lampiran" @click="openPreview(row)"><Eye /></Button>
                        <Button variant="ghost" size="sm" title="Kelola item" @click="goItems(row)"><ListOrdered /></Button>
                        <Button variant="ghost" size="sm" title="Sync Izin Edar" @click="openSync(row)"><Sync /></Button>
                        <Button variant="ghost" size="sm" title="Edit" @click="goEdit(row)"><Pencil /></Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <AppModal v-model:open="previewOpen" :title="`Lampiran ${previewRow?.reg_no ?? ''}`" size="lg">
            <div v-if="previewRow?.is_pdf" class="h-[60vh]">
                <embed :src="`/akls/${previewRow.id}`" type="application/pdf" class="size-full rounded border" />
            </div>
            <img v-else :src="`/akls/${previewRow?.id}`" :alt="previewRow?.reg_no" class="max-h-[60vh] w-full rounded border object-contain" />
            <template #footer>
                <Button variant="ghost" @click="previewOpen = false">Tutup</Button>
                <Button variant="outline" @click="openFile(previewRow)">Buka Tab Baru</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="syncOpen" :title="`Sync Cek ${syncRow?.reg_no ?? ''} → Izin Edar`" size="xl">
            <div v-if="syncLoading" class="space-y-2">
                <div v-for="n in 4" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="space-y-3">
                <p class="text-xs text-muted-foreground">{{ syncMatches.length ? `${syncMatches.length} kandidat cocok (${syncPickable} bisa dipilih)` : 'Tidak ada yang cocok' }}</p>
                <div class="rounded-md border bg-slate-50 p-2 text-xs">
                    <b>Data AKL saat ini:</b><br />
                    Reg No: <b>{{ syncAkl?.reg_no ?? syncRow?.reg_no }}</b> &nbsp;
                    Nama: {{ syncAkl?.reg_name ?? syncRow?.reg_name ?? '-' }} &nbsp;
                    Vendor: {{ syncAkl?.vendor ?? syncRow?.vendor ?? '-' }}<br />
                    Terbit: {{ syncAkl?.date_from ?? syncRow?.date_from ?? '-' }} &nbsp;
                    Expired: {{ syncAkl?.date_expired ?? syncRow?.date_expired ?? '-' }}
                </div>
                <p v-if="syncMatches.length === 0" class="py-4 text-center text-sm text-muted-foreground">Tidak ada data yang cocok di Izin Edar untuk reg_no <b>{{ syncAkl?.reg_no ?? syncRow?.reg_no }}</b>.</p>
                <DataTable
                    v-else
                    :columns="syncColumns"
                    :rows="syncMatches"
                    :loading="false"
                    :page="1"
                    :total-pages="1"
                    :total="syncMatches.length"
                    :per-page="syncMatches.length"
                    :show-footer="false"
                    empty-title="Tidak ada kandidat"
                >
                    <template #cell-pick="{ row }">
                        <input v-model="syncPicked" type="radio" :value="row.id" :disabled="row.is_synced" title="Pilih kandidat" />
                    </template>
                    <template #cell-nomor="{ row }"><b>{{ row.nomor_izin_edar }}</b><br /><small class="text-muted-foreground">{{ row.kategori ?? '' }}</small> <span :class="diffBadge(row.diff?.reg_no_sama)">{{ row.diff?.reg_no_sama ? 'Sama' : 'Beda' }}</span></template>
                    <template #cell-merk="{ row }">{{ row.merk ?? '-' }} <span :class="diffBadge(row.diff?.nama_sama)">{{ row.diff?.nama_sama ? 'Sama' : 'Beda' }}</span><br /><small class="text-muted-foreground">{{ row.jenis_produk ?? '' }}</small></template>
                    <template #cell-pendaftar="{ row }">{{ row.pendaftar ?? '-' }} <span :class="diffBadge(row.diff?.vendor_sama)">{{ row.diff?.vendor_sama ? 'Sama' : 'Beda' }}</span><br /><small class="text-muted-foreground">{{ row.pabrik ?? '' }}</small></template>
                    <template #cell-terbit="{ row }">{{ row.tgl_terbit ?? '-' }}<br /><span :class="diffBadge(row.diff?.terbit_sama)">{{ row.diff?.terbit_sama ? 'Sama' : 'Beda' }}</span></template>
                    <template #cell-expired="{ row }">{{ row.tgl_exp ?? '-' }} <span :class="row.is_expired ? 'inline-flex items-center rounded-md border border-red-200 bg-red-50 px-1.5 py-0.5 text-[11px] font-medium text-red-700' : 'inline-flex items-center rounded-md border border-green-200 bg-green-50 px-1.5 py-0.5 text-[11px] font-medium text-green-700'">{{ row.is_expired ? 'Expired' : 'Aktif' }}</span><br /><span :class="diffBadge(row.diff?.expired_sama)">{{ row.diff?.expired_sama ? 'Sama' : 'Beda' }}</span></template>
                    <template #cell-cek="{ row }">
                        <span v-if="row.is_synced" class="text-xs font-semibold text-green-700">Sudah sama<br /><small class="font-normal text-muted-foreground">Tidak bisa dipilih</small></span>
                        <small v-else class="text-muted-foreground">Saran:<br />Nama: {{ row.saran?.reg_name ?? '-' }}<br />Vendor: {{ row.saran?.vendor ?? '-' }}</small>
                    </template>
                </DataTable>
                <p v-if="syncMatches.length" class="text-xs text-muted-foreground">Baris <b>Sudah sama</b> tidak bisa dipilih. Pilih kandidat lain yang masih <b>Beda</b>, lalu klik <b>Save yang Dipilih</b>.</p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="syncOpen = false">Tutup</Button>
                <Button :disabled="!syncPicked || syncSaving" @click="applySync">{{ syncSaving ? 'Menyimpan...' : 'Save yang Dipilih' }}</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
