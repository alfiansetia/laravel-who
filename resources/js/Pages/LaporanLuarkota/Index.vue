<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { ArrowLeft, ChevronDown, Download, Eraser, FileSpreadsheet, MessageCircle, RotateCcw, Trash2, Truck, Upload } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useBlock } from '@/composables/useBlock';
import api from '@/lib/axios';
import { formatNumber } from '@/lib/format';
import { getSheetMatrix, loadExcelWorkbook, workbookSheetNames } from '@/lib/excel';

const props = defineProps({
    title: { type: String, default: 'Laporan Luar Kota' },
});

const TRACK_BATCH = 20;
const TEMPLATE_KEY = 'luarkota-template';

const DEFAULT_TEMPLATE = 'Selamat Siang Bpk/Ibu *{confirm_with}*, Perkenalkan saya dari *PT Mitra Asa Pratama*. Kami informasikan pengiriman dengan No Do:{no_do}, Tgl Kirim:{tgl_kirim}, Customer/Area:{customer}/{area}, Ekspedisi/Jenis:{ekspedisi}/{jenis_kiriman}, Koli:{koli}, Isi:{jenis_barang}. Mohon konfirmasinya. Terima kasih.';

const COLUMNS = [
    'tgl_kirim', 'no_do', 'tgl_do', 'customer', 'area', 'no_telp', 'ekspedisi', 'jenis_kiriman',
    'tgl_estimasi', 'tgl_real', 'tgl_confirm', 'confirm_with', 'con_brg_y', 'con_brg_n', 'con_qty_y', 'con_qty_n',
    'no_resi', 'jenis_barang', 'koli', 'berat_estimasi', 'berat_real', 'ongkir_estimasi', 'ongkir_real',
];

const HIDDEN_DEFAULT = ['con_brg_y', 'con_brg_n', 'con_qty_y', 'con_qty_n', 'berat_estimasi', 'berat_real', 'ongkir_estimasi', 'ongkir_real'];

const COLUMN_LABELS = {
    tgl_kirim: 'Tgl Kirim', no_do: 'No DO', tgl_do: 'Tgl DO', customer: 'Customer', area: 'Area', no_telp: 'No Telp',
    ekspedisi: 'Ekspedisi', jenis_kiriman: 'Jenis Kirim', tgl_estimasi: 'Tgl Estimasi', tgl_real: 'Tgl Real',
    tgl_confirm: 'Tgl Confirm', confirm_with: 'Confirm Dgn', con_brg_y: 'Brg Y', con_brg_n: 'Brg N',
    con_qty_y: 'Qty Y', con_qty_n: 'Qty N', no_resi: 'No Resi', jenis_barang: 'Jenis Barang', koli: 'Koli',
    berat_estimasi: 'Berat Est', berat_real: 'Berat Real', ongkir_estimasi: 'Ongkir Est', ongkir_real: 'Ongkir Real',
};

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();

const excelInput = ref(null);
const dropActive = ref(false);
const importing = ref(false);
const workbook = ref(null);
const sheetNames = ref([]);
const activeSheet = ref('');
const showSheets = ref(false);

const rawRows = ref([]);
const tglFilter = ref('');
const visibleCols = ref(COLUMNS.filter((c) => !HIDDEN_DEFAULT.includes(c)));
const searchBox = ref('');

const templateText = ref(localStorage.getItem(TEMPLATE_KEY) || DEFAULT_TEMPLATE);

const trackingOpen = ref(false);
const trackingLoading = ref(false);
const trackingGroups = ref([]);
const expandedTrack = ref(0);

let rowSeq = 0;

const table = useClientTable(null, { perPage: 10, searchKeys: COLUMNS });

const tglOptions = computed(() => {
    const set = new Set(rawRows.value.map((r) => r.tgl_kirim).filter(Boolean));
    return [{ label: 'Semua Tanggal', value: '' }, ...[...set].sort().map((t) => ({ label: t, value: t }))];
});

const tableColumns = computed(() => visibleCols.value.map((c) => ({ key: c, label: COLUMN_LABELS[c] ?? c })));

const columnOptions = computed(() => COLUMNS.map((c) => ({ label: COLUMN_LABELS[c] ?? c, value: c })));

