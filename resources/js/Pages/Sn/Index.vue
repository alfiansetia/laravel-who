<script setup>
import { computed, ref, watch } from 'vue';
import { ArrowDownAZ, Copy, CopyCheck, Download, Eraser, Import, Trash2, WandSparkles, X } from '@lucide/vue';
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

const MAX_GENERATE = 5000;

let seq = 0;
function newItem(value) {
    seq += 1;
    return { id: seq, item: value };
}

const items = ref([]);
const genBase = ref('');
const genQty = ref(100);
const importText = ref('');
const skip = ref([]);
const tableSearch = ref('');
const page = ref(1);
const perPage = ref(10);
const perPageOptions = [10, 25, 50, 100, 500, 1000];
const editingId = ref(null);
const editValue = ref('');

const skipOptions = Array.from({ length: 20 }, (_, i) => ({ label: `Kolom ${i + 1}`, value: i + 1 }));

const hasItems = computed(() => items.value.length > 0);

const filtered = computed(() => {
    const q = tableSearch.value.trim().toLowerCase();
    if (!q) {
        return items.value;
    }
    return items.value.filter((it) => String(it.item ?? '').toLowerCase().includes(q));
});

const isSearching = computed(() => tableSearch.value.trim().length > 0);

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage.value)));

const paged = computed(() => {
    const safePage = Math.min(page.value, totalPages.value);
    const start = (safePage - 1) * perPage.value;
    return filtered.value.slice(start, start + perPage.value).map((it, i) => ({ ...it, no: start + i + 1 }));
});

const columns = [
    { key: 'no', label: 'No', align: 'center', width: '3.5rem' },
    { key: 'item', label: 'SN', mono: true, wrap: true },
];

function clampPage() {
    if (page.value > totalPages.value) {
        page.value = totalPages.value;
    }
    if (page.value < 1) {
        page.value = 1;
    }
}

function generate() {
    const base = genBase.value.trim();
    if (!base) {
        toast.warning('Base SN wajib diisi.');
        return;
    }
    const match = base.match(/^(.*?)(\d+)$/);
    if (!match) {
        toast.warning('Base SN harus diakhiri angka, contoh: ABC0010.');
        return;
    }
    const qty = parseInt(genQty.value, 10) || 0;
    if (qty === 0) {
        toast.warning('Qty tidak boleh nol.');
        return;
    }
    const count = Math.abs(qty);
    if (count > MAX_GENERATE) {
        toast.warning(`Maksimal ${MAX_GENERATE.toLocaleString('id-ID')} SN per generate.`);
        return;
    }
    const prefix = match[1];
    const numStr = match[2];
    const startNum = parseInt(numStr, 10);
    const padLength = numStr.length;
    const step = qty > 0 ? 1 : -1;

    const existing = new Set(items.value.map((it) => it.item));
    let added = 0;
    let skipped = 0;
    for (let i = 0; i < count; i++) {
        const current = startNum + i * step;
        if (current < 0) {
            break;
        }
        const sn = prefix + String(current).padStart(padLength, '0');
        if (existing.has(sn)) {
            skipped += 1;
            continue;
        }
        existing.add(sn);
        items.value.push(newItem(sn));
        added += 1;
    }
    page.value = totalPages.value;
    if (added === 0) {
        toast.warning('Tidak ada SN baru, semua sudah ada di daftar.');
        return;
    }
    toast.success(skipped > 0 ? `${added} SN dibuat, ${skipped} duplikat dilewati.` : `${added} SN berhasil dibuat.`);
}

function doImport() {
    const text = importText.value.replace(/\r/g, '');
    if (!text.trim()) {
        toast.warning('Isi teks untuk diimpor dulu.');
        return;
    }
    const skipSet = new Set((skip.value ?? []).map(Number));
    const existing = new Set(items.value.map((it) => it.item));
    let added = 0;
    let skipped = 0;
    text.split('\n').forEach((line) => {
        if (!line || !line.trim() || line.trim() === '-') {
            return;
        }
        line.split('\t').forEach((col, i) => {
            const val = col.trim();
            if (!val || val === '-' || skipSet.has(i + 1)) {
                return;
            }
            if (existing.has(val)) {
                skipped += 1;
                return;
            }
            existing.add(val);
            items.value.push(newItem(val));
            added += 1;
        });
    });
    if (added === 0) {
        toast.warning(skipped > 0 ? 'Tidak ada item baru, semua duplikat.' : 'Tidak ada item valid yang diimpor.');
        return;
    }
    importText.value = '';
    page.value = totalPages.value;
    toast.success(skipped > 0 ? `${added} item diimpor, ${skipped} duplikat dilewati.` : `${added} item berhasil diimpor.`);
}

