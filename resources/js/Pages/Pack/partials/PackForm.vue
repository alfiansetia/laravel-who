<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowDown, ArrowLeft, ArrowRight, ArrowUp, Download, Hash, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
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
    title: { type: String, default: 'Packing List' },
    mode: { type: String, default: 'create' },
    pack: { type: Object, default: null },
    products: { type: Array, default: () => [] },
    vendors: { type: Array, default: () => [] },
});

const toast = useToast();
const { withBlock } = useBlock();
const isEdit = computed(() => props.mode === 'edit');

const form = ref({
    vendor_id: props.pack?.vendor_id ? String(props.pack.vendor_id) : '',
    product_id: props.pack?.product_id ? String(props.pack.product_id) : '',
    name: props.pack?.name ?? 'Default',
    desc: props.pack?.desc ?? '',
    vendor_desc: props.pack?.vendor_desc ?? '',
});
const errors = ref({});
const rows = ref([]);
const importText = ref('');
const templates = ref([]);

const productOptions = computed(() => props.products.map((p) => ({ label: `[${p.code}] ${p.name}`, value: String(p.id) })));
const vendorOptions = computed(() => props.vendors.map((v) => ({ label: v.name, value: String(v.id) })));
const productName = computed(() => props.products.find((p) => String(p.id) === String(form.value.product_id))?.name ?? '');

function blankRow(isGroup = false) {
    return { item: '', qty: '', is_group: isGroup, show_number: true, children: [] };
}

async function loadItems() {
    if (!isEdit.value) {
        return;
    }
    try {
        const res = await web.get('/pack-items', { params: { pack_id: props.pack.id }, silent: true });
        const body = res.data?.data ?? res.data ?? [];
        const flat = Array.isArray(body) ? body : (body.data ?? []);
        const tops = flat.filter((r) => !r.parent_id);
        const kids = flat.filter((r) => r.parent_id);
        rows.value = tops.map((t) => ({
            item: t.item ?? '',
            qty: t.qty ?? '',
            is_group: !!t.is_group,
            show_number: t.show_number !== false,
            children: kids.filter((k) => String(k.parent_id) === String(t.id)).map((c) => ({ item: c.item ?? '', qty: c.qty ?? '', show_number: c.show_number !== false })),
        }));
        if (rows.value.length === 0 && props.pack) {
            const res2 = await web.get(`/packs/${props.pack.id}`, { silent: true });
            const d = res2.data?.data ?? res2.data;
            rows.value = (d?.items ?? []).map((t) => ({
                item: t.item ?? '',
                qty: t.qty ?? '',
                is_group: !!t.is_group,
                show_number: t.show_number !== false,
                children: (t.children ?? []).map((c) => ({ item: c.item ?? '', qty: c.qty ?? '', show_number: c.show_number !== false })),
            }));
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat item.');
    }
}

async function loadTemplates() {
    templates.value = [];
    if (!form.value.product_id) {
        return;
    }
    try {
        const res = await api.get(`/products/${form.value.product_id}`, { silent: true });
        const d = res.data?.data ?? res.data;
        templates.value = d?.packs ?? [];
    } catch (e) {
        templates.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat template pack.');
    }
}

function onProductChange(v) {
    form.value.product_id = v;
    loadTemplates();
}

function addItem() {
    rows.value.push(blankRow(false));
}
function addGroup() {
    rows.value.push(blankRow(true));
}
function emptyRows() {
    rows.value = [];
}
function removeRow(i) {
    rows.value.splice(i, 1);
}
function moveRow(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= rows.value.length) {
        return;
    }
    const t = rows.value[i];
    rows.value[i] = rows.value[j];
    rows.value[j] = t;
}
function indentRow(i) {
    if (i === 0) {
        return;
    }
    const cur = rows.value.splice(i, 1)[0];
    rows.value[i - 1].children.push({ item: cur.item, qty: cur.qty, show_number: cur.show_number });
    (cur.children ?? []).forEach((c) => rows.value[i - 1].children.push(c));
}
function outdentRow(i, ci) {
    const child = rows.value[i].children.splice(ci, 1)[0];
    rows.value.splice(i + 1, 0, { item: child.item, qty: child.qty, is_group: false, show_number: child.show_number, children: [] });
}
function moveChild(i, ci, dir) {
    const arr = rows.value[i].children;
    const j = ci + dir;
    if (j < 0 || j >= arr.length) {
        return;
    }
    const t = arr[ci];
    arr[ci] = arr[j];
    arr[j] = t;
}
function removeChild(i, ci) {
    rows.value[i].children.splice(ci, 1);
}
function toggleNumber(i, ci = null) {
    if (ci === null) {
        rows.value[i].show_number = !rows.value[i].show_number;
    } else {
        rows.value[i].children[ci].show_number = !rows.value[i].children[ci].show_number;
    }
}

