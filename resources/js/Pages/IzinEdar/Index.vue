<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { CheckCircle2, Copy, Download, Eye, RefreshCw, Share, Trash2, Upload, X, XCircle } from '@lucide/vue';
import * as XLSX from 'xlsx';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';
import { copyRows, copyText } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Data Izin Edar' },
    filters: { type: Object, default: () => ({}) },
    kategoriList: { type: Array, default: () => ['AKD', 'AKL', 'PKD', 'PKL', 'Lainnya'] },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();
const query = useTableQuery(
    (p) => api.get('/izin-edars', { params: { ...p, kategori: kategoriBox.value || undefined } }),
    { search: props.filters.search ?? '', page: Number(props.filters.page ?? 1) },
);

const searchBox = ref(props.filters.search ?? '');
const kategoriBox = ref(props.filters.kategori ?? '');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value, 400);
}
function onKategori(v) {
    kategoriBox.value = v;
    query.fetch();
}
function resetFilter() {
    searchBox.value = '';
    kategoriBox.value = '';
    query.setSearch('', 0);
}
const activeCount = computed(() => (query.search.value ? 1 : 0) + (kategoriBox.value ? 1 : 0));

const columns = [
    { key: 'kategori', label: 'Kategori', align: 'center' },
    { key: 'nomor_izin_edar', label: 'No Izin', mono: true },
    { key: 'tgl_terbit', label: 'Tgl Terbit', mono: true },
    { key: 'tgl_exp', label: 'Tgl Exp', mono: true },
    { key: 'merk', label: 'Merk', wrap: true, maxWidth: '180px' },
    { key: 'jenis_produk', label: 'Jenis Produk', wrap: true, maxWidth: '200px' },
    { key: 'pendaftar', label: 'Pendaftar', wrap: true, maxWidth: '180px' },
    { key: 'pabrik', label: 'Pabrik', wrap: true, maxWidth: '180px' },
];
const perPageOptions = [10, 25, 50, 100, 200];
const kategoriOptions = computed(() => props.kategoriList.map((k) => ({ value: k, label: k })));

const KAT_COLORS = {
    AKD: 'bg-sky-50 text-sky-700 border-sky-200',
    AKL: 'bg-violet-50 text-violet-700 border-violet-200',
    PKD: 'bg-amber-50 text-amber-700 border-amber-200',
    PKL: 'bg-green-50 text-green-700 border-green-200',
};
function katClass(kat) {
    return KAT_COLORS[kat] ?? 'bg-slate-100 text-slate-600 border-slate-200';
}

function expClass(tglExp) {
    if (!tglExp) {
        return '';
    }
    const exp = new Date(tglExp);
    if (Number.isNaN(exp.getTime())) {
        return '';
    }
    const diff = Math.ceil((exp - new Date()) / 86400000);
    if (diff < 0) {
        return 'font-semibold text-red-600';
    }
    if (diff <= 30) {
        return 'font-semibold text-amber-600';
    }
    return '';
}

const exportRows = computed(() =>
    query.rows.value.map((r) => ({
        kategori: r.kategori ?? '',
        nomor: r.nomor_izin_edar ?? '',
        terbit: r.tgl_terbit ?? '',
        exp: r.tgl_exp ?? '',
        merk: r.merk ?? '',
        jenis: r.jenis_produk ?? '',
        pendaftar: r.pendaftar ?? '',
        pabrik: r.pabrik ?? '',
    })),
);

