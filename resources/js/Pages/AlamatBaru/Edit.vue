<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import AlamatHeaderForm from './partials/AlamatHeaderForm.vue';
import KoliManager from './partials/KoliManager.vue';
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Edit Alamat Baru' },
    record: { type: Object, required: true },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();

async function duplicate() {
    const ok = await confirm({ title: 'Duplikasi alamat?', message: `DO ${props.record.do ?? ''}`, confirmText: 'Ya, duplikasi' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await api.post(`/alamat-baru/${props.record.id}/duplicate`, {}, { block: true });
            const created = res.data?.data ?? res.data;
            toast.success('Alamat diduplikasi.');
            if (created?.id) {
                window.open(`/alamat-baru/${created.id}/edit`, '_blank');
            }
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menduplikasi alamat.');
        }
    });
}

async function createBast() {
    const ok = await confirm({ title: 'Buat BAST?', message: `Dari DO ${props.record.do ?? ''}`, confirmText: 'Ya, lanjutkan' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await api.post(`/alamat-baru/${props.record.id}/bast`, {}, { block: true });
            const bast = res.data?.data ?? res.data;
            toast.success('BAST dibuat.');
            const id = bast?.id ?? bast?.bast_id;
            if (id) {
                window.open(`/basts/${id}/edit`, '_blank');
            }
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal membuat BAST.');
        }
    });
}

function printAll() {
    window.open(`/alamat-baru/${props.record.id}`, '_blank');
}
function goList() {
    router.visit('/alamat-baru');
}
</script>

<template>
    <AppLayout>
        <AlamatHeaderForm :title="title" mode="edit" :record="record" />
        <div class="mt-3 flex flex-wrap gap-2">
            <Button variant="outline" size="sm" @click="printAll">Print All</Button>
            <Button variant="outline" size="sm" @click="duplicate">Duplikasi</Button>
            <Button variant="outline" size="sm" @click="createBast">Buat BAST</Button>
            <Button variant="ghost" size="sm" @click="goList">Tutup</Button>
        </div>
        <div class="mt-4">
            <KoliManager :alamat-id="record.id" :products="products" />
        </div>
    </AppLayout>
</template>
