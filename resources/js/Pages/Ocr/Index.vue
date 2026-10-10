<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Camera, ChevronLeft, ChevronRight, Copy, FileImage, Layers, RotateCcw, SwitchCamera, Trash2, Upload } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useToast } from '@/composables/useToast';
import { copyText } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'OCR Tool' },
});

const TESSERACT_SRC = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
const PDFJS_SRC = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
const PDFJS_WORKER_SRC = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

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
const resultText = ref('');
const confidence = ref(null);

const cameraOpen = ref(false);
const facingMode = ref('environment');
const hasMultiCam = ref(false);
let stream = null;
let worker = null;
let pdfDoc = null;
let pdfFile = null;

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

function setProgress(percent, status) {
    progressPercent.value = percent;
    progressStatus.value = status;
}

function clearResults() {
    resultText.value = '';
    confidence.value = null;
}

async function recognizeText(imageData) {
    clearResults();
    processing.value = true;
    try {
        setProgress(10, 'Membangunkan AI...');
        const w = await ensureWorker();
        const { data } = await w.recognize(imageData);
        resultText.value = data.text ?? '';
        confidence.value = Number(Number(data.confidence ?? 0).toFixed(1));
        if (resultText.value.trim() !== '') {
            toast.success('Pemindaian selesai.');
        } else {
            toast.warning('Tidak ada teks yang terbaca dari gambar.');
        }
    } catch (e) {
        toast.error(e.message ?? 'Gagal memindai gambar.');
    } finally {
        processing.value = false;
        setProgress(0, '');
    }
}

function readImageFile(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        previewSrc.value = e.target.result;
        recognizeText(e.target.result);
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
        await renderPdfPage(1);
    } catch (e) {
        toast.error(e.message ?? 'Gagal membuka PDF.');
    }
}

async function renderPdfPage(pageNum, autoProcess = true) {
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
    if (autoProcess) {
        await recognizeText(dataUrl);
    }
    return dataUrl;
}

async function changePage(delta) {
    const next = currentPage.value + delta;
    if (next < 1 || next > totalPages.value || processing.value) {
        return;
    }
    currentPage.value = next;
    processing.value = true;
    try {
        await renderPdfPage(next);
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
    clearResults();
    processing.value = true;
    try {
        const w = await ensureWorker();
        const parts = [];
        for (let i = 1; i <= totalPages.value; i++) {
            setProgress(Math.round((i / totalPages.value) * 100), `Memindai halaman ${i}/${totalPages.value}...`);
            const dataUrl = await renderPdfPage(i, false);
            currentPage.value = i;
            const { data } = await w.recognize(dataUrl);
            parts.push(`\n\n[HALAMAN ${i}]\n${data.text ?? ''}`);
        }
        resultText.value = parts.join('').trim();
        confidence.value = null;
        toast.success('Semua halaman selesai dipindai.');
    } catch (e) {
        toast.error(e.message ?? 'Gagal memindai semua halaman.');
    } finally {
        processing.value = false;
        setProgress(0, '');
    }
}

async function handleFile(file) {
    if (!file) {
        return;
    }
    fileLabel.value = file.name;
    isPdf.value = file.type === 'application/pdf';
    if (isPdf.value) {
        pdfFile.value = file;
        previewSrc.value = '';
        clearResults();
        processing.value = true;
        await loadPdf(file);
        processing.value = false;
    } else {
        totalPages.value = 0;
        pdfDoc = null;
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
                toast.info('Gambar dari clipboard dimuat.');
            }
            break;
        }
    }
}

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

function captureImage() {
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
    previewSrc.value = dataUrl;
    stopCamera();
    recognizeText(dataUrl);
}

function resetSource() {
    previewSrc.value = '';
    fileLabel.value = '';
    isPdf.value = false;
    currentPage.value = 1;
    totalPages.value = 0;
    pdfDoc = null;
    pdfFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    clearResults();
    toast.info('Sumber dibersihkan.');
}

function onCopy() {
    copyText(resultText.value.trim(), 'Teks hasil OCR disalin ke clipboard.');
}

onMounted(() => {
    window.addEventListener('paste', onPaste);
});

onBeforeUnmount(() => {
    window.removeEventListener('paste', onPaste);
    stopStream();
    worker?.terminate?.().catch(() => {});
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" />

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
                    <div class="overflow-hidden rounded-lg border bg-slate-50">
                        <img :src="previewSrc" alt="Pratinjau dokumen" class="max-h-[60vh] w-full object-contain" />
                    </div>
                    <p v-if="fileLabel" class="truncate text-xs text-muted-foreground">{{ fileLabel }}</p>

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
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Hasil Ekstraksi</h2>
                    <div class="flex gap-1">
                        <Button variant="outline" size="sm" :disabled="!resultText" @click="onCopy"><Copy /> Salin</Button>
                        <Button variant="ghost" size="sm" :disabled="!resultText && confidence === null" @click="clearResults"><Trash2 /></Button>
                    </div>
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

                <div class="mt-3 space-y-2">
                    <Textarea v-model="resultText" rows="14" class="font-mono text-sm" placeholder="Hasil ekstraksi teks akan tampil di sini..." />
                    <span v-if="confidence !== null" class="inline-flex items-center rounded-md border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                        Keyakinan: {{ confidence }}%
                    </span>
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
