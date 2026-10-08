<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { ArrowLeft, Crop, Images, Loader2, Printer, RotateCcw, Trash2, Upload } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    title: { type: String, default: 'Print Resi' },
});

const JSZIP_SRC = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
const CROPPER_JS = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js';
const CROPPER_CSS = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css';

const toast = useToast();

const excelInput = ref(null);
const cropImage = ref(null);
const dropActive = ref(false);
const extracting = ref(false);
const extractFailed = ref(false);

const images = ref([]);
const cols = ref(2);
const rows = ref(2);
const cropOpen = ref(false);
const cropIndex = ref(-1);
let cropper = null;
let seq = 0;

const hasImages = computed(() => images.value.length > 0);
const itemsPerPage = computed(() => Math.max(1, Number(cols.value) || 1) * Math.max(1, Number(rows.value) || 1));

function loadAsset(src, kind) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`${kind}[href="${src}"],${kind}[src="${src}"]`)) {
            resolve();
            return;
        }
        const el = document.createElement(kind === 'link' ? 'link' : 'script');
        if (kind === 'link') {
            el.rel = 'stylesheet';
            el.href = src;
        } else {
            el.src = src;
        }
        el.onload = () => resolve();
        el.onerror = () => reject(new Error(`Gagal memuat pustaka: ${src}`));
        document.head.appendChild(el);
    });
}

async function ensureLibs() {
    await loadAsset(JSZIP_SRC, 'script');
}

function addImage(url, blob, name) {
    seq += 1;
    images.value.push({ id: `img-${Date.now()}-${seq}`, url, blob, name });
}

function applyExcelCrop(url, crop) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            try {
                const sx = img.width * crop.l;
                const sy = img.height * crop.t;
                const sw = img.width * (1 - crop.l - crop.r);
                const sh = img.height * (1 - crop.t - crop.b);
                if (sw <= 0 || sh <= 0) {
                    resolve({ url, blob: null });
                    return;
                }
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(sw);
                canvas.height = Math.round(sh);
                canvas.getContext('2d').drawImage(img, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    if (blob) {
                        resolve({ url: URL.createObjectURL(blob), blob });
                    } else {
                        resolve({ url, blob: null });
                    }
                }, 'image/png');
            } catch {
                resolve({ url, blob: null });
            }
        };
        img.onerror = () => resolve({ url, blob: null });
        img.src = url;
    });
}

