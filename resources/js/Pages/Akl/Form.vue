<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    mode: { type: String, default: 'create' },
    record: { type: Object, default: null },
});

const toast = useToast();
const isEdit = computed(() => props.mode === 'edit');
const form = ref({
    reg_no: props.record?.reg_no ?? '',
    reg_name: props.record?.reg_name ?? '',
    vendor: props.record?.vendor ?? '',
    date_from: props.record?.date_from ?? '',
    date_expired: props.record?.date_expired ?? '',
});
const fileInput = ref(null);
const errors = ref({});
const saving = ref(false);

const newPreviewUrl = ref('');
const newIsPdf = ref(false);
function onFileChange() {
    const f = fileInput.value?.files?.[0];
    if (newPreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(newPreviewUrl.value);
    }
    if (!f) {
        newPreviewUrl.value = '';
        return;
    }
    newPreviewUrl.value = URL.createObjectURL(f);
    newIsPdf.value = (f.type === 'application/pdf') || /\.pdf$/i.test(f.name);
}

const oldUrl = computed(() => (props.record?.id ? `/akls/${props.record.id}` : ''));
const oldIsPdf = computed(() => !!props.record?.is_pdf);
const showUrl = computed(() => newPreviewUrl.value || oldUrl.value);
const showIsPdf = computed(() => (newPreviewUrl.value ? newIsPdf.value : oldIsPdf.value));

function goBack() {
    router.visit('/akls');
}

function save() {
    errors.value = {};
    if (!form.value.reg_no.trim()) {
        errors.value = { reg_no: ['Reg No wajib diisi.'] };
        return;
    }
    const fd = new FormData();
    fd.append('reg_no', form.value.reg_no);
    fd.append('reg_name', form.value.reg_name ?? '');
    fd.append('vendor', form.value.vendor ?? '');
    fd.append('date_from', form.value.date_from ?? '');
    fd.append('date_expired', form.value.date_expired ?? '');
    const f = fileInput.value?.files?.[0];
    if (f) {
        fd.append('file', f);
    }
    saving.value = true;
    const opts = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => toast.success(isEdit.value ? 'AKL diperbarui.' : 'AKL disimpan.'),
        onError: (e) => {
            errors.value = e;
            toast.error('Gagal menyimpan, periksa field.');
        },
        onFinish: () => {
            saving.value = false;
        },
    };
    if (isEdit.value) {
        fd.append('_method', 'PUT');
        router.post(`/akls/${props.record.id}`, fd, opts);
    } else {
        router.post('/akls', fd, opts);
    }
}
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2">
            <Card class="h-fit p-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <FormField label="Reg No" required :error="errors.reg_no?.[0]" class="sm:col-span-2">
                        <Input v-model="form.reg_no" placeholder="Nomor registrasi..." />
                    </FormField>
                    <FormField label="Nama" :error="errors.reg_name?.[0]" class="sm:col-span-2">
                        <Input v-model="form.reg_name" placeholder="Nama product..." />
                    </FormField>
                    <FormField label="Vendor" :error="errors.vendor?.[0]" class="sm:col-span-2">
                        <Input v-model="form.vendor" placeholder="Pendaftar / pabrik..." />
                    </FormField>
                    <FormField label="Berlaku Dari" :error="errors.date_from?.[0]">
                        <Input v-model="form.date_from" type="date" />
                    </FormField>
                    <FormField label="Expired" :error="errors.date_expired?.[0]">
                        <Input v-model="form.date_expired" type="date" />
                    </FormField>
                    <FormField label="File (jpg/png/webp/pdf, maks 20MB)" :error="errors.file?.[0]" class="sm:col-span-2">
                        <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="w-full text-sm" @change="onFileChange" />
                        <p v-if="record?.file && !newPreviewUrl" class="mt-1 text-xs text-muted-foreground">File saat ini: {{ record.file }} — <a :href="oldUrl" target="_blank" class="underline">buka tab baru</a>. Pilih file baru untuk mengganti (lama dihapus dari S3).</p>
                    </FormField>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <Button variant="ghost" @click="goBack">Kembali</Button>
                    <Button :disabled="saving" @click="save">Simpan</Button>
                </div>
            </Card>
            <Card class="h-fit p-4">
                <b class="text-sm">Preview</b>
                <div v-if="!showUrl" class="mt-2 rounded border border-dashed p-8 text-center text-sm text-muted-foreground">Belum ada file.</div>
                <div v-else-if="showIsPdf" class="mt-2 h-[60vh]">
                    <embed :src="showUrl" type="application/pdf" class="size-full rounded border" />
                </div>
                <img v-else :src="showUrl" alt="Preview lampiran" class="mt-2 max-h-[60vh] w-full rounded border object-contain" />
            </Card>
        </div>
</template>
