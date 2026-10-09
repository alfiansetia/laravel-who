<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, Download } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Edit Kargan' },
    record: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    lastNumber: { type: String, default: '-' },
    picOptions: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const form = ref({
    number: props.record.number ?? '',
    date: props.record.date ?? '',
    product_id: props.record.product_id ?? '',
    sn: props.record.sn ?? '',
    pic: props.record.pic ?? '',
});
const errors = ref({});
const saving = ref(false);

const productOptions = computed(() =>
    props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })),
);
const picSelectOptions = computed(() => props.picOptions.map((p) => ({ value: p, label: p })));

function goBack() {
    router.visit('/kargans');
}

async function save() {
    errors.value = {};
    saving.value = true;
    try {
        await api.put(`/kargans/${props.record.id}`, { ...form.value }, { block: true });
        toast.success('Kargan diperbarui.');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal memperbarui kargan.');
    } finally {
        saving.value = false;
    }
}

async function duplicate() {
    const ok = await confirm({
        title: 'Duplikasi kargan?',
        message: 'Buat salinan kargan ini dengan nomor baru.',
        confirmText: 'Ya, duplikasi',
    });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/kargans/${props.record.id}/duplicate`, {}, { block: true });
        const id = res.data?.data?.id ?? res.data?.id;
        toast.success('Kargan diduplikasi.');
        if (id) {
            window.open(`/kargans/${id}/edit`, '_blank');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal duplikasi kargan.');
    }
}

function download() {
    window.open(`/kargans/${props.record.id}/download`, '_blank');
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" :description="`Kargan ${record.number ?? ''}`">
            <template #actions>
                <Button variant="outline" size="sm" @click="download"><Download /> Download</Button>
                <Button variant="outline" size="sm" @click="duplicate"><Copy /> Duplicate</Button>
            </template>
        </PageHeader>
        <Card class="p-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Nomor" required :error="errors.number?.[0]" :hint="`Terakhir (lainnya): ${lastNumber}`">
                    <Input v-model="form.number" />
                </FormField>
                <FormField label="Tanggal" required :error="errors.date?.[0]">
                    <Input v-model="form.date" type="date" />
                </FormField>
                <FormField label="Product" required :error="errors.product_id?.[0]">
                    <SearchableSelect v-model="form.product_id" :options="productOptions" placeholder="Pilih product..." />
                </FormField>
                <FormField label="SN" :error="errors.sn?.[0]">
                    <Input v-model="form.sn" />
                </FormField>
                <FormField label="PIC" required :error="errors.pic?.[0]">
                    <SearchableSelect v-model="form.pic" :options="picSelectOptions" placeholder="Pilih PIC..." />
                </FormField>
            </div>
            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <Button variant="ghost" @click="goBack">Kembali</Button>
                <Button variant="outline" @click="() => window.close()">Tutup</Button>
                <Button :disabled="saving" @click="save">Simpan</Button>
            </div>
        </Card>
    </AppLayout>
</template>