function startEdit(row) {
    editingId.value = row.id;
    editValue.value = row.item ?? '';
}

function cancelEdit() {
    editingId.value = null;
    editValue.value = '';
}

function commitEdit() {
    if (editingId.value === null) {
        return;
    }
    const val = editValue.value.trim();
    if (!val) {
        toast.warning('SN tidak boleh kosong.');
        return;
    }
    const target = items.value.find((it) => it.id === editingId.value);
    if (target) {
        const duplicate = items.value.some((it) => it.id !== editingId.value && it.item === val);
        if (duplicate) {
            toast.warning('SN sudah ada di daftar.');
            return;
        }
        target.item = val;
    }
    cancelEdit();
}

function removeItem(id) {
    const idx = items.value.findIndex((it) => it.id === id);
    if (idx >= 0) {
        items.value.splice(idx, 1);
    }
    if (editingId.value === id) {
        cancelEdit();
    }
    clampPage();
}

async function emptyAll() {
    const ok = await confirm({
        title: 'Kosongkan semua item?',
        message: `${items.value.length} baris akan dihapus. Tindakan ini tidak bisa dibatalkan.`,
        confirmText: 'Ya, kosongkan',
        tone: 'destructive',
    });
    if (ok) {
        items.value = [];
        cancelEdit();
        page.value = 1;
        toast.success('Semua item dihapus.');
    }
}

async function removeDuplicates() {
    const seen = new Set();
    const before = items.value.length;
    items.value = items.value.filter((it) => {
        if (seen.has(it.item)) {
            return false;
        }
        seen.add(it.item);
        return true;
    });
    const removed = before - items.value.length;
    cancelEdit();
    clampPage();
    if (removed === 0) {
        toast.success('Tidak ada duplikat ditemukan.');
        return;
    }
    toast.success(`${removed} duplikat dihapus.`);
}

function sortItems() {
    items.value = [...items.value].sort((a, b) => String(a.item).localeCompare(String(b.item), 'id-ID'));
    page.value = 1;
    toast.success('Daftar diurutkan A-Z.');
}

function onCopy() {
    copyRows(filtered.value, ['item'], 'SN');
}

function onDownload() {
    downloadCsv('sn-tools.csv', filtered.value, ['item']);
}

function setSkip(values) {
    skip.value = values.map(Number);
}

watch(tableSearch, () => {
    page.value = 1;
});