// Detail modal
const detailOpen = ref(false);
const detail = ref(null);
const detailLoading = ref(false);
async function openDetail(row) {
    detailOpen.value = true;
    detailLoading.value = true;
    await withBlock(async () => {
        try {
            const res = await api.get(`/izin-edars/${row.id}`, { block: true, silent: true });
            detail.value = res.data?.data ?? res.data;
        } catch (e) {
            detail.value = row;
            toast.error(e.response?.data?.message ?? 'Gagal memuat detail.');
        } finally {
            detailLoading.value = false;
        }
    });
}
function detailPairs() {
    const d = detail.value ?? {};
    const pairs = [
        ['Kategori', d.kategori], ['No. Izin Edar', d.nomor_izin_edar], ['Tgl Terbit', d.tgl_terbit],
        ['Tgl Expired', d.tgl_exp], ['Merk', d.merk], ['Jenis Produk', d.jenis_produk],
        ['Pendaftar', d.pendaftar], ['Alamat Pendaftar', d.alamat_pendaftar], ['Pabrik', d.pabrik],
        ['Alamat Pabrik', d.alamat_pabrik],
    ];
    if (d.sub_kategori) {
        pairs.push(['Sub Kategori', d.sub_kategori]);
    }
    if (d.kelompok_produk) {
        pairs.push(['Kelompok Produk', d.kelompok_produk]);
    }
    if (d.tipe) {
        pairs.push(['Tipe', d.tipe]);
    }
    if (d.kelas) {
        pairs.push(['Kelas', d.kelas]);
    }
    if (d.kelas_resiko) {
        pairs.push(['Kelas Resiko', d.kelas_resiko]);
    }
    return pairs;
}

// Copy text full (samakan Blade: semua field "Key: value" per baris)
function copyRowFull(row) {
    const fields = [
        ['Kategori', row.kategori], ['No. Izin Edar', row.nomor_izin_edar],
        ['Tgl Terbit', row.tgl_terbit ?? '-'], ['Tgl Expired', row.tgl_exp ?? '-'],
        ['Merk', row.merk], ['Jenis Produk', row.jenis_produk ?? '-'],
        ['Pendaftar', row.pendaftar], ['Alamat Pendaftar', row.alamat_pendaftar ?? '-'],
        ['Pabrik', row.pabrik ?? '-'], ['Alamat Pabrik', row.alamat_pabrik ?? '-'],
    ];
    if (row.sub_kategori) {
        fields.push(['Sub Kategori', row.sub_kategori]);
    }
    if (row.kelompok_produk) {
        fields.push(['Kelompok Produk', row.kelompok_produk]);
    }
    if (row.tipe) {
        fields.push(['Tipe', row.tipe]);
    }
    if (row.kelas) {
        fields.push(['Kelas', row.kelas]);
    }
    if (row.kelas_resiko) {
        fields.push(['Kelas Resiko', row.kelas_resiko]);
    }
    copyText(fields.map(([k, v]) => `${k}: ${v}`).join('\n'), 'Data disalin ke clipboard.');
}

// Copy ke AKL (info + pesan inline + auto-tutup sukses)
const copyOpen = ref(false);
const copyRow = ref(null);
const copyMsg = ref('');
const copyOk = ref(false);
const copying = ref(false);
function openCopy(row) {
    copyRow.value = row;
    copyMsg.value = '';
    copyOk.value = false;
    copyOpen.value = true;
}
async function doCopy() {
    copyMsg.value = '';
    copying.value = true;
    try {
        const res = await web.post('/akls/copy-from-izin', { izin_edar_id: copyRow.value.id }, { silent: true, block: true });
        copyMsg.value = res.data?.message ?? 'Berhasil dicopy ke AKL.';
        copyOk.value = true;
        toast.success(copyMsg.value);
        setTimeout(() => {
            copyOpen.value = false;
        }, 1200);
    } catch (e) {
        copyMsg.value = e.response?.data?.message ?? 'Gagal copy ke AKL.';
        copyOk.value = false;
    } finally {
        copying.value = false;
    }
}

