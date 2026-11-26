<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Camera, ChevronLeft, ChevronRight, Copy, Crop, Download, FileImage, Layers, RotateCcw, RotateCw, ScanLine, SwitchCamera, Trash2, Upload } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useToast } from '@/composables/useToast';
import { copyRows, copyText, downloadCsv } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'OCR Tool' },
});

const TESSERACT_SRC = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
const PDFJS_SRC = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
const PDFJS_WORKER_SRC = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.worker.js';
const CROPPER_JS_SRC = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js';
const CROPPER_CSS_HREF = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css';

const toast = useToast();

const fileInput = ref(null);
const videoRef = ref(null);
const captureCanvas = ref(null);
const dropActive = ref(false);

const previewSrc = ref('');
const fileLabel = ref('');
const isPdf = ref(false);
const currentPage = ref(1);
const totalPages = ref(0);
const processing = ref(false);
const progressPercent = ref(0);
const progressStatus = ref('');

const cropImg = ref(null);
const cropReady = ref(false);
let cropper = null;

const cameraOpen = ref(false);
const facingMode = ref('environment');
const hasMultiCam = ref(false);
let stream = null;
let worker = null;
let pdfDoc = null;

const table = useClientTable(null, { searchKeys: ['teks', 'sumber'] });
const searchBox = ref('');
const scans = ref([]);
let seq = 0;

const columns = [
    { key: 'no', label: 'No', align: 'center' },
    { key: 'teks', label: 'Teks', mono: true, wrap: true },
    { key: 'keyakinan', label: 'Keyakinan', align: 'center' },
    { key: 'sumber', label: 'Sumber' },
    { key: 'waktu', label: 'Waktu', mono: true },
];
const exportColumns = ['no', 'teks', 'keyakinan', 'sumber', 'waktu'];

const exportRows = computed(() => table.filtered.value.map((r) => ({ no: r.no, teks: r.teks, keyakinan: r.keyakinan, sumber: r.sumber, waktu: r.waktu })));

const hasSource = computed(() => previewSrc.value !== '');

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve();
            return;
        }
        const el = document.createElement('script');
        el.src = src;
        el.onload = () => resolve();
        el.onerror = () => reject(new Error(`Gagal memuat pustaka: ${src}`));
        document.head.appendChild(el);
    });
}

function loadCss(href) {
    return new Promise((resolve) => {
        if (document.querySelector(`link[href="${href}"]`)) {
            resolve();
            return;
        }
        const el = document.createElement('link');
        el.rel = 'stylesheet';
        el.href = href;
        el.onload = () => resolve();
        el.onerror = () => resolve();
        document.head.appendChild(el);
    });
}

async function ensureWorker() {
    if (worker) {
        return worker;
    }
    await loadScript(TESSERACT_SRC);
    progressStatus.value = 'Menyiapkan AI...';
    worker = await window.Tesseract.createWorker('eng', 1, {
        logger: (m) => {
            if (m.status === 'recognizing text') {
                progressStatus.value = 'Membaca karakter...';
                progressPercent.value = Math.round((m.progress ?? 0) * 100);
            } else if (m.status === 'loading tesseract core') {
                progressStatus.value = 'Memuat inti AI...';
            } else if (m.status === 'initializing tesseract') {
                progressStatus.value = 'Menyiapkan AI...';
            }
        },
    });
    return worker;
}

async function ensurePdfjs() {
    await loadScript(PDFJS_SRC);
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER_SRC;
    return window.pdfjsLib;
}

async function ensureCropper() {
    await Promise.all([loadScript(CROPPER_JS_SRC), loadCss(CROPPER_CSS_HREF)]);
    if (!window.Cropper) {
        throw new Error('Pustaka crop tidak tersedia.');
    }
    return window.Cropper;
}

function setProgress(percent, status) {
    progressPercent.value = percent;
    progressStatus.value = status;
}

