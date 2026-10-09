<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ChevronDown, Download, FilePlus, Printer, RefreshCw, Stamp, Trash2 } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import AppModal from '@/components/AppModal.vue';
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
import { odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'BAST' },
    mode: { type: String, default: 'create' },
    bast: { type: Object, default: null },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();
const isEdit = computed(() => props.mode === 'edit');

const form = ref({
    do: props.bast?.do ?? '',
    name: props.bast?.name ?? '',
    address: props.bast?.address ?? '',
    city: props.bast?.city ?? '',
});
const errors = ref({});

// DO lookup Odoo (samakan Blade: input keyword + tombol GET DO + hasil dropdown)
const doKeyword = ref(props.bast?.do ?? 'CENT/OUT/');
const doOptions = ref([]);
const doLoading = ref(false);
const selectedDoId = ref('');

function doLabel(d) {
    let name = d.name ?? d.do ?? d.id;
    if (d.group_id) {
        name += ` (${Array.isArray(d.group_id) ? d.group_id[1] : d.group_id})`;
    }
    if (d.partner_id) {
        name += ` ${Array.isArray(d.partner_id) ? d.partner_id[1] : d.partner_id}`;
    }
    return name;
}

async function searchDo(keyword, overlay = false) {
    doKeyword.value = keyword ?? doKeyword.value;
    if (!doKeyword.value || doKeyword.value.length < 1) {
        return;
    }
    doLoading.value = true;
    const run = async () => {
        try {
            const res = await api.get('/do', { params: { search: doKeyword.value }, silent: true, ...(overlay ? { block: true } : {}) });
            const body = res.data?.data ?? res.data ?? [];
            const list = Array.isArray(body) ? body : (body.data ?? []);
            doOptions.value = list.map((d) => ({ label: doLabel(d), value: String(d.id), raw: d }));
        } catch (e) {
            doOptions.value = [];
            toast.error(e.response?.data?.message ?? 'Gagal memuat DO.');
        } finally {
            doLoading.value = false;
        }
    };
    if (overlay) {
        await withBlock(run);
    } else {
        await run();
    }
}

async function pickDo(val) {
    selectedDoId.value = val;
    if (!val) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await api.get(`/do/${val}`, { block: true, silent: true });
            const d = res.data?.data ?? res.data;
            form.value.do = d.name ?? form.value.do;
            form.value.name = odooName(d.partner_id) ?? form.value.name;
            const addr = [d.partner_address, d.partner_address2, d.partner_address3, d.partner_address4].filter(Boolean).join(', ');
            if (addr) {
                form.value.address = addr;
            }
            const city = d.partner_address3 ?? '';
            if (city) {
                form.value.city = city;
            }
            toast.success('Data DO dimuat.');
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat DO.');
        }
    });
}

async function saveHeader() {
    errors.value = {};
    if (!form.value.do.trim() || !form.value.name.trim() || !form.value.address.trim() || !form.value.city.trim()) {
        toast.error('DO, nama, alamat, dan kota wajib diisi.');
        return;
    }
    await withBlock(async () => {
        try {
            if (isEdit.value) {
                await web.put(`/basts/${props.bast.id}`, form.value, { block: true });
                toast.success('BAST disimpan.');
            } else {
                const res = await web.post('/basts', form.value, { block: true });
                const created = res.data?.data ?? res.data;
                toast.success('BAST dibuat.');
                if (created?.id) {
                    window.open(`/basts/${created.id}/edit`, '_blank');
                }
            }
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data?.errors ?? {};
            }
        }
    });
}

function goBack() {
    router.visit('/basts');
}

// Items (edit only)
const items = ref([]);
const itemsLoading = ref(false);
const itemModalOpen = ref(false);
const itemEditing = ref(null);
const itemForm = ref({ product: '', qty: '100', satuan: 'EA', lot: '' });
const itemErrors = ref({});

const satuanOptions = ['Pcs', 'Pck', 'Unit', 'EA', 'Box', 'Btl', 'Vial'].map((s) => ({ label: s, value: s }));
const productOptions = computed(() =>
    props.products.map((p) => ({ label: `[${p.code}] ${p.name}`, value: String(p.id) })),
);

async function loadItems() {
    if (!isEdit.value) {
        return;
    }
    itemsLoading.value = true;
    try {
        const res = await web.get('/detail-basts', { params: { bast_id: props.bast.id }, silent: true });
        const body = res.data?.data ?? res.data ?? [];
        items.value = Array.isArray(body) ? body : (body.data ?? []);
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat item.');
    } finally {
        itemsLoading.value = false;
    }
}

