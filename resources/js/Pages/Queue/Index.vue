<script setup>
import { computed, ref } from 'vue';
import { Eye, Play, RefreshCw, RotateCcw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import CopyButton from '@/components/CopyButton.vue';
import DetailMeta from '@/components/DetailMeta.vue';
import ModalTabs from '@/components/ModalTabs.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';

const props = defineProps({
    title: { type: String, default: 'Queue' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();

const tab = ref('pending');
const stats = ref(null);
const statsLoading = ref(false);

const pendingQuery = useTableQuery((p) => api.get('/queue/jobs', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const failedQuery = useTableQuery((p) => api.get('/queue/failed', { params: p }), {
    search: '',
    page: 1,
});

const detailOpen = ref(false);
const detailKind = ref('pending');
const detail = ref(null);
const detailLoading = ref(false);

const pendingTabs = computed(() => [
    { value: 'pending', label: 'Antrean', count: pendingQuery.total.value },
    { value: 'failed', label: 'Gagal', count: failedQuery.total.value },
]);

const pendingColumns = [
    { key: 'id', label: 'ID', align: 'center' },
    { key: 'job', label: 'Job' },
    { key: 'queue', label: 'Queue', align: 'center' },
    { key: 'attempts', label: 'Coba', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'available_at', label: 'Tersedia', mono: true },
    { key: 'created_at', label: 'Dibuat', mono: true },
    { key: 'aksi', label: 'Action', align: 'center' },
];
const failedColumns = [
    { key: 'uuid', label: 'UUID', mono: true },
    { key: 'job', label: 'Job' },
    { key: 'queue', label: 'Queue', align: 'center' },
    { key: 'failed_at', label: 'Gagal Pada', mono: true },
    { key: 'error', label: 'Error' },
    { key: 'aksi', label: 'Action', align: 'center' },
];

const pendingSearchBox = ref(props.filters.search ?? '');
const failedSearchBox = ref('');

function onPendingSearch(e) {
    pendingSearchBox.value = e.target.value;
    pendingQuery.setSearch(e.target.value);
}

function onFailedSearch(e) {
    failedSearchBox.value = e.target.value;
    failedQuery.setSearch(e.target.value);
}

function shortUuid(uuid) {
    const s = String(uuid ?? '');
    return s.length > 13 ? `${s.slice(0, 8)}…` : s;
}

async function loadStats(silent = true) {
    statsLoading.value = true;
    try {
        const res = await api.get('/queue/stats', { silent });
        stats.value = res.data?.data ?? res.data;
    } catch {
        // toast error sudah ditangani interceptor axios
    } finally {
        statsLoading.value = false;
    }
}

function refreshAll() {
    loadStats(false);
    if (tab.value === 'pending') {
        pendingQuery.fetch();
    } else {
        failedQuery.fetch();
    }
}

async function openDetail(kind, row) {
    detailKind.value = kind;
    detail.value = null;
    detailLoading.value = true;
    detailOpen.value = true;
    try {
        const url = kind === 'pending' ? `/queue/jobs/${row.id}` : `/queue/failed/${row.uuid}`;
        const res = await api.get(url, { silent: true, block: true });
        detail.value = res.data?.data ?? res.data;
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail job.');
        detailOpen.value = false;
    } finally {
        detailLoading.value = false;
    }
}

const detailMetaItems = computed(() => {
    const d = detail.value ?? {};
    if (detailKind.value === 'pending') {
        return [
            { label: 'ID', value: String(d.id ?? '-'), mono: true },
            { label: 'Job', value: d.job ?? '-' },
            { label: 'Class', value: d.job_class ?? '-', mono: true },
            { label: 'Connection', value: stats?.driver ?? '-' },
            { label: 'Queue', value: d.queue ?? '-' },
            { label: 'Attempts', value: String(d.attempts ?? 0) },
            { label: 'Status', value: d.reserved ? 'Reserved (dikerjakan worker)' : 'Pending (menunggu worker)' },
            { label: 'Reserved At', value: d.reserved_at ?? '-' },
            { label: 'Available At', value: d.available_at ?? '-' },
            { label: 'Created At', value: d.created_at ?? '-' },
        ];
    }
    return [
        { label: 'ID', value: String(d.id ?? '-'), mono: true },
        { label: 'UUID', value: d.uuid ?? '-', mono: true },
        { label: 'Job', value: d.job ?? '-' },
        { label: 'Class', value: d.job_class ?? '-', mono: true },
        { label: 'Connection', value: d.connection ?? '-' },
        { label: 'Queue', value: d.queue ?? '-' },
        { label: 'Failed At', value: d.failed_at ?? '-' },
    ];
});

const detailJson = computed(() => {
    const d = detail.value ?? {};
    return JSON.stringify({ command_props: d.command_props ?? null, payload: d.payload ?? null }, null, 2);
});

function copyDetailJson() {
    navigator.clipboard?.writeText(detailJson.value).then(
        () => toast.success('Detail job disalin ke clipboard.'),
        () => toast.error('Gagal menyalin.'),
    );
}

async function retryFailed(row) {
    const ok = await confirm({ title: `Retry ${row.job}?`, message: `Job ${shortUuid(row.uuid)} dimasukkan antrean lagi.`, confirmText: 'Ya, retry' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/queue/failed/${row.uuid}/retry`, {}, { block: true });
        toast.success(res.data?.message ?? 'Job di-retry.');
        refreshAll();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal retry.');
    }
}

async function releaseJob(row) {
    const ok = await confirm({ title: `Lepas job #${row.id}?`, message: 'Job reserved dikembalikan ke antrean agar dikerjakan ulang worker.', confirmText: 'Ya, lepaskan' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/queue/jobs/${row.id}/release`, {}, { block: true });
        toast.success(res.data?.message ?? 'Job dilepas.');
        refreshAll();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal melepas job.');
    }
}

async function deleteRow(kind, row) {
    const label = kind === 'pending' ? `job #${row.id}` : `failed job ${shortUuid(row.uuid)}`;
    const ok = await confirm({ title: `Hapus ${label}?`, message: 'Baris dihapus dari tabel, tidak bisa dibatalkan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        const url = kind === 'pending' ? `/queue/jobs/${row.id}` : `/queue/failed/${row.uuid}`;
        const res = await api.delete(url, { block: true });
        toast.success(res.data?.message ?? 'Dihapus.');
        refreshAll();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus.');
    }
}

async function flushJobs() {
    const ok = await confirm({ title: 'Kosongkan tabel antrean?', message: 'SELURUH job tertunda di tabel jobs dihapus permanen.', confirmText: 'Ya, kosongkan', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.delete('/queue/jobs', { block: true });
        toast.success(res.data?.message ?? 'Antrean dikosongkan.');
        refreshAll();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengosongkan antrean.');
    }
}

async function flushFailed() {
    const ok = await confirm({ title: 'Kosongkan tabel gagal?', message: 'SELURUH failed job di tabel failed_jobs dihapus permanen.', confirmText: 'Ya, kosongkan', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.delete('/queue/failed', { block: true });
        toast.success(res.data?.message ?? 'Tabel gagal dikosongkan.');
        refreshAll();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengosongkan.');
    }
}

loadStats(true);
pendingQuery.fetch();
failedQuery.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <div class="space-y-4">
            <Card class="p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <b class="text-sm">Ringkasan Queue</b>
                    <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">Driver: {{ stats?.driver ?? '-' }}</span>
                    <span class="ml-auto flex flex-wrap gap-1">
                        <Button variant="outline" size="sm" @click="refreshAll"><RefreshCw /> Segarkan</Button>
                        <Button variant="outline" size="sm" @click="flushJobs"><Trash2 /> Kosongkan Antrean</Button>
                        <Button variant="outline" size="sm" @click="flushFailed"><Trash2 /> Kosongkan Gagal</Button>
                    </span>
                </div>
                <div class="mt-3 grid gap-2 sm:grid-cols-3">
                    <div class="rounded-xl border bg-white p-3 text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Antre (pending)</p>
                        <p class="mt-1 text-2xl font-bold text-sky-700">{{ statsLoading && !stats ? '…' : (stats?.pending ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl border bg-white p-3 text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Dikerjakan (reserved)</p>
                        <p class="mt-1 text-2xl font-bold text-amber-700">{{ statsLoading && !stats ? '…' : (stats?.reserved ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl border bg-white p-3 text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Gagal</p>
                        <p class="mt-1 text-2xl font-bold text-red-700">{{ statsLoading && !stats ? '…' : (stats?.failed ?? 0) }}</p>
                    </div>
                </div>
            </Card>

            <ModalTabs v-model="tab" :tabs="pendingTabs" class="mb-2" />

            <div v-if="tab === 'pending'">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="pendingSearchBox" type="search" placeholder="Cari job / queue..." class="max-w-xs" @input="onPendingSearch" />
                </div>
                <DataTable
                    :columns="pendingColumns"
                    :rows="pendingQuery.rows.value"
                    :loading="pendingQuery.loading.value"
                    :error="pendingQuery.error.value"
                    :page="pendingQuery.page.value"
                    :total-pages="pendingQuery.totalPages.value"
                    :total="pendingQuery.total.value"
                    :per-page="pendingQuery.perPage.value"
                    clickable
                    empty-title="Antrean kosong"
                    empty-message="Tidak ada job tertunda. Worker menganggur dengan tenang."
                    @update:page="pendingQuery.setPage"
                    @update:per-page="pendingQuery.setPerPage"
                    @row-click="(row) => openDetail('pending', row)"
                >
                    <template #cell-job="{ row }"><b>{{ row.job }}</b></template>
                    <template #cell-queue="{ row }"><span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs">{{ row.queue }}</span></template>
                    <template #cell-status="{ row }">
                        <span v-if="row.reserved" class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800">reserved</span>
                        <span v-else class="rounded bg-sky-100 px-1.5 py-0.5 text-xs font-medium text-sky-800">pending</span>
                    </template>
                    <template #cell-aksi="{ row }">
                        <div class="flex justify-center gap-1" @click.stop>
                            <Button variant="outline" size="sm" title="Detail" @click.stop="openDetail('pending', row)"><Eye /></Button>
                            <Button v-if="row.reserved" variant="outline" size="sm" title="Lepas (kembalikan ke antrean)" @click.stop="releaseJob(row)"><Play /></Button>
                            <Button variant="outline" size="sm" title="Hapus" @click.stop="deleteRow('pending', row)"><Trash2 /></Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <div v-else>
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <Input :model-value="failedSearchBox" type="search" placeholder="Cari job / error..." class="max-w-xs" @input="onFailedSearch" />
                </div>
                <DataTable
                    :columns="failedColumns"
                    :rows="failedQuery.rows.value"
                    :loading="failedQuery.loading.value"
                    :error="failedQuery.error.value"
                    :page="failedQuery.page.value"
                    :total-pages="failedQuery.totalPages.value"
                    :total="failedQuery.total.value"
                    :per-page="failedQuery.perPage.value"
                    clickable
                    empty-title="Tidak ada failed job"
                    empty-message="Semua job selesai tanpa drama."
                    @update:page="failedQuery.setPage"
                    @update:per-page="failedQuery.setPerPage"
                    @row-click="(row) => openDetail('failed', row)"
                >
                    <template #cell-uuid="{ row }"><span class="font-mono" :title="row.uuid">{{ shortUuid(row.uuid) }}</span></template>
                    <template #cell-job="{ row }"><b>{{ row.job }}</b></template>
                    <template #cell-queue="{ row }"><span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs">{{ row.queue }}</span></template>
                    <template #cell-error="{ row }"><span class="line-clamp-2 text-xs text-red-700">{{ row.error || '-' }}</span></template>
                    <template #cell-aksi="{ row }">
                        <div class="flex justify-center gap-1" @click.stop>
                            <Button variant="outline" size="sm" title="Detail" @click.stop="openDetail('failed', row)"><Eye /></Button>
                            <Button variant="outline" size="sm" title="Retry (masuk antrean lagi)" @click.stop="retryFailed(row)"><RotateCcw /></Button>
                            <Button variant="outline" size="sm" title="Hapus" @click.stop="deleteRow('failed', row)"><Trash2 /></Button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <AppModal v-model:open="detailOpen" size="2xl">
            <template #title>
                Detail {{ detailKind === 'pending' ? 'Job' : 'Failed Job' }} {{ detailKind === 'pending' ? `#${detail?.id ?? ''}` : shortUuid(detail?.uuid) }}
                <CopyButton :text="detailJson" label="Detail job" />
            </template>
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 6" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else-if="detail" class="space-y-3">
                <DetailMeta :items="detailMetaItems" />
                <div>
                    <p class="mb-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">Payload / Properti</p>
                    <pre class="max-h-[30vh] overflow-y-auto whitespace-pre-wrap rounded-md bg-slate-900 p-2.5 font-mono text-[11px] leading-relaxed text-slate-100 [overflow-wrap:anywhere]"><code>{{ detailJson }}</code></pre>
                </div>
                <div v-if="detailKind === 'failed' && detail.exception">
                    <p class="mb-1 flex items-center gap-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">Exception <CopyButton :text="detail.exception" label="Exception" /></p>
                    <pre class="max-h-[30vh] overflow-y-auto whitespace-pre-wrap rounded-md bg-red-950 p-2.5 font-mono text-[11px] leading-relaxed text-red-100 [overflow-wrap:anywhere]"><code>{{ detail.exception }}</code></pre>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
                <Button v-if="detailKind === 'pending' && detail?.reserved" variant="outline" @click="detailOpen = false; releaseJob(detail)"><Play /> Lepas</Button>
                <Button v-if="detailKind === 'failed'" @click="detailOpen = false; retryFailed(detail)"><RotateCcw /> Retry</Button>
                <Button variant="destructive" @click="detailOpen = false; deleteRow(detailKind, detail)"><Trash2 /> Hapus</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
