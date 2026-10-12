<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Save, Upload, X } from '@lucide/vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useToast } from '@/composables/useToast';
import { formatFileSize } from '@/lib/format';

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
const dragging = ref(false);

const MAX_FILE = 20 * 1024 * 1024;
const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];

const newFile = ref(null);
const newPreviewUrl = ref('');
const newIsPdf = ref(false);

function setFile(f) {
    if (!f) {
        return;
    }
    const okType = ACCEPTED_TYPES.includes(f.type) || /\.pdf$/i.test(f.name);
    if (!okType) {
        toast.warning('Tipe file tidak didukung (jpg/png/webp/pdf).');
        return;
    }
    if (f.size > MAX_FILE) {
        toast.warning('Ukuran file maksimal 20MB.');
        return;
    }
    clearNewFile();
    newFile.value = f;
    newPreviewUrl.value = URL.createObjectURL(f);
    newIsPdf.value = (f.type === 'application/pdf') || /\.pdf$/i.test(f.name);
}

function clearNewFile() {
    if (newPreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(newPreviewUrl.value);
    }
    newFile.value = null;
    newPreviewUrl.value = '';
    newIsPdf.value = false;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function onFilePicked(e) {
    setFile(e.target.files?.[0]);
    e.target.value = '';
}

function onDrop(e) {
    dragging.value = false;
    setFile(e.dataTransfer?.files?.[0]);
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
    const f = newFile.value;
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
                        <div
                            role="button"
                            tabindex="0"
                            class="flex min-h-32 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                            :class="dragging ? 'border-primary bg-primary/5' : 'border-input hover:border-primary'"
                            @click="fileInput?.click()"
                            @keydown.enter="fileInput?.click()"
                            @dragover.prevent="dragging = true"
                            @dragleave="dragging = false"
                            @drop.prevent="onDrop"
                        >
                            <Upload class="size-6 text-muted-foreground" />
                            <p class="text-sm font-medium">Tarik &amp; letakkan file di sini, atau <span class="text-primary underline">klik untuk pilih</span></p>
                            <p class="text-xs text-muted-foreground">jpg / png / webp / pdf • maks 20MB</p>
                            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden" @change="onFilePicked" />
                        </div>
                        <div v-if="newFile" class="mt-2 flex items-center justify-between gap-2 rounded-md border bg-slate-50 px-2 py-1.5 text-xs">
                            <span class="min-w-0 truncate font-medium">{{ newFile.name }} <span class="text-muted-foreground">({{ formatFileSize(newFile.size) }})</span></span>
                            <button type="button" title="Hapus file" class="shrink-0 rounded p-1 text-muted-foreground hover:bg-accent hover:text-accent-foreground" @click="clearNewFile"><X class="size-3.5" /></button>
                        </div>
                        <p v-if="record?.file && !newPreviewUrl" class="mt-1 text-xs text-muted-foreground">File saat ini: {{ record.file }} — <a :href="oldUrl" target="_blank" class="underline">buka tab baru</a>. Pilih file baru untuk mengganti (lama dihapus dari S3).</p>
                    </FormField>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <Button variant="ghost" @click="goBack"><ArrowLeft /> Kembali</Button>
                    <Button :disabled="saving" @click="save"><Save /> Simpan</Button>
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
