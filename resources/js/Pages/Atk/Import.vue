<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Download, Save, Trash2, Upload, X } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useToast } from '@/composables/useToast';
import { useBlock } from '@/composables/useBlock';
import { getSheetMatrix, loadExcelWorkbook } from '@/lib/excel';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Import Data ATK' },
    satuanList: { type: Array, default: () => ['pcs', 'dus', 'pack', 'kotak', 'lusin', 'pad', 'rim', 'roll', 'tube', 'box', 'buah', 'buku'] },
});

const toast = useToast();
const { withBlock } = useBlock();
const rows = ref([]);
const skipped = ref([]);
const serverErrors = ref({});
const fileInput = ref(null);
const dragging = ref(false);
const fileName = ref('');
const fileSizeHuman = ref('');
const table = useClientTable(null, { searchKeys: ['code', 'name', 'satuan'] });
function syncTable() {
    table.setRows([...rows.value]);
}

const allowedSet = computed(() => new Set(props.satuanList.map((s) => String(s).toLowerCase())));

const columns = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama' },
    { key: 'satuan', label: 'Satuan', align: 'center' },
];
const searchBox = ref('');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

function downloadTemplate() {
    window.open('/master/template_atk.xlsx', '_blank');
}

function clearAll() {
    rows.value = [];
    skipped.value = [];
    serverErrors.value = {};
    fileName.value = '';
    fileSizeHuman.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    syncTable();
}

