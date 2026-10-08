<script setup>
import { Bell, Megaphone, MessageCircle, Send } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import AppModal from '@/components/AppModal.vue';
import Button from '@/components/ui/Button.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import api from '@/lib/axios';
import { fcmSupported, fcmToken, refreshFcmToken, testLocalNotif } from '@/lib/fcm';

const props = defineProps({
    envLoggedIn: { type: Boolean, default: false },
});

const open = defineModel('open', { type: Boolean, default: false });
const result = ref({ tone: '', message: '' });
const busy = ref('');
const refreshKey = ref(0);

watch(open, (value) => {
    if (value) {
        result.value = { tone: '', message: '' };
        refreshKey.value += 1;
    }
});

const permission = computed(() => {
    void refreshKey.value;
    if (!('Notification' in window)) {
        return { label: 'Tidak didukung', tone: 'danger' };
    }
    if (Notification.permission === 'granted') {
        return { label: 'Aktif', tone: 'success' };
    }
    if (Notification.permission === 'denied') {
        return { label: 'Ditolak', tone: 'danger' };
    }
    return { label: 'Belum diminta', tone: 'warning' };
});

const support = computed(() => {
    void refreshKey.value;
    const ok = fcmSupported();
    return { label: ok ? 'Ya' : 'Tidak', tone: ok ? 'success' : 'danger' };
});

const tokenState = computed(() => {
    void refreshKey.value;
    const token = fcmToken();
    return {
        registered: !!token,
        short: token ? token.slice(0, 40) + (token.length > 40 ? '...' : '') : '',
        full: token ?? '',
    };
});

function setResult(tone, message) {
    result.value = { tone, message };
}

function enable() {
    if (!('Notification' in window)) {
        setResult('danger', 'Browser tidak mendukung notifikasi.');
        return;
    }
    if (!refreshFcmToken()) {
        setResult('danger', 'Modul notifikasi belum siap, coba lagi sebentar.');
        return;
    }
    setResult('info', 'Meminta izin / mendaftarkan ulang... status diperbarui otomatis.');
    setTimeout(() => (refreshKey.value += 1), 1500);
    setTimeout(() => (refreshKey.value += 1), 4000);
}

function testLocal() {
    if (!('Notification' in window)) {
        setResult('danger', 'Browser tidak mendukung notifikasi.');
        return;
    }
    if (Notification.permission !== 'granted') {
        setResult('warning', 'Izin belum diberikan. Klik "Aktifkan / Daftar Ulang" dulu.');
        return;
    }
    try {
        testLocalNotif();
        setResult('success', 'Notif lokal ditampilkan. Tidak muncul? Cek izin notifikasi di browser/OS.');
    } catch (e) {
        setResult('danger', `Gagal: ${e.message}`);
    }
}

async function testFcm() {
    const token = fcmToken();
    if (!token) {
        setResult('warning', 'Perangkat belum terdaftar. Klik "Aktifkan / Daftar Ulang" dulu.');
        return;
    }
    busy.value = 'fcm';
    setResult('info', 'Mengirim test via server...');
    try {
        const res = await api.post(route('api.tokens.test'), { token }, { silent: true });
        setResult('success', `${res.data?.message || 'Terkirim!'} Tunggu beberapa detik, notif akan masuk.`);
    } catch (err) {
        setResult('danger', `Gagal: ${err.response?.data?.message || err.message}`);
    } finally {
        busy.value = '';
    }
}

async function broadcast() {
    busy.value = 'broadcast';
    setResult('info', 'Mengirim test ke semua perangkat...');
    try {
        const res = await api.delete(route('api.settings.test_notif'), { silent: true });
        setResult('success', res.data?.message || 'Terkirim ke semua perangkat!');
    } catch (err) {
        setResult('danger', `Gagal: ${err.response?.data?.message || err.message}`);
    } finally {
        busy.value = '';
    }
}

const resultClasses = {
    success: 'border-green-200 bg-green-50 text-green-700',
    danger: 'border-red-200 bg-red-50 text-red-700',
    warning: 'border-amber-200 bg-amber-50 text-amber-700',
    info: 'border-sky-200 bg-sky-50 text-sky-700',
};
</script>

<template>
    <AppModal v-model:open="open" size="md">
        <template #title>
            <span class="flex items-center gap-2"><Bell class="size-4" /> Notifikasi</span>
        </template>

        <ul class="divide-y rounded-lg border">
            <li class="flex items-center justify-between px-3 py-2 text-sm">
                <span>Izin notifikasi</span>
                <StatusBadge :status="permission.tone">{{ permission.label }}</StatusBadge>
            </li>
            <li class="flex items-center justify-between px-3 py-2 text-sm">
                <span>Dukungan browser</span>
                <StatusBadge :status="support.tone">{{ support.label }}</StatusBadge>
            </li>
            <li class="px-3 py-2 text-sm">
                <div class="flex items-center justify-between">
                    <span>Token perangkat ini</span>
                    <StatusBadge :status="tokenState.registered ? 'success' : 'secondary'">
                        {{ tokenState.registered ? 'Terdaftar' : 'Belum ada' }}
                    </StatusBadge>
                </div>
                <small
                    class="block truncate text-xs text-muted-foreground"
                    :title="tokenState.full"
                >
                    {{
                        tokenState.registered
                            ? tokenState.short
                            : 'Klik "Aktifkan / Daftar Ulang" untuk mendaftarkan perangkat ini.'
                    }}
                </small>
            </li>
        </ul>

        <div
            v-if="result.message"
            class="rounded-md border px-3 py-2 text-sm"
            :class="resultClasses[result.tone]"
        >
            {{ result.message }}
        </div>

        <div class="flex flex-wrap gap-2">
            <Button size="sm" variant="outline" @click="enable">
                <Bell class="size-4" /> Aktifkan / Daftar Ulang
            </Button>
            <Button size="sm" variant="outline" @click="testLocal">
                <MessageCircle class="size-4" /> Tes Lokal
            </Button>
            <Button size="sm" variant="outline" :disabled="busy === 'fcm'" @click="testFcm">
                <Send class="size-4" /> Tes FCM ke Perangkat Ini
            </Button>
            <Button
                v-if="envLoggedIn"
                size="sm"
                variant="outline"
                :disabled="busy === 'broadcast'"
                @click="broadcast"
            >
                <Megaphone class="size-4" /> Tes ke Semua Perangkat
            </Button>
        </div>
        <p class="text-xs text-muted-foreground">
            Tes Lokal tampil langsung tanpa server. Tes FCM mengirim push sungguhan lewat server ke perangkat ini.
        </p>

        <template #footer>
            <Button variant="secondary" @click="open = false">Tutup</Button>
        </template>
    </AppModal>
</template>