watch(perPage, () => {
    page.value = 1;
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title">
            <template #actions>
                <span v-if="hasItems" class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold tabular-nums text-primary">
                    {{ items.length.toLocaleString('id-ID') }} SN{{ isSearching ? ` / ${filtered.length.toLocaleString('id-ID')} cocok` : '' }}
                </span>
                <Button variant="outline" size="sm" :disabled="!hasItems" title="Salin yang tampil (ikut filter cari)" @click="onCopy"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" :disabled="!hasItems" title="Unduh CSV yang tampil (ikut filter cari)" @click="onDownload"><Download /> Unduh CSV</Button>
                <Button variant="destructive" size="sm" :disabled="!hasItems" @click="emptyAll"><Eraser /> Kosongkan</Button>
            </template>
        </PageHeader>

        <div class="grid items-start gap-4 lg:grid-cols-3">
            <div class="space-y-3 lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2">
                    <Input v-model="tableSearch" type="search" placeholder="Cari SN..." class="max-w-xs flex-1 sm:flex-none" />
                    <div class="flex flex-wrap gap-2">
                        <Button variant="ghost" size="sm" :disabled="!hasItems" title="Urutkan A-Z" @click="sortItems"><ArrowDownAZ /> Urutkan</Button>
                        <Button variant="ghost" size="sm" :disabled="!hasItems" title="Hapus SN yang sama" @click="removeDuplicates"><CopyCheck /> Hapus Duplikat</Button>
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
                    row-key="id"
                    empty-title="Belum ada SN"
                    empty-message="Generate dari Base SN atau impor dari teks di panel kanan."
                    @update:page="page = $event"
                    @update:per-page="perPage = Number($event); page = 1"
                >
                    <template #cell-item="{ row }">
                        <Input
                            v-if="editingId === row.id"
                            v-model="editValue"
                            autofocus
                            class="h-8 font-mono text-xs"
                            placeholder="SN..."
                            @blur="commitEdit"
                            @keydown.enter.prevent="commitEdit"
                            @keydown.esc.prevent="cancelEdit"
                        />
                        <button
                            v-else
                            type="button"
                            class="block w-full cursor-pointer truncate text-left font-mono text-xs hover:text-primary hover:underline"
                            :title="`Klik untuk ubah: ${row.item}`"
                            @click="startEdit(row)"
                        >
                            {{ row.item }}
                        </button>
                    </template>
                    <template #actions="{ row }">
                        <div class="flex justify-end gap-1">
                            <Button
                                v-if="editingId === row.id"
                                variant="ghost"
                                size="sm"
                                title="Batal ubah (Esc)"
                                @click="cancelEdit"
                            >
                                <X />
                            </Button>
                            <Button variant="ghost" size="sm" title="Hapus baris" @click="removeItem(row.id)"><Trash2 class="text-destructive" /></Button>
                        </div>
                    </template>
                </DataTable>
                <p class="text-xs text-muted-foreground">Klik SN pada tabel untuk mengubah. Tekan Enter untuk simpan, Esc untuk batal.</p>
            </div>

            <div class="space-y-4">
                <Card class="p-4">
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold"><WandSparkles class="size-4 text-primary" /> SN Generator</h2>
                    <div class="space-y-3">
                        <FormField label="Base SN" hint="Wajib diakhiri angka, contoh: ABC0010">
                            <Input v-model="genBase" placeholder="ABC0010" class="font-mono uppercase" @keydown.enter.prevent="generate" />
                        </FormField>
                        <FormField label="Qty (+ maju / - mundur)" :hint="`Maks ${MAX_GENERATE.toLocaleString('id-ID')} per generate`">
                            <Input v-model="genQty" type="number" step="1" :min="-MAX_GENERATE" :max="MAX_GENERATE" @keydown.enter.prevent="generate" />
                        </FormField>
                        <Button class="w-full" @click="generate"><WandSparkles /> Generate</Button>
                    </div>
                </Card>

                <Card class="p-4">
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold"><Import class="size-4 text-primary" /> Impor dari Teks</h2>
                    <div class="space-y-3">
                        <FormField label="Tempel teks / kolom Excel" hint="Tab memisahkan kolom, baris baru memisahkan baris">
                            <div class="flex items-start gap-2">
                                <Textarea v-model="importText" rows="5" placeholder="Tempel di sini..." class="flex-1 font-mono text-xs" />
                                <Button variant="outline" size="sm" title="Bersihkan teks" :disabled="!importText" @click="importText = ''"><X /></Button>
                            </div>
                        </FormField>
                        <FormField label="Skip kolom ke-">
                            <MultiSelect v-model="skip" :options="skipOptions" placeholder="Tidak ada yang di-skip" />
                        </FormField>
                        <div class="flex flex-wrap gap-1">
                            <Button variant="outline" size="sm" @click="setSkip([2, 4])">MDS 2,4</Button>
                            <Button variant="outline" size="sm" @click="setSkip([2, 3])">FR202 2,3</Button>
                            <Button variant="outline" size="sm" @click="setSkip([2, 4, 6, 8, 10])">500E 2,4,6,8,10</Button>
                            <Button variant="ghost" size="sm" @click="setSkip([])">Reset</Button>
                        </div>
                        <Button class="w-full" variant="secondary" :disabled="!importText.trim()" @click="doImport"><Import /> Impor</Button>
                    </div>
                </Card>
            </div>
        </div>

        <ConfirmDialog />
    </AppLayout>
</template>