function openAddItem() {
    itemEditing.value = null;
    itemForm.value = { product: '', qty: '100', satuan: 'EA', lot: '' };
    itemErrors.value = {};
    itemModalOpen.value = true;
}
function openEditItem(row) {
    itemEditing.value = row;
    itemForm.value = { product: String(row.product_id ?? row.product?.id ?? ''), qty: String(row.qty ?? ''), satuan: row.satuan ?? 'EA', lot: row.lot ?? '' };
    itemErrors.value = {};
    itemModalOpen.value = true;
}

async function saveItem() {
    itemErrors.value = {};
    await withBlock(async () => {
        try {
            if (itemEditing.value) {
                await web.put(`/detail-basts/${itemEditing.value.id}`, { qty: itemForm.value.qty, satuan: itemForm.value.satuan, lot: itemForm.value.lot }, { block: true });
            } else {
                await web.post('/detail-basts', { bast: props.bast.id, product: Number(itemForm.value.product), qty: itemForm.value.qty, satuan: itemForm.value.satuan, lot: itemForm.value.lot }, { block: true });
            }
            toast.success('Item disimpan.');
            itemModalOpen.value = false;
            loadItems();
        } catch (e) {
            if (e.response?.status === 422) {
                itemErrors.value = e.response.data?.errors ?? {};
            }
        }
    });
}

async function deleteItem(row) {
    const ok = await confirm({ title: 'Hapus item?', message: `${row.product?.code ?? ''}`, confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            await web.delete(`/detail-basts/${row.id}`, { block: true });
            toast.success('Item dihapus.');
            loadItems();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menghapus item.');
        }
    });
}

async function orderItem(row, type) {
    await withBlock(async () => {
        try {
            await web.post(`/detail-basts/${row.id}/order`, { type }, { block: true });
            loadItems();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal mengurutkan item.');
        }
    });
}

async function karganItem(row) {
    const ok = await confirm({ title: 'Buat kargan?', message: `Dari item ${row.product?.code ?? ''}`, confirmText: 'Ya, lanjutkan' });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.post(`/detail-basts/${row.id}/kargan`, {}, { block: true });
            const k = res.data?.data ?? res.data;
            toast.success('Kargan dibuat.');
            if (k?.id) {
                window.open(`/kargans/${k.id}/edit`, '_blank');
            }
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal membuat kargan.');
        }
    });
}

async function syncOdoo() {
    await withBlock(async () => {
        try {
            const res = await web.get(`/basts/${props.bast.id}/sync`, { block: true });
            toast.success(res.data?.data?.message ?? res.data?.message ?? 'Sinkron selesai.');
            loadItems();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menyinkron Odoo.');
        }
    });
}

function downloadFile(type = 'tanda_terima') {
    window.open(`/basts/${props.bast.id}/download?type=${type}`, '_blank');
}
function downloadZip() {
    window.open(`/basts/${props.bast.id}/download-zip`, '_blank');
}
function printBast(type = 'tanda_terima') {
    window.open(`/basts/${props.bast.id}/print?type=${type}`, '_blank');
}

const docMenuOpen = ref(false);

if (isEdit.value) {
    loadItems();
}
</script>

