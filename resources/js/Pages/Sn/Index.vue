<script setup>
import { computed, ref, watch } from 'vue';
import { Copy, Download, Eraser, Import, Trash2, WandSparkles, X } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { copyRows, downloadCsv } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'SN Tools' },
});

const toast = useToast();
const { confirm } = useConfirm();

const items = ref([]);
const genBase = ref('');
const genQty = ref(100);
const importText = ref('');
const skip = ref([]);
const tableSearch = ref('');
const page = ref(1);
const perPage = ref(10);
const perPageOptions = [10, 50, 100, 500, 1000];
const editingIndex = ref(-1);
const editValue = ref('');

const skipOptions = Array.from({ length: 20 }, (_, i) => ({ label: String(i), value: i }));

const filtered = computed(() => {
    const q = tableSearch.value.trim().toLowerCase();
    if (!q) {
        return items.value;
    }
    return items.value.filter((it) => String(it.item ?? '').toLowerCase().includes(q));
});

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage.value)));

const paged = computed(() => {
    const start = (page.value - 1) * perPage.value;
    return filtered.value.slice(start, start + perPage.value).map((it, i) => ({ ...it, _idx: start + i }));
});

const columns = [
    { key: 'sn', label: 'SN' },
];

function generate() {
    const base = genBase.value.trim();
    if (!base) {
        toast.warning('Base SN wajib diisi.');
        return;
    }
    const match = base.match(/^(.*?)(\d+)$/);
    if (!match) {
        toast.warning('Base SN harus diakhiri angka.');
        return;
    }
    const qty = parseInt(genQty.value, 10) || 0;
    const count = Math.abs(qty);
    if (count === 0) {
        toast.warning('Qty tidak boleh nol.');
        return;
    }
    const prefix = match[1];
    const numStr = match[2];
    const startNum = parseInt(numStr, 10);
    const padLength = numStr.length;
    const step = qty > 0 ? 1 : -1;
    let added = 0;
    for (let i = 0; i < count; i++) {
        const current = startNum + i * step;
        if (current < 0) {
            break;
        }
        items.value.push({ item: prefix + String(current).padStart(padLength, '0') });
        added += 1;
    }
    page.value = totalPages.value;
    toast.success(`${added} SN berhasil dibuat.`);
}

function doImport() {
    const text = importText.value.trim();
    if (!text) {
        toast.warning('Isi teks untuk diimpor dulu.');
        return;
    }
    const skipSet = new Set((skip.value ?? []).map(Number));
    let count = 0;
    text.split('\n').forEach((line) => {
        if (!line || !line.trim() || line.trim() === '-') {
            return;
        }
        line.split('\t').forEach((col, i) => {
            const val = col.trim();
            if (val && val !== '-' && !skipSet.has(i + 1)) {
                items.value.push({ item: val });
                count += 1;
            }
        });
    });
    toast.success(`${count} item berhasil diimpor.`);
}

function startEdit(row) {
    editingIndex.value = row._idx;
    editValue.value = items.value[row._idx]?.item ?? '';
}

function commitEdit() {
    if (editingIndex.value >= 0) {
        items.value[editingIndex.value].item = editValue.value;
    }
    editingIndex.value = -1;
}

function removeItem(idx) {
    items.value.splice(idx, 1);
    if (page.value > totalPages.value) {
        page.value = totalPages.value;
    }
}

async function emptyAll() {
    const ok = await confirm({
        title: 'Kosongkan semua item?',
        message: `${items.value.length} baris akan dihapus.`,
        confirmText: 'Ya, kosongkan',
        tone: 'destructive',
    });
    if (ok) {
        items.value = [];
        page.value = 1;
    }
}

function onCopy() {
    copyRows(items.value, ['item'], 'SN');
}

function onDownload() {
    downloadCsv('sn-tools.csv', items.value, ['item']);
}

function setSkip(values) {
    skip.value = values;
}

