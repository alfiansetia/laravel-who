<script setup>
import { computed, ref, watch } from 'vue';
import { Download, Printer, RefreshCw, Trash2, Upload, X } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppModal from '@/components/AppModal.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import EmptyState from '@/components/EmptyState.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Product Images' },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(false);
const allImages = ref([]);
const table = useClientTable(null, { searchKeys: ['code', 'name'], perPage: 5 });

function syncTable() {
    const groups = new Map();
    allImages.value.forEach((img) => {
        const pid = img.product_id;
        if (!groups.has(pid)) {
            groups.set(pid, {
                product_id: pid,
                code: img.product?.code ?? '',
                name: img.product?.name ?? '',
                images: [],
            });
        }
        groups.get(pid).images.push(img);
    });
    const list = [...groups.values()].sort((a, b) => String(a.name).localeCompare(String(b.name), 'id-ID'));
    table.setRows(list);
}

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/product_images', { silent: true });
        const body = res.data?.data ?? res.data;
        allImages.value = Array.isArray(body) ? body : (body.data ?? []);
        syncTable();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat gallery.');
    } finally {
        loading.value = false;
    }
}

const searchBox = ref('');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}
function resetSearch() {
    searchBox.value = '';
    table.setSearch('', 0);
}

const productOptions = computed(() =>
    props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })),
);

// Upload (dropzone + preview)
const uploadOpen = ref(false);
const uploadProduct = ref('');
const fileInput = ref(null);
const previews = ref([]);
const uploading = ref(false);
const dragging = ref(false);
const MAX_SIZE = 5 * 1024 * 1024;
const ACCEPTED = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

function openUpload() {
    uploadProduct.value = '';
    existingUploads.value = [];
    clearPreviews();
    uploadOpen.value = true;
}

function clearPreviews() {
    previews.value.forEach((p) => URL.revokeObjectURL(p.url));
    previews.value = [];
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function addFiles(fileList) {
    const files = fileList instanceof FileList ? [...fileList] : [];
    files.forEach((f) => {
        const okType = ACCEPTED.includes(f.type);
        const okSize = f.size <= MAX_SIZE;
        previews.value.push({
            file: f,
            url: URL.createObjectURL(f),
            name: f.name,
            size: f.size,
            valid: okType && okSize,
            error: !okType ? 'Tipe file tidak didukung.' : (!okSize ? 'Melebihi 5MB.' : ''),
        });
    });
    const invalid = previews.value.filter((p) => !p.valid).length;
    if (invalid > 0) {
        toast.warning(`${invalid} file tidak valid dan tidak akan diupload.`);
    }
}

function onFilePicked(e) {
    addFiles(e.target.files);
    e.target.value = '';
}

function onDrop(e) {
    dragging.value = false;
    addFiles(e.dataTransfer?.files);
}

function removePreview(i) {
    URL.revokeObjectURL(previews.value[i]?.url);
    previews.value.splice(i, 1);
}

function closeUpload() {
    if (uploading.value) {
        return;
    }
    clearPreviews();
    uploadOpen.value = false;
}

function fmtSize(bytes) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }
    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

const validPreviews = computed(() => previews.value.filter((p) => p.valid));

// Gambar yang sudah ada di server untuk product terpilih (pembanding anti-dobel)
const existingUploads = ref([]);
const existingLoading = ref(false);

async function fetchExisting() {
    existingUploads.value = [];
    if (!uploadProduct.value) {
        return;
    }
    existingLoading.value = true;
    try {
        const res = await api.get('/product_images', { silent: true });
        const body = res.data?.data ?? res.data;
        const list = Array.isArray(body) ? body : (body.data ?? []);
        existingUploads.value = list.filter((img) => String(img.product_id) === String(uploadProduct.value));
    } catch (e) {
        existingUploads.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat gambar product.');
    } finally {
        existingLoading.value = false;
    }
}

watch(uploadProduct, fetchExisting);

async function doUpload() {
    if (!uploadProduct.value) {
        toast.warning('Pilih product dulu.');
        return;
    }
    if (validPreviews.value.length === 0) {
        toast.warning('Tarik & letakkan gambar ke dropzone, atau klik untuk pilih file.');
        return;
    }
    const fd = new FormData();
    fd.append('product_id', uploadProduct.value);
    validPreviews.value.forEach((p) => fd.append('images[]', p.file));
    uploading.value = true;
    try {
        await api.post('/product_images', fd, { headers: { 'Content-Type': 'multipart/form-data' }, block: true });
        toast.success(`${validPreviews.value.length} gambar diupload.`);
        closeUpload();
        load();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal upload gambar.');
    } finally {
        uploading.value = false;
    }
}

