<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Camera, ClipboardPaste, Copy, Crop, Download, FileImage, QrCode, RotateCcw, RotateCw, ScanLine, Square, SwitchCamera, Trash2, Upload } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useToast } from '@/composables/useToast';
import { copyRows, copyText, downloadCsv } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Barcode / QR Reader' },
});

const HTML5_SRC = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
const CROPPER_JS_SRC = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js';
const CROPPER_CSS_HREF = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css';
const NATIVE_FORMATS = ['qr_code', 'aztec', 'code_128', 'code_39', 'code_93', 'codabar', 'data_matrix', 'ean_13', 'ean_8', 'itf', 'pdf417', 'upc_a', 'upc_e'];

const toast = useToast();

const fileInput = ref(null);
const dropActive = ref(false);
const previewSrc = ref('');
const fileLabel = ref('');
const processing = ref(false);
const cropReady = ref(false);
const cropImg = ref(null);
let cropper = null;
let pendingFile = null;

const scanning = ref(false);
const cameras = ref([]);
const cameraIndex = ref(0);
let cameraScanner = null;
let lastScanAt = 0;
let lastScanValue = '';

const table = useClientTable(null, { searchKeys: ['hasil', 'format'] });
const searchBox = ref('');
const scans = ref([]);
let seq = 0;

const columns = [
    { key: 'no', label: 'No', align: 'center' },
    { key: 'hasil', label: 'Hasil', mono: true, wrap: true },
    { key: 'format', label: 'Format', align: 'center' },
    { key: 'waktu', label: 'Waktu', mono: true },
];
const exportColumns = ['no', 'hasil', 'format', 'waktu'];

const exportRows = computed(() => table.filtered.value.map((r) => ({ no: r.no, hasil: r.hasil, format: r.format, waktu: r.waktu })));

const hasSource = computed(() => previewSrc.value !== '' || scanning.value);
const hasMultiCam = computed(() => cameras.value.length > 1);

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve();
            return;
        }
        const el = document.createElement('script');
        el.src = src;
        el.onload = () => resolve();
        el.onerror = () => reject(new Error('Gagal memuat pustaka. Periksa koneksi internet.'));
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

async function ensureHtml5() {
    await loadScript(HTML5_SRC);
    if (!window.Html5Qrcode) {
        throw new Error('Pustaka pemindai tidak tersedia.');
    }
    return window.Html5Qrcode;
}

async function ensureCropper() {
    await Promise.all([loadScript(CROPPER_JS_SRC), loadCss(CROPPER_CSS_HREF)]);
    if (!window.Cropper) {
        throw new Error('Pustaka crop tidak tersedia.');
    }
    return window.Cropper;
}

function readAsDataURL(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = () => reject(new Error('File tidak bisa dibaca.'));
        reader.readAsDataURL(file);
    });
}

function pushResult(text, format) {
    const value = String(text ?? '').trim();
    if (value === '') {
        return;
    }
    seq += 1;
    scans.value.unshift({
        id: seq,
        no: seq,
        hasil: value,
        format: format || 'Tidak diketahui',
        waktu: new Date().toLocaleString('id-ID'),
    });
    table.setRows(scans.value);
}

// ---------- Decode ----------

async function decodeWithNative(dataUrl) {
    if (!('BarcodeDetector' in window)) {
        throw new Error('Native detector tidak tersedia.');
    }
    let formats = NATIVE_FORMATS;
    try {
        const supported = await window.BarcodeDetector.getSupportedFormats();
        if (Array.isArray(supported) && supported.length > 0) {
            formats = supported;
        }
    } catch {
        // abaikan, pakai daftar baku
    }
    const detector = new window.BarcodeDetector({ formats });
    const img = new Image();
    await new Promise((resolve, reject) => {
        img.onload = () => resolve();
        img.onerror = () => reject(new Error('Gambar tidak bisa dibaca.'));
        img.src = dataUrl;
    });
    const codes = await detector.detect(img);
    if (!codes || codes.length === 0) {
        throw new Error('Tidak ada kode yang terbaca dari gambar.');
    }
    return { text: codes[0].rawValue ?? '', format: codes[0].format ?? 'BarcodeDetector' };
}

async function decodeWithHtml5(file) {
    const Html5Qrcode = await ensureHtml5();
    const tmp = new Html5Qrcode('barcode-file-tmp', { verbose: false });
    try {
        const decoded = await tmp.scanFile(file, true);
        const text = typeof decoded === 'string' ? decoded : (decoded?.decodedText ?? '');
        if (!text) {
            throw new Error('Tidak ada kode yang terbaca dari gambar.');
        }
        return { text, format: decoded?.result?.format?.formatName ?? 'Gambar' };
    } finally {
        try {
            await tmp.clear();
        } catch {
            // abaikan
        }
    }
}

