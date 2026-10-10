<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Save, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Card from '@/components/ui/Card.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useToast } from '@/composables/useToast';
import { useBlock } from '@/composables/useBlock';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Import Data ATK' },
});

const toast = useToast();
const { withBlock } = useBlock();
const rows = ref([]);
const table = useClientTable(null, { searchKeys: ['code', 'name', 'satuan'] });
function syncTable() {
    table.setRows([...rows.value]);
}

const columns = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama' },
    { key: 'satuan', label: 'Satuan', align: 'center' },
];
const searchBox = ref('');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
}

async function onFile(e) {
    const file = e.target.files?.[0];
    if (!file) {
        return;
    }
    await withBlock(async () => {
        await new Promise((r) => requestAnimationFrame(() => r()));
        const text = await file.text();
        const out = [];
        text.split(/\r?\n/).forEach((line, idx) => {
            if (!line.trim()) {
                return;
            }
            const c = line.split(/[\t;,]/);
            if (idx === 0 && /kode/i.test(c[0] ?? '')) {
                return;
            }
            const code = (c[0] ?? '').trim();
            const name = (c[1] ?? '').trim();
            const satuan = (c[2] ?? '').trim().toLowerCase();
            if (!code || !name || !satuan) {
                return;
            }
            out.push({ code, name, satuan });
        });
        rows.value = out;
        syncTable();
        toast.success(`${out.length} baris dimuat dari file.`);
        e.target.value = '';
    });
}

function removeRow(row) {
    const i = rows.value.indexOf(row);
    if (i >= 0) {
        rows.value.splice(i, 1);
        syncTable();
    }
}

async function saveAll() {
    if (rows.value.length === 0) {
        toast.warning('Tidak ada data untuk disimpan.');
        return;
    }
    try {
        await api.post('/atk-import', { data: rows.value }, { block: true });
        toast.success(`${rows.value.length} ATK diimport.`);
        rows.value = [];
        syncTable();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal import ATK.');
    }
}

const tableRows = computed(() => table.rows.value);
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Import ATK dari CSV (Kode, Nama, Satuan)">
            <template #actions>
                <Button variant="ghost" size="sm" @click="router.visit('/atk')"><ArrowLeft /> Kembali</Button>
                <Button variant="outline" size="sm" @click="rows = []; syncTable()">Hapus Data</Button>
                <Button size="sm" @click="saveAll"><Save /> Simpan Data ({{ rows.length }})</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <Card class="p-4">
                <FormField label="File CSV">
                    <Input type="file" accept=".csv,.txt,.xls,.xlsx" @change="onFile" />
                </FormField>
                <p class="mt-2 text-xs text-muted-foreground">Simpan Excel sebagai CSV dulu (kolom: KODE, NAME, Satuan).</p>
            </Card>
            <FormField label="Cari preview">
                <Input :model-value="searchBox" type="search" placeholder="Cari kode / nama..." @input="onSearchInput" />
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
                empty-message="Pilih file CSV di atas."
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