// Lightbox
const preview = ref({ open: false, images: [], index: 0 });
function openPreview(images, i) {
    preview.value = { open: true, images, index: i };
}
function stepPreview(d) {
    const n = preview.value.images.length;
    preview.value.index = (preview.value.index + d + n) % n;
}
function onKeyPreview(e) {
    if (!preview.value.open) {
        return;
    }
    if (e.key === 'Escape') {
        preview.value.open = false;
    }
    if (e.key === 'ArrowRight') {
        stepPreview(1);
    }
    if (e.key === 'ArrowLeft') {
        stepPreview(-1);
    }
}
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', onKeyPreview);
}

async function deleteImage(img) {
    const ok = await confirm({ title: 'Hapus gambar?', message: 'File gambar dihapus permanen.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/product_images/${img.id}`, { block: true });
        toast.success('Gambar dihapus.');
        allImages.value = allImages.value.filter((x) => x.id !== img.id);
        syncTable();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus gambar.');
    }
}

async function deleteProduct(group) {
    const ok = await confirm({ title: `Hapus ${group.images.length} gambar ${group.code}?`, message: 'Semua gambar product ini dihapus permanen.', confirmText: 'Ya, hapus semua', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/product_images-batch', { data: { ids: group.images.map((x) => x.id) }, block: true });
        toast.success('Gambar product dihapus.');
        const ids = new Set(group.images.map((x) => x.id));
        allImages.value = allImages.value.filter((x) => !ids.has(x.id));
        syncTable();
    } catch {
        // Fallback kontrak web batch per product_id
        try {
            const fd = new FormData();
            fd.append('product_id', group.product_id);
            fd.append('_method', 'DELETE');
            await api.post('/product_images-batch', fd, { block: true });
            toast.success('Gambar product dihapus.');
            load();
        } catch (e2) {
            toast.error(e2.response?.data?.message ?? 'Gagal menghapus gambar product.');
        }
    }
}

function downloadZip(group) {
    window.open(`/product_images/${group.product_id}/download`, '_blank');
}
function openCollage(group) {
    window.open(`/product_images/${group.product_id}/collage`, '_blank');
}

const pageGroups = computed(() => table.rows.value);

load();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="load"><RefreshCw /> Segarkan</Button>
                <Button size="sm" @click="openUpload"><Upload /> Upload</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-0 flex-1">
                    <Input :model-value="searchBox" type="search" placeholder="Cari nama / kode product..." autofocus @input="onSearchInput" />
                </div>
                <Button variant="ghost" size="sm" @click="resetSearch">Bersihkan</Button>
                <span class="text-xs text-muted-foreground">{{ table.total.value }} product</span>
            </div>

            <div v-if="loading" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="n in 6" :key="n" class="h-40 animate-pulse rounded-lg bg-slate-100" />
            </div>
            <div v-else-if="pageGroups.length === 0" class="rounded-lg border bg-white p-4">
                <EmptyState title="Tidak ada gambar" message="Ubah kata kunci atau upload gambar baru." />
            </div>
            <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="g in pageGroups" :key="g.product_id" class="overflow-hidden">
                    <div class="flex items-start justify-between gap-2 border-b p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">[{{ g.code }}] {{ g.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ g.images.length }} Gambar</p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <Button variant="outline" size="sm" title="Download ZIP" @click="downloadZip(g)"><Download /></Button>
                            <Button variant="outline" size="sm" title="Cetak kolase" @click="openCollage(g)"><Printer /></Button>
                            <Button variant="ghost" size="sm" title="Hapus semua" @click="deleteProduct(g)"><Trash2 /></Button>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-1 p-2">
                        <div v-for="(img, i) in g.images" :key="img.id" class="group relative aspect-square overflow-hidden rounded bg-slate-100">
                            <img :src="img.url ?? img.name" :alt="g.name" loading="lazy" class="size-full cursor-pointer object-cover transition-transform group-hover:scale-105" @click="openPreview(g.images, i)" />
                            <button type="button" title="Hapus gambar" class="absolute right-1 top-1 hidden rounded bg-black/60 p-1 text-white group-hover:block" @click="deleteImage(img)"><X class="size-3" /></button>
                        </div>
                    </div>
                </Card>
            </div>

            <div v-if="table.totalPages.value > 1" class="flex items-center justify-between gap-2 text-sm">
                <Button variant="outline" size="sm" :disabled="table.page.value <= 1" @click="table.setPage(table.page.value - 1)">Sebelumnya</Button>
                <span class="text-xs text-muted-foreground">Halaman {{ table.page.value }} dari {{ table.totalPages.value }}</span>
                <Button variant="outline" size="sm" :disabled="table.page.value >= table.totalPages.value" @click="table.setPage(table.page.value + 1)">Berikutnya</Button>
            </div>
        </div>

        <AppModal v-model:open="uploadOpen" title="Upload Gambar" size="lg" @update:open="(v) => { if (!v) closeUpload(); }">
            <div class="space-y-3">
                <FormField label="Product" required>
                    <SearchableSelect v-model="uploadProduct" :options="productOptions" placeholder="Pilih product..." />
                </FormField>
                <div v-if="uploadProduct" class="rounded-lg border bg-slate-50 p-2">
                    <p v-if="existingLoading" class="text-xs text-muted-foreground">Memeriksa gambar yang sudah ada...</p>
                    <p v-else-if="existingUploads.length === 0" class="text-xs text-muted-foreground">Belum ada gambar untuk product ini — aman upload baru.</p>
                    <div v-else>
                        <p class="mb-1 text-xs font-semibold">Sudah ada {{ existingUploads.length }} gambar — bandingkan agar tidak dobel:</p>
                        <div class="grid grid-cols-4 gap-1 sm:grid-cols-6">
                            <img v-for="img in existingUploads" :key="img.id" :src="img.url ?? img.name" :alt="img.name" loading="lazy" class="aspect-square w-full rounded border object-cover" />
                        </div>
                    </div>
                </div>
                <FormField label="Gambar (jpg/png/webp, maks 5MB/file, bisa banyak)" required>
                    <div
                        role="button"
                        tabindex="0"
                        class="flex min-h-36 cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                        :class="dragging ? 'border-primary bg-sky-50' : 'border-slate-300 bg-slate-50 hover:border-slate-400'"
                        @click="fileInput?.click()"
                        @keydown.enter="fileInput?.click()"
                        @dragover.prevent="dragging = true"
                        @dragleave="dragging = false"
                        @drop.prevent="onDrop"
                    >
                        <Upload class="size-8 text-slate-400" />
                        <p class="text-sm font-medium">Tarik &amp; letakkan gambar di sini, atau <span class="text-primary underline">klik untuk pilih</span></p>
                        <p class="text-xs text-muted-foreground">{{ validPreviews.length }} file siap upload</p>
                        <input ref="fileInput" type="file" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="onFilePicked" />
                    </div>
                </FormField>
                <div v-if="previews.length > 0" class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                    <div v-for="(p, i) in previews" :key="`${p.name}-${i}`" class="group relative overflow-hidden rounded border" :class="p.valid ? 'border-slate-200' : 'border-red-400'">
                        <img :src="p.url" :alt="p.name" class="aspect-square w-full object-cover" :class="!p.valid && 'opacity-50'" />
                        <button type="button" title="Hapus" class="absolute right-1 top-1 rounded bg-black/60 p-1 text-white" @click="removePreview(i)"><X class="size-3" /></button>
                        <p class="truncate px-1 pt-1 text-[11px] font-medium">{{ p.name }}</p>
                        <p class="px-1 pb-1 text-[11px]" :class="p.valid ? 'text-muted-foreground' : 'text-red-600'">{{ p.valid ? fmtSize(p.size) : p.error }}</p>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" :disabled="uploading" @click="closeUpload">Batal</Button>
                <Button :disabled="uploading || validPreviews.length === 0" @click="doUpload">{{ uploading ? 'Mengupload...' : `Upload (${validPreviews.length})` }}</Button>
            </template>
        </AppModal>

        <div v-if="preview.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="preview.open = false">
            <Button variant="ghost" size="sm" class="absolute right-4 top-4 text-white" @click="preview.open = false"><X /></Button>
            <Button variant="ghost" size="sm" class="absolute left-2 text-white" @click="stepPreview(-1)">‹</Button>
            <img :src="preview.images[preview.index]?.url ?? preview.images[preview.index]?.name" class="max-h-[85vh] max-w-full rounded object-contain" />
            <Button variant="ghost" size="sm" class="absolute right-2 text-white" @click="stepPreview(1)">›</Button>
        </div>
    </AppLayout>
</template>
