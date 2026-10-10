<script setup>
import { computed, reactive, ref } from 'vue';
import { ArrowDown, ArrowUp, Calculator, Copy, Eraser, Pencil, Plus, Printer, RefreshCw, Save, Trash2 } from '@lucide/vue';
import AppModal from '@/components/AppModal.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';
import { formatNumber } from '@/lib/format';
import { getCode } from '@/lib/odoo';

const props = defineProps({
    alamatId: { type: [Number, String], required: true },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();

const kolis = ref([]);
const loading = ref(false);
const drafts = reactive({});

function initDraft(koli) {
    drafts[koli.id] = {
        urutan: koli.urutan ?? '',
        nilai: koli.nilai ?? '',
        is_do: koli.is_do ?? 'no',
        is_pk: koli.is_pk ?? 'no',
        is_asuransi: koli.is_asuransi ?? 'no',
        is_banting: koli.is_banting ?? 'no',
    };
}

async function loadKolis() {
    loading.value = true;
    try {
        const res = await web.get('/koli', { params: { alamat_baru_id: props.alamatId }, silent: true });
        const body = res.data?.data ?? res.data ?? [];
        kolis.value = Array.isArray(body) ? body : (body.data ?? []);
        kolis.value.forEach(initDraft);
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat koli.');
    } finally {
        loading.value = false;
    }
}

const leftKolis = computed(() => kolis.value.filter((_, i) => i % 2 === 0));
const rightKolis = computed(() => kolis.value.filter((_, i) => i % 2 === 1));

function idr(nilai) {
    const n = parseInt(nilai ?? 0, 10);
    if (!n) {
        return '';
    }
    return `IDR ${formatNumber(n)}`;
}

async function saveInline(koli) {
    const d = drafts[koli.id];
    if (!d) {
        return;
    }
    if (!String(d.urutan ?? '').trim()) {
        toast.error('Koli harus diisi');
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.put(`/koli/${koli.id}`, {
                urutan: d.urutan,
                nilai: d.nilai,
                is_do: d.is_do ?? 'no',
                is_pk: d.is_pk ?? 'no',
                is_asuransi: d.is_asuransi ?? 'no',
                is_banting: d.is_banting ?? 'no',
            }, { block: true });
            toast.success(res.data?.message ?? 'Data koli berhasil disimpan!');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menyimpan data koli!');
        }
    });
}

// Koli add modal
const koliModalOpen = ref(false);
const koliForm = ref({ urutan: '', nilai: '', is_do: false, is_pk: false, is_asuransi: true, is_banting: false });

function openAddKoli() {
    koliForm.value = { urutan: '', nilai: '', is_do: false, is_pk: false, is_asuransi: true, is_banting: false };
    koliModalOpen.value = true;
}

async function saveKoli() {
    if (!String(koliForm.value.urutan ?? '').trim()) {
        toast.error('Nomor koli wajib diisi.');
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.post('/koli', {
                alamat_baru_id: Number(props.alamatId),
                urutan: koliForm.value.urutan,
                nilai: koliForm.value.nilai,
                is_do: koliForm.value.is_do ? 'yes' : 'no',
                is_pk: koliForm.value.is_pk ? 'yes' : 'no',
                is_asuransi: koliForm.value.is_asuransi ? 'yes' : 'no',
                is_banting: koliForm.value.is_banting ? 'yes' : 'no',
            }, { block: true });
            toast.success(res.data?.message ?? 'Koli ditambah.');
            koliModalOpen.value = false;
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menambah koli.');
        }
    });
}

async function deleteKoli(row) {
    const ok = await confirm({ title: 'Hapus Koli?', message: `Koli ${row.urutan ?? ''}`, confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.delete(`/koli/${row.id}`, { block: true });
            toast.success(res.data?.message ?? 'Koli dihapus.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghapus koli.');
        }
    });
}