function pushRow(teks, confidence, sumber) {
    const value = String(teks ?? '').trim();
    if (value === '') {
        return false;
    }
    seq += 1;
    scans.value.unshift({
        id: seq,
        no: seq,
        teks: value,
        keyakinan: `${confidence ?? 0}%`,
        sumber,
        waktu: new Date().toLocaleString('id-ID'),
    });
    table.setRows(scans.value);
    return true;
}

function sourceLabel(pageNum = null) {
    const base = fileLabel.value || 'Gambar';
    if (pageNum !== null) {
        return `${base} · Hal ${pageNum}`;
    }
    return base;
}

async function recognizeImage(imageData) {
    processing.value = true;
    try {
        setProgress(10, 'Membangunkan AI...');
        const w = await ensureWorker();
        const { data } = await w.recognize(imageData);
        return { text: (data.text ?? '').trim(), confidence: Number(Number(data.confidence ?? 0).toFixed(1)) };
    } finally {
        processing.value = false;
        setProgress(0, '');
    }
}

// ---------- Cropper ----------

function destroyCropper() {
    try {
        cropper?.destroy();
    } catch {
        // abaikan
    }
    cropper = null;
    cropReady.value = false;
}

function waitImageLoaded(img) {
    return new Promise((resolve, reject) => {
        if (img.complete && img.naturalWidth > 0) {
            resolve();
            return;
        }
        img.onload = () => resolve();
        img.onerror = () => reject(new Error('Gambar tidak bisa dimuat ke cropper.'));
    });
}

async function initCropper() {
    destroyCropper();
    try {
        const Cropper = await ensureCropper();
        await nextTick();
        const img = cropImg.value;
        if (!img) {
            return;
        }
        await waitImageLoaded(img);
        cropper = new Cropper(img, {
            viewMode: 1,
            dragMode: 'crop',
            autoCropArea: 0.8,
            responsive: true,
            guides: true,
        });
        cropReady.value = true;
    } catch (e) {
        toast.error(e.message ?? 'Gagal menyiapkan cropper.');
    }
}

async function scanCrop() {
    if (!cropper) {
        toast.warning('Cropper belum siap.');
        return;
    }
    const canvas = cropper.getCroppedCanvas({ maxWidth: 2048, maxHeight: 2048 });
    if (!canvas) {
        toast.warning('Area potongan tidak valid.');
        return;
    }
    try {
        const r = await recognizeImage(canvas.toDataURL('image/png'));
        if (pushRow(r.text, r.confidence, sourceLabel(isPdf.value ? currentPage.value : null))) {
            toast.success('Pemindaian selesai.');
        } else {
            toast.warning('Tidak ada teks yang terbaca dari potongan.');
        }
    } catch (e) {
        toast.error(e.message ?? 'Gagal memindai gambar.');
    }
}

async function scanFull() {
    if (!previewSrc.value) {
        toast.warning('Belum ada gambar.');
        return;
    }
    try {
        const r = await recognizeImage(previewSrc.value);
        if (pushRow(r.text, r.confidence, sourceLabel(isPdf.value ? currentPage.value : null))) {
            toast.success('Pemindaian selesai.');
        } else {
            toast.warning('Tidak ada teks yang terbaca dari gambar.');
        }
    } catch (e) {
        toast.error(e.message ?? 'Gagal memindai gambar.');
    }
}

function rotateCrop() {
    cropper?.rotate(90);
}

function resetCrop() {
    cropper?.reset();
}

// ---------- Sumber: file / PDF ----------

function readImageFile(file) {
    const reader = new FileReader();
    reader.onload = async (e) => {
        destroyCropper();
        totalPages.value = 0;
        pdfDoc = null;
        isPdf.value = false;
        previewSrc.value = e.target.result;
        await initCropper();
    };
    reader.readAsDataURL(file);
}

async function loadPdf(file) {
    try {
        const pdfjs = await ensurePdfjs();
        const buffer = await file.arrayBuffer();
        pdfDoc = await pdfjs.getDocument({ data: buffer }).promise;
        totalPages.value = pdfDoc.numPages;
        currentPage.value = 1;
        await showPage(1);
    } catch (e) {
        toast.error(e.message ?? 'Gagal membuka PDF.');
    }
}