function importRows() {
    const lines = importText.value.split('\n');
    let lastGroup = -1;
    lines.forEach((raw) => {
        const line = raw.replace(/\r/g, '');
        if (!line.trim()) {
            return;
        }
        const parts = line.split('\t');
        const item = (parts[0] ?? '').trim();
        const qty = (parts[1] ?? '').trim();
        const indented = /^\s+/.test(line) || item.startsWith('- ');
        const clean = item.replace(/^[-\s]+/, '');
        const isGroup = /:$/.test(clean) || /^Part/i.test(clean);
        if (isGroup && !indented) {
            rows.value.push({ item: clean.replace(/:$/, ''), qty: '', is_group: true, show_number: !/^Part/i.test(clean), children: [] });
            lastGroup = rows.value.length - 1;
        } else if (indented && lastGroup >= 0) {
            rows.value[lastGroup].children.push({ item: clean, qty, show_number: true });
        } else {
            rows.value.push({ item: clean, qty, is_group: false, show_number: true, children: [] });
        }
    });
    importText.value = '';
}

function applyTemplate(pack) {
    (pack.items ?? []).forEach((t) => {
        rows.value.push({
            item: t.item ?? '',
            qty: t.qty ?? '',
            is_group: !!t.is_group,
            show_number: t.show_number !== false,
            children: (t.children ?? []).map((c) => ({ item: c.item ?? '', qty: c.qty ?? '', show_number: c.show_number !== false })),
        });
    });
    toast.success('Template ditambahkan.');
}

const displayRows = computed(() => {
    let n = 0;
    return rows.value.map((t) => {
        n += 1;
        const no = t.show_number ? String(n) : '';
        const letters = 'abcdefghijklmnopqrstuvwxyz';
        let ci = 0;
        let cj = 0;
        const children = t.children.map((c) => {
            let cno = '';
            if (c.show_number) {
                cno = t.is_group ? letters[ci++] ?? String(ci) : String(++cj);
            }
            return { ...c, no: cno };
        });
        return { ...t, no };
    });
});

function payloadItems() {
    return rows.value
        .map((t) => ({
            item: t.item,
            qty: t.is_group ? null : t.qty,
            is_group: !!t.is_group,
            show_number: !!t.show_number,
            children: t.children.map((c) => ({ item: c.item, qty: c.qty, show_number: !!c.show_number })),
        }))
        .filter((t) => String(t.item).trim() !== '' || (t.children?.length ?? 0) > 0);
}

async function save() {
    errors.value = {};
    const items = payloadItems();
    if (!form.value.product_id || !form.value.vendor_id || !form.value.name.trim() || items.length === 0) {
        toast.error('Product, vendor, nama PL, dan minimal 1 item wajib diisi.');
        return;
    }
    await withBlock(async () => {
        try {
            const payload = { product_id: Number(form.value.product_id), vendor_id: Number(form.value.vendor_id), name: form.value.name, desc: form.value.desc, vendor_desc: form.value.vendor_desc, items };
            if (isEdit.value) {
                await web.put(`/packs/${props.pack.id}`, payload, { block: true });
                toast.success('Packing list disimpan.');
            } else {
                await web.post('/packs', payload, { block: true });
                toast.success('Packing list dibuat.');
                router.visit('/packs');
            }
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data?.errors ?? {};
            }
        }
    });
}

function downloadCurrent() {
    if (isEdit.value) {
        window.open(`/packs/${props.pack.id}/download`, '_blank');
    }
}
function printCurrent() {
    if (isEdit.value) {
        window.open(`/packs/${props.pack.id}/print`, '_blank');
    }
}
function goBack() {
    router.visit('/packs');
}

if (isEdit.value) {
    loadItems();
}
loadTemplates();
</script>