async function decodeBlob(file) {
    processing.value = true;
    try {
        const dataUrl = await readAsDataURL(file);
        try {
            const native = await decodeWithNative(dataUrl);
            pushResult(native.text, native.format);
            toast.success('Kode terbaca.');
            return;
        } catch {
            // lanjut ke html5-qrcode sebagai fallback
        }
        const result = await decodeWithHtml5(file);
        pushResult(result.text, result.format);
        toast.success('Kode terbaca.');
    } catch (e) {
        toast.error(e.message ?? 'Tidak ada kode yang terbaca dari gambar.');
    } finally {
        processing.value = false;
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

async function handleFile(file) {
    if (!file) {
        return;
    }
    if (!String(file.type || '').startsWith('image/')) {
        toast.warning('Pilih berkas gambar (JPG/PNG/WebP).');
        return;
    }
    await stopCamera(true);
    destroyCropper();
    pendingFile = file;
    fileLabel.value = file.name || 'Berkas gambar';
    previewSrc.value = await readAsDataURL(file);
    await initCropper();
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
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
    if (!blob) {
        toast.error('Gagal memotong gambar.');
        return;
    }
    await decodeBlob(new File([blob], 'potongan.png', { type: 'image/png' }));
}

async function scanFull() {
    if (!pendingFile) {
        toast.warning('Belum ada gambar.');
        return;
    }
    await decodeBlob(pendingFile);
}

function rotateCrop() {
    if (!cropper) {
        return;
    }
    cropper.rotate(90);
}

function resetCrop() {
    cropper?.reset();
}

function resetSource(silent = false) {
    destroyCropper();
    pendingFile = null;
    previewSrc.value = '';
    fileLabel.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    if (!silent) {
        toast.info('Sumber dibersihkan.');
    }
}

// ---------- Kamera ----------

function onLiveSuccess(decodedText, decodedResult) {
    const now = Date.now();
    if (decodedText === lastScanValue && now - lastScanAt < 2000) {
        return;
    }
    lastScanValue = decodedText;
    lastScanAt = now;
    const format = decodedResult?.result?.format?.formatName ?? 'Kamera';
    pushResult(decodedText, format);
    toast.success('Kode terbaca.');
}

function onLiveFailure() {
    // Abaikan frame tanpa kode agar tidak membanjiri log.
}

async function startCamera() {
    try {
        resetSource(true);
        const Html5Qrcode = await ensureHtml5();
        if (cameraScanner && scanning.value) {
            return;
        }
        const ids = await Html5Qrcode.getCameras().catch(() => []);
        if (ids.length > 0) {
            cameras.value = ids;
            if (cameraIndex.value >= ids.length) {
                cameraIndex.value = 0;
            }
        }
        cameraScanner = new Html5Qrcode('barcode-camera-viewport', { verbose: false });
        const device = cameras.value[cameraIndex.value]?.id
            ? { deviceId: { exact: cameras.value[cameraIndex.value].id } }
            : { facingMode: 'environment' };
        await cameraScanner.start(device, { fps: 10, qrbox: { width: 250, height: 250 } }, onLiveSuccess, onLiveFailure);
        scanning.value = true;
    } catch {
        toast.error('Gagal mengakses kamera. Periksa izin kamera browser.');
    }
}

async function stopCamera(silent = false) {
    if (cameraScanner && scanning.value) {
        try {
            await cameraScanner.stop();
        } catch {
            // abaikan: scanner mungkin sudah berhenti
        }
        try {
            await cameraScanner.clear();
        } catch {
            // abaikan
        }
    }
    cameraScanner = null;
    scanning.value = false;
    if (!silent) {
        toast.info('Kamera dihentikan.');
    }
}

async function switchCamera() {
    if (cameras.value.length < 2) {
        toast.warning('Hanya satu kamera yang terdeteksi.');
        return;
    }
    cameraIndex.value = (cameraIndex.value + 1) % cameras.value.length;
    const keepScanning = scanning.value;
    await stopCamera(true);
    if (keepScanning) {
        await startCamera();
    }
}

// ---------- Input file / paste / drop ----------

function onBrowse() {
    fileInput.value?.click();
}

function onFileChange(e) {
    handleFile(e.target.files?.[0]);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function onDrop(e) {
    e.preventDefault();
    dropActive.value = false;
    handleFile(e.dataTransfer.files?.[0]);
}

function onPaste(e) {
    const items = e.clipboardData?.items ?? [];
    for (const item of items) {
        if (item.kind === 'file' && String(item.type || '').startsWith('image/')) {
            const file = item.getAsFile();
            if (file) {
                handleFile(file);
                toast.info('Gambar dari clipboard dimuat. Sesuaikan potongan lalu pindai.');
            }
            break;
        }
    }
}

// ---------- Tabel hasil ----------

function onCopyItem(row) {
    copyText(row.hasil, 'Hasil pindaian disalin ke clipboard.');
}

function onCopyTable() {
    copyRows(exportRows.value, exportColumns, 'hasil pindaian');
}

function onDownload() {
    downloadCsv('barcode-scan', exportRows.value, exportColumns);
}

function removeItem(id) {
    scans.value = scans.value.filter((s) => s.id !== id);
    table.setRows(scans.value);
}

function clearHistory() {
    scans.value = [];
    table.setRows([]);
    toast.info('Riwayat pindaian dibersihkan.');
}

onMounted(() => {
    window.addEventListener('paste', onPaste);
});

onBeforeUnmount(() => {
    window.removeEventListener('paste', onPaste);
    destroyCropper();
    stopCamera(true);
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
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Sumber Pindaian</h2>

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
                    <p class="text-sm font-medium">Seret gambar ke sini, tempel dari clipboard (Ctrl+V), atau</p>
                    <Button size="sm" variant="outline"><Upload /> Pilih Gambar</Button>
                    <p class="text-xs text-muted-foreground">Mendukung QR, Code 128, EAN, UPC, dan format umum lain</p>
                </div>

                <div v-if="previewSrc && !scanning" class="mt-3 space-y-3">
                    <div class="max-h-[60vh] overflow-hidden rounded-lg border bg-slate-100">
                        <img ref="cropImg" :src="previewSrc" alt="Potong area kode" class="block max-w-full" />
                    </div>
                    <p v-if="fileLabel" class="truncate text-xs text-muted-foreground">{{ fileLabel }}</p>
                    <p class="text-xs text-muted-foreground">Seret untuk mengatur kotak potongan pada area barcode / QR, lalu pindai. Ulangi untuk memindai berkali-kali.</p>

                    <div class="flex flex-wrap gap-2">
                        <Button size="sm" :disabled="!cropReady || processing" @click="scanCrop"><Crop /> Pindai Potongan</Button>
                        <Button variant="secondary" size="sm" :disabled="processing" @click="scanFull"><ScanLine /> Pindai Penuh</Button>
                        <Button variant="outline" size="sm" :disabled="!cropReady" @click="rotateCrop"><RotateCw /> Putar</Button>
                        <Button variant="outline" size="sm" :disabled="!cropReady" @click="resetCrop"><RotateCcw /> Reset Potongan</Button>
                        <Button variant="ghost" size="sm" @click="resetSource()"><Trash2 /> Bersihkan</Button>
                    </div>
                    <p v-if="processing" class="text-xs font-medium text-muted-foreground">Memindai gambar...</p>
                </div>

                <div v-show="scanning" class="mt-3">
                    <div id="barcode-camera-viewport" class="overflow-hidden rounded-lg border bg-black" />
                    <p class="mt-2 text-xs text-muted-foreground">Arahkan kamera ke barcode / QR. Hasil otomatis masuk tabel.</p>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <Button v-if="!scanning" size="sm" variant="outline" @click="startCamera"><Camera /> Mulai Kamera</Button>
                    <Button v-else variant="destructive" size="sm" @click="stopCamera()"><Square /> Hentikan</Button>
                    <Button v-if="hasMultiCam" variant="outline" size="sm" @click="switchCamera"><SwitchCamera /> Ganti Kamera</Button>
                    <Button v-if="hasSource" variant="outline" size="sm" @click="onBrowse"><Upload /> Ganti Gambar</Button>
                </div>

                <div class="mt-3 flex items-start gap-2 rounded-md border border-slate-200 bg-slate-50 p-3 text-xs text-muted-foreground">
                    <ClipboardPaste class="mt-0.5 size-4 shrink-0" />
                    <span>Tempel gambar langsung dengan Ctrl+V di halaman ini. Kamera butuh izin browser dan koneksi internet untuk memuat pustaka pindai pertama kali.</span>
                </div>

                <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
                <div id="barcode-file-tmp" class="hidden" />
            </Card>

            <Card class="p-4 sm:p-6">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Hasil Pindaian ({{ table.total.value }})</h2>
                    <QrCode class="size-4 text-muted-foreground" />
                </div>

                <div class="mt-3">
                    <TableToolbar v-model="searchBox" placeholder="Cari hasil atau format..." @search="table.setSearch">
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
                        empty-message="Pindai lewat kamera atau potong lalu pindai gambar berisi barcode / QR."
                        @update:page="table.setPage"
                        @update:per-page="table.setPerPage"
                    >
                        <template #cell-hasil="{ row }">
                            <span class="break-all font-mono text-xs">{{ row.hasil }}</span>
                        </template>
                        <template #actions="{ row }">
                            <div class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" @click="onCopyItem(row)"><Copy /> Salin</Button>
                                <Button variant="ghost" size="sm" @click="removeItem(row.id)"><Trash2 /></Button>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
