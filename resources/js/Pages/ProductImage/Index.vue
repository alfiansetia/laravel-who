<script setup>
import { computed, ref } from 'vue';
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

// Upload
const uploadOpen = ref(false);
const uploadProduct = ref('');
const uploadFiles = ref(null);
const uploading = ref(false);
function openUpload() {
    uploadProduct.value = '';
    uploadFiles.value = null;
    uploadOpen.value = true;
}
async function doUpload() {
    if (!uploadProduct.value) {
        toast.warning('Pilih product dulu.');
        return;
    }
    const files = uploadFiles.value?.$el?.files ?? uploadFiles.value?.files ?? null;
    const list = files instanceof FileList ? [...files] : [];
    if (list.length === 0) {
        toast.warning('Pilih minimal 1 gambar.');
        return;
    }
    const fd = new FormData();
    fd.append('product_id', uploadProduct.value);
    list.forEach((f) => fd.append('images[]', f));
    uploading.value = true;
    try {
        await api.post('/product_images', fd, { headers: { 'Content-Type': 'multipart/form-data' }, block: true });
        toast.success(`${list.length} gambar diupload.`);
        uploadOpen.value = false;
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
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            await fetch('/product_images-batch', { method: 'POST', body: fd, headers: token ? { 'X-CSRF-TOKEN': token } : {} });
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
        <PageHeader :title="title" description="Gallery foto product + upload + collage">
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

        <AppModal v-model:open="uploadOpen" title="Upload Gambar" size="md">
            <div class="space-y-3">
                <FormField label="Product" required>
                    <SearchableSelect v-model="uploadProduct" :options="productOptions" placeholder="Pilih product..." />
                </FormField>
                <FormField label="Gambar (jpg/png/webp, maks 5MB/file)" required>
                    <input ref="uploadFiles" type="file" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-sm" />
                </FormField>
            </div>
            <template #footer>
                <Button variant="ghost" @click="uploadOpen = false">Batal</Button>
                <Button :disabled="uploading" @click="doUpload">Upload</Button>
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