<template>
    <div class="space-y-4">
        <PageHeader :title="title" :description="isEdit ? `DO ${bast?.do ?? ''}` : 'Buat BAST baru'">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack">Kembali</Button>
                <Button v-if="isEdit" variant="outline" size="sm" @click="() => window.close()">Tutup</Button>
                <Button v-if="!isEdit" size="sm" @click="saveHeader">Simpan & Lanjut ke Item</Button>
            </template>
        </PageHeader>

        <div class="rounded-lg border bg-white p-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Cari No DO">
                    <div class="flex gap-2">
                        <Input v-model="doKeyword" placeholder="CENT/OUT/" class="flex-1" @keyup.enter="searchDo(null, true)" />
                        <Button variant="outline" size="sm" @click="searchDo(null, true)">GET DO</Button>
                    </div>
                </FormField>
                <FormField label="Pilih Hasil Pencarian">
                    <SearchableSelect :model-value="selectedDoId" :options="doOptions" :loading="doLoading" placeholder="--- Pilih Hasil Pencarian ---" @update:model-value="pickDo" @search="searchDo" />
                </FormField>
                <FormField label="Kepada" required :error="errors.name?.[0]">
                    <Textarea v-model="form.name" rows="4" placeholder="Nama Penerima" />
                </FormField>
                <FormField label="Alamat Lengkap" required :error="errors.address?.[0]">
                    <Textarea v-model="form.address" rows="4" placeholder="Alamat Pengiriman" />
                </FormField>
                <FormField label="Kota" required :error="errors.city?.[0]">
                    <Input v-model="form.city" placeholder="Kota..." />
                </FormField>
                <FormField label="No Surat Jalan / DO" required :error="errors.do?.[0]">
                    <Input v-model="form.do" placeholder="Nomor DO" class="font-bold" />
                </FormField>
            </div>
        </div>

        <div v-if="isEdit" class="rounded-lg border bg-white">
            <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
                <b class="text-sm">Item BAST ({{ items.length }})</b>
                <span class="ml-auto flex flex-wrap items-center gap-1">
                    <Button size="sm" @click="openAddItem"><FilePlus /> Tambah Item</Button>
                    <span class="relative">
                        <Button variant="outline" size="sm" @click="docMenuOpen = !docMenuOpen"><Printer /> Print / Unduh <ChevronDown /></Button>
                        <span v-if="docMenuOpen" class="fixed inset-0 z-40" @click="docMenuOpen = false" />
                        <span v-if="docMenuOpen" class="absolute right-0 z-50 mt-1 w-56 rounded-lg border bg-white p-1 text-left shadow-lg">
                            <p class="px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Print</p>
                            <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="docMenuOpen = false; printBast('tanda_terima')"><Printer class="size-4 text-slate-500" /> Tanda Terima</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="docMenuOpen = false; printBast('training')"><Printer class="size-4 text-slate-500" /> Training</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="docMenuOpen = false; printBast('bast')"><Printer class="size-4 text-slate-500" /> BAST</button>
                            <p class="mt-1 border-t px-2 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Docx</p>
                            <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="docMenuOpen = false; downloadFile('tanda_terima')"><Download class="size-4 text-slate-500" /> Docx Terima</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="docMenuOpen = false; downloadFile('training')"><Download class="size-4 text-slate-500" /> Docx Training</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="docMenuOpen = false; downloadFile('bast')"><Download class="size-4 text-slate-500" /> Docx BAST</button>
                        </span>
                    </span>
                    <Button variant="outline" size="sm" @click="downloadZip"><Download /> ZIP</Button>
                    <Button variant="outline" size="sm" @click="syncOdoo"><RefreshCw /> Sync Odoo</Button>
                    <Button size="sm" @click="saveHeader">Simpan</Button>
                </span>
            </div>
            <div v-if="itemsLoading" class="space-y-2 p-3">
                <div v-for="n in 4" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else-if="items.length === 0" class="p-6 text-center text-sm text-slate-500">Belum ada item. Tambah atau Sync Odoo.</div>
            <div v-else class="divide-y">
                <div v-for="(row, i) in items" :key="row.id" class="flex flex-wrap items-center gap-2 px-3 py-2 text-sm">
                    <span class="w-8 text-xs text-slate-500">{{ i + 1 }}</span>
                    <span class="min-w-40 flex-1"><b>[{{ row.product?.code ?? '-' }}]</b> {{ row.product?.name ?? '-' }}</span>
                    <span class="font-mono">{{ row.qty ?? '-' }} {{ row.satuan ?? '' }}</span>
                    <span class="font-mono text-xs text-slate-500">{{ row.lot ?? '-' }}</span>
                    <span class="flex gap-1">
                        <Button variant="outline" size="sm" title="Naik" :disabled="i === 0" @click="orderItem(row, 'up')"><ArrowUp /></Button>
                        <Button variant="outline" size="sm" title="Turun" :disabled="i === items.length - 1" @click="orderItem(row, 'down')"><ArrowDown /></Button>
                        <Button variant="outline" size="sm" title="Buat kargan" @click="karganItem(row)"><Stamp /></Button>
                        <Button variant="outline" size="sm" @click="openEditItem(row)">Edit</Button>
                        <Button variant="outline" size="sm" title="Hapus" @click="deleteItem(row)"><Trash2 /></Button>
                    </span>
                </div>
            </div>
        </div>

        <AppModal v-model:open="itemModalOpen" :title="itemEditing ? 'Edit Item' : 'Tambah Item'" size="md">
            <div class="space-y-3">
                <FormField v-if="!itemEditing" label="Product" required>
                    <SearchableSelect v-model="itemForm.product" :options="productOptions" placeholder="Pilih product..." />
                </FormField>
                <div class="grid grid-cols-2 gap-3">
                    <FormField label="Qty" required>
                        <Input v-model="itemForm.qty" placeholder="100" />
                    </FormField>
                    <FormField label="Satuan" required>
                        <SearchableSelect v-model="itemForm.satuan" :options="satuanOptions" placeholder="EA" />
                    </FormField>
                </div>
                <FormField label="Lot / Expiration Date (ED)">
                    <Textarea v-model="itemForm.lot" rows="5" placeholder="Masukkan keterangan Lot atau ED..." />
                </FormField>
            </div>
            <template #footer>
                <Button variant="ghost" @click="itemModalOpen = false">Batal</Button>
                <Button @click="saveItem">Simpan</Button>
            </template>
        </AppModal>
    </div>
</template>
