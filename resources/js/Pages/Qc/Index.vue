<script setup>
import { computed, onMounted, ref } from 'vue';
import { ArrowLeft, Ban, Check, ChevronDown, ChevronUp, CircleCheck, Copy, Download, Eraser, Eye, LoaderCircle, Minus, Package, PackagePlus, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import TerlampirButton from './partials/TerlampirButton.vue';
import QcRadioGroup from './partials/QcRadioGroup.vue';
import api from '@/lib/axios';
import web from '@/lib/web';
import { copyText } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Form QC' },
    defaultDate: { type: String, default: '' },
});

const PREFIX_MAP = {
    '3a': '3A', bdf: 'BEDFONT', djo: 'CHATTANOOGA', glb: 'GENERAL LIFE B', hc: 'HISENSE', ibd: 'INBODY',
    cw: 'CAREWELL', jpd: 'JUMPER', mrid: 'MINDRAY', lfn: 'LIFOTRONIC', pdl: 'PT Mitra Artha Pandulang',
    phi: 'PHILIPS', pts: 'POLIMER TECHNOLOGY', nox: 'NOXBOX', nx: 'NOXBOX', var: 'VARITEKS',
};

const FISIK_DEFAULT = [
    'Kondisi Fisik',
    'Pembungkus alat dalam kardus atau peti',
    'Pengaman alat dalam kardus atau peti',
    'Kondisi kardus atau peti',
    'Penempelan penandaan AKL dialat',
    'Penempelan nomor izin edar pada dus',
];

const REAGEN_DEFAULT = [
    'Pemeriksaan keakuratan',
    'Pemeriksaan suhu',
    'Panel saklar ON/OFF',
    'Sistem kerja alat',
];

const KEL_DEFAULT = ['Buku Manual Bahasa Indonesia', 'Kabel Power', 'SOP'];

const toast = useToast();
const { confirm } = useConfirm();

function defaultDate() {
    if (props.defaultDate) {
        return props.defaultDate;
    }
    const d = new Date();
    d.setDate(d.getDate() - (d.getDay() === 1 ? 3 : 1));
    return d.toISOString().slice(0, 10);
}

const selectedProduct = ref('');
const productOptions = ref([]);
const productLoading = ref(false);
const pic = ref('Karim A S');
const showDetail = ref(false);

const form = ref({
    no: 1,
    tgl: defaultDate(),
    name: '',
    merk: '',
    type: '',
    sn_lot: 'Tanpa Lot/Sn',
    qty: '1 Unit',
    jenis: 'QC Import',
    qc_sebelumnya: new Date().getFullYear(),
    jenis_qc: 'Uji Fungsi & Check kelengkapan alat',
});

const fisikRows = ref(FISIK_DEFAULT.map((text) => ({ text, radio: 'other', desc: '' })));
const reagenRows = ref(REAGEN_DEFAULT.map((text) => ({ text, radio: 'other', desc: '' })));
const kelRows = ref(KEL_DEFAULT.map((text) => ({ text, radio: 'other', desc: '' })));

const packModalOpen = ref(false);
const packList = ref([]);
const saving = ref(false);
const getPlLoading = ref(false);
const previewReady = ref(false);
const previewTab = ref('lampiran');

const picOptions = [
    { label: 'Karim', value: 'Karim A S' },
    { label: 'Sofyan', value: 'Sofyan S' },
];
const jenisOptions = ['QC Import', 'QC Ulang'].map((v) => ({ label: v, value: v }));
const jenisQcOptions = [
    { label: 'Uji Fungsi', value: 'Uji Fungsi & Check kelengkapan alat' },
    { label: 'Fisik', value: 'Cek Fisik' },
];

const fisikRadios = [
    { value: 'yes', label: 'Baik' },
    { value: 'no', label: 'Tidak' },
    { value: 'other', label: 'N/A' },
];

const kelRadios = [
    { value: 'yes', label: 'Ada' },
    { value: 'no', label: 'Tidak' },
    { value: 'other', label: 'N/A' },
];

