<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import AlamatForm from './partials/AlamatForm.vue';
import DetailManager from './partials/DetailManager.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';

const props = defineProps({
    title: { type: String, default: 'Edit Alamat' },
    record: { type: Object, required: true },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const formRef = ref(null);
const detailRef = ref(null);

function goBack() {
    router.visit('/alamats');
}

async function savePrint() {
    const id = await formRef.value?.save();
    if (id) {
        window.open(`/alamats/${id}`, '_blank');
    }
}

function printNow() {
    window.open(`/alamats/${props.record.id}`, '_blank');
}

async function syncProduct() {
    const ok = await confirm({ title: 'Sync product dari DO?', message: `Tarik item DO ${props.record.do ?? ''} dari Odoo.`, confirmText: 'Ya, sync' });
    if (!ok) {
        return;
    }
    try {
        await api.get(`/alamats/${props.record.id}/sync`, { block: true });
        toast.success('Sync selesai.');
        detailRef.value?.reload();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal sync product.');
    }
}

async function duplicate() {
    const ok = await confirm({ title: 'Duplikasi alamat?', message: `DO ${props.record.do ?? ''}`, confirmText: 'Ya, duplikasi' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/alamats/${props.record.id}/duplicate`, {}, { block: true });
        const created = res.data?.data ?? res.data;
        toast.success('Alamat diduplikasi.');
        if (created?.id) {
            window.open(`/alamats/${created.id}/edit`, '_blank');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal duplikasi.');
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack"><ArrowLeft /> Kembali</Button>
                <Button variant="outline" size="sm" @click="detailRef?.openAdd()">Tambah Product</Button>
                <Button size="sm" @click="savePrint"><Save /> Simpan &amp; Print</Button>
                <Button variant="outline" size="sm" @click="syncProduct">Sync Product</Button>
                <Button variant="outline" size="sm" @click="duplicate">Duplikasi</Button>
                <Button variant="outline" size="sm" @click="printNow">Print</Button>
                <Button variant="ghost" size="sm" @click="goBack">Tutup</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <AlamatForm ref="formRef" mode="edit" :record="record" />
            <DetailManager ref="detailRef" :alamat-id="record.id" :products="products" />
        </div>
    </AppLayout>
</template>
