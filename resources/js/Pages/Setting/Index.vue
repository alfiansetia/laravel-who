<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Bell, Cog, History, Laptop, RefreshCw, Save, Server, Trash2, Wifi } from '@lucide/vue';
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
const cekJson = ref('');

const currentToken = (localStorage.getItem('fcm_token') ?? '').trim();

const deviceTotalPages = computed(() => Math.max(1, Math.ceil(deviceTotal.value / devicePerPage.value)));

const deviceColumns = [
    { key: 'platform', label: 'Platform' },
    { key: 'user_agent', label: 'User Agent' },
    { key: 'ip', label: 'IP', align: 'center' },
    { key: 'token', label: 'Token' },
    { key: 'last_status', label: 'Last Status', align: 'center' },
];

function short(text, len) {
    const s = String(text ?? '');
    return s.length > len ? `${s.slice(0, len)}...` : s;
}

function isCurrentDevice(row) {
    return currentToken !== '' && String(row.token ?? '').trim() === currentToken;
}

async function refreshSession() {
    sessionBusy.value = true;
    try {
        const res = await api.get('/settings/', { silent: true });
        const d = res.data?.data ?? {};
        odooEnv.value = d.session_id ?? '';
        sessionUsername.value = d.username ?? '';
        sessionName.value = d.name && d.uid ? `${d.name} (${d.uid})` : '';
        toast.success('Session dimuat ulang.');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat session.');
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
        await api.post('/settings/', { env_value: odooEnv.value });
        toast.success('Session tersimpan.');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan session.');
    } finally {
        sessionBusy.value = false;
    }
}

async function fixSession() {
    sessionBusy.value = true;
    try {
        await api.put('/settings/');
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
        const res = await api.delete('/settings/');
        toast.success(res.data?.message ?? 'Notifikasi terkirim.');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengirim notifikasi.');
    } finally {
        sessionBusy.value = false;
    }
}

async function cekOdoo() {
    try {
        const res = await api.get('/settings/cek-odoo', { silent: true });
        cekJson.value = JSON.stringify(res.data ?? res, null, 2);
    } catch (e) {
        cekJson.value = JSON.stringify(e.response?.data ?? { message: 'Gagal mengecek Odoo.' }, null, 2);
        toast.error('Gagal mengecek Odoo, lihat respons.');
    } finally {
        cekOpen.value = true;
    }
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

async function clearLog() {
    const ok = await confirm({
        title: 'Kosongkan log?',
        message: 'Isi laravel.log akan dihapus.',
        confirmText: 'Ya, kosongkan',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        const res = await api.delete('/resources');
        toast.success(res.data?.message ?? 'Log dikosongkan.');
        loadResource();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mengosongkan log.');
    }
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
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-sm font-semibold"><Cog class="size-4 text-primary" /> Odoo Session</h2>
                    <div class="flex flex-wrap gap-1">
                        <Button variant="outline" size="sm" :disabled="sessionBusy" @click="refreshSession"><RefreshCw /> Refresh</Button>
                        <Button variant="outline" size="sm" :disabled="sessionBusy" @click="fixSession">Fix Session</Button>
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
                <FormField label="Session ID" required class="mt-3">
                    <Textarea v-model="odooEnv" rows="3" class="font-mono text-xs" />
                </FormField>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Button size="sm" :disabled="sessionBusy" @click="saveSession"><Save /> Simpan</Button>
                    <Button variant="outline" size="sm" :disabled="sessionBusy" @click="testNotif"><Bell /> Tes Notif</Button>
                    <Button variant="outline" size="sm" @click="cekOdoo"><Wifi /> Cek Odoo</Button>
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
                        <span>Logs</span>
                        <span class="font-medium">{{ resource.logs?.value ?? 0 }} bytes</span>
                        <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ resource.logs?.parse }}</span>
                    </div>
                </div>
            </Card>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-[8fr_4fr]">
            <div>
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

            <Card class="p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-sm font-semibold"><History class="size-4 text-slate-500" /> Logs</h2>
                    <Button variant="outline" size="sm" @click="clearLog"><Trash2 /> Bersihkan</Button>
                </div>
                <Textarea :model-value="resource?.logs?.content ?? ''" rows="12" readonly class="bg-slate-900 font-mono text-xs text-slate-100" />
            </Card>
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

        <AppModal v-model:open="cekOpen" title="Respons Odoo" size="xl">
            <pre class="max-h-[60vh] overflow-auto rounded-lg bg-slate-900 p-3 font-mono text-xs text-slate-100"><code>{{ cekJson }}</code></pre>
            <template #footer>
                <Button variant="ghost" @click="cekOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <ConfirmDialog />
    </AppLayout>
</template>
