<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Bell, Building2, CircleCheck, CircleX, Cog, Copy, Download, Eye, EyeOff, FileText, History, KeyRound, Laptop, RefreshCw, Save, Search, Server, SlidersHorizontal, Trash2, User, Wifi } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyText } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'App Setting' },
    session: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();

const odooEnv = ref(props.session?.session_id ?? '');
const sessionName = ref(props.session?.name && props.session?.uid ? `${props.session.name} (${props.session.uid})` : '');
const sessionUsername = ref(props.session?.username ?? '');
const sessionBusy = ref(false);
const showSessionId = ref(false);

const resource = ref(null);
const resourceLoading = ref(false);
const resourceError = ref('');

const deviceSearchBox = ref('');
const devicePage = ref(1);
const devicePerPage = ref(10);
const deviceRows = ref([]);
const deviceTotal = ref(0);
const deviceLoading = ref(false);
const deviceError = ref('');
let deviceTimer = null;

const detailOpen = ref(false);
const detailRow = ref(null);
const testingToken = ref('');

const cekOpen = ref(false);
const cekBusy = ref(false);
const cekResult = ref(null);

const logTab = ref('readable');
const logFile = ref(null);
const logMeta = ref(null);
const logTruncated = ref(false);
const logLevels = ref({});
const logEntries = ref([]);
const logRawTail = ref('');
const logPage = ref(1);
const logPerPage = ref(25);
const logTotal = ref(0);
const logTotalPages = ref(1);
const logLoading = ref(false);
const logError = ref('');
const logDetailOpen = ref(false);
const logSearchBox = ref('');
const logLevel = ref('ALL');
const expandedEntry = ref(null);
let logTimer = null;

const currentToken = (localStorage.getItem('fcm_token') ?? '').trim();

const deviceTotalPages = computed(() => Math.max(1, Math.ceil(deviceTotal.value / devicePerPage.value)));

const deviceColumns = [
    { key: 'platform', label: 'Platform' },
    { key: 'user_agent', label: 'User Agent' },
    { key: 'ip', label: 'IP', align: 'center' },
    { key: 'token', label: 'Token' },
    { key: 'last_status', label: 'Last Status', align: 'center' },
];

const logFiles = computed(() => resource.value?.logs?.files ?? []);
const logsTotalParse = computed(() => resource.value?.logs?.parse ?? '-');

const logColumns = [
    { key: 'name', label: 'File', mono: true },
    { key: 'size', label: 'Ukuran', align: 'right' },
    { key: 'modified', label: 'Diubah' },
    { key: 'lines', label: 'Baris', align: 'center' },
];

const cekOk = computed(() => cekResult.value?.ok ?? null);
const cekUser = computed(() => cekResult.value?.user ?? null);
const cekSession = computed(() => cekResult.value?.session ?? null);

const maskedSession = computed(() => {
    const s = odooEnv.value ?? '';
    if (showSessionId.value) {
        return s;
    }
    if (s.length <= 12) {
        return s ? '•'.repeat(Math.min(s.length, 24)) : '-';
    }
    return `${s.slice(0, 6)}…${s.slice(-4)}`;
});

const userInitial = computed(() => {
    const name = cekUser.value?.display_name || cekUser.value?.name || sessionName.value || '?';
    return String(name).trim().charAt(0).toUpperCase() || '?';
});

function short(text, len) {
    const s = String(text ?? '');
    return s.length > len ? `${s.slice(0, len)}...` : s;
}

function levelBadge(level) {
    const lv = String(level ?? '').toUpperCase();
    if (lv === 'ERROR' || lv === 'CRITICAL' || lv === 'ALERT' || lv === 'EMERGENCY') {
        return 'border-red-200 bg-red-50 text-red-700';
    }
    if (lv === 'WARNING') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }
    if (lv === 'INFO' || lv === 'NOTICE') {
        return 'border-blue-200 bg-blue-50 text-blue-700';
    }
    return 'border-slate-200 bg-slate-100 text-slate-600';
}

function isCurrentDevice(row) {
    return currentToken !== '' && String(row.token ?? '').trim() === currentToken;
}