async function renderPdfPage(pageNum) {
    if (!pdfDoc) {
        return null;
    }
    const page = await pdfDoc.getPage(pageNum);
    const viewport = page.getViewport({ scale: 2 });
    const canvas = document.createElement('canvas');
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
    const dataUrl = canvas.toDataURL('image/png');
    previewSrc.value = dataUrl;
    return dataUrl;
}

async function showPage(pageNum) {
    destroyCropper();
    await renderPdfPage(pageNum);
    currentPage.value = pageNum;
    await initCropper();
}

async function changePage(delta) {
    const next = currentPage.value + delta;
    if (next < 1 || next > totalPages.value || processing.value) {
        return;
    }
    processing.value = true;
    try {
        await showPage(next);
    } catch (e) {
        toast.error(e.message ?? 'Gagal merender halaman PDF.');
    } finally {
        processing.value = false;
    }
}

async function processAllPages() {
    if (!pdfDoc || processing.value) {
        return;
    }
    destroyCropper();
    processing.value = true;
    try {
        const w = await ensureWorker();
        for (let i = 1; i <= totalPages.value; i++) {
            setProgress(Math.round((i / totalPages.value) * 100), `Memindai halaman ${i}/${totalPages.value}...`);
            const dataUrl = await renderPdfPage(i);
            currentPage.value = i;
            const { data } = await w.recognize(dataUrl);
            pushRow(data.text ?? '', Number(Number(data.confidence ?? 0).toFixed(1)), sourceLabel(i));
        }
        toast.success('Semua halaman selesai dipindai.');
    } catch (e) {
        toast.error(e.message ?? 'Gagal memindai semua halaman.');
    } finally {
        processing.value = false;
        setProgress(0, '');
        await showPage(currentPage.value);
    }
}

async function handleFile(file) {
    if (!file) {
        return;
    }
    fileLabel.value = file.name;
    isPdf.value = file.type === 'application/pdf';
    if (isPdf.value) {
        destroyCropper();
        previewSrc.value = '';
        await loadPdf(file);
    } else {
        readImageFile(file);
    }
}

function onBrowse() {
    fileInput.value?.click();
}

function onFileChange(e) {
    handleFile(e.target.files?.[0]);
}

function onDrop(e) {
    e.preventDefault();
    dropActive.value = false;
    handleFile(e.dataTransfer.files?.[0]);
}

function onPaste(e) {
    const items = e.clipboardData?.items ?? [];
    for (const item of items) {
        if (item.kind === 'file' && item.type.includes('image')) {
            const file = item.getAsFile();
            if (file) {
                isPdf.value = false;
                totalPages.value = 0;
                pdfDoc = null;
                fileLabel.value = 'Tempelan clipboard';
                readImageFile(file);
                toast.info('Gambar dari clipboard dimuat. Sesuaikan potongan lalu pindai.');
            }
            break;
        }
    }
}

// ---------- Kamera ----------

async function startCamera() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        hasMultiCam.value = devices.filter((d) => d.kind === 'videoinput').length > 1;
    } catch {
        hasMultiCam.value = false;
    }
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode.value } });
        cameraOpen.value = true;
        requestAnimationFrame(() => {
            if (videoRef.value) {
                videoRef.value.srcObject = stream;
            }
        });
    } catch {
        toast.error('Gagal mengakses kamera. Periksa izin kamera browser.');
    }
}

function stopStream() {
    stream?.getTracks().forEach((t) => t.stop());
    stream = null;
}

function stopCamera() {
    stopStream();
    cameraOpen.value = false;
}

async function switchCamera() {
    stopStream();
    facingMode.value = facingMode.value === 'environment' ? 'user' : 'environment';
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode.value } });
        if (videoRef.value) {
            videoRef.value.srcObject = stream;
        }
    } catch {
        toast.error('Gagal berganti kamera.');
    }
}