async function hitungKoli(row) {
    await withBlock(async () => {
        try {
            const res = await web.post(`/koli/${row.id}/hitung`, {}, { block: true });
            toast.success(res.data?.message ?? 'Nilai koli dihitung.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghitung nilai koli.');
        }
    });
}
async function syncKoli(row) {
    await withBlock(async () => {
        try {
            const res = await web.get(`/koli/${row.id}/sync`, { block: true });
            toast.success(res.data?.message ?? 'Koli disinkron.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menyinkron koli.');
        }
    });
}
async function duplicateKoli(row) {
    await withBlock(async () => {
        try {
            const res = await web.post(`/koli/${row.id}/duplicate`, {}, { block: true });
            toast.success(res.data?.message ?? 'Koli diduplikasi.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menduplikasi koli.');
        }
    });
}
function printKoli(row = null) {
    const q = row ? `?koli_id=${row.id}` : '';
    window.open(`/alamat-baru/${props.alamatId}${q}`, '_blank');
}

// Item add modal (3 tabs)
const itemModalOpen = ref(false);
const itemTab = ref('manual');
const activeKoli = ref(null);
const manualForm = ref({ product: '', qty: 'Ea', desc: '', lot: '' });
const productOptions = computed(() => props.products.map((p) => ({ label: `[${p.code}] ${p.name ?? ''}`, value: String(p.id) })));

// DO tab
const doOptions = ref([]);
const doLoading = ref(false);
const selectedDoId = ref(null);
const doLines = ref([]);
const doLinesLoading = ref(false);

function refLabel(item) {
    const partner = Array.isArray(item.partner_id) ? (item.partner_id[1] ?? '') : '';
    return `${item.name ?? ''} - ${item.origin ?? ''} - (${partner})`;
}