function getPrefix(code) {
    const str = String(code ?? '');
    const pref = str.includes('.') ? str.split('.')[0] : str;
    return PREFIX_MAP[pref.toLowerCase()] ?? pref;
}

async function loadProductsOnce() {
    productLoading.value = true;
    try {
        const res = await api.get('/products', { silent: true });
        const list = res.data?.data ?? [];
        productOptions.value = list.map((p) => ({ label: `[${p.code}] ${p.name}`, value: p.id }));
    } catch {
        productOptions.value = [];
    } finally {
        productLoading.value = false;
    }
}

async function pickProduct() {
    if (!selectedProduct.value) {
        form.value.name = 'TERLAMPIR';
        form.value.merk = 'TERLAMPIR';
        form.value.type = 'TERLAMPIR';
        generatePreview();
        return;
    }
    try {
        const res = await api.get(`/products/${selectedProduct.value}`, { silent: true, block: true });
        const p = res.data?.data ?? {};
        form.value.name = p.name ?? '';
        form.value.merk = getPrefix(p.code);
        form.value.type = p.code ?? '';
        generatePreview();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Produk tidak ditemukan.');
    }
}

function setTerlampir(field) {
    form.value[field] = 'Terlampir';
    generatePreview();
}

function applyFisik(allBaik) {
    fisikRows.value = FISIK_DEFAULT.map((text) => ({ text, radio: allBaik ? 'yes' : 'other', desc: '' }));
    generatePreview();
}

function applyReagen() {
    reagenRows.value = REAGEN_DEFAULT.map((text) => ({ text, radio: 'other', desc: '' }));
    generatePreview();
}

function applyKel() {
    kelRows.value = KEL_DEFAULT.map((text) => ({ text, radio: 'other', desc: '' }));
    generatePreview();
}

async function confirmResetFisik(allBaik) {
    const ok = await confirm({
        title: allBaik ? 'Tandai semua Baik?' : 'Reset Pemeriksaan Fisik?',
        message: 'Isian pemeriksaan fisik akan diganti dengan bawaan.',
        confirmText: 'Ya, lanjutkan',
    });
    if (!ok) {
        return;
    }
    applyFisik(allBaik);
}

async function confirmResetReagen() {
    const ok = await confirm({
        title: 'Reset Fungsi dan Sistem?',
        message: 'Isian fungsi dan sistem akan diganti dengan bawaan.',
        confirmText: 'Ya, lanjutkan',
    });
    if (!ok) {
        return;
    }
    applyReagen();
}

async function confirmResetKel() {
    const ok = await confirm({
        title: 'Reset Kelengkapan?',
        message: 'Daftar kelengkapan aksesoris akan diganti dengan bawaan.',
        confirmText: 'Ya, lanjutkan',
    });
    if (!ok) {
        return;
    }
    applyKel();
}

function addKel(text = '', checked = false) {
    kelRows.value.push({ text, radio: checked ? 'yes' : 'other', desc: '' });
}

function delKel(index) {
    kelRows.value.splice(index, 1);
    generatePreview();
}