async function captureImage() {
    const video = videoRef.value;
    const canvas = captureCanvas.value;
    if (!video || !canvas || !video.videoWidth) {
        return;
    }
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const dataUrl = canvas.toDataURL('image/png');
    isPdf.value = false;
    totalPages.value = 0;
    pdfDoc = null;
    fileLabel.value = 'Hasil jepretan kamera';
    destroyCropper();
    previewSrc.value = dataUrl;
    stopCamera();
    await initCropper();
}

function resetSource() {
    destroyCropper();
    previewSrc.value = '';
    fileLabel.value = '';
    isPdf.value = false;
    currentPage.value = 1;
    totalPages.value = 0;
    pdfDoc = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    toast.info('Sumber dibersihkan.');
}

// ---------- Tabel hasil ----------

function onCopyItem(row) {
    copyText(row.teks, 'Teks hasil OCR disalin ke clipboard.');
}

function onCopyTable() {
    copyRows(exportRows.value, exportColumns, 'hasil OCR');
}

function onDownload() {
    downloadCsv('ocr-hasil', exportRows.value, exportColumns);
}

function removeItem(id) {
    scans.value = scans.value.filter((s) => s.id !== id);
    table.setRows(scans.value);
}

function clearHistory() {
    scans.value = [];
    table.setRows([]);
    toast.info('Riwayat hasil dibersihkan.');
}

onMounted(() => {
    window.addEventListener('paste', onPaste);
});

