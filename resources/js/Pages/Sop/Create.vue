<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Download, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppModal from '@/components/AppModal.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Manage SOP QC' },
    products: { type: Array, default: () => [] },
    sopItems: { type: Array, default: () => [] },
});

const toast = useToast();
const { withBlock } = useBlock();

const productId = ref('');
const target = ref('');
const importText = ref('');
const rows = ref([]);
const errors = ref({});
const savedSopId = ref(null);
const itemModalOpen = ref(false);
const itemText = ref('');

const productOptions = computed(() =>
    props.products.map((p) => ({ label: `[${p.code}] ${p.name}`, value: String(p.id) })),
);
const itemListOptions = computed(() => (props.sopItems ?? []).map((s) => ({ label: String(s), value: String(s) })));
const selectedItemList = ref('');

async function onProductChange(val) {
    productId.value = val;
    target.value = '';
    rows.value = [];
    savedSopId.value = null;
    if (!val) {
        return;
    }
    try {
        const res = await api.get(`/products/${val}`, { silent: true, block: true });
        const sop = res.data?.data?.sop ?? res.data?.sop;
        if (sop) {
            target.value = sop.target ?? '';
            rows.value = (sop.items ?? []).map((i) => ({ item: i.item ?? '' }));
            savedSopId.value = sop.id ?? null;
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat SOP product.');
    }
}

function addRow(content = '') {
    rows.value.push({ item: content });
}
function removeRow(i) {
    rows.value.splice(i, 1);
}
function moveRow(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= rows.value.length) {
        return;
    }
    const tmp = rows.value[i];
    rows.value[i] = rows.value[j];
    rows.value[j] = tmp;
}
function emptyRows() {
    rows.value = [];
}
function importRows() {
    const lines = importText.value.split('\n').map((s) => s.trim()).filter(Boolean);
    lines.forEach((l) => rows.value.push({ item: l }));
    importText.value = '';
}
function exportToImport() {
    importText.value = rows.value.map((r) => r.item).join('\n');
}
function addFromItemList(val) {
    if (val) {
        addRow(val);
        selectedItemList.value = '';
    }
}
function saveItemModal() {
    if (!itemText.value.trim()) {
        return;
    }
    addRow(itemText.value.trim());
    itemText.value = '';
    itemModalOpen.value = false;
}

async function save() {
    errors.value = {};
    const items = rows.value.map((r) => ({ item: r.item ?? '' })).filter((r) => String(r.item).trim() !== '');
    if (!productId.value || !target.value.trim() || items.length === 0) {
        toast.error('Product, target, dan minimal 1 item wajib diisi.');
        return;
    }
    await withBlock(async () => {
        try {
            const res = await web.post('/sops', { product_id: Number(productId.value), target: target.value, items }, { block: true });
            const sop = res.data?.data ?? res.data;
            savedSopId.value = sop?.id ?? savedSopId.value;
            toast.success('SOP disimpan.');
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data?.errors ?? {};
            }
        }
    });
}

function downloadSaved() {
    if (savedSopId.value) {
        window.open(`/sops/${savedSopId.value}/download`, '_blank');
    }
}
function goBack() {
    router.visit('/sops');
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Tambah / kelola SOP QC per product">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack">Kembali</Button>
                <Button variant="outline" size="sm" :disabled="!savedSopId" @click="downloadSaved"><Download /> Download</Button>
                <Button size="sm" @click="save">Simpan</Button>
            </template>
        </PageHeader>
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-1">
                <FormField label="Product" required :error="errors.product_id?.[0]">
                    <SearchableSelect :model-value="productId" :options="productOptions" placeholder="Pilih product..." @update:model-value="onProductChange" />
                </FormField>
                <FormField label="Target" required :error="errors.target?.[0]">
                    <div class="flex gap-2">
                        <Input v-model="target" placeholder="Target pemeriksaan..." class="flex-1" />
                        <Button variant="outline" size="sm" @click="target = ''">Clear</Button>
                    </div>
                </FormField>
                <FormField label="Dari Item List">
                    <SearchableSelect :model-value="selectedItemList" :options="itemListOptions" placeholder="Pilih item..." @update:model-value="addFromItemList" />
                </FormField>
                <FormField label="Import (satu baris = satu item)">
                    <Textarea v-model="importText" rows="4" placeholder="Tempel daftar item..." />
                    <div class="mt-2 flex gap-2">
                        <Button variant="outline" size="sm" @click="importText = ''">Clear</Button>
                        <Button variant="outline" size="sm" @click="importRows">Import</Button>
                    </div>
                </FormField>
            </div>
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-lg border bg-white">
                    <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
                        <Button size="sm" @click="itemModalOpen = true"><Plus /> Add Item</Button>
                        <Button variant="outline" size="sm" @click="emptyRows"><Trash2 /> Kosongkan</Button>
                        <Button variant="outline" size="sm" @click="exportToImport">Export ke Import</Button>
                        <span class="ml-auto text-xs text-slate-500">{{ rows.length }} item</span>
                    </div>
                    <div class="divide-y">
                        <div v-if="rows.length === 0" class="p-6 text-center text-sm text-slate-500">Belum ada item. Tambah lewat tombol di atas atau import.</div>
                        <div v-for="(r, i) in rows" :key="i" class="flex items-start gap-2 px-3 py-2">
                            <span class="w-8 pt-2 text-xs text-slate-500">{{ i + 1 }}</span>
                            <Textarea v-model="r.item" rows="2" class="flex-1" :placeholder="`Item ${i + 1}...`" />
                            <div class="flex flex-col gap-1">
                                <Button variant="outline" size="sm" title="Naik" @click="moveRow(i, -1)"><ArrowUp /></Button>
                                <Button variant="outline" size="sm" title="Turun" @click="moveRow(i, 1)"><ArrowDown /></Button>
                                <Button variant="outline" size="sm" title="Hapus" @click="removeRow(i)"><Trash2 /></Button>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="errors.items?.[0]" class="mt-1 text-xs text-destructive">{{ errors.items[0] }}</p>
            </div>
        </div>
        <AppModal v-model:open="itemModalOpen" title="Tambah Item" size="md">
            <FormField label="Item" required>
                <Textarea v-model="itemText" rows="6" placeholder="Tulis item pemeriksaan..." />
            </FormField>
            <template #footer>
                <Button variant="ghost" @click="itemModalOpen = false">Batal</Button>
                <Button @click="saveItemModal">Simpan</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