function formatEd(expired) {
    if (!expired || expired === 'False' || expired === false) {
        return '';
    }
    const d = new Date(expired);
    if (Number.isNaN(d.getTime())) {
        return '';
    }
    const pad = (v) => String(v).padStart(2, '0');
    return ` Ed. ${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
}

function mapMoveLines(detail) {
    return (detail.move_line_detail ?? []).map((item) => ({
        product_code: getCode(Array.isArray(item.product_id) ? item.product_id[1] : ''),
        product: Array.isArray(item.product_id) ? item.product_id[1] : (item.product ?? ''),
        qty: `${item.qty_done ?? ''} Ea`,
        lot: `${item.lot_id && item.lot_id !== false ? item.lot_id[1] : ''}${formatEd(item.expired_date_do)}`,
        id: item.id,
    }));
}

async function searchDo(kw) {
    if (!kw || kw.length < 1) {
        return;
    }
    doLoading.value = true;
    try {
        const res = await api.get('/do', { params: { search: kw }, silent: true });
        const body = res.data?.data ?? res.data ?? [];
        const list = Array.isArray(body) ? body : (body.data ?? []);
        doOptions.value = list.map((d) => ({ label: refLabel(d), value: String(d.id) }));
    } catch (e) {
        doOptions.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat DO.');
    } finally {
        doLoading.value = false;
    }
}
async function pickDo(val) {
    selectedDoId.value = val;
    doLines.value = [];
    if (!val) {
        return;
    }
    doLinesLoading.value = true;
    await withBlock(async () => {
        try {
            const res = await api.get(`/do/${val}`, { block: true, silent: true });
            const d = res.data?.data ?? res.data;
            doLines.value = mapMoveLines(d);
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat DO.');
        } finally {
            doLinesLoading.value = false;
        }
    });
}

// IT tab
const gudangOptions = [
    { label: 'CENTER', value: '5' },
    { label: 'BADSTOCK', value: '760' },
    { label: 'KARANTINA', value: '990' },
    { label: 'CIBUBUR', value: '1310' },
];
const itGudang = ref('5');
const itOptions = ref([]);
const itLoading = ref(false);
const selectedItId = ref(null);
const itLines = ref([]);
const itLinesLoading = ref(false);
async function searchIt(kw) {
    if (!kw || kw.length < 1) {
        return;
    }
    itLoading.value = true;
    try {
        const res = await api.get('/it', { params: { search: kw, gudang: itGudang.value }, silent: true });
        const body = res.data?.data ?? res.data ?? [];
        const list = Array.isArray(body) ? body : (body.data ?? []);
        itOptions.value = list.map((d) => ({ label: refLabel(d), value: String(d.id) }));
    } catch (e) {
        itOptions.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat IT.');
    } finally {
        itLoading.value = false;
    }
}
async function pickIt(val) {
    selectedItId.value = val;
    itLines.value = [];
    if (!val) {
        return;
    }
    itLinesLoading.value = true;
    await withBlock(async () => {
        try {
            const res = await api.get(`/it/${val}`, { block: true, silent: true });
            const d = res.data?.data ?? res.data;
            itLines.value = mapMoveLines(d);
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat IT.');
        } finally {
            itLinesLoading.value = false;
        }
    });
}

function openAddItem(koli) {
    activeKoli.value = koli;
    itemTab.value = 'manual';
    manualForm.value = { product: '', qty: 'Ea', desc: '', lot: '' };
    itemModalOpen.value = true;
}

async function saveManualItem() {
    if (!manualForm.value.product) {
        toast.error('Pilih produk!');
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.post('/koli-item', { koli_id: activeKoli.value.id, product_id: Number(manualForm.value.product), qty: manualForm.value.qty, desc: manualForm.value.desc, lot: manualForm.value.lot }, { block: true });
            toast.success(res.data?.message ?? 'Barang ditambah.');
            itemModalOpen.value = false;
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menambah barang.');
        }
    });
}

function codeOf(line) {
    return line.product_code ?? getCode(line.product ?? '');
}
async function pickDoLine(line) {
    await withBlock(async () => {
        try {
            const res = await web.post('/koli-item/from-do-it', { koli_id: activeKoli.value.id, product_code: codeOf(line), product: line.product ?? '', qty: line.qty ?? '', lot: line.lot ?? '', id: line.id }, { block: true });
            toast.success(res.data?.message ?? 'Barang ditambah dari DO.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menambah barang dari DO.');
        }
    });
}
async function pickItLine(line) {
    await withBlock(async () => {
        try {
            const res = await web.post('/koli-item/from-do-it', { koli_id: activeKoli.value.id, product_code: codeOf(line), product: line.product ?? '', qty: line.qty ?? '', lot: line.lot ?? '', id: line.id }, { block: true });
            toast.success(res.data?.message ?? 'Barang ditambah dari IT.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menambah barang dari IT.');
        }
    });
}

// Item edit modal
const editItemOpen = ref(false);
const editingItem = ref(null);
const editItemForm = ref({ qty: '', desc: '', lot: '' });
async function openEditItem(item) {
    await withBlock(async () => {
        try {
            const res = await web.get(`/koli-item/${item.id}`, { block: true });
            const d = res.data?.data ?? res.data;
            editingItem.value = item;
            editItemForm.value = { qty: d.qty ?? '', desc: d.desc ?? '', lot: d.lot ?? '' };
            editItemOpen.value = true;
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat item.');
        }
    });
}
async function saveEditItem() {
    await withBlock(async () => {
        try {
            const res = await web.put(`/koli-item/${editingItem.value.id}`, editItemForm.value, { block: true });
            toast.success(res.data?.message ?? 'Item disimpan.');
            editItemOpen.value = false;
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menyimpan item.');
        }
    });
}
async function deleteItem(item) {
    await withBlock(async () => {
        try {
            const res = await web.delete(`/koli-item/${item.id}`, { block: true });
            toast.success(res.data?.message ?? 'Item dihapus.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghapus item.');
        }
    });
}
async function orderItem(item, type) {
    await withBlock(async () => {
        try {
            await web.post(`/koli-item/${item.id}/order`, { type }, { block: true });
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal mengurutkan item.');
        }
    });
}
async function clearLot(item) {
    const ok = await confirm({ title: 'Hapus Lot?', message: item.product?.code ?? '', confirmText: 'Ya, lanjutkan' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.post(`/koli-item/${item.id}/clear-lot`, {}, { block: true });
            toast.success(res.data?.message ?? 'Lot dikosongkan.');
            loadKolis();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal mengosongkan lot.');
        }
    });
}

const itemColumns = [
    { key: 'idx', label: '#', sortable: false },
    { key: 'product', label: 'Produk', sortable: false, wrap: true, maxWidth: '280px' },
    { key: 'desc', label: 'Deskripsi', sortable: false, wrap: true, maxWidth: '220px' },
    { key: 'qty', label: 'Qty', align: 'center', sortable: false },
    { key: 'lot', label: 'Lot/Batch', sortable: false, wrap: true, maxWidth: '160px' },
];

loadKolis();

defineExpose({ openAddKoli });
</script>

<template>
    <div class="space-y-3">
        <div class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide">
            Daftar Koli &amp; Packing List ({{ kolis.length }})
        </div>
        <div v-if="loading" class="space-y-2">
            <div v-for="n in 3" :key="n" class="h-16 animate-pulse rounded-lg bg-slate-100" />
        </div>
        <p v-else-if="kolis.length === 0" class="text-center text-sm">Belum ada koli. Klik "Tambah Koli" untuk menambahkan.</p>
        <div v-else class="grid gap-4 xl:grid-cols-2">
            <div v-for="list in [leftKolis, rightKolis]" :key="list === leftKolis ? 'l' : 'r'" class="space-y-4">
                <div v-for="koli in list" :key="koli.id" class="overflow-hidden rounded-lg border bg-white">
                    <div class="flex items-center justify-between gap-2 border-b px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center overflow-hidden rounded-md border text-sm">
                                <span class="border-r bg-white px-2 py-1.5 font-bold">Koli</span>
                                <input
                                    :value="drafts[koli.id]?.urutan ?? ''"
                                    class="w-[70px] px-2 py-1.5 text-sm font-bold outline-none"
                                    placeholder="1-1"
                                    @input="drafts[koli.id].urutan = $event.target.value"
                                    @change="saveInline(koli)"
                                />
                            </span>
                            <span v-if="idr(koli.nilai)" class="rounded bg-blue-600 px-2 py-1 text-xs font-semibold text-white">{{ idr(koli.nilai) }}</span>
                        </div>
                        <div class="flex items-center gap-0.5">
                            <Button variant="outline" size="sm" class="border-0 text-amber-600" title="Hitung Nilai" @click="hitungKoli(koli)"><Calculator /></Button>
                            <Button variant="outline" size="sm" class="border-0 text-red-600" title="Hapus" @click="deleteKoli(koli)"><Trash2 /></Button>
                            <Button variant="outline" size="sm" class="border-0 text-slate-500" title="Sync Odoo" @click="syncKoli(koli)"><RefreshCw /></Button>
                            <Button variant="outline" size="sm" class="border-0 text-blue-600" title="Duplikat" @click="duplicateKoli(koli)"><Copy /></Button>
                            <Button variant="outline" size="sm" class="border-0 text-cyan-600" title="Tambah Barang" @click="openAddItem(koli)"><Plus /></Button>
                            <Button size="sm" class="ml-1" title="Cetak Label" @click="printKoli(koli)"><Printer /></Button>
                        </div>
                    </div>
                    <DataTable
                        :columns="itemColumns"
                        :rows="koli.items ?? []"
                        :loading="false"
                        :page="1"
                        :total-pages="1"
                        :total="(koli.items ?? []).length"
                        :per-page="100"
                        :per-page-options="[100]"
                        :show-footer="false"
                        empty-title="Belum ada item"
                        empty-message=""
                    >
                        <template #cell-idx="{ row }">{{ (koli.items ?? []).indexOf(row) + 1 }}</template>
                        <template #cell-product="{ row }"><span class="font-bold">[{{ row.product?.code ?? '-' }}]</span> {{ row.product?.name ?? '' }}</template>
                        <template #cell-desc="{ row }">{{ row.desc ?? '' }}</template>
                        <template #cell-qty="{ row }">{{ row.qty ?? '' }}</template>
                        <template #cell-lot="{ row }">{{ row.lot ?? '' }}</template>
                        <template #actions="{ row }">
                            <div class="flex justify-center gap-0.5">
                                <Button variant="secondary" size="sm" class="h-7 w-7 px-0" title="Naik" :disabled="(koli.items ?? []).indexOf(row) === 0" @click="orderItem(row, 'up')"><ArrowUp /></Button>
                                <Button variant="secondary" size="sm" class="h-7 w-7 px-0" title="Turun" :disabled="(koli.items ?? []).indexOf(row) === (koli.items ?? []).length - 1" @click="orderItem(row, 'down')"><ArrowDown /></Button>
                                <Button variant="outline" size="sm" class="h-7 w-7 px-0 text-amber-600" title="Ubah" @click="openEditItem(row)"><Pencil /></Button>
                                <Button variant="outline" size="sm" class="h-7 w-7 px-0 text-cyan-600" title="Bersihkan Lot" :disabled="!row.lot" @click="clearLot(row)"><Eraser /></Button>
                                <Button variant="destructive" size="sm" class="h-7 w-7 px-0" title="Hapus" @click="deleteItem(row)"><Trash2 /></Button>
                            </div>
                        </template>
                    </DataTable>
                    <div class="border-t bg-slate-50 p-2">
                        <div class="mb-2 flex items-center overflow-hidden rounded-md border bg-white text-sm">
                            <span class="border-r bg-white px-2 py-1.5">Nilai Rp.</span>
                            <CurrencyInput
                                :model-value="drafts[koli.id]?.nilai ?? ''"
                                class="h-auto flex-1 rounded-none border-0 px-2 py-1.5 shadow-none focus-visible:ring-0"
                                placeholder="0"
                                @update:model-value="drafts[koli.id].nilai = $event"
                                @change="saveInline(koli)"
                            />
                        </div>
                        <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 px-1">
                            <label class="flex items-center gap-1 text-xs text-slate-500"><input v-model="drafts[koli.id].is_do" type="checkbox" true-value="yes" false-value="no" @change="saveInline(koli)" /> SURAT JALAN/DO</label>
                            <label class="flex items-center gap-1 text-xs text-slate-500"><input v-model="drafts[koli.id].is_pk" type="checkbox" true-value="yes" false-value="no" @change="saveInline(koli)" /> P. KAYU</label>
                            <label class="flex items-center gap-1 text-xs text-slate-500"><input v-model="drafts[koli.id].is_asuransi" type="checkbox" true-value="yes" false-value="no" @change="saveInline(koli)" /> ASURANSI</label>
                            <label class="flex items-center gap-1 text-xs text-slate-500"><input v-model="drafts[koli.id].is_banting" type="checkbox" true-value="yes" false-value="no" @change="saveInline(koli)" /> FRAGILE</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AppModal v-model:open="koliModalOpen" title="Tambah Koli" size="md">
            <div class="space-y-3">
                <FormField label="Nomor Koli (Urutan)" required hint="Format: Angka (1), Range (1-7), atau Koma (1,3,5)">
                    <Input v-model="koliForm.urutan" placeholder="Contoh: 1 atau 1-7" />
                </FormField>
                <FormField label="Nilai (Rp)">
                    <CurrencyInput v-model="koliForm.nilai" placeholder="0" />
                </FormField>
                <div class="grid grid-cols-2 gap-2 border-t pt-3">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input v-model="koliForm.is_do" type="checkbox" /> SURAT JALAN/DO</label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input v-model="koliForm.is_pk" type="checkbox" /> PACKING KAYU</label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input v-model="koliForm.is_asuransi" type="checkbox" /> ASURANSI</label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input v-model="koliForm.is_banting" type="checkbox" /> FRAGILE</label>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="koliModalOpen = false">Tutup</Button>
                <Button @click="saveKoli"><Save /> Simpan Koli</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="itemModalOpen" :title="`Tambah Barang — Koli ${activeKoli?.urutan ?? ''}`" size="xl">
            <div class="mb-3 flex gap-2 border-b text-sm">
                <button v-for="t in [['manual', 'Data Produk'], ['do', 'Ambil dari DO'], ['it', 'Ambil dari IT']]" :key="t[0]" class="px-3 py-2" :class="itemTab === t[0] ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="itemTab = t[0]">{{ t[1] }}</button>
            </div>
            <div v-if="itemTab === 'manual'" class="space-y-3">
                <FormField label="Nama/Kode Produk" required>
                    <SearchableSelect v-model="manualForm.product" :options="productOptions" placeholder="Cari Produk..." />
                </FormField>
                <div class="grid gap-3 sm:grid-cols-3">
                    <FormField label="Kuantitas (Qty)"><Input v-model="manualForm.qty" placeholder="Mis: 1 Ea" /></FormField>
                    <FormField label="Deskripsi Item" class="sm:col-span-2"><Input v-model="manualForm.desc" placeholder="Keterangan tambahan barang" /></FormField>
                </div>
                <FormField label="Nomor Lot / Batch">
                    <Textarea v-model="manualForm.lot" rows="3" placeholder="Ketik nomor lot jika ada..." />
                </FormField>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="itemModalOpen = false">Batal</Button>
                    <Button @click="saveManualItem"><Plus /> Tambahkan Barang</Button>
                </div>
            </div>
            <div v-if="itemTab === 'do'" class="space-y-3">
                <FormField label="Cari Nomor DO">
                    <SearchableSelect :model-value="selectedDoId" :options="doOptions" :loading="doLoading" placeholder="Cari NO DO" @update:model-value="pickDo" @search="searchDo" />
                </FormField>
                <div v-if="doLinesLoading" class="h-4 animate-pulse rounded bg-slate-100" />
                <div v-else-if="doLines.length === 0" class="text-sm text-slate-500">Pilih DO dulu.</div>
                <div v-else class="max-h-64 divide-y overflow-y-auto rounded border text-sm">
                    <div class="flex gap-2 bg-slate-50 px-2 py-1.5 font-semibold"><span class="flex-1">Produk</span><span class="w-32">Lot/ED</span><span class="w-20 text-center">Qty</span><span class="w-16 text-center">#</span></div>
                    <div v-for="(l, i) in doLines" :key="i" class="flex items-center gap-2 px-2 py-1.5">
                        <span class="flex-1">{{ l.product ?? '-' }}</span><span class="w-32">{{ l.lot ?? '-' }}</span><span class="w-20 text-center">{{ l.qty ?? '' }}</span>
                        <span class="w-16 text-center"><Button size="sm" @click="pickDoLine(l)">Pilih</Button></span>
                    </div>
                </div>
                <div class="flex justify-end"><Button variant="ghost" @click="itemModalOpen = false">Tutup</Button></div>
            </div>
            <div v-if="itemTab === 'it'" class="space-y-3">
                <div class="grid gap-3 sm:grid-cols-3">
                    <FormField label="Gudang Asal">
                        <SearchableSelect v-model="itGudang" :options="gudangOptions" placeholder="Pilih Gudang" />
                    </FormField>
                    <FormField label="Cari Nomor IT" class="sm:col-span-2">
                        <SearchableSelect :model-value="selectedItId" :options="itOptions" :loading="itLoading" placeholder="Cari NO IT" @update:model-value="pickIt" @search="searchIt" />
                    </FormField>
                </div>
                <div v-if="itLinesLoading" class="h-4 animate-pulse rounded bg-slate-100" />
                <div v-else-if="itLines.length === 0" class="text-sm text-slate-500">Pilih IT dulu.</div>
                <div v-else class="max-h-64 divide-y overflow-y-auto rounded border text-sm">
                    <div class="flex gap-2 bg-slate-50 px-2 py-1.5 font-semibold"><span class="flex-1">Produk</span><span class="w-32">Lot/ED</span><span class="w-20 text-center">Qty</span><span class="w-16 text-center">#</span></div>
                    <div v-for="(l, i) in itLines" :key="i" class="flex items-center gap-2 px-2 py-1.5">
                        <span class="flex-1">{{ l.product ?? '-' }}</span><span class="w-32">{{ l.lot ?? '-' }}</span><span class="w-20 text-center">{{ l.qty ?? '' }}</span>
                        <span class="w-16 text-center"><Button size="sm" @click="pickItLine(l)">Pilih</Button></span>
                    </div>
                </div>
                <div class="flex justify-end"><Button variant="ghost" @click="itemModalOpen = false">Tutup</Button></div>
            </div>
        </AppModal>

        <AppModal v-model:open="editItemOpen" title="Ubah Item Pengiriman" size="md">
            <div class="space-y-3">
                <FormField label="Kuantitas (Qty)"><Input v-model="editItemForm.qty" placeholder="Qty" /></FormField>
                <FormField label="Deskripsi Item"><Input v-model="editItemForm.desc" placeholder="Deskripsi" /></FormField>
                <FormField label="Nomor Lot / Batch"><Textarea v-model="editItemForm.lot" rows="3" placeholder="Lot" /></FormField>
            </div>
            <template #footer>
                <Button variant="ghost" @click="editItemOpen = false">Batal</Button>
                <Button @click="saveEditItem"><Save /> Simpan Update</Button>
            </template>
        </AppModal>
    </div>
</template>
