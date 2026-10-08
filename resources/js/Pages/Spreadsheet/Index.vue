<script setup>
import { computed, ref, watch } from 'vue';
import { Copy, Download, RefreshCw, Scale } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Spreadsheet PLTBB' },
});

const toast = useToast();
const { confirm } = useConfirm();

const searchBox = ref('');
const statusFilter = ref('all');
const syncingCode = ref('');
const syncingAll = ref(false);
const compareOpen = ref(false);
const compareLoading = ref(false);
const compareCode = ref('');
const sheetRow = ref(null);
const dbPltbb = ref(null);

function mapRow(r) {
    const p = r[8] ?? '';
    const l = r[9] ?? '';
    const t = r[10] ?? '';
    const b = r[11] ?? '';
    const complete = [p, l, t, b].every((v) => v !== '' && v !== null && v !== undefined && String(v) !== '0');
    return {
        code: r[3] ?? '',
        name: r[4] ?? '',
        uom: r[6] ?? '',
        p, l, t, b,
        note: r[12] ?? '',
        tgl: r[13] ?? '',
        status: complete ? 'lengkap' : 'tidak_lengkap',
        pltbbText: [p, l, t, b].every((v) => v === '' || v === null || v === undefined) ? '-' : `${p || 0}x${l || 0}x${t || 0}/${b || 0}`,
    };
}

const table = useClientTable(async () => {
    const res = await api.get('/spreadsheet');
    const list = res.data?.data ?? [];
    return { data: list.map(mapRow) };
}, { perPage: 10, searchKeys: ['code', 'name'] });

const rawAll = computed(() => table.allRows.value);

function applyStatusFilter() {
    if (statusFilter.value === 'all') {
        table.setRows(rawAll.value);
    } else {
        table.setRows(rawAll.value.filter((r) => r.status === statusFilter.value));
    }
}

watch(rawAll, () => applyStatusFilter());

const columns = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama Produk' },
    { key: 'uom', label: 'UOM', align: 'center' },
    { key: 'pltbbText', label: 'PLTBB', align: 'center', mono: true },
    { key: 'note', label: 'Note' },
    { key: 'tgl', label: 'Tgl Input', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
];

function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

function setStatusFilter(v) {
    statusFilter.value = v;
    applyStatusFilter();
}

const exportCols = ['code', 'name', 'uom', 'pltbbText', 'note', 'tgl'];

function onCopy() {
    copyRows(table.filtered.value, exportCols, 'PLTBB');
}

function onDownload() {
    downloadCsv('spreadsheet-pltbb.csv', table.filtered.value, exportCols);
}

async function syncRow(row) {
    if (!row.code || row.p === '' || row.l === '' || row.t === '' || row.b === '') {
        toast.warning(`Kode dan P/L/T/B wajib ada untuk ${row.code || 'baris ini'}.`);
        return;
    }
    const ok = await confirm({
        title: `Sinkron ${row.code}?`,
        message: `Dimensi ${row.pltbbText} disimpan ke database.`,
        confirmText: 'Ya, sinkron',
    });
    if (!ok) {
        return;
    }
    syncingCode.value = row.code;
    try {
        const res = await api.post('/spreadsheet', {
            code: row.code, p: String(row.p), l: String(row.l), t: String(row.t), b: String(row.b), note: row.note ?? '',
        });
        toast.success(res.data?.message ?? 'Data berhasil disimpan.');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal sinkron.');
    } finally {
        syncingCode.value = '';
    }
}

async function syncAll() {
    const ok = await confirm({
        title: 'Sinkron semua data?',
        message: 'Seluruh baris spreadsheet disinkronkan ke database.',
        confirmText: 'Ya, sinkron semua',
    });
    if (!ok) {
        return;
    }
    syncingAll.value = true;
    try {
        const res = await api.post('/spreadsheet/sync-all');
        toast.success(res.data?.message ?? 'Sinkronisasi selesai.');
        table.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal sinkronisasi.');
    } finally {
        syncingAll.value = false;
    }
}

async function openCompare(row) {
    compareCode.value = row.code;
    sheetRow.value = row;
    dbPltbb.value = null;
    compareOpen.value = true;
    compareLoading.value = true;
    try {
        const res = await api.get(`/products/${encodeURIComponent(row.code)}/compare`, { silent: true });
        dbPltbb.value = res.data?.data?.pltbb ?? null;
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat data pembanding.');
    } finally {
        compareLoading.value = false;
    }
}

