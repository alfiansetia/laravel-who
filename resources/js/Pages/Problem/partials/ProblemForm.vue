<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Trash2, X } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Card from '@/components/ui/Card.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Problem' },
    mode: { type: String, default: 'create' },
    record: { type: Object, default: null },
    products: { type: Array, default: () => [] },
    picOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    stockOptions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    nextNumber: { type: String, default: '' },
    defaultDate: { type: String, default: '' },
});

const toast = useToast();
const isEdit = computed(() => props.mode === 'edit');

const f = ref({
    number: props.record?.number ?? props.nextNumber ?? '',
    date: props.record?.date ?? props.defaultDate ?? new Date().toISOString().slice(0, 10),
    pic: props.record?.pic ?? '',
    ri_po: props.record?.ri_po ?? '',
    type: props.record?.type ?? 'unit',
    stock: props.record?.stock ?? 'stock',
    status: props.record?.status ?? 'pending',
    email_on: props.record?.email_on ?? '',
});
const items = ref((props.record?.items ?? []).map((i) => ({ product_id: i.product_id, qty: i.qty, lot: i.lot ?? '', desc: i.desc ?? '' })));
const logs = ref((props.record?.logs ?? []).map((l) => ({ date: l.date ?? '', desc: l.desc ?? '' })));
const itemForm = ref({ product_id: '', qty: 1, lot: '', desc: '' });
const logForm = ref({ date: '', desc: '' });
const pasteText = ref('');
const errors = ref({});
const saving = ref(false);

const productOptions = computed(() => props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })));
const productMap = computed(() => {
    const m = {};
    props.products.forEach((p) => {
        m[String(p.code).toUpperCase()] = p.id;
    });
    return m;
});
const itemColumns = [
    { key: 'product', label: 'Product' },
    { key: 'qty', label: 'Qty', align: 'right', mono: true },
    { key: 'lot', label: 'Lot', mono: true },
    { key: 'desc', label: 'Desc', wrap: true, maxWidth: '200px' },
];
function itemLabel(it) {
    return productOptions.value.find((o) => String(o.value) === String(it.product_id))?.label ?? it.product_id;
}
function toOptions(list) {
    return (list ?? []).map((v) => ({ value: v, label: v }));
}

function addItem() {
    if (!itemForm.value.product_id) {
        toast.warning('Pilih product dulu.');
        return;
    }
    items.value.push({ ...itemForm.value });
    itemForm.value = { product_id: '', qty: 1, lot: '', desc: '' };
}
function removeItem(i) {
    items.value.splice(i, 1);
}
function addLog() {
    if (!logForm.value.date || !logForm.value.desc) {
        toast.warning('Tanggal dan deskripsi log wajib diisi.');
        return;
    }
    logs.value.push({ ...logForm.value });
    logForm.value = { date: '', desc: '' };
}
function removeLog(i) {
    logs.value.splice(i, 1);
}
function importPaste() {
    if (!pasteText.value.trim()) {
        return;
    }
    let added = 0;
    pasteText.value.split('\n').forEach((line) => {
        const c = line.split('\t');
        if (c.length < 2) {
            return;
        }
        const code = (c[0] ?? '').trim().toUpperCase();
        const pid = productMap.value[code];
        if (!pid) {
            return;
        }
        items.value.push({ product_id: pid, qty: Number(c[1]) || 1, lot: (c[2] ?? '').trim(), desc: (c[3] ?? '').trim() });
        added++;
    });
    toast.success(`${added} item ditambah dari paste.`);
}

function goBack() {
    router.visit('/problems');
}

function closeTab() {
    window.close();
}