watch([tableSearch], () => {
    page.value = 1;
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Kumpulkan serial number: generate sekuens atau impor dari teks" />

        <div class="grid gap-4 lg:grid-cols-[8fr_4fr]">
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <Input v-model="tableSearch" type="search" placeholder="Cari SN..." class="max-w-xs" />
                    <div class="flex flex-wrap gap-2">
                        <Button variant="destructive" size="sm" :disabled="items.length === 0" @click="emptyAll"><Eraser /> Kosongkan</Button>
                        <Button variant="outline" size="sm" :disabled="items.length === 0" @click="onCopy"><Copy /> Salin</Button>
                        <Button variant="outline" size="sm" :disabled="items.length === 0" @click="onDownload"><Download /> Unduh CSV</Button>
                    </div>
                </div>

                <DataTable
                    :columns="columns"
                    :rows="paged"
                    :page="page"
                    :total-pages="totalPages"
                    :total="filtered.length"
                    :per-page="perPage"
                    :per-page-options="perPageOptions"
                    row-key="_idx"
                    empty-title="Belum ada SN"
                    empty-message="Generate dari Base SN atau impor dari teks."
                    @update:page="page = $event"
                    @update:per-page="perPage = Number($event); page = 1"
                >
                    <template #cell-sn="{ row }">
                        <Input
                            v-if="editingIndex === row._idx"
                            v-model="editValue"
                            class="h-8"
                            @blur="commitEdit"
                            @keydown.enter="commitEdit"
                        />
                        <button v-else type="button" class="block w-full cursor-pointer truncate text-left" @click="startEdit(row)">
                            {{ row.item }}
                        </button>
                    </template>
                    <template #actions="{ row }">
                        <Button variant="ghost" size="sm" title="Hapus baris" @click="removeItem(row._idx)"><Trash2 class="text-destructive" /></Button>
                    </template>
                </DataTable>
            </div>

            <div class="space-y-4">
                <Card class="p-4">
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold"><WandSparkles class="size-4" /> SN Generator</h2>
                    <div class="space-y-3">
                        <FormField label="Base SN" hint="Wajib diakhiri angka, cth: ABC0010">
                            <Input v-model="genBase" placeholder="ABC0010" />
                        </FormField>
                        <FormField label="Qty (+/-)">
                            <Input v-model="genQty" type="number" />
                        </FormField>
                        <Button class="w-full" @click="generate"><WandSparkles /> Generate</Button>
                    </div>
                </Card>

                <Card class="p-4">
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold"><Import class="size-4" /> Impor dari Teks</h2>
                    <div class="space-y-3">
                        <FormField label="Tempel teks / kolom Excel (tab sebagai pemisah)">
                            <div class="flex gap-2">
                                <Textarea v-model="importText" rows="5" class="flex-1 font-mono text-xs" />
                                <Button variant="outline" size="sm" title="Bersihkan teks" @click="importText = ''"><X /></Button>
                            </div>
                        </FormField>
                        <FormField label="Skip (kolom ke-, untuk paste multi-kolom)">
                            <MultiSelect v-model="skip" :options="skipOptions" placeholder="Tidak ada yang di-skip" />
                        </FormField>
                        <div class="flex flex-wrap gap-1">
                            <Button variant="outline" size="sm" @click="setSkip([2, 4])">MDS 2,4</Button>
                            <Button variant="outline" size="sm" @click="setSkip([2, 3])">FR202 2,3</Button>
                            <Button variant="outline" size="sm" @click="setSkip([2, 4, 6, 8, 10])">500E 2,4,6,8,10</Button>
                            <Button variant="ghost" size="sm" @click="setSkip([])">Reset</Button>
                        </div>
                        <Button class="w-full" variant="secondary" @click="doImport"><Import /> Impor</Button>
                    </div>
                </Card>
            </div>
        </div>

        <ConfirmDialog />
    </AppLayout>
</template>
