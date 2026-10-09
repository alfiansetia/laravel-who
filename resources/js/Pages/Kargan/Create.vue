<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Create Kargan' },
    products: { type: Array, default: () => [] },
    newNumber: { type: String, default: '' },
    lastNumber: { type: String, default: '-' },
    defaultDate: { type: String, default: '' },
    picOptions: { type: Array, default: () => [] },
});

const toast = useToast();
const form = ref({
    number: props.newNumber,
    date: props.defaultDate,
    product_id: '',
    sn: '',
    pic: '',
});
const errors = ref({});
const saving = ref(false);

const ROMAN = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
function refreshNumber() {
    const parts = String(form.value.number || '').split('/');
    if (parts.length >= 3 && form.value.date) {
        const d = new Date(form.value.date);
        if (!Number.isNaN(d.getTime())) {
            parts[1] = ROMAN[d.getMonth()];
            parts[2] = String(d.getFullYear());
            form.value.number = parts.join('/');
        }
    }
}

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
        const res = await api.post('/kargans', { ...form.value }, { block: true });
        const id = res.data?.data?.id ?? res.data?.id;
        toast.success('Kargan disimpan.');
        if (id) {
            window.open(`/kargans/${id}/edit`, '_blank');
        }
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan kargan.');
    } finally {
        saving.value = false;
    }
}

refreshNumber();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Buat kartu garansi baru" />
        <Card class="p-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Nomor" required :error="errors.number?.[0]" :hint="`Terakhir: ${lastNumber}`">
                    <Input v-model="form.number" placeholder="SUPP-GAR/..." />
                </FormField>
                <FormField label="Tanggal" required :error="errors.date?.[0]">
                    <Input v-model="form.date" type="date" @change="refreshNumber" />
                </FormField>
                <FormField label="Product" required :error="errors.product_id?.[0]">
                    <SearchableSelect v-model="form.product_id" :options="productOptions" placeholder="Pilih product..." />
                </FormField>
                <FormField label="SN" :error="errors.sn?.[0]">
                    <Input v-model="form.sn" placeholder="Serial number (opsional)" />
                </FormField>
                <FormField label="PIC" required :error="errors.pic?.[0]">
                    <SearchableSelect v-model="form.pic" :options="picSelectOptions" placeholder="Pilih PIC..." />
                </FormField>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <Button variant="ghost" @click="goBack">Kembali</Button>
                <Button :disabled="saving" @click="save">Simpan &amp; Lanjut ke Detail</Button>
            </div>
        </Card>
    </AppLayout>
</template>