// ── Sync (download via queue, samakan _sync.blade.php) ──
const STATUS_LABELS = {
    pending: 'Menunggu', downloading: 'Mengunduh', downloaded: 'Unduhan Selesai',
    failed: 'Gagal', idle: 'Tidak Aktif', stopped: 'Dihentikan', completed: 'Selesai', importing: 'Mengimpor',
};
const STATUS_COLORS = {
    pending: 'bg-slate-100 text-slate-600 border-slate-200',
    downloading: 'bg-sky-50 text-sky-700 border-sky-200',
    downloaded: 'bg-green-50 text-green-700 border-green-200',
    failed: 'bg-red-50 text-red-700 border-red-200',
    idle: 'bg-slate-100 text-slate-600 border-slate-200',
    stopped: 'bg-amber-50 text-amber-700 border-amber-200',
    completed: 'bg-green-50 text-green-700 border-green-200',
    importing: 'bg-violet-50 text-violet-700 border-violet-200',
};
function statusLabel(s) {
    return STATUS_LABELS[s] ?? s;
}
function statusClass(s) {
    return STATUS_COLORS[s] ?? STATUS_COLORS.idle;
}

const syncLog = ref(null);
const syncVisible = ref(false);
const syncStarting = ref(false);
let pollTimer = null;
let hideTimer = null;

const syncCats = computed(() => {
    const cats = syncLog.value?.categories ?? {};
    if (Array.isArray(cats)) {
        return cats.map((c) => ({ name: c.kategori ?? c.name, ...c }));
    }
    return Object.entries(cats).map(([name, c]) => ({ name, ...(c ?? {}) }));
});
const syncPct = computed(() => {
    const cats = syncCats.value;
    if (cats.length === 0) {
        return 0;
    }
    const done = cats.filter((c) => ['downloaded', 'failed'].includes(c.status)).length;
    return Math.round((done / cats.length) * 100);
});
const syncText = computed(() => {
    const cats = syncCats.value;
    const label = statusLabel(syncLog.value?.status ?? 'idle');
    if (cats.length === 0) {
        return label;
    }
    const done = cats.filter((c) => ['downloaded', 'failed'].includes(c.status)).length;
    return `${label} — ${done} / ${cats.length} file`;
});
const showReset = computed(() => {
    const st = syncLog.value?.status ?? 'idle';
    return syncLog.value?.is_running || syncLog.value?.can_stop
        || (syncLog.value?.is_stale && ['pending', 'downloading'].includes(st))
        || st === 'failed' || st === 'stopped';
});