<template>
    <div class="space-y-4">
        <PageHeader :title="title" description="Editor packing list 2 level (grup + anak)">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack">Kembali</Button>
                <template v-if="isEdit">
                    <Button variant="outline" size="sm" @click="loadItems"><RefreshCw /> Muat Ulang Item</Button>
                    <Button variant="outline" size="sm" @click="downloadCurrent"><Download /> Excel</Button>
                    <Button variant="outline" size="sm" @click="printCurrent">Print</Button>
                </template>
                <Button size="sm" @click="save">Simpan</Button>
            </template>
        </PageHeader>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 rounded-lg border bg-white p-4 lg:col-span-1">
                <FormField label="Vendor" required :error="errors.vendor_id?.[0]">
                    <SearchableSelect v-model="form.vendor_id" :options="vendorOptions" placeholder="Pilih vendor..." />
                </FormField>
                <FormField label="Product" required :error="errors.product_id?.[0]">
                    <SearchableSelect :model-value="form.product_id" :options="productOptions" placeholder="Pilih product..." @update:model-value="onProductChange" />
                </FormField>
                <p v-if="productName" class="text-xs text-slate-500">{{ productName }}</p>
                <FormField label="PL Name" required :error="errors.name?.[0]">
                    <Input v-model="form.name" maxlength="200" />
                </FormField>
                <FormField label="PL Desc">
                    <Input v-model="form.desc" maxlength="200" />
                </FormField>
                <FormField label="Vendor Desc">
                    <Input v-model="form.vendor_desc" maxlength="200" />
                </FormField>
                <FormField label="Import (item + TAB + qty)">
                    <Textarea v-model="importText" rows="4" placeholder="Tempel dari Excel..." />
                    <div class="mt-2 flex gap-2">
                        <Button variant="outline" size="sm" @click="importText = ''">Clear</Button>
                        <Button variant="outline" size="sm" @click="importRows">Import</Button>
                    </div>
                </FormField>
                <div v-if="templates.length > 0">
                    <p class="mb-1 text-xs font-semibold text-slate-500">Template pack product ini ({{ templates.length }})</p>
                    <div class="flex flex-wrap gap-1">
                        <Button v-for="t in templates" :key="t.id" variant="outline" size="sm" @click="applyTemplate(t)">{{ t.name }}</Button>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-lg border bg-white">
                    <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
                        <Button size="sm" @click="addItem"><Plus /> Item</Button>
                        <Button variant="outline" size="sm" @click="addGroup"><Plus /> Grup</Button>
                        <Button variant="outline" size="sm" @click="emptyRows"><Trash2 /> Kosongkan</Button>
                        <span class="ml-auto text-xs text-slate-500">{{ rows.length }} baris</span>
                    </div>
                    <div v-if="rows.length === 0" class="p-6 text-center text-sm text-slate-500">Belum ada baris.</div>
                    <div v-else class="divide-y">
                        <div v-for="(t, i) in displayRows" :key="i" class="px-3 py-2" :class="t.is_group && 'bg-amber-50/50'">
                            <div class="flex items-start gap-2">
                                <span class="w-8 pt-2 text-xs font-semibold text-slate-500">{{ t.no }}</span>
                                <div class="flex-1 space-y-1">
                                    <div class="flex gap-2">
                                        <Input v-model="rows[i].item" class="flex-1" :class="t.is_group && 'font-semibold'" :placeholder="t.is_group ? 'Nama grup...' : 'Nama item...'" />
                                        <Input v-model="rows[i].qty" class="w-24" placeholder="Qty" :disabled="t.is_group" />
                                    </div>
                                    <div v-for="(c, ci) in t.children" :key="ci" class="flex items-center gap-2 pl-6">
                                        <span class="w-6 text-xs text-slate-400">{{ c.no }}</span>
                                        <Input v-model="rows[i].children[ci].item" class="flex-1" placeholder="Anak..." />
                                        <Input v-model="rows[i].children[ci].qty" class="w-24" placeholder="Qty" />
                                        <span class="flex gap-1">
                                            <Button variant="outline" size="sm" title="Naik" @click="moveChild(i, ci, -1)"><ArrowUp /></Button>
                                            <Button variant="outline" size="sm" title="Turun" @click="moveChild(i, ci, 1)"><ArrowDown /></Button>
                                            <Button variant="outline" size="sm" title="Jadikan baris atas" @click="outdentRow(i, ci)"><ArrowLeft /></Button>
                                            <Button variant="outline" size="sm" :title="c.show_number ? 'Sembunyikan nomor' : 'Tampilkan nomor'" @click="toggleNumber(i, ci)"><Hash /></Button>
                                            <Button variant="outline" size="sm" title="Hapus" @click="removeChild(i, ci)"><Trash2 /></Button>
                                        </span>
                                    </div>
                                </div>
                                <span class="flex flex-col gap-1">
                                    <Button variant="outline" size="sm" title="Naik" @click="moveRow(i, -1)"><ArrowUp /></Button>
                                    <Button variant="outline" size="sm" title="Turun" @click="moveRow(i, 1)"><ArrowDown /></Button>
                                    <Button variant="outline" size="sm" title="Jadikan anak baris atas" @click="indentRow(i)"><ArrowRight /></Button>
                                    <Button variant="outline" size="sm" :title="t.show_number ? 'Sembunyikan nomor' : 'Tampilkan nomor'" @click="toggleNumber(i)"><Hash /></Button>
                                    <Button variant="outline" size="sm" title="Hapus" @click="removeRow(i)"><Trash2 /></Button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