async function getPL() {
    if (!selectedProduct.value) {
        toast.warning('Pilih produk dulu.');
        return;
    }
    getPlLoading.value = true;
    try {
        const res = await api.get(`/products/${selectedProduct.value}`, { silent: true, block: true });
        const packs = res.data?.data?.packs ?? [];
        if (packs.length === 0) {
            toast.warning('Tidak ada Packing List untuk produk ini.');
        } else if (packs.length === 1) {
            addPackItems(packs[0]);
        } else {
            packList.value = packs;
            packModalOpen.value = true;
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat Packing List.');
    } finally {
        getPlLoading.value = false;
    }
}

function addPackItems(pack) {
    (pack.items ?? []).forEach((it) => {
        const qty = String(it.qty ?? '').trim();
        addKel(qty ? `${it.item} (${qty})` : String(it.item ?? ''), true);
    });
    packModalOpen.value = false;
    generatePreview();
    toast.success(`Packing List "${pack.name ?? ''}" ditambahkan.`);
}

async function confirmResetAll() {
    const ok = await confirm({
        title: 'Reset semua isian?',
        message: 'Seluruh pemeriksaan (fisik, fungsi, kelengkapan) kembali ke bawaan.',
        confirmText: 'Ya, reset semua',
    });
    if (!ok) {
        return;
    }
    applyFisik(false);
    applyReagen();
    applyKel();
}

const rekapCat = computed(() => `${form.value.jenis === 'QC Ulang' ? 'Ulang' : 'Import'} (${form.value.qc_sebelumnya})`);

function generatePreview() {
    previewReady.value = true;
}

function copyLampiranA() {
    copyText(`${form.value.type}\t${form.value.name}`, 'Lampiran disalin.');
}

function copyLampiranL() {
    copyText(`Kode Barang : ${form.value.type}\nNama Barang : ${form.value.name}`, 'Lampiran disalin.');
}

function buildPayload() {
    const pick = (rows, key) => rows.map((r) => r[key]);
    return {
        no: form.value.no,
        tgl: form.value.tgl,
        name: form.value.name,
        merk: form.value.merk,
        type: form.value.type,
        sn_lot: form.value.sn_lot,
        qty: form.value.qty,
        jenis: form.value.jenis,
        qc_sebelumnya: form.value.qc_sebelumnya,
        jenis_qc: form.value.jenis_qc,
        pic: pic.value,
        fisik: pick(fisikRows.value, 'text'),
        fisik_radio: pick(fisikRows.value, 'radio'),
        fisik_desc: pick(fisikRows.value, 'desc'),
        reagen: pick(reagenRows.value, 'text'),
        reagen_radio: pick(reagenRows.value, 'radio'),
        reagen_desc: pick(reagenRows.value, 'desc'),
        kelengkapan: pick(kelRows.value, 'text'),
        kelengkapan_radio: pick(kelRows.value, 'radio'),
        kelengkapan_desc: pick(kelRows.value, 'desc'),
    };
}

async function submitDownload() {
    if (!form.value.no) {
        toast.warning('Nomor wajib diisi.');
        return;
    }
    saving.value = true;
    try {
        const res = await web.post('/form-qc', buildPayload(), { responseType: 'blob' });
        const disposition = res.headers?.['content-disposition'] ?? '';
        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        const filename = match?.[1]?.replace(/['"]/g, '') ?? 'form-qc.docx';
        const url = URL.createObjectURL(new Blob([res.data]));
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        toast.success('Dokumen QC diunduh.');
    } catch (e) {
        let message = 'Gagal membuat dokumen.';
        try {
            const text = await e.response?.data?.text?.();
            message = JSON.parse(text)?.message ?? message;
        } catch {
            message = e.response?.data?.message ?? message;
        }
        toast.error(message);
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadProductsOnce();
    generatePreview();
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Isi pemeriksaan lalu unduh dokumen QC (.docx)" />

        <Card class="p-4">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-bold text-primary">{{ props.title }}</h2>
                <Button size="sm" variant="outline" @click="showDetail = !showDetail"><Eye /> {{ showDetail ? 'Sembunyikan' : 'Detail Form' }} <component :is="showDetail ? ChevronUp : ChevronDown" /></Button>
            </div>
            <hr class="my-2" />
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1">
                    <label class="ml-1 text-xs font-bold">Cari Produk</label>
                    <div class="flex gap-0">
                        <SearchableSelect
                            v-model="selectedProduct"
                            :options="productOptions"
                            :loading="productLoading"
                            placeholder="Terlampir, ketik untuk saring..."
                            class="flex-1"
                            @update:model-value="pickProduct"
                        />
                        <Button size="sm" class="ml-2 shrink-0" title="Terapkan produk terpilih" @click="pickProduct"><Check /> Pilih</Button>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="ml-1 text-xs font-bold">PIC QC</label>
                    <SearchableSelect v-model="pic" :options="picOptions" placeholder="Pilih PIC" :clearable="false" />
                </div>
            </div>

            <div v-if="showDetail" class="mt-3 grid gap-x-6 gap-y-2 border-t pt-3 sm:grid-cols-2">
                <div class="space-y-2">
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">NO</label>
                        <div class="flex gap-1">
                            <Button variant="destructive" size="sm" title="Kurangi nomor" @click="form.no = Math.max(1, Number(form.no) - 1)"><Minus /></Button>
                            <Input v-model="form.no" type="number" min="1" class="h-8 text-center" />
                            <Button size="sm" title="Tambah nomor" @click="form.no = Number(form.no) + 1"><Plus /></Button>
                        </div>
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">Tgl</label>
                        <Input v-model="form.tgl" type="date" class="h-8" />
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">Nama Alat</label>
                        <Input v-model="form.name" class="h-8" />
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">Merk</label>
                        <Input v-model="form.merk" class="h-8" />
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">Tipe</label>
                        <div class="flex gap-1">
                            <Input v-model="form.type" class="h-8 flex-1" />
                            <TerlampirButton @apply="setTerlampir('type')" />
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">SN/Lot</label>
                        <div class="flex gap-1">
                            <Input v-model="form.sn_lot" class="h-8 flex-1" />
                            <TerlampirButton @apply="setTerlampir('sn_lot')" />
                            <Button variant="outline" size="sm" title="Tanpa Lot/SN" @click="form.sn_lot = 'Tanpa Lot/SN'"><Ban /> Tanpa SN</Button>
                        </div>
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">QTY</label>
                        <div class="flex gap-1">
                            <Input v-model="form.qty" class="h-8 flex-1" />
                            <TerlampirButton @apply="setTerlampir('qty')" />
                        </div>
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">Jenis</label>
                        <SearchableSelect v-model="form.jenis" :options="jenisOptions" :clearable="false" />
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">QC Sblm</label>
                        <Input v-model="form.qc_sebelumnya" class="h-8" />
                    </div>
                    <div class="grid grid-cols-[90px_1fr] items-center gap-2">
                        <label class="text-sm">Jenis QC</label>
                        <SearchableSelect v-model="form.jenis_qc" :options="jenisQcOptions" :clearable="false" />
                    </div>
                </div>

                <div class="mt-2 sm:col-span-2">
                    <div class="mb-2 flex items-center gap-3 border-b pb-1">
                        <h3 class="text-sm font-bold text-slate-600">PEMERIKSAAN FISIK</h3>
                        <div class="flex gap-1">
                            <Button variant="outline" size="sm" title="Ulangi ke bawaan" @click="confirmResetFisik(false)"><RefreshCw /> Reset</Button>
                            <Button size="sm" title="Tandai semua Baik" @click="confirmResetFisik(true)"><CircleCheck /> All Baik</Button>
                        </div>
                    </div>
                    <div class="space-y-2 px-2">
                        <div v-for="(row, i) in fisikRows" :key="i" class="grid items-center gap-2 sm:grid-cols-[4fr_4fr_4fr]">
                            <Input v-model="row.text" class="h-8 bg-slate-100" @input="generatePreview" />
                            <QcRadioGroup v-model="row.radio" :options="fisikRadios" :name="`fisik-${i}`" @change="generatePreview" />
                            <Input v-model="row.desc" class="h-8" placeholder="Catatan/Keterangan" @input="generatePreview" />
                        </div>
                    </div>
                </div>

                <div class="mt-2 sm:col-span-2">
                    <div class="mb-2 flex items-center gap-3 border-b pb-1">
                        <h3 class="text-sm font-bold text-slate-600">FUNGSI DAN SISTEM</h3>
                        <Button variant="outline" size="sm" title="Ulangi ke bawaan" @click="confirmResetReagen"><RefreshCw /> Reset</Button>
                    </div>
                    <div class="space-y-2 px-2">
                        <div v-for="(row, i) in reagenRows" :key="i" class="grid items-center gap-2 sm:grid-cols-[4fr_4fr_4fr]">
                            <Input v-model="row.text" class="h-8 bg-slate-100" @input="generatePreview" />
                            <QcRadioGroup v-model="row.radio" :options="fisikRadios" :name="`reagen-${i}`" @change="generatePreview" />
                            <Input v-model="row.desc" class="h-8" placeholder="Catatan/Keterangan" @input="generatePreview" />
                        </div>
                    </div>
                </div>

                <div class="mt-2 sm:col-span-2">
                    <div class="mb-2 flex items-center gap-3 border-b pb-1">
                        <h3 class="text-sm font-bold text-slate-600">KELENGKAPAN AKSESORIS</h3>
                        <div class="flex gap-1">
                            <Button variant="outline" size="sm" title="Tambah baris kelengkapan" @click="addKel()"><Plus /> Tambah</Button>
                            <Button variant="secondary" size="sm" title="Ambil dari Packing List produk" :disabled="getPlLoading" @click="getPL"><component :is="getPlLoading ? LoaderCircle : PackagePlus" :class="getPlLoading ? 'animate-spin' : ''" /> {{ getPlLoading ? 'Memuat...' : 'Get PL' }}</Button>
                            <Button variant="outline" size="sm" title="Ulangi ke bawaan" @click="confirmResetKel"><RefreshCw /> Reset</Button>
                        </div>
                    </div>
                    <div class="space-y-2 px-2">
                        <div v-for="(row, i) in kelRows" :key="i" class="grid items-center gap-2 sm:grid-cols-[4fr_4fr_4fr]">
                            <div class="flex gap-1">
                                <Button variant="destructive" size="sm" title="Hapus baris" @click="delKel(i)"><Trash2 /></Button>
                                <Input v-model="row.text" class="h-8 flex-1 bg-slate-100" @input="generatePreview" />
                            </div>
                            <QcRadioGroup v-model="row.radio" :options="kelRadios" :name="`kel-${i}`" @change="generatePreview" />
                            <Input v-model="row.desc" class="h-8" placeholder="Keterangan" @input="generatePreview" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap justify-center gap-2 border-t pt-4">
                <Button size="sm" variant="secondary" as="a" :href="route('products.index')"><ArrowLeft /> Kembali</Button>
                <Button size="sm" variant="outline" title="Kembalikan semua isian ke bawaan" @click="confirmResetAll"><Eraser /> Reset Semua</Button>
                <Button size="sm" variant="outline" title="Perbarui tabel pratinjau" @click="generatePreview"><Eye /> Preview Tabel</Button>
                <Button size="sm" title="Simpan dan unduh dokumen QC" :disabled="saving" @click="submitDownload"><Download /> {{ saving ? 'Memproses...' : 'Simpan & Unduh' }}</Button>
            </div>
        </Card>

        <Card class="mt-4 p-4">
            <div class="mb-3 flex gap-2 border-b text-sm">
                <button v-for="t in [['rekap', 'Rekap QC'], ['lampiran', 'Lampiran'], ['tabel', 'Table QC']]" :key="t[0]" class="px-3 py-2" :class="previewTab === t[0] ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="previewTab = t[0]">{{ t[1] }}</button>
                <span class="ml-auto hidden items-center text-xs text-slate-500 sm:flex">PIC: {{ pic }}</span>
            </div>
            <div v-if="previewTab === 'rekap'">
            <h2 class="mb-2 text-sm font-semibold">Rekap QC: {{ pic }}</h2>
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Tgl</th><th class="px-3 py-2">NO</th><th class="px-3 py-2">Kode</th>
                            <th class="px-3 py-2">Qty</th><th class="px-3 py-2">SN-LOT</th><th class="px-3 py-2">Kategori</th>
                            <th class="px-3 py-2 text-center">OK</th><th class="px-3 py-2 text-center">Error</th><th class="px-3 py-2">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3 py-2">{{ form.tgl }}</td><td class="px-3 py-2">{{ form.no }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ form.type || '-' }}</td><td class="px-3 py-2">{{ form.qty }}</td>
                            <td class="px-3 py-2">{{ form.sn_lot }}</td><td class="px-3 py-2">{{ rekapCat }}</td>
                            <td class="px-3 py-2 text-center">V</td><td class="px-3 py-2 text-center" />
                            <td class="px-3 py-2">{{ form.jenis_qc }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
            <div v-if="previewTab === 'lampiran'">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold">Preview Lampiran</h2>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="copyLampiranA"><Copy /> Salin Kode</Button>
                    <Button variant="outline" size="sm" @click="copyLampiranL"><Copy /> Salin Barang</Button>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b"><td class="px-3 py-2 text-muted-foreground">Kode</td><td class="px-3 py-2 font-medium">{{ form.type || '-' }}</td></tr>
                            <tr><td class="px-3 py-2 text-muted-foreground">Nama</td><td class="px-3 py-2 font-medium">{{ form.name || '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b"><td class="px-3 py-2 text-muted-foreground">Kode Barang</td><td class="px-3 py-2 font-medium">{{ form.type || '-' }}</td></tr>
                            <tr><td class="px-3 py-2 text-muted-foreground">Nama Barang</td><td class="px-3 py-2 font-medium">{{ form.name || '-' }}</td></tr>
                            <tr class="border-b"><td class="px-3 py-2 text-muted-foreground">SN</td><td class="px-3 py-2">{{ form.sn_lot }}</td></tr>
                            <tr><td class="px-3 py-2 text-muted-foreground">Keterangan</td><td class="px-3 py-2">{{ form.jenis_qc }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
            <div v-if="previewTab === 'tabel'">
            <h2 class="mb-2 text-sm font-semibold">Preview Table QC</h2>
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b bg-slate-50 text-left uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">No</th><th class="px-2 py-2">Nama</th><th class="px-2 py-2">Merk</th>
                            <th class="px-2 py-2">Tipe</th><th class="px-2 py-2">SN</th><th class="px-2 py-2">Qty</th>
                            <th class="px-2 py-2">Jenis</th><th class="px-2 py-2">QC Sblm</th>
                            <th class="px-2 py-2 text-center">F-Y</th><th class="px-2 py-2 text-center">F-N</th>
                            <th class="px-2 py-2 text-center">R-Y</th><th class="px-2 py-2 text-center">R-N</th>
                            <th class="px-2 py-2 text-center">K-Y</th><th class="px-2 py-2 text-center">K-N</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-2 py-2">{{ form.no }}</td><td class="px-2 py-2">{{ form.name }}</td>
                            <td class="px-2 py-2">{{ form.merk }}</td><td class="px-2 py-2 font-mono">{{ form.type }}</td>
                            <td class="px-2 py-2">{{ form.sn_lot }}</td><td class="px-2 py-2">{{ form.qty }}</td>
                            <td class="px-2 py-2">{{ form.jenis }}</td><td class="px-2 py-2">{{ form.qc_sebelumnya }}</td>
                            <td class="px-2 py-2 text-center">{{ fisikRows.slice(0, 6).filter((r) => r.radio === 'yes').length }}</td>
                            <td class="px-2 py-2 text-center">{{ fisikRows.slice(0, 6).filter((r) => r.radio === 'no').length }}</td>
                            <td class="px-2 py-2 text-center">{{ reagenRows.slice(0, 4).filter((r) => r.radio === 'yes').length }}</td>
                            <td class="px-2 py-2 text-center">{{ reagenRows.slice(0, 4).filter((r) => r.radio === 'no').length }}</td>
                            <td class="px-2 py-2 text-center">{{ kelRows.slice(0, 3).filter((r) => r.radio === 'yes').length }}</td>
                            <td class="px-2 py-2 text-center">{{ kelRows.slice(0, 3).filter((r) => r.radio === 'no').length }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
        </Card>

        <AppModal v-model:open="packModalOpen" title="Pilih Packing List" size="md">
            <div class="space-y-2">
                <Button
                    v-for="pack in packList"
                    :key="pack.id"
                    variant="outline"
                    class="w-full justify-start"
                    @click="addPackItems(pack)"
                >
                    <Package /> {{ pack.name ?? `Pack #${pack.id}` }} ({{ (pack.items ?? []).length }} item)
                </Button>
            </div>
            <template #footer>
                <Button variant="ghost" @click="packModalOpen = false">Tutup</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
