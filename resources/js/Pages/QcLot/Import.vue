<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Card from '@/components/ui/Card.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Import QC Lot' },
});

const toast = useToast();
const rows = ref([]);
const textBox = ref('');
const table = useClientTable(null, { searchKeys: ['product', 'lot', 'ed', 'date', 'qc_by', 'qc_note'] });

function syncTable() {
    table.setRows([...rows.value]);
}

const columns = [
    { key: 'product', label: 'Product', mono: true },
    { key: 'lot', label: 'Lot', mono: true },
    { key: 'ed', label: 'ED', mono: true },
    { key: 'date', label: 'Date', mono: true },
    { key: 'qc_by', label: 'QC By' },
    { key: 'qc_note', label: 'Note', wrap: true, maxWidth: '220px' },
];

const searchBox = ref('');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

function normalizeDate(v) {
    const s = String(v ?? '').trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
        return s;
    }
    let m = s.match(/^(\d{2})[-/](\d{2})[-/](\d{4})$/);
    if (m) {
        return `${m[3]}-${m[2]}-${m[1]}`;
    }
    const num = Number(s);
    if (s && !Number.isNaN(num) && num > 20000 && num < 80000) {
        const d = new Date(Math.round((num - 25569) * 86400 * 1000));
        return d.toISOString().slice(0, 10);
    }
    return s;
}

async function onFile(e) {
    const file = e.target.files?.[0];
    if (!file) {
        return;
    }
    const text = await file.text();
    const lines = text.split(/\r?\n/);
    const out = [];
    // Terima CSV (koma/semicolon/tab). Header di baris 1 dilewati bila bukan data.
    lines.forEach((line, idx) => {
        if (!line.trim()) {
            return;
        }
        const c = line.split(/[\t;,]/);
        if (idx === 0 && /product/i.test(c[0] ?? '')) {
            return;
        }
        const product = (c[0] ?? '').trim();
        const lot = (c[1] ?? '').trim();
        const date = normalizeDate((c[2] ?? '').trim());
        if (!product || !lot || !date) {
            return;
        }
        out.push({ product, lot, ed: (c[3] ?? '').trim(), date, qc_by: (c[4] ?? '').trim(), qc_note: (c[5] ?? '').trim() });
    });
    rows.value = out;
    syncTable();
    toast.success(`${out.length} baris dimuat dari file.`);
    e.target.value = '';
}

function importText() {
    const out = [];
    textBox.value.split('\n').forEach((line) => {
        const c = line.split('\t');
        if (c.length < 3) {
            return;
        }
        const product = (c[0] ?? '').trim();
        const lot = (c[1] ?? '').trim();
        const date = normalizeDate(c[2]);
        if (!product || !lot || !date) {
            return;
        }
        out.push({ product, lot, ed: (c[3] ?? '').trim(), date, qc_by: (c[4] ?? '').trim(), qc_note: (c[5] ?? '').trim() });
    });
    rows.value = [...rows.value, ...out];
    syncTable();
    toast.success(`${out.length} baris ditambah dari teks.`);
}

function removeRow(row) {
    const i = rows.value.indexOf(row);
    if (i >= 0) {
        rows.value.splice(i, 1);
        syncTable();
    }
}
function clearAll() {
    rows.value = [];
    syncTable();
}

async function saveAll() {
    if (rows.value.length === 0) {
        toast.warning('Tidak ada data untuk disimpan.');
        return;
    }
    try {
        await api.post('/qc-lots/import', { data: rows.value }, { block: true });
        toast.success(`${rows.value.length} QC Lot diimport.`);
        rows.value = [];
        syncTable();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal import QC Lot.');
    }
}

const tableRows = computed(() => table.rows.value);
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Import QC Lot dari Excel atau teks tab-separated">
            <template #actions>
                <Button variant="ghost" size="sm" @click="router.visit('/qc-lots')">Kembali</Button>
                <Button variant="outline" size="sm" @click="clearAll">Hapus Data</Button>
                <Button size="sm" @click="saveAll">Simpan Data ({{ rows.length }})</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <div class="grid gap-4 lg:grid-cols-2">
                <Card class="p-4">
                    <FormField label="File CSV (Product, Lot, Date, ED, QC By, Note)">
                        <Input type="file" accept=".csv,.txt" @change="onFile" />
                    </FormField>
                    <p class="mt-2 text-xs text-muted-foreground">Simpan Excel sebagai CSV dulu. Baris pertama dianggap header bila berisi kata "product".</p>
                </Card>
                <Card class="p-4">
                    <FormField label="Import dari teks (Product, Lot, Date, ED, QC By, Note dipisah Tab)">
                        <Textarea v-model="textBox" rows="4" placeholder="Paste dari spreadsheet..." />
                    </FormField>
                    <div class="mt-2 flex gap-2">
                        <Button variant="outline" size="sm" @click="importText">Import Teks</Button>
                        <Button variant="ghost" size="sm" @click="textBox = ''">Bersihkan</Button>
                    </div>
                </Card>
            </div>
            <FormField label="Cari preview">
                <Input :model-value="searchBox" type="search" placeholder="Cari product / lot..." @input="onSearchInput" />
            </FormField>
            <DataTable
                :columns="columns"
                :rows="tableRows"
                :loading="false"
                :page="table.page.value"
                :total-pages="table.totalPages.value"
                :total="table.total.value"
                :per-page="table.perPage.value"
                empty-title="Belum ada data"
                empty-message="Import dari Excel atau teks di atas."
                @update:page="table.setPage"
                @update:per-page="table.setPerPage"
            >
                <template #actions="{ row }">
                    <Button variant="ghost" size="sm" @click="removeRow(row)"><Trash2 /></Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