async function save() {
    errors.value = {};
    if (items.value.length === 0) {
        toast.warning('Minimal 1 product bermasalah.');
        return;
    }
    const payload = { ...f.value, items: items.value, logs: logs.value };
    saving.value = true;
    try {
        if (isEdit.value) {
            await api.put(`/problem/${props.record.id}`, payload, { block: true });
            toast.success('Problem diperbarui.');
        } else {
            const res = await api.post('/problem', payload, { block: true });
            const id = res.data?.data?.id ?? res.data?.id;
            toast.success('Problem disimpan.');
            if (id) {
                window.open(`/problems/${id}/edit`, '_blank');
            }
        }
        router.visit('/problems');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan problem.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="space-y-4">
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack"><ArrowLeft /> Kembali</Button>
                <Button v-if="isEdit" variant="outline" size="sm" @click="closeTab"><X /> Tutup</Button>
                <Button size="sm" :disabled="saving" @click="save"><Save /> Simpan</Button>
            </template>
        </PageHeader>

        <Card class="p-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <FormField label="Nomor" required :error="errors.number?.[0]">
                    <Input v-model="f.number" />
                </FormField>
                <FormField label="Tanggal" required :error="errors.date?.[0]">
                    <Input v-model="f.date" type="date" />
                </FormField>
                <FormField label="PIC" required :error="errors.pic?.[0]">
                    <SearchableSelect v-model="f.pic" :options="toOptions(picOptions)" placeholder="Pilih PIC..." />
                </FormField>
                <FormField label="RI/PO" :error="errors.ri_po?.[0]">
                    <Input v-model="f.ri_po" />
                </FormField>
                <FormField label="Tipe" required :error="errors.type?.[0]">
                    <SearchableSelect v-model="f.type" :options="toOptions(typeOptions)" placeholder="Tipe..." />
                </FormField>
                <FormField label="Stock" required :error="errors.stock?.[0]">
                    <SearchableSelect v-model="f.stock" :options="toOptions(stockOptions)" placeholder="Stock..." />
                </FormField>
                <FormField label="Status" :error="errors.status?.[0]">
                    <SearchableSelect v-model="f.status" :options="toOptions(statusOptions)" placeholder="Status..." />
                </FormField>
                <FormField label="Email On" :error="errors.email_on?.[0]">
                    <Input v-model="f.email_on" type="date" />
                </FormField>
            </div>
        </Card>

        <Card class="p-4">
            <p class="mb-2 text-sm font-semibold">Product Bermasalah ({{ items.length }})</p>
            <DataTable
                v-if="items.length"
                :columns="itemColumns"
                :rows="items"
                :loading="false"
                :page="1"
                :total-pages="1"
                :total="items.length"
                :per-page="items.length"
                :show-footer="false"
                empty-title="Belum ada item"
            >
                <template #cell-product="{ row }">{{ itemLabel(row) }}</template>
                <template #cell-qty="{ row }">{{ row.qty }}</template>
                <template #cell-lot="{ row }">{{ row.lot || '-' }}</template>
                <template #cell-desc="{ row }">{{ row.desc || '-' }}</template>
                <template #actions="{ row }">
                    <Button variant="ghost" size="sm" @click="removeItem(items.indexOf(row))"><Trash2 /></Button>
                </template>
            </DataTable>
            <div class="mt-2 grid gap-2 sm:grid-cols-4">
                <SearchableSelect v-model="itemForm.product_id" :options="productOptions" placeholder="Product..." />
                <Input v-model.number="itemForm.qty" type="number" min="1" placeholder="Qty" />
                <Input v-model="itemForm.lot" placeholder="Lot" />
                <div class="flex gap-1">
                    <Input v-model="itemForm.desc" placeholder="Desc" class="flex-1" />
                    <Button variant="outline" size="sm" @click="addItem"><Plus /></Button>
                </div>
            </div>
            <FormField label="Paste dari Excel (Kode, Qty, Lot, Desc dipisah Tab)" class="mt-2">
                <Textarea v-model="pasteText" rows="3" placeholder="Paste baris spreadsheet..." />
            </FormField>
            <Button variant="outline" size="sm" class="mt-2" @click="importPaste">Tambah dari Paste</Button>
        </Card>

        <Card class="p-4">
            <p class="mb-2 text-sm font-semibold">Log Aktivitas ({{ logs.length }})</p>
            <div v-for="(l, i) in logs" :key="i" class="flex items-center gap-2 text-sm">
                <span class="font-mono text-xs">{{ l.date }}</span>
                <span class="flex-1">{{ l.desc }}</span>
                <Button variant="ghost" size="sm" @click="removeLog(i)"><Trash2 /></Button>
            </div>
            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                <Input v-model="logForm.date" type="date" />
                <Input v-model="logForm.desc" placeholder="Deskripsi log..." class="sm:col-span-2" />
            </div>
            <Button variant="outline" size="sm" class="mt-2" @click="addLog"><Plus /> Tambah Log</Button>
        </Card>
    </div>
</template>