onBeforeUnmount(() => {
    window.removeEventListener('paste', onPaste);
    destroyCropper();
    stopStream();
    worker?.terminate?.().catch(() => {});
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title">
            <template #actions>
                <Button variant="outline" size="sm" :disabled="table.total.value === 0" @click="onCopyTable"><Copy /> Salin Tabel</Button>
                <Button variant="outline" size="sm" :disabled="table.total.value === 0" @click="onDownload"><Download /> CSV</Button>
            </template>
        </PageHeader>

        <div class="grid gap-4 xl:grid-cols-[7fr_5fr]">
            <Card class="p-4 sm:p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Dokumen Sumber</h2>

                <div
                    v-if="!hasSource"
                    role="button"
                    tabindex="0"
                    class="mt-3 flex min-h-56 cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-8 text-center transition-colors"
                    :class="dropActive ? 'border-primary bg-primary/5' : 'border-input hover:border-primary'"
                    @click="onBrowse"
                    @keydown.enter="onBrowse"
                    @dragover.prevent="dropActive = true"
                    @dragleave="dropActive = false"
                    @drop="onDrop"
                >
                    <FileImage class="size-10 text-muted-foreground" />
                    <p class="text-sm font-medium">Seret gambar / PDF ke sini, tempel dari clipboard, atau</p>
                    <Button size="sm" variant="outline"><Upload /> Pilih Berkas</Button>
                    <p class="text-xs text-muted-foreground">Mendukung gambar dan PDF</p>
                </div>

                <div v-else class="mt-3 space-y-3">
                    <div class="max-h-[60vh] overflow-hidden rounded-lg border bg-slate-100">
                        <img ref="cropImg" :src="previewSrc" alt="Potong area dokumen" class="block max-w-full" />
                    </div>
                    <p v-if="fileLabel" class="truncate text-xs text-muted-foreground">{{ fileLabel }}</p>
                    <p class="text-xs text-muted-foreground">Seret untuk mengatur kotak potongan pada area teks, lalu pindai. Ulangi untuk memindai berkali-kali.</p>

                    <div class="flex flex-wrap gap-2">
                        <Button size="sm" :disabled="!cropReady || processing" @click="scanCrop"><Crop /> Pindai Potongan</Button>
                        <Button variant="secondary" size="sm" :disabled="processing" @click="scanFull"><ScanLine /> Pindai Penuh</Button>
                        <Button variant="outline" size="sm" :disabled="!cropReady" @click="rotateCrop"><RotateCw /> Putar</Button>
                        <Button variant="outline" size="sm" :disabled="!cropReady" @click="resetCrop"><RotateCcw /> Reset Potongan</Button>
                    </div>

                    <div v-if="isPdf" class="flex flex-wrap items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="processing || currentPage <= 1" @click="changePage(-1)">
                            <ChevronLeft /> Sebelumnya
                        </Button>
                        <span class="text-sm text-muted-foreground">Halaman {{ currentPage }} / {{ totalPages }}</span>
                        <Button variant="outline" size="sm" :disabled="processing || currentPage >= totalPages" @click="changePage(1)">
                            Berikutnya <ChevronRight />
                        </Button>
                        <Button variant="secondary" size="sm" :disabled="processing" @click="processAllPages"><Layers /> Pindai Semua</Button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button variant="outline" size="sm" @click="onBrowse"><Upload /> Ganti Berkas</Button>
                        <Button variant="outline" size="sm" @click="startCamera"><Camera /> Kamera</Button>
                        <Button variant="ghost" size="sm" @click="resetSource"><RotateCcw /> Bersihkan</Button>
                    </div>
                </div>

                <div v-if="!hasSource" class="mt-3 flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="startCamera"><Camera /> Kamera</Button>
                </div>

                <input ref="fileInput" type="file" accept="image/*,application/pdf" class="hidden" @change="onFileChange" />
            </Card>

            <Card class="p-4 sm:p-6">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Hasil Ekstraksi ({{ table.total.value }})</h2>
                </div>

                <div v-if="processing" class="mt-3 space-y-2">
                    <div class="relative overflow-hidden rounded-md border bg-slate-50 p-3">
                        <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-blue-500 transition-all" :style="{ width: `${progressPercent}%` }" />
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                            <span>{{ progressStatus || 'Memindai...' }}</span>
                            <span>{{ progressPercent }}%</span>
                        </div>
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 animate-pulse bg-blue-500" />
                    </div>
                </div>

                <div class="mt-3">
                    <TableToolbar v-model="searchBox" placeholder="Cari teks atau sumber..." @search="table.setSearch">
                        <Button variant="ghost" size="sm" :disabled="table.total.value === 0" @click="clearHistory"><Trash2 /> Hapus</Button>
                    </TableToolbar>
                    <DataTable
                        :columns="columns"
                        :rows="table.rows.value"
                        :loading="table.loading.value"
                        :error="table.error.value"
                        :page="table.page.value"
                        :total-pages="table.totalPages.value"
                        :total="table.total.value"
                        :per-page="table.perPage.value"
                        row-key="id"
                        empty-title="Belum ada hasil"
                        empty-message="Potong area teks lalu pindai, atau pindai penuh gambar / halaman PDF."
                        @update:page="table.setPage"
                        @update:per-page="table.setPerPage"
                    >
                        <template #cell-teks="{ row }">
                            <div class="max-h-32 overflow-y-auto whitespace-pre-wrap break-words font-mono text-xs">{{ row.teks }}</div>
                        </template>
                        <template #actions="{ row }">
                            <div class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" @click="onCopyItem(row)"><Copy /> Salin</Button>
                                <Button variant="ghost" size="sm" @click="removeItem(row.id)"><Trash2 /></Button>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <p class="mt-3 text-xs text-muted-foreground">Pemrosesan lokal di browser. Data Anda tetap privat.</p>
            </Card>
        </div>

        <AppModal v-model:open="cameraOpen" title="Ambil Foto" size="lg">
            <div class="relative overflow-hidden rounded-lg bg-black">
                <video ref="videoRef" autoplay playsinline class="max-h-[60vh] w-full object-contain" />
            </div>
            <template #footer>
                <Button v-if="hasMultiCam" variant="outline" @click="switchCamera"><SwitchCamera /> Ganti Kamera</Button>
                <Button variant="ghost" @click="stopCamera">Batal</Button>
                <Button @click="captureImage"><Camera /> Jepret</Button>
            </template>
        </AppModal>

        <canvas ref="captureCanvas" class="hidden" />
    </AppLayout>
</template>