function formatSize(bytes) {
    if (!bytes) {
        return '-';
    }
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${units[i]}`;
}

function parseMatrix(matrix) {
    const out = [];
    const bad = [];
    matrix.forEach((r, idx) => {
        const cells = Array.isArray(r) ? r : [];
        const isFirst = idx === 0;
        const c0 = String(cells[0] ?? '').trim();
        if (isFirst && /kode/i.test(c0)) {
            return;
        }
        if (cells.every((v) => v === null || v === undefined || String(v).trim() === '')) {
            return;
        }
        const lineNo = idx + 1;
        const code = String(cells[0] ?? '').trim();
        const name = String(cells[1] ?? '').trim();
        const satuan = String(cells[2] ?? '').trim().toLowerCase();
        if (!code || !name || !satuan) {
            bad.push(`Baris ${lineNo}: kode/nama/satuan wajib diisi.`);
            return;
        }
        if (!allowedSet.value.has(satuan)) {
            bad.push(`Baris ${lineNo}: satuan "${satuan}" tidak dikenal. Pilihan: ${props.satuanList.join(', ')}.`);
            return;
        }
        out.push({ code, name, satuan });
    });
    return { out, bad };
}

async function parseFile(file) {
    await withBlock(async () => {
        await new Promise((r) => requestAnimationFrame(() => r()));
        serverErrors.value = {};
        const lower = file.name.toLowerCase();
        try {
            let matrix = [];
            if (lower.endsWith('.xlsx')) {
                const buf = await file.arrayBuffer();
                const wb = await loadExcelWorkbook(buf);
                matrix = await getSheetMatrix(wb, 0);
            } else if (lower.endsWith('.csv') || lower.endsWith('.txt')) {
                const text = await file.text();
                if (!text.trim()) {
                    rows.value = [];
                    skipped.value = [];
                    syncTable();
                    toast.error('File kosong: tidak ada data untuk diimport.');
                    return;
                }
                matrix = text.split(/\r?\n/).map((line) => line.split(/[\t;,]/));
            } else {
                toast.error('Format tidak didukung. Pakai template .xlsx atau file .csv.');
                return;
            }
            if (matrix.length === 0) {
                rows.value = [];
                skipped.value = [];
                syncTable();
                toast.error('File kosong: tidak ada data untuk diimport.');
                return;
            }
            const { out, bad } = parseMatrix(matrix);
            rows.value = out;
            skipped.value = bad;
            syncTable();
            if (out.length === 0) {
                toast.error(`Tidak ada data valid di file. ${bad.length} baris dilewati.`);
            } else if (bad.length > 0) {
                toast.warning(`${out.length} baris dimuat, ${bad.length} baris dilewati (tidak valid).`);
            } else {
                toast.success(`${out.length} baris dimuat dari file.`);
            }
        } catch (err) {
            rows.value = [];
            skipped.value = [];
            syncTable();
            toast.error('Gagal membaca file. Pakai template .xlsx atau simpan sebagai .xlsx/.csv dulu.');
        }
    });
}

function pickFile(file) {
    if (!file) {
        return;
    }
    const lower = file.name.toLowerCase();
    if (!lower.endsWith('.xlsx') && !lower.endsWith('.csv') && !lower.endsWith('.txt')) {
        toast.error('Format tidak didukung. Pakai template .xlsx atau file .csv.');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        toast.error('Ukuran file maksimal 10MB.');
        return;
    }
    fileName.value = file.name;
    fileSizeHuman.value = formatSize(file.size);
    parseFile(file);
}

function onPickFile() {
    const file = fileInput.value?.files?.[0];
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    pickFile(file);
}

function onDrop(e) {
    dragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    pickFile(file);
}

function removeRow(row) {
    const i = rows.value.indexOf(row);
    if (i >= 0) {
        rows.value.splice(i, 1);
        syncTable();
    }
}

async function saveAll() {
    serverErrors.value = {};
    if (rows.value.length === 0) {
        toast.warning('Tidak ada data untuk disimpan. Pilih file .xlsx/.csv dulu atau unduh template.');
        return;
    }
    try {
        const res = await api.post('/atk-import', { data: rows.value }, { block: true });
        const count = rows.value.length;
        const summary = res.data?.data;
        const msg = res.data?.message ?? `${count} ATK diimport.`;
        toast.success(summary ? msg : `${count} ATK diimport.`);
        clearAll();
    } catch (e) {
        const status = e.response?.status;
        const errors = e.response?.data?.errors ?? {};
        if (status === 422 && Object.keys(errors).length > 0) {
            serverErrors.value = errors;
            const first = Object.values(errors).flat()[0];
            toast.error(first ?? e.response?.data?.message ?? 'Validasi gagal. Periksa baris yang ditandai.');
        } else {
            toast.error(e.response?.data?.message ?? 'Gagal import ATK.');
        }
    }
}

const tableRows = computed(() => table.filtered.value);
const serverErrorList = computed(() => Object.entries(serverErrors.value).slice(0, 8).map(([k, v]) => `${k}: ${(Array.isArray(v) ? v : [v]).join(' ')}`));
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="ghost" size="sm" @click="router.visit('/atk')"><ArrowLeft /> Kembali</Button>
                <Button variant="outline" size="sm" @click="downloadTemplate"><Download /> Unduh Template</Button>
                <Button variant="outline" size="sm" @click="clearAll"><Trash2 /> Hapus Data</Button>
                <Button size="sm" @click="saveAll"><Save /> Simpan Data ({{ rows.length }})</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <Card class="p-4">
                <FormField label="File Excel / CSV">
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
                        <p class="text-xs text-muted-foreground">.xlsx / .csv / .txt • maks 10MB</p>
                        <input ref="fileInput" type="file" accept=".xlsx,.csv,.txt" class="hidden" @change="onPickFile" />
                    </div>
                    <div v-if="fileName" class="mt-2 flex items-center justify-between gap-2 rounded-md border bg-slate-50 px-2 py-1.5 text-xs">
                        <span class="min-w-0 truncate font-medium">{{ fileName }} <span class="text-muted-foreground">({{ fileSizeHuman }})</span></span>
                        <button type="button" title="Hapus file" class="shrink-0 rounded p-1 text-muted-foreground hover:bg-accent hover:text-accent-foreground" @click="clearAll"><X class="size-3.5" /></button>
                    </div>
                </FormField>
                <p class="mt-2 text-xs text-muted-foreground">
                    Unduh <b>Template</b> (<span class="font-mono">/master/template_atk.xlsx</span>, kolom: KODE, NAME, Satuan),
                    isi, lalu upload lagi. Satuan yang diizinkan: {{ satuanList.join(', ') }}.
                    File lama <span class="font-mono">.xls</span> tidak didukung ExcelJS — Save As <span class="font-mono">.xlsx</span> dulu.
                </p>
                <p v-if="skipped.length > 0" class="mt-2 text-xs text-amber-600">
                    {{ skipped.length }} baris dilewati: {{ skipped.slice(0, 3).join(' ') }}{{ skipped.length > 3 ? ' ...' : '' }}
                </p>
                <div v-if="serverErrorList.length > 0" class="mt-2 rounded border border-destructive/30 bg-destructive/5 p-2">
                    <p class="text-xs font-semibold text-destructive">Validasi server menolak {{ Object.keys(serverErrors).length }} baris:</p>
                    <ul class="mt-1 list-disc pl-4 text-xs text-destructive">
                        <li v-for="(msg, i) in serverErrorList" :key="i">{{ msg }}</li>
                    </ul>
                </div>
            </Card>
            <FormField label="Cari preview">
                <Input :model-value="searchBox" type="search" placeholder="Cari kode / nama..." @input="onSearchInput" />
            </FormField>
            <DataTable
                :columns="columns"
                :rows="tableRows"
                :loading="false"
                :show-footer="false"
                empty-title="Belum ada data"
                empty-message="Pilih file .xlsx/.csv di atas atau unduh template."
            >
                <template #actions="{ row }">
                    <Button variant="ghost" size="sm" @click="removeRow(row)"><Trash2 /></Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
