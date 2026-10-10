<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Item AKL' },
    record: { type: Object, required: true },
});

const toast = useToast();
const { confirm } = useConfirm();
const items = ref([]);
const loading = ref(false);

async function loadItems() {
    loading.value = true;
    try {
        const res = await web.get('/akl-items', { params: { akl_id: props.record.id }, silent: true });
        items.value = res.data?.data ?? res.data ?? [];
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat item.');
    } finally {
        loading.value = false;
    }
}

const itemColumns = [
    { key: 'code', label: 'Code', mono: true },
    { key: 'name', label: 'Nama', wrap: true, maxWidth: '260px' },
    { key: 'ref', label: 'Ref', align: 'center' },
];

// Tambah item + autocomplete product (boleh custom di luar master)
const codeBox = ref('');
const nameBox = ref('');
const codeState = ref(''); // '' | 'terdaftar' | 'custom' | 'mencari'
let codeTimer = null;
async function lookupCode(v) {
    codeBox.value = v;
    clearTimeout(codeTimer);
    const code = v.trim();
    if (!code) {
        codeState.value = '';
        return;
    }
    codeState.value = 'mencari';
    codeTimer = setTimeout(async () => {
        try {
            const res = await api.get('/products/search', { params: { q: code }, silent: true });
            const body = res.data?.data ?? res.data ?? [];
            const list = Array.isArray(body) ? body : (body.data ?? []);
            const exact = list.find((p) => String(p.code).toLowerCase() === code.toLowerCase());
            if (exact) {
                codeState.value = 'terdaftar';
                if (!nameBox.value.trim()) {
                    nameBox.value = exact.name ?? '';
                }
            } else {
                codeState.value = list.length > 0 ? 'terdaftar' : 'custom';
                if (list.length > 0 && !nameBox.value.trim()) {
                    nameBox.value = list[0].name ?? '';
                }
            }
        } catch (e) {
            codeState.value = '';
            toast.error(e.response?.data?.message ?? 'Gagal mencari product.');
        }
    }, 400);
}
const codeHint = computed(() => {
    if (codeState.value === 'terdaftar') {
        return 'Terdaftar di master product';
    }
    if (codeState.value === 'custom') {
        return 'Custom (di luar master)';
    }
    if (codeState.value === 'mencari') {
        return 'Mencari...';
    }
    return '';
});
async function addItem() {
    if (!codeBox.value.trim()) {
        toast.warning('Isi code dulu.');
        return;
    }
    try {
        await web.post('/akl-items', { akl_id: props.record.id, code: codeBox.value.trim(), name: nameBox.value.trim() || null }, { block: true });
        toast.success('Item ditambahkan.');
        codeBox.value = '';
        nameBox.value = '';
        loadItems();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal tambah item.');
    }
}
async function deleteItem(row) {
    const ok = await confirm({ title: `Hapus item ${row.code}?`, message: 'Item dihapus permanen.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await web.delete(`/akl-items/${row.id}`, { block: true });
        toast.success('Item dihapus.');
        loadItems();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal hapus item.');
    }
}

// Cek product modal (sync code/name dari master, samakan items.blade.php)
const checkOpen = ref(false);
const checkRow = ref(null);
const checkItem = ref(null);
const checkMatches = ref([]);
const checkLoading = ref(false);
const checkPicked = ref('');
const checkSaving = ref(false);
const checkColumns = [
    { key: 'pick', label: '' },
    { key: 'code', label: 'Code', mono: true },
    { key: 'name', label: 'Nama', wrap: true, maxWidth: '240px' },
    { key: 'cek', label: 'Cek' },
];
const checkPickable = computed(() => checkMatches.value.filter((m) => !m.is_synced).length);
function checkBadge(sama) {
    return sama
        ? 'inline-flex items-center rounded-md border border-green-200 bg-green-50 px-1.5 py-0.5 text-[11px] font-medium text-green-700'
        : 'inline-flex items-center rounded-md border border-red-200 bg-red-50 px-1.5 py-0.5 text-[11px] font-medium text-red-700';
}
async function openCheck(row) {
    checkRow.value = row;
    checkItem.value = null;
    checkMatches.value = [];
    checkPicked.value = '';
    checkOpen.value = true;
    checkLoading.value = true;
    try {
        const res = await web.get(`/akl-items/${row.id}/check-product`, { silent: true, block: true });
        checkItem.value = res.data?.data?.item ?? row;
        checkMatches.value = res.data?.data?.matches ?? [];
        if (checkMatches.value.length === 0) {
            toast.warning(res.data?.message ?? 'Tidak ada yang cocok di product.');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal cek product.');
    } finally {
        checkLoading.value = false;
    }
}
async function applyCheck() {
    if (!checkPicked.value) {
        toast.warning('Pilih kandidat dulu.');
        return;
    }
    checkSaving.value = true;
    try {
        const res = await web.put(`/akl-items/${checkRow.value.id}/apply-product`, { product_id: checkPicked.value }, { silent: true, block: true });
        toast.success(res.data?.message ?? 'Item disinkron dari product.');
        checkOpen.value = false;
        loadItems();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal sinkron.');
    } finally {
        checkSaving.value = false;
    }
}

const fileUrl = computed(() => (props.record?.file ? `/akls/${props.record.id}` : ''));
const fileIsPdf = computed(() => !!props.record?.is_pdf);

loadItems();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="ghost" size="sm" @click="router.visit('/akls')">Kembali ke AKL</Button>
            </template>
        </PageHeader>
        <div class="grid gap-4 lg:grid-cols-5">
            <div class="space-y-4 lg:col-span-3">
                <Card class="p-4">
                    <div class="grid gap-1 text-sm sm:grid-cols-2">
                        <p><b>Reg No:</b> {{ record.reg_no }}</p>
                        <p><b>Vendor:</b> {{ record.vendor ?? '-' }}</p>
                        <p class="sm:col-span-2"><b>Nama:</b> {{ record.reg_name ?? '-' }}</p>
                        <p><b>Expired:</b> {{ record.date_expired ?? '-' }}</p>
                        <p><b>Jumlah Item:</b> {{ items.length }}</p>
                    </div>
                </Card>
                <Card class="p-4">
                    <b class="text-sm">Tambah Item</b>
                    <div class="mt-2 grid gap-2 sm:grid-cols-3">
                        <FormField label="Code" :hint="codeHint">
                            <Input :model-value="codeBox" placeholder="Ketik code (bebas custom)..." @input="lookupCode($event.target.value)" />
                        </FormField>
                        <FormField label="Nama (opsional)">
                            <Input v-model="nameBox" placeholder="Otomatis dari master bila kosong" />
                        </FormField>
                        <div class="flex items-end">
                            <Button class="w-full" @click="addItem"><Plus /> Tambah</Button>
                        </div>
                    </div>
                </Card>
                <DataTable
                    :columns="itemColumns"
                    :rows="items"
                    :loading="loading"
                    :page="1"
                    :total-pages="1"
                    :total="items.length"
                    :per-page="items.length || 10"
                    :show-footer="false"
                    empty-title="Belum ada item"
                    empty-message="Tambah item di atas."
                >
                    <template #cell-code="{ row }"><b>{{ row.code }}</b></template>
                    <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
                    <template #cell-ref="{ row }">{{ row.product ? 'Terdaftar' : 'Custom' }}</template>
                    <template #actions="{ row }">
                        <div class="flex justify-end gap-1">
                            <Button variant="outline" size="sm" @click="openCheck(row)">Cek</Button>
                            <Button variant="ghost" size="sm" @click="deleteItem(row)"><Trash2 /></Button>
                        </div>
                    </template>
                </DataTable>
            </div>
            <Card class="h-fit p-4 lg:col-span-2">
                <b class="text-sm">Preview Dokumen</b>
                <div v-if="!fileUrl" class="mt-2 rounded border border-dashed p-8 text-center text-sm text-muted-foreground">Tanpa lampiran.</div>
                <div v-else-if="fileIsPdf" class="mt-2 h-[60vh]">
                    <embed :src="fileUrl" type="application/pdf" class="size-full rounded border" />
                </div>
                <img v-else :src="fileUrl" alt="Lampiran" class="mt-2 max-h-[60vh] w-full rounded border object-contain" />
            </Card>
        </div>

        <AppModal v-model:open="checkOpen" :title="`Cek ${checkRow?.code ?? ''} → Product`" size="lg">
            <div v-if="checkLoading" class="space-y-2">
                <div v-for="n in 3" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="space-y-3">
                <p class="text-xs text-muted-foreground">{{ checkMatches.length ? `${checkMatches.length} kandidat (${checkPickable} bisa dipilih)` : 'Tidak ada yang cocok' }}</p>
                <div class="rounded-md border bg-slate-50 p-2 text-xs">
                    <b>Item saat ini:</b> <b>{{ checkItem?.code ?? checkRow?.code }}</b> — {{ checkItem?.name ?? checkRow?.name ?? '-' }}
                </div>
                <p v-if="checkMatches.length === 0" class="py-4 text-center text-sm text-muted-foreground">Tidak ada yang cocok di tabel product.</p>
                <DataTable
                    v-else
                    :columns="checkColumns"
                    :rows="checkMatches"
                    :loading="false"
                    :page="1"
                    :total-pages="1"
                    :total="checkMatches.length"
                    :per-page="checkMatches.length"
                    :show-footer="false"
                    empty-title="Tidak ada kandidat"
                >
                    <template #cell-pick="{ row }">
                        <input v-model="checkPicked" type="radio" :value="row.id" :disabled="row.is_synced" title="Pilih kandidat" />
                    </template>
                    <template #cell-code="{ row }"><b>{{ row.code }}</b><br /><span :class="checkBadge(row.diff?.code_sama)">{{ row.diff?.code_sama ? 'Sama' : 'Beda' }}</span></template>
                    <template #cell-name="{ row }">{{ row.name ?? '-' }}<br /><span :class="checkBadge(row.diff?.nama_sama)">{{ row.diff?.nama_sama ? 'Sama' : 'Beda' }}</span></template>
                    <template #cell-cek="{ row }">
                        <span v-if="row.is_synced" class="text-xs font-semibold text-green-700">Sudah sama<br /><small class="font-normal text-muted-foreground">Tidak bisa dipilih</small></span>
                        <span v-else class="text-xs text-muted-foreground">Beda — bisa dipilih</span>
                    </template>
                </DataTable>
                <p v-if="checkMatches.length" class="text-xs text-muted-foreground">Baris hijau <b>Sudah sama</b> tidak bisa dipilih. Pilih kandidat lain lalu klik <b>Save yang Dipilih</b>.</p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="checkOpen = false">Tutup</Button>
                <Button :disabled="!checkPicked || checkSaving" @click="applyCheck">{{ checkSaving ? 'Menyimpan...' : 'Save yang Dipilih' }}</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