async function loadProgress(silent = true) {
    try {
        const res = await api.get('/izin-edars/sync/progress', { silent });
        syncLog.value = res.data;
        const st = res.data?.status;
        if (['completed', 'failed', 'idle', 'stopped'].includes(st)) {
            stopPolling();
        }
        if (st === 'completed') {
            clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                syncVisible.value = false;
            }, 5000);
        }
    } catch {
        // abaikan
    }
}
function startPolling() {
    stopPolling();
    loadProgress(true);
    pollTimer = setInterval(() => loadProgress(true), 10000);
}
function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}
function syncBusyToast() {
    toast.warning('Download sedang berjalan. Gunakan Cancel / Reset di kartu Sync untuk memaksa berhenti.');
    syncVisible.value = true;
    startPolling();
}
async function triggerSync() {
    await loadProgress(true);
    if (syncLog.value?.is_running) {
        syncBusyToast();
        return;
    }
    syncStarting.value = true;
    try {
        const res = await api.post('/izin-edars/sync', {}, { silent: true, block: true });
        if (res.data?.success === false) {
            if (res.data?.can_stop) {
                syncBusyToast();
            } else {
                toast.error(res.data?.message ?? 'Gagal memulai sync.');
            }
            return;
        }
        syncVisible.value = true;
        startPolling();
    } catch (e) {
        if (e.response?.status === 409 && e.response?.data?.can_stop) {
            syncBusyToast();
        } else {
            toast.error(e.response?.data?.message ?? 'Gagal menghubungi server.');
        }
    } finally {
        syncStarting.value = false;
    }
}
async function stopSync() {
    const ok = await confirm({ title: 'Batalkan download?', message: 'Proses download yang berjalan dibatalkan.', confirmText: 'Ya, batalkan', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.post('/izin-edars/sync/stop', {}, { silent: true, block: true });
        stopPolling();
        toast.success('Download dihentikan.');
        query.fetch();
        loadProgress(true);
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghentikan sync.');
    }
}
async function resetSync() {
    const ok = await confirm({ title: 'Reset sync log?', message: 'Proses macet dibersihkan agar sync baru bisa mulai.', confirmText: 'Ya, reset', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/izin-edars/sync/reset', { silent: true, block: true });
        stopPolling();
        syncVisible.value = false;
        toast.success('Sync log direset.');
        query.fetch();
        loadProgress(true);
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mereset sync log.');
    }
}

// ── File Excel server ──
const files = ref([]);
const filesVisible = ref(false);
function fileSize(bytes) {
    if (!bytes) {
        return '-';
    }
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${units[i]}`;
}
async function checkFiles() {
    try {
        const res = await api.get('/izin-edars/sync/files', { silent: true, block: true });
        const raw = res.data?.files ?? [];
        files.value = Array.isArray(raw)
            ? raw
            : Object.entries(raw).map(([kategori, info]) => ({ kategori, ...(info ?? {}) }));
        filesVisible.value = true;
        if (!res.data?.has_any) {
            toast.warning(res.data?.message ?? 'Tidak ada file tersimpan.');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal cek file.');
    }
}
function downloadFile(kat) {
    window.open(`/api/izin-edars/files/${kat}/download`, '_blank');
}
async function deleteFile(kat) {
    const ok = await confirm({ title: `Hapus file ${kat}.xlsx?`, message: 'File di storage server dihapus.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.delete(`/izin-edars/files/${kat}`, { silent: true, block: true });
        toast.success(res.data?.message ?? 'File dihapus.');
        checkFiles();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal hapus file.');
    }
}

// ── Hapus data per kategori ──
async function deleteKategori(kat) {
    const ok = await confirm({ title: `Hapus data ${kat}?`, message: 'Seluruh baris kategori ini dihapus permanen.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.delete(`/izin-edars/kategori/${kat}`, { silent: true, block: true });
        toast.success(res.data?.message ?? `Data ${kat} dihapus.`);
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal hapus data.');
    }
}
async function deleteAll() {
    const ok = await confirm({ title: 'Hapus SEMUA data?', message: 'PKD + PKL + AKD + AKL dihapus permanen.', confirmText: 'Ya, hapus semua', tone: 'destructive' });
    if (!ok) {
        return;
    }
    let failed = 0;
    for (const kat of ['PKD', 'PKL', 'AKD', 'AKL']) {
        try {
            await api.delete(`/izin-edars/kategori/${kat}`, { silent: true, block: true });
        } catch {
            failed++;
        }
    }
    if (failed > 0) {
        toast.warning('Sebagian data gagal dihapus.');
    } else {
        toast.success('Semua data dihapus.');
    }
    query.fetch();
}

// ── Upload Excel (SheetJS, batch 500, samakan _upload.blade.php) ──
const HEADER_TRANSLATION = {
    NOMOR: 'nomor_izin_edar', 'TGL TERBIT': 'tgl_terbit', 'TGL EXP': 'tgl_exp', MERK: 'merk',
    'JENIS PRODUK': 'jenis_produk', PENDAFTAR: 'pendaftar', 'ALAMAT PENDAFTAR': 'alamat_pendaftar',
    PABRIK: 'pabrik', 'ALAMAT PABRIK': 'alamat_pabrik', 'SUB KATEGORI': 'sub_kategori',
    'KELOMPOK PRODUK': 'kelompok_produk', TIPE: 'tipe', KELAS: 'kelas', 'KELAS RESIKO': 'kelas_resiko', PABRIK2: 'pabrik2',
};
const uploadOpen = ref(false);
const uploadKategori = ref('');
const uploadFile = ref(null);
const uploadName = ref('');
const uploadSize = ref('');
const uploadPct = ref(0);
const uploadText = ref('');
const uploading = ref(false);
let importCancelled = false;
function openUpload() {
    uploadKategori.value = '';
    uploadFile.value = null;
    uploadName.value = '';
    uploadSize.value = '';
    uploadPct.value = 0;
    uploadText.value = '';
    uploading.value = false;
    importCancelled = false;
    uploadOpen.value = true;
}
function onPickFile() {
    const file = uploadFile.value?.files?.[0];
    if (!file) {
        return;
    }
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls'].includes(ext)) {
        toast.error('Format file tidak valid. Gunakan .xlsx atau .xls');
        uploadFile.value.value = '';
        uploadName.value = '';
        return;
    }
    if (file.size > 200 * 1024 * 1024) {
        toast.error('Ukuran file maksimal 200MB');
        uploadFile.value.value = '';
        uploadName.value = '';
        return;
    }
    uploadName.value = file.name;
    uploadSize.value = fileSize(file.size);
}
function onDrop(e) {
    e.preventDefault();
    const files = e.dataTransfer?.files;
    if (files?.length && uploadFile.value) {
        uploadFile.value.files = files;
        onPickFile();
    }
}
function formatISO(d) {
    if (!(d instanceof Date) || Number.isNaN(d.getTime())) {
        return null;
    }
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
function cancelUpload() {
    importCancelled = true;
}
async function doUpload() {
    if (!uploadKategori.value) {
        toast.warning('Pilih kategori dulu.');
        return;
    }
    const file = uploadFile.value?.files?.[0];
    if (!file) {
        toast.warning('Pilih file Excel dulu.');
        return;
    }
    await loadProgress(true);
    if (syncLog.value?.is_running) {
        uploadOpen.value = false;
        syncBusyToast();
        return;
    }
    importingReset();
    uploading.value = true;
    try {
        uploadText.value = 'Membaca file Excel di browser...';
        const buf = await file.arrayBuffer();
        if (importCancelled) {
            return importingReset();
        }
        uploadPct.value = 5;
        uploadText.value = 'Mem-parse file Excel...';
        const wb = XLSX.read(buf, { type: 'array', cellDates: true, raw: false });
        const ws = wb.Sheets[wb.SheetNames[0]];
        if (!ws) {
            throw new Error('Sheet tidak ditemukan dalam file Excel.');
        }
        uploadPct.value = 10;
        const allRows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: null, blankrows: false });
        if (allRows.length === 0) {
            throw new Error('File Excel kosong.');
        }
        let headerIdx = -1;
        let headerMap = {};
        for (let i = 0; i < Math.min(allRows.length, 100); i++) {
            const norm = (allRows[i] ?? []).map((h) => String(h ?? '').trim().toUpperCase());
            if (norm.includes('NOMOR') || norm.includes('MERK')) {
                headerIdx = i;
                norm.forEach((h, ci) => {
                    if (HEADER_TRANSLATION[h]) {
                        headerMap[ci] = HEADER_TRANSLATION[h];
                    }
                });
                break;
            }
        }
        if (headerIdx === -1) {
            throw new Error('Header tidak ditemukan. Pastikan ada kolom "NOMOR" atau "MERK".');
        }
        const dataRows = [];
        for (let i = headerIdx + 1; i < allRows.length; i++) {
            const row = allRows[i];
            if (!row || !row.some((v) => v !== null && v !== undefined && String(v).trim() !== '')) {
                continue;
            }
            const rec = {};
            Object.entries(headerMap).forEach(([ci, col]) => {
                let v = row[Number(ci)] ?? null;
                if (typeof v === 'string') {
                    v = v.trim() || null;
                }
                if (v instanceof Date) {
                    v = formatISO(v);
                }
                rec[col] = v;
            });
            if (rec.nomor_izin_edar && String(rec.nomor_izin_edar).trim() !== '') {
                dataRows.push(rec);
            }
        }
        if (dataRows.length === 0) {
            throw new Error('Tidak ada data valid setelah header.');
        }
        const BATCH = 500;
        const total = Math.ceil(dataRows.length / BATCH);
        let imported = 0;
        let failed = 0;
        uploadPct.value = 20;
        uploadText.value = `${dataRows.length} baris ditemukan. Mengirim ke server...`;
        for (let b = 0; b < total; b++) {
            if (importCancelled) {
                break;
            }
            uploadPct.value = 20 + Math.round((b / total) * 80);
            uploadText.value = `Batch ${b + 1}/${total} — ${imported} / ${dataRows.length} baris`;
            try {
                const res = await api.post('/izin-edars/import-batch', { kategori: uploadKategori.value, rows: dataRows.slice(b * BATCH, (b + 1) * BATCH) }, { silent: true, block: true });
                if (res.data?.success) {
                    imported += res.data?.imported ?? 0;
                } else {
                    failed++;
                }
            } catch {
                failed++;
            }
        }
        if (importCancelled) {
            toast.warning('Import dibatalkan.');
            return importingReset();
        }
        uploadPct.value = 100;
        uploadText.value = `Selesai! ${imported} baris diimport.`;
        if (failed > 0) {
            toast.warning(`${imported} baris diimport (${failed} batch gagal).`);
        } else {
            toast.success(`${imported} baris diimport (${uploadKategori.value}).`);
        }
        query.fetch();
        setTimeout(() => {
            uploadOpen.value = false;
        }, 1000);
    } catch (e) {
        toast.error(e.message ?? 'Gagal memproses file.');
        importingReset();
    } finally {
        uploading.value = false;
    }
}
function importingReset() {
    uploadPct.value = 0;
    uploadText.value = '';
}

onBeforeUnmount(() => {
    stopPolling();
    clearTimeout(hideTimer);
});
query.fetch();
loadProgress(true).then(() => {
    if (syncLog.value?.is_running) {
        syncVisible.value = true;
        startPolling();
    }
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Data izin edar produk + sync Kemenkes">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['kategori', 'nomor', 'terbit', 'exp', 'merk', 'jenis', 'pendaftar', 'pabrik'], 'izin-edar')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="checkFiles"><Download /> Cek File</Button>
                <Button variant="outline" size="sm" @click="openUpload"><Upload /> Upload File</Button>
                <Button variant="outline" size="sm" @click="() => { syncVisible = !syncVisible; loadProgress(true); }">Sync Data</Button>
                <Button variant="destructive" size="sm" @click="deleteAll"><Trash2 /> Hapus Semua</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <Card v-if="syncVisible" class="p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <b class="text-sm">Sync Progress</b>
                    <span v-if="syncLog" :class="`inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium ${statusClass(syncLog.status)}`">{{ statusLabel(syncLog.status) }}</span>
                    <span class="ml-auto flex gap-1">
                        <Button v-if="syncLog?.is_running || syncLog?.can_stop" variant="outline" size="sm" @click="stopSync">Cancel</Button>
                        <Button v-if="showReset" variant="outline" size="sm" @click="resetSync">Reset</Button>
                        <Button v-if="!syncLog?.is_running" size="sm" :disabled="syncStarting" @click="triggerSync">Mulai Sync</Button>
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-muted-foreground">
                    <span>{{ syncText }}</span>
                    <b>{{ syncPct }}%</b>
                </div>
                <div class="mt-1 h-5 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="flex h-full items-center justify-center text-[11px] font-semibold text-white transition-all"
                        :class="syncLog?.status === 'downloading' ? 'bg-primary' : 'bg-primary/70'"
                        :style="{ width: `${syncPct}%` }"
                    >
                        {{ syncPct }}%
                    </div>
                </div>
                <div v-if="syncCats.length" class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="c in syncCats" :key="c.name" class="rounded-xl border bg-white p-3 text-center shadow-sm">
                        <p class="text-sm font-bold">{{ c.name }}</p>
                        <span :class="`mb-1 inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium ${statusClass(c.status)}`">{{ statusLabel(c.status) }}</span>
                        <p v-if="c.size" class="text-xs text-muted-foreground">{{ fileSize(c.size) }}</p>
                        <p v-if="c.error" class="text-xs text-red-600">{{ String(c.error).substring(0, 80) }}</p>
                    </div>
                </div>
                <p v-if="syncLog?.error || syncLog?.message" class="mt-2 text-xs text-muted-foreground">{{ syncLog.error || syncLog.message }}</p>
            </Card>

            <Card v-if="filesVisible" class="p-4">
                <div class="flex items-center justify-between">
                    <b class="text-sm">File Excel Tersimpan</b>
                    <Button variant="ghost" size="sm" @click="filesVisible = false"><X /></Button>
                </div>
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="fl in files" :key="fl.kategori" class="rounded-xl border bg-white p-3 text-center shadow-sm">
                        <span :class="`inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-bold ${katClass(fl.kategori)}`">{{ fl.kategori }}</span>
                        <CheckCircle2 v-if="fl.exists" class="mx-auto my-2 size-8 text-green-600" />
                        <XCircle v-else class="mx-auto my-2 size-8 text-red-500" />
                        <p class="text-xs font-bold" :class="fl.exists ? 'text-green-700' : 'text-red-600'">{{ fl.exists ? (fl.size_human ?? fileSize(fl.size)) : 'Tidak ada' }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ fl.file ?? '' }}</p>
                        <div v-if="fl.exists" class="mt-2 flex justify-center gap-1">
                            <Button variant="outline" size="sm" title="Download file" @click="downloadFile(fl.kategori)"><Download /></Button>
                            <Button variant="outline" size="sm" title="Hapus file" @click="deleteFile(fl.kategori)"><Trash2 /></Button>
                        </div>
                    </div>
                </div>
            </Card>

            <FilterPanel title="Filter Izin Edar" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari nomor izin, merk, pendaftar..." @input="onSearchInput" />
                </FormField>
                <FormField label="Kategori">
                    <SearchableSelect :model-value="kategoriBox" :options="kategoriOptions" placeholder="Semua Kategori" @update:model-value="onKategori" />
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
                empty-title="Izin edar tidak ditemukan"
                empty-message="Ubah filter atau kata kunci."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @row-click="openDetail"
            >
                <template #cell-kategori="{ row }"><span :class="`inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-bold ${katClass(row.kategori)}`">{{ row.kategori }}</span></template>
                <template #cell-nomor_izin_edar="{ row }"><b>{{ row.nomor_izin_edar }}</b></template>
                <template #cell-tgl_terbit="{ row }">{{ row.tgl_terbit ?? '-' }}</template>
                <template #cell-tgl_exp="{ row }"><span :class="expClass(row.tgl_exp)">{{ row.tgl_exp ?? '-' }}</span></template>
                <template #cell-merk="{ row }">{{ row.merk ?? '-' }}</template>
                <template #cell-jenis_produk="{ row }">{{ row.jenis_produk ?? '-' }}</template>
                <template #cell-pendaftar="{ row }">{{ row.pendaftar ?? '-' }}</template>
                <template #cell-pabrik="{ row }">{{ row.pabrik ?? '-' }}</template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1" @click.stop>
                        <Button variant="ghost" size="sm" title="Detail" @click="openDetail(row)"><Eye /></Button>
                        <Button variant="ghost" size="sm" title="Copy ke AKL" @click="openCopy(row)"><Share /></Button>
                        <Button variant="ghost" size="sm" title="Copy text" @click="copyRowFull(row)"><Copy /></Button>
                    </div>
                </template>
            </DataTable>

            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-muted-foreground">Hapus per kategori:</span>
                <Button v-for="k in kategoriList" :key="k" variant="outline" size="sm" @click="deleteKategori(k)"><Trash2 /> {{ k }}</Button>
            </div>
        </div>

        <AppModal v-model:open="detailOpen" :title="`Izin Edar ${detail?.nomor_izin_edar ?? ''}`" size="lg">
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 8" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                <p v-for="[k, v] in detailPairs()" :key="k"><b>{{ k }}:</b> {{ v ?? '-' }}</p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
                <Button variant="outline" @click="() => { detailOpen = false; openCopy(detail); }"><Share /> Copy ke AKL</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="copyOpen" title="Copy ke AKL" size="md">
            <div class="rounded-md border border-sky-200 bg-sky-50 p-2 text-xs">
                Data ini akan dibuat sebagai <b>baris baru di tabel AKL</b> (tanpa lampiran).
                Kunci duplikat: <b>Reg No + Tgl Expired</b> — kalau pasangan itu sudah ada di AKL, copy ditolak.
            </div>
            <div class="mt-2 space-y-1 text-sm">
                <p><b>Reg No:</b> {{ copyRow?.nomor_izin_edar }}</p>
                <p><b>Nama:</b> {{ copyRow?.merk ?? copyRow?.jenis_produk }}</p>
                <p><b>Vendor:</b> {{ copyRow?.pendaftar ?? copyRow?.pabrik }}</p>
                <p><b>Berlaku Dari:</b> {{ copyRow?.tgl_terbit ?? '-' }}</p>
                <p><b>Expired:</b> {{ copyRow?.tgl_exp ?? '-' }}</p>
            </div>
            <p v-if="copyMsg" class="mt-2 rounded p-2 text-xs" :class="copyOk ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">{{ copyMsg }}</p>
            <template #footer>
                <Button variant="ghost" @click="copyOpen = false">Batal</Button>
                <Button :disabled="copying" @click="doCopy">Simpan ke AKL</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="uploadOpen" title="Upload File Excel" size="md">
            <div class="space-y-3">
                <FormField label="Kategori" required>
                    <div class="flex flex-wrap gap-2">
                        <Button v-for="k in ['AKD', 'AKL', 'PKD', 'PKL']" :key="k" :variant="uploadKategori === k ? 'default' : 'outline'" size="sm" @click="uploadKategori = k">{{ k }}</Button>
                    </div>
                </FormField>
                <FormField label="File (.xlsx/.xls, maks 200MB)" required>
                    <div
                        class="cursor-pointer rounded-lg border-2 border-dashed p-6 text-center text-sm"
                        :class="uploadName ? 'border-green-300 bg-green-50/50' : 'border-slate-200 hover:border-slate-300'"
                        @click="uploadFile?.click()"
                        @dragover.prevent
                        @drop="onDrop"
                    >
                        <input ref="uploadFile" type="file" accept=".xlsx,.xls" class="hidden" @change="onPickFile" @click.stop />
                        <Upload class="mx-auto mb-1 size-6 text-muted-foreground" />
                        <p v-if="uploadName"><b>{{ uploadName }}</b> ({{ uploadSize }})</p>
                        <p v-else class="text-muted-foreground">Klik atau seret file ke sini</p>
                    </div>
                </FormField>
                <div v-if="uploading || uploadPct > 0" class="space-y-1">
                    <div class="h-2 overflow-hidden rounded bg-slate-100">
                        <div class="h-full bg-primary transition-all" :style="{ width: `${uploadPct}%` }" />
                    </div>
                    <p class="text-xs text-muted-foreground">{{ uploadPct }}% — {{ uploadText }}</p>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="uploadOpen = false">Tutup</Button>
                <Button v-if="uploading" variant="outline" @click="cancelUpload">Batalkan</Button>
                <Button :disabled="uploading || !uploadKategori || !uploadName" @click="doUpload"><Upload /> Parse &amp; Import</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