const ID_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function formatDateTime(value) {
    const m = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}:\d{2}(:\d{2})?)/);
    if (!m) {
        return String(value ?? '-') || '-';
    }
    const month = ID_MONTHS[Number(m[2]) - 1] ?? m[2];
    return `${m[3]} ${month} ${m[1]} ${m[4]}`;
}

const sessionMatchesProfile = computed(() => {
    if (!cekSession.value || !cekUser.value) {
        return null;
    }
    return Number(cekSession.value.uid) === Number(cekUser.value.id);
});

async function copyCekEmail() {
    await copyText(cekUser.value?.email ?? '', 'Email disalin ke clipboard.');
}

async function copyCekSession() {
    await copyText(cekSession.value?.session_id ?? '', 'Session ID profil disalin ke clipboard.');
}

async function refreshSession(quiet = false) {
    sessionBusy.value = true;
    try {
        const res = await api.get('/settings', { silent: true });
        const d = res.data?.data ?? {};
        odooEnv.value = d.session_id ?? '';
        sessionUsername.value = d.username ?? '';
        sessionName.value = d.name && d.uid ? `${d.name} (${d.uid})` : '';
        if (!quiet) {
            toast.success('Session dimuat ulang.');
        }
    } catch (e) {
        if (!quiet) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat session.');
        }
    } finally {
        sessionBusy.value = false;
    }
}

async function saveSession() {
    if (!odooEnv.value.trim()) {
        toast.warning('Session ID wajib diisi.');
        return;
    }
    sessionBusy.value = true;
    try {
        const res = await api.post('/settings', { env_value: odooEnv.value });
        toast.success(res.data?.message ?? 'Session tersimpan.');
        refreshSession(true);
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan session.');
    } finally {
        sessionBusy.value = false;
    }
}

async function fixSession() {
    sessionBusy.value = true;
    try {
        await api.put('/settings');
        toast.success('Login ulang Odoo berhasil.');
        refreshSession();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memperbaiki session.');
    } finally {
        sessionBusy.value = false;
    }
}

async function testNotif() {
    sessionBusy.value = true;
    try {
        const res = await api.delete('/settings');
        toast.success(res.data?.message ?? 'Notifikasi terkirim.');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengirim notifikasi.');
    } finally {
        sessionBusy.value = false;
    }
}

async function cekOdoo() {
    cekBusy.value = true;
    try {
        const res = await api.get('/settings/cek-odoo', { silent: true });
        cekResult.value = res.data?.data ?? null;
        if (cekResult.value?.ok) {
            toast.success(`Terkoneksi ke Odoo (${cekResult.value.latency_ms ?? '-'} ms).`);
        } else {
            toast.error(cekResult.value?.error ?? 'Koneksi Odoo gagal, lihat detail.');
        }
    } catch (e) {
        cekResult.value = e.response?.data?.data ?? { ok: false, error: e.response?.data?.message ?? 'Gagal mengecek Odoo.' };
        toast.error('Gagal mengecek Odoo, lihat detail.');
    } finally {
        cekBusy.value = false;
        cekOpen.value = true;
    }
}

async function copySessionId() {
    await copyText(odooEnv.value, 'Session ID disalin ke clipboard.');
}

