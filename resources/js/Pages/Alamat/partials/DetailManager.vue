<script setup>
import { computed, ref } from 'vue';
import { ArrowDown, ArrowUp, Pencil, Plus, Trash2 } from '@lucide/vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';

const props = defineProps({
    alamatId: { type: [Number, String], required: true },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();

const details = ref([]);
const loading = ref(false);

const columns = [
    { key: 'no', label: '#', align: 'center' },
    { key: 'product', label: 'Product' },
    { key: 'desc', label: 'Desc' },
    { key: 'qty', label: 'Qty', align: 'center' },
    { key: 'lot', label: 'Lot' },
];

const productOptions = computed(() => props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })));
const productLabel = (d) => (d?.product ? `[${d.product.code}] ${d.product.name}` : '-');

async function reload() {
    loading.value = true;
    try {
        const res = await web.get(`/alamats/${props.alamatId}`, { silent: true });
        const d = res.data?.data ?? res.data;
        details.value = [...(d?.detail ?? [])].sort((a, b) => (a.order ?? 0) - (b.order ?? 0));
    } catch (e) {
        details.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail.');
    } finally {
        loading.value = false;
    }
}

const rows = computed(() => details.value.map((d, i) => ({ ...d, no: (d.order ?? i) + 1 })));

// Add / Edit modal
const modalOpen = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const itemForm = ref({ product: '', desc: '', qty: '100 Ea', lot: '' });
const itemErrors = ref({});

function openAdd() {
    isEdit.value = false;
    editId.value = null;
    itemForm.value = { product: '', desc: '', qty: '100 Ea', lot: '' };
    itemErrors.value = {};
    modalOpen.value = true;
}

function openEdit(row) {
    isEdit.value = true;
    editId.value = row.id;
    itemForm.value = { product: row.product_id, desc: row.desc ?? '', qty: row.qty ?? '', lot: row.lot ?? '' };
    itemErrors.value = {};
    modalOpen.value = true;
}

async function saveItem() {
    itemErrors.value = {};
    if (!isEdit.value && !itemForm.value.product) {
        toast.error('Pilih product dulu.');
        return;
    }
    if (!String(itemForm.value.qty ?? '').trim()) {
        toast.error('Qty wajib diisi.');
        return;
    }
    try {
        if (isEdit.value) {
            await api.put(`/detail_alamat/${editId.value}`, { qty: itemForm.value.qty, lot: itemForm.value.lot, desc: itemForm.value.desc }, { block: true });
            toast.success('Detail diperbarui.');
        } else {
            await api.post('/detail_alamat', { alamat: props.alamatId, product: itemForm.value.product, qty: itemForm.value.qty, lot: itemForm.value.lot, desc: itemForm.value.desc }, { block: true });
            toast.success('Detail ditambah.');
        }
        modalOpen.value = false;
        reload();
    } catch (e) {
        if (e.response?.status === 422) {
            itemErrors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan detail.');
    }
}

async function deleteDetail(row) {
    const ok = await confirm({ title: 'Hapus detail?', message: 'Baris detail akan dihapus.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/detail_alamat/${row.id}`, { block: true });
        toast.success('Detail dihapus.');
        reload();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus detail.');
    }
}

async function moveDetail(row, type) {
    try {
        await api.post(`/detail_alamat/${row.id}/order`, { type }, { block: true, silent: true });
        reload();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memindah baris.');
    }
}

reload();

defineExpose({ reload, openAdd });
</script>

<template>
    <div class="rounded-lg border bg-white">
        <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
            <span class="text-sm font-semibold">Detail Product ({{ details.length }})</span>
            <Button size="sm" class="ml-auto" @click="openAdd"><Plus /> Tambah Product</Button>
        </div>
        <div class="p-3">
            <DataTable
                :columns="columns"
                :rows="rows"
                :loading="loading"
                :page="1"
                :total-pages="1"
                :total="rows.length"
                :per-page="rows.length || 1"
                :show-footer="false"
                empty-title="Belum ada detail"
                empty-message="Tambah product atau gunakan Sync Product dari DO."
            >
                <template #cell-no="{ row }">{{ row.no }}</template>
                <template #cell-product="{ row }">{{ productLabel(row) }}</template>
                <template #cell-desc="{ row }">{{ row.desc ?? '-' }}</template>
                <template #cell-qty="{ row }">{{ row.qty }}</template>
                <template #cell-lot="{ row }">{{ row.lot ?? '-' }}</template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1" @click.stop>
                        <Button variant="outline" size="sm" title="Naik" :disabled="row.no <= 1" @click="moveDetail(row, 'up')"><ArrowUp /></Button>
                        <Button variant="outline" size="sm" title="Turun" :disabled="row.no >= rows.length" @click="moveDetail(row, 'down')"><ArrowDown /></Button>
                        <Button variant="ghost" size="sm" title="Edit" @click="openEdit(row)"><Pencil /></Button>
                        <Button variant="ghost" size="sm" title="Hapus" @click="deleteDetail(row)"><Trash2 /></Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </div>

    <AppModal v-model:open="modalOpen" :title="isEdit ? 'Edit Detail' : 'Tambah Product'" size="md">
        <div class="grid gap-3">
            <FormField v-if="!isEdit" label="Product" required :error="itemErrors.product?.[0]">
                <SearchableSelect v-model="itemForm.product" :options="productOptions" placeholder="Pilih product..." />
            </FormField>
            <FormField label="Desc" :error="itemErrors.desc?.[0]">
                <Input v-model="itemForm.desc" placeholder="Keterangan..." />
            </FormField>
            <FormField label="Qty" required :error="itemErrors.qty?.[0]">
                <Input v-model="itemForm.qty" placeholder="100 Ea" />
            </FormField>
            <FormField label="Lot" :error="itemErrors.lot?.[0]">
                <Textarea v-model="itemForm.lot" rows="4" placeholder="Lot..." />
            </FormField>
        </div>
        <template #footer>
            <Button variant="ghost" @click="modalOpen = false">Batal</Button>
            <Button @click="saveItem">Simpan</Button>
        </template>
    </AppModal>
</template>