function excelDateToString(value) {
    if (value === null || value === undefined || value === '') {
        return '';
    }
    if (value instanceof Date) {
        if (Number.isNaN(value.getTime())) {
            return '';
        }
        const pad = (v) => String(v).padStart(2, '0');
        return `${pad(value.getDate())}/${pad(value.getMonth() + 1)}/${value.getFullYear()}`;
    }
    const serial = value;
    if (typeof serial === 'string' && Number.isNaN(Number(serial))) {
        return serial;
    }
    const n = Number(serial);
    if (!Number.isFinite(n)) {
        return String(serial ?? '');
    }
    const d = new Date(Date.UTC(1899, 11, 30) + n * 86400000);
    const pad = (v) => String(v).padStart(2, '0');
    return `${pad(d.getUTCDate())}/${pad(d.getUTCMonth() + 1)}/${d.getUTCFullYear()}`;
}

function parsePhone(value) {
    let s = String(value ?? '').replace(/[^0-9+]/g, '');
    if (s.startsWith('+62')) {
        s = `62${s.slice(3)}`;
    } else if (s.startsWith('0')) {
        s = `62${s.slice(1)}`;
    }
    return s;
}

function parseDecimal(value) {
    if (value === null || value === undefined || value === '') {
        return '0';
    }
    const n = Number(String(value).replace(/[^0-9.,-]/g, '').replace(/,/g, '.'));
    if (!Number.isFinite(n)) {
        return '0';
    }
    return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function applyView() {
    const list = tglFilter.value ? rawRows.value.filter((r) => r.tgl_kirim === tglFilter.value) : rawRows.value;
    table.setRows(list);
}

async function handleFile(file) {
    if (!file) {
        return;
    }
    if (!/\.xlsx$/i.test(file.name)) {
        toast.warning('Simpan sebagai .xlsx dulu, file .xls tidak didukung.');
        return;
    }
    importing.value = true;
    try {
        await withBlock(async () => {
            const buffer = await file.arrayBuffer();
            workbook.value = await loadExcelWorkbook(buffer);
            sheetNames.value = workbookSheetNames(workbook.value);
        });
        showSheets.value = true;
        activeSheet.value = '';
        toast.info('Pilih sheet untuk dimuat.');
    } catch (e) {
        toast.error(e.message ?? 'Gagal membaca Excel.');
    } finally {
        importing.value = false;
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

function loadSheet(name) {
    if (!workbook.value) {
        return;
    }
    withBlock(async () => {
        // Beri kesempatan overlay ter-render sebelum parse berat memblokir thread.
        await new Promise((r) => requestAnimationFrame(() => r()));
        activeSheet.value = name;
        if (!workbookSheetNames(workbook.value).includes(name)) {
            toast.warning('Sheet tidak ditemukan.');
            return;
        }
        const json = await getSheetMatrix(workbook.value, name);
        const result = [];
        json.forEach((row) => {
            if (!Array.isArray(row) || !Number.isInteger(row[0])) {
                return;
            }
            if (row[0] === null || row[1] === null || row[2] === null || row[3] === null) {
                return;
            }
            rowSeq += 1;
            result.push({
                id: rowSeq,
                tgl_kirim: excelDateToString(row[0]),
                no_do: row[1] ?? null,
                tgl_do: excelDateToString(row[2]),
                customer: row[3] ?? null,
                area: row[4] ?? null,
                no_telp: parsePhone(row[5]),
                ekspedisi: row[6] ?? null,
                jenis_kiriman: row[7] ?? null,
                tgl_estimasi: excelDateToString(row[8]),
                tgl_real: excelDateToString(row[9]),
                tgl_confirm: excelDateToString(row[10]),
                confirm_with: row[11] ?? null,
                con_brg_y: row[12] ?? null,
                con_brg_n: row[13] ?? null,
                con_qty_y: row[14] ?? null,
                con_qty_n: row[15] ?? null,
                no_resi: row[16] ?? null,
                jenis_barang: row[17] ?? null,
                koli: row[18] ?? null,
                berat_estimasi: parseDecimal(row[19]),
                berat_real: parseDecimal(row[20]),
                ongkir_estimasi: row[21] ?? null,
                ongkir_real: row[22] ?? null,
            });
        });
        rawRows.value = result;
        tglFilter.value = '';
        applyView();
        if (result.length === 0) {
            toast.warning('Tidak ada data valid pada sheet ini.');
        } else {
            toast.success(`Berhasil impor ${result.length} data.`);
        }
    });
}

function resetImport() {
    workbook.value = null;
    sheetNames.value = [];
    activeSheet.value = '';
    showSheets.value = false;
    rawRows.value = [];
    tglFilter.value = '';
    applyView();
}

function parseTemplate(data) {
    return String(templateText.value).replace(/{(.*?)}/g, (_, key) => data[key.trim()] ?? '');
}

function insertVariable(col) {
    templateText.value += `{${col}}`;
}

function saveTemplate() {
    localStorage.setItem(TEMPLATE_KEY, templateText.value || DEFAULT_TEMPLATE);
    if (!templateText.value) {
        templateText.value = DEFAULT_TEMPLATE;
    }
    toast.success('Template tersimpan.');
}

function resetTemplate() {
    localStorage.setItem(TEMPLATE_KEY, DEFAULT_TEMPLATE);
    templateText.value = DEFAULT_TEMPLATE;
    toast.success('Template dikembalikan bawaan.');
}

function sendWA(row) {
    if (!row.no_telp) {
        toast.error('Nomor telepon kosong.');
        return;
    }
    const text = encodeURIComponent(parseTemplate(row));
    window.open(`https://api.whatsapp.com/send/?phone=${row.no_telp}&text=${text}`, '_blank');
}

function removeRow(id) {
    rawRows.value = rawRows.value.filter((r) => r.id !== id);
    applyView();
}

async function clearRows() {
    const ok = await confirm({
        title: 'Hapus semua data?',
        message: `${rawRows.value.length} baris preview akan dihapus.`,
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (ok) {
        rawRows.value = [];
        applyView();
    }
}

function statusColor(status) {
    const s = String(status ?? '').toUpperCase();
    if (s === 'MDE') {
        return 'bg-blue-100 text-blue-700 border-blue-200';
    }
    if (s === 'INC') {
        return 'bg-sky-100 text-sky-700 border-sky-200';
    }
    if (s === 'CON') {
        return 'bg-amber-100 text-amber-700 border-amber-200';
    }
    if (['ROS', 'DEL', 'POD'].includes(s)) {
        return 'bg-green-100 text-green-700 border-green-200';
    }
    return 'bg-slate-100 text-slate-600 border-slate-200';
}

async function trackTiki() {
    const eligible = table.filtered.filter((r) => String(r.ekspedisi ?? '').toUpperCase() === 'TIKI' && String(r.no_resi ?? '').trim() !== '');
    if (eligible.length === 0) {
        toast.error('Tidak ada data TIKI dengan No Resi.');
        return;
    }
    const resiToDo = {};
    eligible.forEach((r) => {
        resiToDo[String(r.no_resi).trim()] = r.no_do;
    });
    const resiList = Object.keys(resiToDo);
    trackingOpen.value = true;
    trackingLoading.value = true;
    trackingGroups.value = [];
    try {
        const batches = [];
        for (let i = 0; i < resiList.length; i += TRACK_BATCH) {
            batches.push(resiList.slice(i, i + TRACK_BATCH));
        }
        const results = await Promise.all(batches.map(async (batch) => {
            try {
                const res = await api.get('/tiki/track', { params: { resi: batch.join(',') }, silent: true });
                const payload = res.data?.data ?? res.data;
                return payload?.response ?? [];
            } catch (e) {
                toast.error(e.response?.data?.message ?? 'Gagal memuat tracking TIKI.');
                return [];
            }
        }));
        const all = results.flat();
        const grouped = {};
        all.forEach((item) => {
            const cnno = item.cnno;
            if (!cnno) {
                return;
            }
            (grouped[cnno] ??= []).push(item);
        });
        trackingGroups.value = Object.entries(grouped).map(([cnno, items]) => {
            const history = items.flatMap((i) => i.history ?? []).sort((a, b) => String(b.entry_date ?? '').localeCompare(String(a.entry_date ?? '')));
            return { cnno, noDo: resiToDo[cnno] ?? '', items: items[0], history, last: history[0] ?? {} };
        });
        expandedTrack.value = 0;
    } finally {
        trackingLoading.value = false;
    }
}

function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

watch(tglFilter, () => applyView());

onBeforeUnmount(() => {
    // tidak ada stream/worker yang perlu dibersihkan
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" />

        <div class="grid gap-4 lg:grid-cols-[5fr_7fr]">
            <Card class="p-4">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold"><FileSpreadsheet class="size-4" /> Impor Data</h2>
                <div
                    role="button"
                    tabindex="0"
                    class="flex min-h-32 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                    :class="dropActive ? 'border-primary bg-primary/5' : 'border-input hover:border-primary'"
                    @click="onBrowse"
                    @keydown.enter="onBrowse"
                    @dragover.prevent="dropActive = true"
                    @dragleave="dropActive = false"
                    @drop="onDrop"
                >
                    <Upload class="size-6 text-muted-foreground" />
                    <p class="text-sm font-medium">Seret Excel ke sini atau klik</p>
                    <p class="text-xs text-muted-foreground">.xlsx • {{ importing ? 'membaca...' : 'maks 1 berkas' }}</p>
                </div>
                <input ref="excelInput" type="file" accept=".xlsx" class="hidden" @change="onFileChange" />

                <div v-if="showSheets" class="mt-3 space-y-2">
                    <p class="text-sm font-medium">Pilih sheet:</p>
                    <div class="flex flex-wrap gap-1">
                        <Button
                            v-for="name in sheetNames"
                            :key="name"
                            size="sm"
                            :variant="activeSheet === name ? 'default' : 'outline'"
                            @click="loadSheet(name)"
                        >
                            {{ name }}
                        </Button>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as="a" :href="route('index')"><ArrowLeft /> Kembali</Button>
                    <Button variant="secondary" size="sm" @click="resetImport"><RotateCcw /> Ulangi</Button>
                </div>
            </Card>

            <Card class="p-4">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold"><MessageCircle class="size-4" /> Template Kirim Pesan</h2>
                <Textarea v-model="templateText" rows="5" class="font-mono text-xs" />
                <div class="mt-2 flex flex-wrap gap-1">
                    <button
                        v-for="col in COLUMNS"
                        :key="col"
                        type="button"
                        class="rounded-md border border-input bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] hover:bg-accent"
                        :title="`Sisipkan {${col}}`"
                        @click="insertVariable(col)"
                    >
                        {{ '{' + col + '}' }}
                    </button>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="resetTemplate">Reset Bawaan</Button>
                    <Button size="sm" @click="saveTemplate"><Download /> Simpan Template</Button>
                </div>
            </Card>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <Input :model-value="searchBox" type="search" placeholder="Cari data..." class="max-w-xs" @input="onSearchInput" />
            <SearchableSelect v-model="tglFilter" :options="tglOptions" placeholder="Semua Tanggal" class="w-48" />
            <MultiSelect v-model="visibleCols" :options="columnOptions" placeholder="Kolom tampil" class="w-52" />
            <span class="rounded-md bg-primary px-2 py-1 text-xs font-bold text-primary-foreground">{{ table.total.value }} data</span>
            <div class="flex flex-wrap gap-2">
                <Button variant="destructive" size="sm" :disabled="rawRows.length === 0" @click="clearRows"><Eraser /> Hapus Data</Button>
                <Button variant="outline" size="sm" :disabled="rawRows.length === 0" @click="trackTiki"><Truck /> Tracking Tiki</Button>
            </div>
        </div>

        <div class="mt-3">
            <DataTable
                :columns="tableColumns"
                :rows="table.rows.value"
                :loading="false"
                :page="table.page.value"
                :total-pages="table.totalPages.value"
                :total="table.total.value"
                :per-page="table.perPage.value"
                :per-page-options="[10, 25, 50, 100]"
                row-key="id"
                empty-title="Belum ada data"
                empty-message="Impor Excel lalu pilih sheet."
                @update:page="table.setPage"
                @update:per-page="table.setPerPage"
            >
                <template v-for="col in tableColumns" :key="col.key" #[`cell-${col.key}`]="{ row }">
                    {{ row[col.key] ?? '-' }}
                </template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1">
                        <Button variant="outline" size="sm" title="Kirim WA" @click="sendWA(row)"><MessageCircle class="text-green-600" /></Button>
                        <Button variant="ghost" size="sm" title="Hapus baris" @click="removeRow(row.id)"><Trash2 class="text-destructive" /></Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <AppModal v-model:open="trackingOpen" title="Tracking TIKI" size="xl">
            <div v-if="trackingLoading" class="flex items-center justify-center gap-2 p-8 text-sm text-muted-foreground">
                <span class="size-5 animate-spin rounded-full border-2 border-primary border-t-transparent" /> Memuat data tracking...
            </div>
            <div v-else-if="trackingGroups.length === 0" class="p-8 text-center text-sm text-muted-foreground">
                Tidak ada hasil tracking.
            </div>
            <div v-else class="space-y-2">
                <div v-for="(g, idx) in trackingGroups" :key="g.cnno" class="overflow-hidden rounded-lg border">
                    <button type="button" class="flex w-full items-center justify-between gap-2 bg-slate-50 px-3 py-2 text-left hover:bg-slate-100" @click="expandedTrack = expandedTrack === idx ? -1 : idx">
                        <span class="flex min-w-0 flex-wrap items-center gap-2 text-sm">
                            <strong>{{ g.noDo }}</strong>
                            <code class="rounded bg-slate-200 px-1 font-mono text-xs">{{ g.cnno }}</code>
                        </span>
                        <span class="flex shrink-0 items-center gap-2">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium" :class="statusColor(g.last.status)">
                                {{ g.last.status ?? '-' }}
                            </span>
                            <ChevronDown class="size-4 transition-transform" :class="expandedTrack === idx && 'rotate-180'" />
                        </span>
                    </button>
                    <div v-if="expandedTrack === idx" class="grid gap-3 border-t p-3 text-sm sm:grid-cols-2">
                        <dl class="space-y-0.5 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">No Resi</dt><dd class="font-medium">{{ g.cnno }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">No DO</dt><dd class="font-medium">{{ g.noDo }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Pengirim</dt><dd class="text-right">{{ g.items.consignor_name ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Penerima</dt><dd class="text-right">{{ g.items.consignee_name ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Tujuan</dt><dd class="text-right">{{ g.items.destination_city_name ?? '-' }}</dd></div>
                        </dl>
                        <dl class="space-y-0.5 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Berat</dt><dd>{{ g.items.weight ?? '-' }} kg</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Ongkir</dt><dd>Rp {{ formatNumber(g.items.shipment_fee ?? 0) }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Asuransi</dt><dd>Rp {{ formatNumber(g.items.insurance_fee ?? 0) }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Estimasi</dt><dd>{{ g.items.est_day ?? '-' }} hari ({{ g.items.est_date ?? '-' }})</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Koli</dt><dd>{{ g.items.pieces_no ?? '-' }}</dd></div>
                        </dl>
                        <div class="rounded-md border p-2 text-sm sm:col-span-2" :class="statusColor(g.last.status)">
                            <b>{{ g.last.status ?? '-' }}</b> — {{ g.last.noted ?? '' }}<br />
                            <span class="text-xs">{{ g.last.entry_name ?? '' }} • {{ g.last.entry_date ?? '' }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Riwayat Tracking</p>
                            <ol class="space-y-1.5 border-l-2 border-slate-200 pl-3">
                                <li v-for="(h, hi) in g.history" :key="hi" class="text-sm">
                                    <span class="inline-flex items-center rounded border px-1.5 py-px text-[11px] font-medium" :class="statusColor(h.status)">{{ h.status }}</span>
                                    <span class="ml-1 text-xs text-muted-foreground">{{ h.entry_date }}</span>
                                    <p>{{ h.noted }}</p>
                                    <p class="text-xs text-muted-foreground">{{ h.entry_name }}<span v-if="h.entry_place"> ({{ h.entry_place }})</span></p>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">{{ trackingGroups.length }} resi ditemukan.</p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="trackingOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <ConfirmDialog />
    </AppLayout>
</template>