async function logout() {
    const ok = await confirm({
        title: 'Keluar dari server?',
        message: 'Sesi login server akan diakhiri.',
        confirmText: 'Ya, keluar',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        await api.post('/auth/logout');
        router.visit(route('index'));
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal logout.');
    }
}

async function loadResource() {
    resourceLoading.value = true;
    resourceError.value = '';
    try {
        const res = await api.get('/resources', { silent: true });
        resource.value = res.data?.data ?? null;
    } catch (e) {
        resourceError.value = 'Gagal mengambil data resource.';
    } finally {
        resourceLoading.value = false;
    }
}

async function fetchLogDetail(name, page = 1) {
    logLoading.value = true;
    logError.value = '';
    try {
        const res = await api.get(`/resources/logs/${encodeURIComponent(name)}`, {
            silent: true,
            params: { page, per_page: logPerPage.value, search: logSearchBox.value, level: logLevel.value },
        });
        const d = res.data?.data ?? {};
        logFile.value = d.file?.name ?? name;
        logMeta.value = d.file ?? null;
        logTruncated.value = !!d.truncated;
        logLevels.value = d.levels ?? {};
        logEntries.value = d.entries ?? [];
        logRawTail.value = d.raw_tail ?? '';
        logPage.value = d.pagination?.page ?? 1;
        logTotal.value = Number(d.pagination?.total ?? 0);
        logTotalPages.value = Number(d.pagination?.total_pages ?? 1);
        expandedEntry.value = null;
    } catch (e) {
        logEntries.value = [];
        logError.value = e.response?.data?.message ?? 'Gagal memuat isi log.';
    } finally {
        logLoading.value = false;
    }
}

function openLogDetail(name) {
    logTab.value = 'readable';
    logSearchBox.value = '';
    logLevel.value = 'ALL';
    logDetailOpen.value = true;
    fetchLogDetail(name, 1);
}

function onLogSearch(e) {
    logSearchBox.value = e.target.value;
    clearTimeout(logTimer);
    logTimer = setTimeout(() => fetchLogDetail(logFile.value, 1), 1000);
}

function setLogLevel(level) {
    if (logLevel.value === level) {
        return;
    }
    logLevel.value = level;
    fetchLogDetail(logFile.value, 1);
}

function toggleEntry(id) {
    expandedEntry.value = expandedEntry.value === id ? null : id;
}

async function clearLogFile(name) {
    const ok = await confirm({
        title: `Kosongkan ${name}?`,
        message: 'Seluruh isi file log akan dihapus dan tidak bisa dikembalikan.',
        confirmText: 'Ya, kosongkan',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/resources/logs/${encodeURIComponent(name)}/clear`);
        toast.success(res.data?.message ?? 'Log dikosongkan.');
        loadResource();
        if (logDetailOpen.value && logFile.value === name) {
            fetchLogDetail(name, 1);
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengosongkan log.');
    }
}

async function deleteLogFile(name) {
    const ok = await confirm({
        title: `Hapus ${name}?`,
        message: 'File log akan dihapus dari server. File baru dibuat otomatis saat aplikasi menulis log lagi.',
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        const res = await api.delete(`/resources/logs/${encodeURIComponent(name)}`);
        toast.success(res.data?.message ?? 'Log dihapus.');
        if (logDetailOpen.value && logFile.value === name) {
            logDetailOpen.value = false;
        }
        loadResource();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus log.');
    }
}

async function copyRawLog() {
    await copyText(logRawTail.value, 'Isi log disalin ke clipboard.');
}

function downloadRawLog() {
    if (!logRawTail.value) {
        toast.warning('Tidak ada isi log untuk diunduh.');
        return;
    }
    const blob = new Blob([logRawTail.value], { type: 'text/plain;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = logFile.value ?? 'log.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toast.success(`File ${logFile.value} diunduh.`);
}

async function fetchDevices() {
    deviceLoading.value = true;
    deviceError.value = '';
    try {
        const res = await api.get('/tokens', {
            silent: true,
            params: {
                draw: devicePage.value,
                start: (devicePage.value - 1) * devicePerPage.value,
                length: devicePerPage.value,
                'search[value]': deviceSearchBox.value,
            },
        });
        deviceRows.value = res.data?.data ?? [];
        deviceTotal.value = Number(res.data?.recordsFiltered ?? 0);
    } catch (e) {
        deviceRows.value = [];
        deviceError.value = e.response?.data?.message ?? 'Gagal memuat device.';
    } finally {
        deviceLoading.value = false;
    }
}

function onDeviceSearch(e) {
    deviceSearchBox.value = e.target.value;
    clearTimeout(deviceTimer);
    deviceTimer = setTimeout(() => {
        devicePage.value = 1;
        fetchDevices();
    }, 1000);
}

async function testDevice(row) {
    if (!row.token) {
        toast.warning('Token device kosong.');
        return;
    }
    const ok = await confirm({
        title: 'Kirim notifikasi tes?',
        message: `Notifikasi tes dikirim ke device ${row.platform ?? ''}.`,
        confirmText: 'Ya, kirim',
    });
    if (!ok) {
        return;
    }
    testingToken.value = row.token;
    try {
        const res = await api.post('/tokens/test', { token: row.token });
        toast.success(res.data?.message ?? 'Notifikasi terkirim.');
        fetchDevices();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengirim notifikasi.');
        fetchDevices();
    } finally {
        testingToken.value = '';
    }
}

function openDetail(row) {
    detailRow.value = row;
    detailOpen.value = true;
}

refreshSession();
loadResource();
fetchDevices();
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Kelola session Odoo, resource, device, dan log" />

        <div class="grid gap-4 lg:grid-cols-[8fr_4fr]">
            <Card class="p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-sm font-semibold"><Cog class="size-4 text-primary" /> Odoo Session</h2>
                    <div class="flex flex-wrap items-center gap-1">
                        <span v-if="cekOk === true" class="inline-flex items-center gap-1 rounded-md border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                            <CircleCheck class="size-3.5" /> Terkoneksi
                        </span>
                        <span v-else-if="cekOk === false" class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700">
                            <CircleX class="size-3.5" /> Gagal
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            Belum dicek
                        </span>
                        <Button variant="outline" size="sm" :disabled="sessionBusy" @click="refreshSession"><RefreshCw /> Refresh</Button>
                        <Button variant="outline" size="sm" :disabled="sessionBusy" @click="fixSession">Fix Session</Button>
                    </div>
                </div>

                <div v-if="cekUser" class="mb-3 flex flex-wrap items-center gap-3 rounded-lg border border-green-200 bg-green-50/60 p-3">
                    <span class="flex size-11 items-center justify-center rounded-full bg-green-600 text-lg font-bold text-white">{{ userInitial }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ cekUser.display_name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ cekUser.email }}</p>
                        <div class="mt-1 flex flex-wrap gap-1">
                            <span class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-1.5 py-px text-[11px] font-medium text-blue-700">{{ cekUser.company_name }}</span>
                            <span class="inline-flex items-center rounded-md border border-slate-200 bg-white px-1.5 py-px text-[11px] text-slate-600">UID {{ cekUser.id }}</span>
                            <span class="inline-flex items-center rounded-md border border-slate-200 bg-white px-1.5 py-px text-[11px] text-slate-600">{{ cekUser.lang }} • {{ cekUser.tz }}</span>
                            <span class="inline-flex items-center rounded-md border border-slate-200 bg-white px-1.5 py-px text-[11px] text-slate-600">{{ cekResult.latency_ms }} ms</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <FormField label="Session Name">
                        <Input :model-value="sessionName" disabled />
                    </FormField>
                    <FormField label="Username">
                        <Input :model-value="sessionUsername" disabled />
                    </FormField>
                </div>
                <FormField label="Session ID" required class="mt-3" hint="Klik ikon mata untuk menampilkan, tombol salin untuk menyalin penuh.">
                    <div class="flex gap-2">
                        <Textarea v-model="odooEnv" rows="2" class="font-mono text-xs" placeholder="Tempel session_id Odoo di sini" />
                    </div>
                    <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                        <span class="font-mono">{{ maskedSession }}</span>
                        <button type="button" class="inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 hover:bg-accent" @click="showSessionId = !showSessionId">
                            <Eye v-if="!showSessionId" class="size-3.5" /><EyeOff v-else class="size-3.5" /> {{ showSessionId ? 'Sembunyikan' : 'Tampilkan' }}
                        </button>
                        <button type="button" class="inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 hover:bg-accent" @click="copySessionId">
                            <Copy class="size-3.5" /> Salin
                        </button>
                    </p>
                </FormField>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Button size="sm" :disabled="sessionBusy" @click="saveSession"><Save /> Simpan</Button>
                    <Button variant="outline" size="sm" :disabled="sessionBusy" @click="testNotif"><Bell /> Tes Notif</Button>
                    <Button variant="outline" size="sm" :disabled="cekBusy" @click="cekOdoo"><Wifi /> {{ cekBusy ? 'Mengecek...' : 'Cek Odoo' }}</Button>
                    <Button variant="outline" size="sm" @click="logout">Logout</Button>
                    <Button variant="ghost" size="sm" as="a" :href="route('index')">Kembali</Button>
                </div>
            </Card>

            <Card class="p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-sm font-semibold"><Server class="size-4 text-blue-500" /> Resource</h2>
                    <Button variant="outline" size="sm" :disabled="resourceLoading" @click="loadResource"><RefreshCw /> Refresh</Button>
                </div>
                <div v-if="resourceLoading" class="space-y-2">
                    <div v-for="n in 3" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
                </div>
                <p v-else-if="resourceError" class="text-sm text-red-600">{{ resourceError }}</p>
                <div v-else-if="resource" class="space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span>Products (S3)</span>
                        <span class="font-medium">{{ resource.products?.error ?? `${Number(resource.products?.files ?? 0).toLocaleString('id-ID')}${resource.products?.truncated ? '+' : ''} files • ${resource.products?.value ?? 0} bytes` }}</span>
                        <span class="rounded-md border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ resource.products?.parse }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span>Products (lokal)</span>
                        <span class="font-medium">{{ resource.products_local?.value ?? 0 }} bytes</span>
                        <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ resource.products_local?.parse }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span>Logs ({{ resource.logs?.count ?? 0 }} file)</span>
                        <span class="font-medium">{{ resource.logs?.value ?? 0 }} bytes</span>
                        <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ logsTotalParse }}</span>
                    </div>
                </div>
            </Card>
        </div>

        <Card class="mt-4 p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="flex items-center gap-2 text-sm font-semibold">
                    <FileText class="size-4 text-slate-500" /> File Log
                    <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ logFiles.length }} file • {{ logsTotalParse }}</span>
                </h2>
                <Button variant="outline" size="sm" :disabled="resourceLoading" @click="loadResource"><RefreshCw /> Refresh</Button>
            </div>
            <DataTable
                :columns="logColumns"
                :rows="logFiles"
                :loading="resourceLoading"
                :error="resourceError"
                :page="1"
                :total-pages="1"
                :total="logFiles.length"
                :per-page="Math.max(logFiles.length, 1)"
                :show-footer="false"
                row-key="name"
                empty-title="Tidak ada file log"
                empty-message="Folder storage/logs kosong."
            >
                <template #cell-name="{ row }">
                    <span class="inline-flex items-center gap-1.5 font-mono text-xs"><FileText class="size-3.5 text-slate-400" />{{ row.name }}</span>
                </template>
                <template #cell-size="{ row }">{{ row.parse }} <span class="text-xs text-muted-foreground">({{ Number(row.size).toLocaleString('id-ID') }} B)</span></template>
                <template #cell-lines="{ row }">{{ row.lines ?? '-' }}</template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="outline" size="sm" title="Lihat detail log" @click="openLogDetail(row.name)">
                            <Search />
                        </Button>
                        <Button variant="outline" size="sm" title="Kosongkan isi log" @click="clearLogFile(row.name)">
                            <History />
                        </Button>
                        <Button variant="outline" size="sm" title="Hapus file log" @click="deleteLogFile(row.name)">
                            <Trash2 />
                        </Button>
                    </div>
                </template>
            </DataTable>
            <p class="mt-2 text-xs text-muted-foreground">Mendukung rotasi harian (mis. laravel-2026-10-09.log). Klik ikon kaca pembesar untuk melihat isi readable dan mentah.</p>
        </Card>

        <div class="mt-4">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <h2 class="flex items-center gap-2 text-sm font-semibold"><Laptop class="size-4 text-amber-500" /> List Device</h2>
                <Input v-model="deviceSearchBox" type="search" placeholder="Cari device..." class="max-w-xs" @input="onDeviceSearch" />
                <Button variant="outline" size="sm" @click="fetchDevices"><RefreshCw /> Refresh</Button>
            </div>
            <DataTable
                :columns="deviceColumns"
                :rows="deviceRows"
                :loading="deviceLoading"
                :error="deviceError"
                :page="devicePage"
                :total-pages="deviceTotalPages"
                :total="deviceTotal"
                :per-page="devicePerPage"
                :per-page-options="[10, 25, 50, 100]"
                empty-title="Device tidak ditemukan"
                @update:page="devicePage = $event; fetchDevices()"
                @update:per-page="devicePerPage = Number($event); devicePage = 1; fetchDevices()"
            >
                <template #cell-platform="{ row }">
                    <span class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                        {{ row.platform || '-' }}
                    </span>
                </template>
                <template #cell-user_agent="{ row }">
                    <span :title="row.user_agent">{{ short(row.user_agent, 40) || '-' }}</span>
                </template>
                <template #cell-ip="{ row }">{{ row.ip || '-' }}</template>
                <template #cell-token="{ row }">
                    <span v-if="isCurrentDevice(row)" class="mr-1 inline-flex items-center rounded-md border border-green-200 bg-green-50 px-1.5 py-px text-[11px] font-medium text-green-700">Device Ini</span>
                    <span class="font-mono text-xs" :title="row.token">{{ short(row.token, 30) }}</span>
                </template>
                <template #cell-last_status="{ row }">
                    <span :title="row.last_status_at">{{ row.last_status || '-' }}</span>
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="outline" size="sm" title="Tes notifikasi ke device ini" :disabled="testingToken === row.token" @click="testDevice(row)">
                            <Bell />
                        </Button>
                        <Button variant="outline" size="sm" title="Detail" @click="openDetail(row)"><History /></Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <AppModal v-model:open="detailOpen" title="Detail Device" size="lg">
            <div v-if="detailRow" class="space-y-2 text-sm">
                <p><b>Platform:</b> {{ detailRow.platform || '-' }}</p>
                <p><b>IP:</b> {{ detailRow.ip || '-' }}</p>
                <p><b>User Agent:</b> {{ detailRow.user_agent || '-' }}</p>
                <p><b>Token:</b> <span class="break-all font-mono text-xs">{{ detailRow.token || '-' }}</span>
                    <span v-if="isCurrentDevice(detailRow)" class="ml-1 inline-flex items-center rounded-md border border-green-200 bg-green-50 px-1.5 py-px text-[11px] font-medium text-green-700">Device Ini</span>
                </p>
                <p><b>Last Status:</b> {{ detailRow.last_status || '-' }}</p>
                <p><b>Last Status At:</b> {{ detailRow.last_status_at || '-' }}</p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="cekOpen" title="Status Koneksi Odoo" size="xl">
            <div v-if="cekResult" class="space-y-3">
                <div
                    class="flex flex-wrap items-center gap-2 rounded-lg border p-3 text-sm"
                    :class="cekResult.ok ? 'border-green-200 bg-green-50/70' : 'border-red-200 bg-red-50/70'"
                >
                    <CircleCheck v-if="cekResult.ok" class="size-5 text-green-600" />
                    <CircleX v-else class="size-5 text-red-600" />
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">{{ cekResult.ok ? 'Terkoneksi ke Odoo dan session valid' : 'Koneksi Odoo gagal atau session tidak valid' }}</p>
                        <p class="text-xs text-muted-foreground">Dicek {{ formatDateTime(cekResult.checked_at) }} • respons {{ cekResult.latency_ms ?? '-' }} ms</p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium"
                        :class="(cekResult.latency_ms ?? 9999) < 1000 ? 'border-green-200 bg-white text-green-700' : 'border-amber-200 bg-white text-amber-700'"
                    >
                        {{ cekResult.latency_ms ?? '-' }} ms
                    </span>
                </div>

                <p v-if="!cekResult.ok && cekResult.error" class="rounded-lg border border-red-200 bg-white p-3 text-sm text-red-700">{{ cekResult.error }}</p>

                <div v-if="cekUser" class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3 rounded-lg border border-green-200 bg-green-50/60 p-3">
                        <span class="flex size-11 items-center justify-center rounded-full bg-green-600 text-lg font-bold text-white">{{ userInitial }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ cekUser.display_name }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ cekUser.email }}</p>
                        </div>
                        <span v-if="sessionMatchesProfile === true" class="inline-flex items-center gap-1 rounded-md border border-green-200 bg-white px-2 py-0.5 text-xs font-medium text-green-700">
                            <CircleCheck class="size-3.5" /> Session cocok dengan profil
                        </span>
                        <span v-else-if="sessionMatchesProfile === false" class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-white px-2 py-0.5 text-xs font-medium text-amber-700">
                            <CircleX class="size-3.5" /> UID session beda dengan profil
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border p-3">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500"><User class="size-3.5" /> Pengguna</p>
                            <dl class="space-y-1.5 text-sm">
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Nama</dt><dd class="font-medium">{{ cekUser.name }}</dd></div>
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="text-muted-foreground">Email</dt>
                                    <dd class="flex min-w-0 items-center gap-1 font-medium">
                                        <span class="truncate">{{ cekUser.email }}</span>
                                        <button type="button" class="shrink-0 rounded-md border px-1 py-0.5 hover:bg-accent" title="Salin email" @click="copyCekEmail"><Copy class="size-3" /></button>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">User ID</dt><dd class="font-medium">{{ cekUser.id }}</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Avatar</dt><dd class="font-medium">{{ cekUser.has_avatar ? 'Ada' : 'Tidak ada' }}</dd></div>
                            </dl>
                        </div>
                        <div class="rounded-lg border p-3">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500"><Building2 class="size-3.5" /> Perusahaan</p>
                            <dl class="space-y-1.5 text-sm">
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Nama</dt><dd class="text-right font-medium">{{ cekUser.company_name }}</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">ID perusahaan</dt><dd class="font-medium">{{ cekUser.company_id ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Database</dt><dd class="font-mono text-xs font-medium">{{ cekSession?.db }}</dd></div>
                            </dl>
                        </div>
                        <div class="rounded-lg border p-3">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500"><KeyRound class="size-3.5" /> Session tersimpan</p>
                            <dl class="space-y-1.5 text-sm">
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">UID</dt><dd class="font-medium">{{ cekSession?.uid }}</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Partner</dt><dd class="font-medium">{{ cekSession?.partner_display_name }} ({{ cekSession?.partner_id }})</dd></div>
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="text-muted-foreground">Session ID</dt>
                                    <dd class="flex min-w-0 items-center gap-1 font-mono text-xs font-medium">
                                        <span class="truncate" :title="cekSession?.session_id">{{ cekSession?.session_short }}</span>
                                        <button type="button" class="shrink-0 rounded-md border px-1 py-0.5 hover:bg-accent" title="Salin session ID penuh" @click="copyCekSession"><Copy class="size-3" /></button>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="rounded-lg border p-3">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500"><SlidersHorizontal class="size-3.5" /> Preferensi</p>
                            <dl class="space-y-1.5 text-sm">
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Bahasa</dt><dd class="font-medium">{{ cekUser.lang }}</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Zona waktu</dt><dd class="font-medium">{{ cekUser.tz }} ({{ cekUser.tz_offset }})</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Notifikasi</dt><dd class="font-medium">{{ cekUser.notification_type }}</dd></div>
                                <div class="flex items-center justify-between gap-2"><dt class="text-muted-foreground">Profil diubah</dt><dd class="font-medium">{{ formatDateTime(cekUser.last_update) }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>

                <details class="rounded-lg border">
                    <summary class="cursor-pointer px-3 py-2 text-sm font-medium">Respons mentah (raw JSON)</summary>
                    <pre class="max-h-[40vh] min-w-0 overflow-y-auto whitespace-pre-wrap border-t bg-slate-900 p-3 font-mono text-xs text-slate-100 [overflow-wrap:anywhere]"><code>{{ JSON.stringify(cekResult.raw ?? cekResult, null, 2) }}</code></pre>
                </details>
            </div>
            <p v-else class="text-sm text-muted-foreground">Belum ada hasil pemeriksaan.</p>
            <template #footer>
                <Button v-if="cekOk === false" variant="outline" @click="cekOpen = false; fixSession()">Fix Session</Button>
                <Button variant="ghost" @click="cekOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="logDetailOpen" :title="`Log: ${logFile ?? ''}`" size="xl">
            <div v-if="logMeta" class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 font-medium text-slate-600">{{ logMeta.parse }}</span>
                <span>{{ logMeta.modified }}</span>
                <span>{{ Number(logTotal).toLocaleString('id-ID') }} entri</span>
                <span v-if="logTruncated" class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 font-medium text-amber-700">File besar, hanya 2 MB terakhir yang dibaca</span>
            </div>

            <div class="flex gap-1 rounded-lg border bg-slate-50 p-1">
                <button
                    type="button"
                    class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="logTab === 'readable' ? 'bg-white shadow' : 'text-muted-foreground hover:text-foreground'"
                    @click="logTab = 'readable'"
                >
                    Readable
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="logTab === 'raw' ? 'bg-white shadow' : 'text-muted-foreground hover:text-foreground'"
                    @click="logTab = 'raw'"
                >
                    Raw
                </button>
            </div>

            <div v-if="logTab === 'readable'" class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <Input :model-value="logSearchBox" type="search" placeholder="Cari pesan log..." class="max-w-xs" @input="onLogSearch" />
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="lv in ['ALL', ...Object.keys(logLevels)]"
                            :key="lv"
                            type="button"
                            class="rounded-md border px-2 py-1 text-xs font-medium transition-colors"
                            :class="logLevel === lv ? 'border-primary bg-primary text-white' : 'hover:bg-accent'"
                            @click="setLogLevel(lv)"
                        >
                            {{ lv === 'ALL' ? 'Semua' : lv }}<span v-if="lv !== 'ALL'"> ({{ logLevels[lv] }})</span>
                        </button>
                    </div>
                </div>

                <div v-if="logLoading" class="space-y-2">
                    <div v-for="n in 4" :key="n" class="h-12 animate-pulse rounded-lg bg-slate-100" />
                </div>
                <p v-else-if="logError" class="text-sm text-red-600">{{ logError }}</p>
                <div v-else-if="logEntries.length > 0" class="space-y-2">
                    <div v-for="entry in logEntries" :key="entry.id" class="min-w-0 overflow-hidden rounded-lg border p-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs text-muted-foreground">{{ entry.timestamp }}</span>
                            <span class="inline-flex items-center rounded-md border px-1.5 py-px text-[11px] font-semibold" :class="levelBadge(entry.level)">{{ entry.level }}</span>
                            <span class="text-[11px] text-muted-foreground">{{ entry.env }}</span>
                            <button type="button" class="ml-auto text-xs font-medium text-primary hover:underline" @click="toggleEntry(entry.id)">
                                {{ expandedEntry === entry.id ? 'Sembunyikan' : 'Detail' }}
                            </button>
                        </div>
                        <p class="mt-1 min-w-0 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ entry.message }}</p>
                        <p v-if="entry.preview && expandedEntry !== entry.id" class="mt-0.5 max-w-full truncate text-xs text-muted-foreground">{{ entry.preview }}</p>
                        <pre v-if="expandedEntry === entry.id" class="mt-2 max-h-[40vh] min-w-0 overflow-y-auto whitespace-pre-wrap rounded-md bg-slate-900 p-2.5 font-mono text-[11px] leading-relaxed text-slate-100 [overflow-wrap:anywhere]"><code>{{ entry.raw }}</code></pre>
                    </div>
                </div>
                <p v-else class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">Tidak ada entri log yang cocok.</p>

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
                    <span>{{ logTotal > 0 ? `Menampilkan ${(logPage - 1) * logPerPage + 1}–${Math.min(logPage * logPerPage, logTotal)} dari ${Number(logTotal).toLocaleString('id-ID')} entri` : 'Tidak ada entri' }}</span>
                    <div v-if="logTotalPages > 1" class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="logPage <= 1 || logLoading" @click="fetchLogDetail(logFile, logPage - 1)">Sebelumnya</Button>
                        <span>Halaman {{ logPage }} dari {{ logTotalPages }}</span>
                        <Button variant="outline" size="sm" :disabled="logPage >= logTotalPages || logLoading" @click="fetchLogDetail(logFile, logPage + 1)">Berikutnya</Button>
                    </div>
                </div>
            </div>

            <div v-else class="min-w-0 space-y-2">
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="copyRawLog"><Copy /> Salin</Button>
                    <Button variant="outline" size="sm" @click="downloadRawLog"><Download /> Unduh</Button>
                </div>
                <pre class="max-h-[55vh] min-w-0 overflow-y-auto whitespace-pre-wrap rounded-lg bg-slate-900 p-3 font-mono text-xs leading-relaxed text-slate-100 [overflow-wrap:anywhere]"><code>{{ logRawTail || '(File log kosong)' }}</code></pre>
            </div>

            <template #footer>
                <Button variant="outline" @click="clearLogFile(logFile)"><History /> Kosongkan</Button>
                <Button variant="destructive" @click="deleteLogFile(logFile)"><Trash2 /> Hapus</Button>
                <Button variant="ghost" @click="logDetailOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <ConfirmDialog />
    </AppLayout>
</template>