function cellVal(v) {
    return v === '' || v === null || v === undefined ? '-' : v;
}

table.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Sinkron dimensi PLTBB spreadsheet ke database produk">
            <template #actions>
                <Button variant="outline" size="sm" :disabled="table.loading.value" @click="table.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="onCopy"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="onDownload"><Download /> CSV</Button>
                <Button variant="destructive" size="sm" :disabled="syncingAll" @click="syncAll"><RefreshCw /> {{ syncingAll ? 'Sinkron...' : 'Sync All' }}</Button>
            </template>
        </PageHeader>

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <Input :model-value="searchBox" type="search" placeholder="Cari kode atau nama..." class="max-w-xs" @input="onSearchInput" />
            <div class="flex items-center gap-1 rounded-md border p-1">
                <Button
                    v-for="opt in [{ value: 'all', label: 'Semua' }, { value: 'lengkap', label: 'Lengkap' }, { value: 'tidak_lengkap', label: 'Belum Lengkap' }]"
                    :key="opt.value"
                    :variant="statusFilter === opt.value ? 'default' : 'ghost'"
                    size="sm"
                    @click="setStatusFilter(opt.value)"
                >
                    {{ opt.label }}
                </Button>
            </div>
        </div>

        <DataTable
            :columns="columns"
            :rows="table.rows.value"
            :loading="table.loading.value"
            :error="table.error.value"
            :page="table.page.value"
            :total-pages="table.totalPages.value"
            :total="table.total.value"
            :per-page="table.perPage.value"
            :per-page-options="[10, 25, 50, 100]"
            row-key="code"
            empty-title="Data tidak ditemukan"
            empty-message="Ubah kata kunci atau filter status."
            @update:page="table.setPage"
            @update:per-page="table.setPerPage"
        >
            <template #cell-code="{ row }"><b>{{ row.code }}</b></template>
            <template #cell-note="{ row }">{{ row.note || '-' }}</template>
            <template #cell-tgl="{ row }">{{ row.tgl || '-' }}</template>
            <template #cell-status="{ row }">
                <StatusBadge :status="row.status === 'lengkap' ? 'success' : 'danger'">
                    {{ row.status === 'lengkap' ? 'Lengkap' : 'Belum Lengkap' }}
                </StatusBadge>
            </template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Button variant="outline" size="sm" title="Sinkron baris ini" :disabled="syncingCode === row.code" @click="syncRow(row)">
                        <RefreshCw :class="syncingCode === row.code && 'animate-spin'" />
                    </Button>
                    <Button variant="outline" size="sm" title="Bandingkan" @click="openCompare(row)"><Scale /></Button>
                </div>
            </template>
        </DataTable>

        <AppModal v-model:open="compareOpen" :title="`Perbandingan Data: ${compareCode}`" size="lg">
            <div v-if="compareLoading" class="space-y-2">
                <div v-for="n in 4" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="mb-2 text-sm font-semibold">PLTB (Spreadsheet)</p>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between"><dt class="text-muted-foreground">P</dt><dd class="font-medium">{{ cellVal(sheetRow?.p) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">L</dt><dd class="font-medium">{{ cellVal(sheetRow?.l) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">T</dt><dd class="font-medium">{{ cellVal(sheetRow?.t) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">B</dt><dd class="font-medium">{{ cellVal(sheetRow?.b) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">Note</dt><dd class="font-medium">{{ cellVal(sheetRow?.note) }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="mb-2 text-sm font-semibold">Product (Database)</p>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between"><dt class="text-muted-foreground">P</dt><dd class="font-medium">{{ cellVal(dbPltbb?.p) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">L</dt><dd class="font-medium">{{ cellVal(dbPltbb?.l) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">T</dt><dd class="font-medium">{{ cellVal(dbPltbb?.t) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">B</dt><dd class="font-medium">{{ cellVal(dbPltbb?.b) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted-foreground">Note</dt><dd class="font-medium">{{ cellVal(dbPltbb?.note) }}</dd></div>
                    </dl>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="compareOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <ConfirmDialog />
    </AppLayout>
</template>
