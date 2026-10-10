<script setup>
import { router } from '@inertiajs/vue3';
import { Lock } from '@lucide/vue';
import { nextTick, ref, watch } from 'vue';
import AppModal from '@/components/AppModal.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useAuthModal } from '@/composables/useAuthModal';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';

const { isOpen, close } = useAuthModal();
const toast = useToast();
const password = ref('');
const error = ref('');
const busy = ref(false);

watch(isOpen, (open) => {
    if (open) {
        password.value = '';
        error.value = '';
        nextTick(() => {
            document.getElementById('envPassword')?.focus();
        });
    }
});

async function submit() {
    if (!password.value.trim() || busy.value) {
        return;
    }
    busy.value = true;
    error.value = '';
    try {
        const res = await api.post(route('auth.verify'), { password: password.value }, { silent: true });
        if (res.data?.data?.auth) {
            close();
            toast.success('Login berhasil. Akses server dibuka 24 jam.');
            const intended = res.data?.data?.intended;
            if (typeof intended === 'string' && intended.startsWith('/') && !intended.startsWith('//')) {
                router.visit(intended);
            } else {
                router.reload();
            }
        } else {
            error.value = 'Password salah.';
        }
    } catch {
        error.value = 'Password tidak valid.';
    } finally {
        busy.value = false;
        password.value = '';
    }
}
</script>

<template>
    <AppModal v-model:open="isOpen" size="sm">
        <template #title>
            <span class="flex items-center gap-2"><Lock class="size-4" /> Verifikasi Akses</span>
        </template>
        <form @submit.prevent="submit" class="space-y-3">
            <div class="space-y-1.5">
                <label for="envPassword" class="text-sm font-medium">Masukkan Password Akses</label>
                <Input
                    id="envPassword"
                    v-model="password"
                    type="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                />
                <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
            </div>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="close">Tutup</Button>
                <Button type="submit" :disabled="busy">
                    <Lock class="size-4" /> {{ busy ? 'Verifikasi...' : 'Login' }}
                </Button>
            </div>
        </form>
    </AppModal>
</template>