async function handleFile(file) {
    if (!file) {
        return;
    }
    if (!/\.xlsx$/i.test(file.name)) {
        toast.warning('Simpan sebagai .xlsx dulu, file .xls tidak didukung.');
        return;
    }
    extracting.value = true;
    extractFailed.value = false;
    try {
        await ensureLibs();
        const zip = await window.JSZip.loadAsync(file);
        const drawingNames = Object.keys(zip.files).filter((n) => /^xl\/drawings\/drawing\d+\.xml$/.test(n));
        const drawings = [];
        for (const dName of drawingNames) {
            const num = dName.match(/drawing(\d+)\.xml/)[1];
            const relsName = `xl/drawings/_rels/drawing${num}.xml.rels`;
            const dXml = new DOMParser().parseFromString(await zip.files[dName].async('string'), 'application/xml');
            const relsFile = zip.files[relsName];
            const relsXml = relsFile ? new DOMParser().parseFromString(await relsFile.async('string'), 'application/xml') : null;
            const relMap = {};
            if (relsXml) {
                relsXml.querySelectorAll('Relationship').forEach((rel) => {
                    relMap[rel.getAttribute('Id')] = rel.getAttribute('Target').replace(/^\.\.\//, 'xl/drawings/').replace(/^\//, '');
                });
            }
            dXml.getElementsByTagNameNS('*', 'pic').forEach((pic) => {
                const blip = pic.getElementsByTagNameNS('*', 'blip')[0];
                const embed = blip?.getAttribute('r:embed') ?? blip?.getAttribute('embed');
                let mediaPath = relMap[embed] ?? '';
                if (mediaPath.startsWith('xl/drawings/../media/')) {
                    mediaPath = mediaPath.replace('xl/drawings/../media/', 'xl/media/');
                } else if (!mediaPath.startsWith('xl/')) {
                    mediaPath = `xl/media/${mediaPath.split('/').pop()}`;
                }
                const srcRect = pic.getElementsByTagNameNS('*', 'srcRect')[0];
                const crop = srcRect
                    ? {
                        l: (Number(srcRect.getAttribute('l') ?? 0)) / 100000,
                        t: (Number(srcRect.getAttribute('t') ?? 0)) / 100000,
                        r: (Number(srcRect.getAttribute('r') ?? 0)) / 100000,
                        b: (Number(srcRect.getAttribute('b') ?? 0)) / 100000,
                    }
                    : { l: 0, t: 0, r: 0, b: 0 };
                drawings.push({ mediaPath, crop });
            });
        }
        if (drawings.length === 0) {
            const mediaNames = Object.keys(zip.files).filter((n) => /^xl\/media\//.test(n) && !zip.files[n].dir);
            for (const mPath of mediaNames) {
                const blob = await zip.files[mPath].async('blob');
                addImage(URL.createObjectURL(blob), blob, mPath.split('/').pop());
            }
        } else {
            for (const d of drawings) {
                const zf = zip.files[d.mediaPath];
                if (!zf) {
                    continue;
                }
                const blob = await zf.async('blob');
                const rawUrl = URL.createObjectURL(blob);
                if (d.crop.l + d.crop.r + d.crop.t + d.crop.b > 0) {
                    const { url, blob: cropped } = await applyExcelCrop(rawUrl, d.crop);
                    if (url !== rawUrl) {
                        URL.revokeObjectURL(rawUrl);
                    }
                    addImage(url, cropped ?? blob, d.mediaPath.split('/').pop());
                } else {
                    addImage(rawUrl, blob, d.mediaPath.split('/').pop());
                }
            }
        }
        if (images.value.length === 0) {
            toast.warning('Tidak ada resi ditemukan dalam file Excel.');
        } else {
            toast.success(`${images.value.length} gambar resi diekstrak.`);
        }
    } catch (e) {
        extractFailed.value = true;
        toast.error(e.message ?? 'Ekstraksi gagal.');
    } finally {
        extracting.value = false;
    }
}

function onBrowse() {
    excelInput.value?.click();
}

function onFileChange(e) {
    handleFile(e.target.files?.[0]);
    e.target.value = '';
}

function onDrop(e) {
    e.preventDefault();
    dropActive.value = false;
    handleFile(e.dataTransfer.files?.[0]);
}

function removeImage(id) {
    const idx = images.value.findIndex((i) => i.id === id);
    if (idx >= 0) {
        URL.revokeObjectURL(images.value[idx].url);
        images.value.splice(idx, 1);
    }
}

function resetAll() {
    images.value.forEach((i) => URL.revokeObjectURL(i.url));
    images.value = [];
    extractFailed.value = false;
}

function openCrop(index) {
    cropIndex.value = index;
    cropOpen.value = true;
}

async function initCropper() {
    await loadAsset(CROPPER_CSS, 'link');
    await loadAsset(CROPPER_JS, 'script');
    await nextTick();
    destroyCropper();
    if (cropImage.value) {
        cropper = new window.Cropper(cropImage.value, {
            viewMode: 2,
            autoCropArea: 1,
            responsive: true,
            checkOrientation: false,
        });
    }
}

function destroyCropper() {
    try {
        cropper?.destroy();
    } catch {
        // abaikan
    }
    cropper = null;
}

function saveCrop() {
    if (!cropper || cropIndex.value < 0) {
        return;
    }
    cropper.getCroppedCanvas({ imageSmoothingEnabled: true, imageSmoothingQuality: 'high' }).toBlob((blob) => {
        if (!blob) {
            toast.error('Gagal memotong gambar.');
            return;
        }
        const item = images.value[cropIndex.value];
        URL.revokeObjectURL(item.url);
        item.url = URL.createObjectURL(blob);
        item.blob = blob;
        cropOpen.value = false;
        toast.success('Potongan disimpan.');
    }, 'image/png');
}

watch(cropOpen, (open) => {
    if (open) {
        initCropper().catch((e) => {
            toast.error(e.message ?? 'Gagal memuat Cropper.');
            cropOpen.value = false;
        });
    } else {
        destroyCropper();
        cropIndex.value = -1;
    }
});

function printAll() {
    const c = Math.max(1, Number(cols.value) || 1);
    const r = Math.max(1, Number(rows.value) || 1);
    const perPage = c * r;
    let pages = '';
    for (let i = 0; i < images.value.length; i += perPage) {
        const chunk = images.value.slice(i, i + perPage);
        let cells = chunk.map((img) => `<div class="print-item"><img src="${img.url}" /></div>`).join('');
        for (let k = chunk.length; k < perPage; k++) {
            cells += '<div class="print-item"></div>';
        }
        pages += `<div class="page" style="grid-template-columns:repeat(${c},1fr);grid-template-rows:repeat(${r},1fr);">${cells}</div>`;
    }
    const win = window.open('', '_blank');
    if (!win) {
        toast.error('Popup diblokir browser. Izinkan popup untuk mencetak.');
        return;
    }
    win.document.write(`<html><head><title>Cetak Resi</title><style>
        @page{margin:5mm;size:A4 landscape}
        body{margin:0}
        .page{display:grid;width:287mm;height:200mm;page-break-after:always}
        .print-item{border:0.1mm dashed #ddd;display:flex;align-items:center;justify-content:center;overflow:hidden}
        .print-item img{width:100%;height:100%;object-fit:fill}
    </style></head><body onload="window.focus();window.print();window.close()">${pages}</body></html>`);
    win.document.close();
}

onBeforeUnmount(() => {
    destroyCropper();
    images.value.forEach((i) => URL.revokeObjectURL(i.url));
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Ekstrak gambar resi dari Excel lalu cetak massal">
            <template #actions>
                <Button variant="outline" size="sm" as="a" :href="route('index')"><ArrowLeft /> Kembali</Button>
            </template>
        </PageHeader>

        <div
            role="button"
            tabindex="0"
            class="flex min-h-40 cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-8 text-center transition-colors"
            :class="dropActive ? 'border-primary bg-primary/5' : 'border-input hover:border-primary'"
            @click="onBrowse"
            @keydown.enter="onBrowse"
            @dragover.prevent="dropActive = true"
            @dragleave="dropActive = false"
            @drop="onDrop"
        >
            <Upload class="size-8 text-muted-foreground" />
            <p class="text-sm font-medium">Seret file Excel ke sini atau klik untuk memilih</p>
            <p class="text-xs text-muted-foreground">Hanya berkas .xlsx</p>
        </div>
        <input ref="excelInput" type="file" accept=".xlsx" class="hidden" @change="onFileChange" />

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <div class="space-y-1">
                <label for="pr-cols" class="text-sm font-medium">Kolom cetak</label>
                <Input id="pr-cols" v-model="cols" type="number" min="1" class="w-28" />
            </div>
            <div class="space-y-1">
                <label for="pr-rows" class="text-sm font-medium">Baris cetak</label>
                <Input id="pr-rows" v-model="rows" type="number" min="1" class="w-28" />
            </div>
            <span v-if="hasImages" class="rounded-md border px-2 py-1 text-xs font-medium">{{ images.length }} Gambar</span>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" :disabled="!hasImages" @click="resetAll"><RotateCcw /> Reset</Button>
                <Button size="sm" :disabled="!hasImages" @click="printAll"><Printer /> Print Semua ({{ itemsPerPage }}/hal)</Button>
            </div>
        </div>

        <div v-if="extracting" class="mt-6 flex items-center justify-center gap-2 rounded-lg border p-8 text-sm text-muted-foreground">
            <Loader2 class="size-5 animate-spin" /> Sedang mengekstrak gambar...
        </div>
        <div v-else-if="extractFailed" class="mt-6 rounded-lg border p-8 text-center text-sm font-medium text-red-600">
            Ekstraksi gagal. Coba lagi dengan file lain.
        </div>
        <div v-else-if="!hasImages" class="mt-6 flex flex-col items-center gap-2 rounded-lg border border-dashed p-12 text-center">
            <Images class="size-10 text-muted-foreground" />
            <p class="text-sm text-muted-foreground">Belum ada gambar. Unggah file Excel berisi resi untuk mulai.</p>
        </div>
        <div v-else class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <div v-for="(img, idx) in images" :key="img.id" class="group relative overflow-hidden rounded-lg border bg-white">
                <img :src="img.url" :alt="img.name" class="aspect-square w-full object-cover" />
                <p class="truncate px-2 py-1 text-xs text-muted-foreground">{{ img.name }}</p>
                <div class="absolute right-1 top-1 flex gap-1 opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100">
                    <button
                        type="button"
                        title="Potong gambar"
                        class="rounded-md bg-black/60 p-1.5 text-white hover:bg-black/80"
                        @click="openCrop(idx)"
                    >
                        <Crop class="size-4" />
                    </button>
                    <button
                        type="button"
                        title="Hapus gambar"
                        class="rounded-md bg-black/60 p-1.5 text-white hover:bg-red-600"
                        @click="removeImage(img.id)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </div>
            </div>
        </div>

        <AppModal v-model:open="cropOpen" title="Potong Gambar" size="lg">
            <div class="max-h-[450px] overflow-hidden rounded-lg bg-black">
                <img ref="cropImage" :src="images[cropIndex]?.url ?? ''" alt="Potong gambar" class="max-h-[450px] w-full object-contain" />
            </div>
            <template #footer>
                <Button variant="ghost" @click="cropOpen = false">Batal</Button>
                <Button @click="saveCrop"><Crop /> Simpan Potongan</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
