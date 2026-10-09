<script setup>
import { computed, ref, watch } from 'vue';
import { Copy, Download, Eraser, Search, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, downloadCsv } from '@/lib/export';
import { getCode, odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Laporan Pengiriman' },
});

const toast = useToast();
const { confirm } = useConfirm();

const doKeyword = ref('CENT/OUT/');
const doOptions = ref([]);
const doLoading = ref(false);
const selectedDo = ref('');
const tableSearch = ref('');
const page = ref(1);
const perPage = ref(10);
const perPageOptions = [10, 50, 100, 500, 1000];
const editing = ref({ idx: -1, key: '' });
const editValue = ref('');

const rows = ref([]);

const columns = [
    { key: 'name', label: 'Nama Customer' },
    { key: 'do', label: 'No DO', mono: true },
    { key: 'po', label: 'PO No', mono: true },
    { key: 'koli', label: 'Koli', align: 'center' },
    { key: 'ekspedisi', label: 'Ekspedisi' },
    { key: 'desc', label: 'Keterangan' },
];

const filtered = computed(() => {
    const q = tableSearch.value.trim().toLowerCase();
    if (!q) {
        return rows.value;
    }
    return rows.value.filter((r) => Object.values(r).some((v) => String(v ?? '').toLowerCase().includes(q)));
});

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage.value)));

const paged = computed(() => {
    const start = (page.value - 1) * perPage.value;
    return filtered.value.slice(start, start + perPage.value).map((r, i) => ({ ...r, _idx: start + i }));
});

function doLabel(item) {
    const group = item.group_id && item.group_id !== false ? ` (${item.group_id[1]})` : '';
    const partner = item.partner_id && item.partner_id !== false ? ` ${item.partner_id[1]}` : '';
    return `${item.name ?? ''}${group}${partner}`;
}

async function searchDo() {
    doLoading.value = true;
    try {
        const res = await api.get('/do', { params: { search: doKeyword.value }, silent: true });
        const list = res.data?.data ?? [];
        doOptions.value = list.map((d) => ({ label: doLabel(d), value: d.id }));
        if (list.length === 0) {
            toast.warning('DO tidak ditemukan.');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal mencari DO.');
    } finally {
        doLoading.value = false;
    }
}

function onDoSearch(keyword) {
    doKeyword.value = keyword;
    searchDo();
}

async function onPickDo(id) {
    selectedDo.value = id;
    if (!id) {
        return;
    }
    try {
        const res = await api.get(`/do/${id}`, { silent: true, block: true });
        const d = res.data?.data ?? res.data;
        const partner = odooName(d.partner_id);
        const desc = (d.move_ids_detail ?? []).map((m) => {
            const code = getCode(odooName(m.product_id));
            return `${code} (${m.quantity_done ?? 0})`;
        }).join(', ');
        rows.value.push({
            name: d.partner_address3 ? `${partner} / ${d.partner_address3}` : partner,
            do: d.name ?? '',
            po: d.no_po ?? '',
            koli: 1,
            ekspedisi: odooName(d.ekspedisi_id),
            desc,
        });
        selectedDo.value = '';
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail DO.');
    }
}

function startEdit(row, key) {
    editing.value = { idx: row._idx, key };
    editValue.value = rows.value[row._idx]?.[key] ?? '';
}

function commitEdit() {
    const { idx, key } = editing.value;
    if (idx >= 0 && key) {
        rows.value[idx][key] = editValue.value;
    }
    editing.value = { idx: -1, key: '' };
}

function removeRow(idx) {
    rows.value.splice(idx, 1);
    if (page.value > totalPages.value) {
        page.value = totalPages.value;
    }
}

async function emptyAll() {
    const ok = await confirm({
        title: 'Kosongkan semua item?',
        message: `${rows.value.length} baris akan dihapus.`,
        confirmText: 'Ya, kosongkan',
        tone: 'destructive',
    });
    if (ok) {
        rows.value = [];
        page.value = 1;
    }
}

const exportCols = ['name', 'do', 'po', 'koli', 'ekspedisi', 'desc'];

function onCopy() {
    copyRows(rows.value, exportCols, 'pengiriman');
}

function onDownload() {
    downloadCsv('laporan-pengiriman.csv', rows.value, exportCols);
}

watch([tableSearch], () => {
    page.value = 1;
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Susun manifest ekspedisi: cari DO lalu kumpulkan barisnya" />

        <div class="mb-3 grid gap-2 sm:grid-cols-[1fr_2fr]">
            <FormField label="Kata kunci DO">
                <div class="flex gap-2">
                    <Input v-model="doKeyword" placeholder="CENT/OUT/" @keydown.enter="searchDo" />
                    <Button variant="outline" :disabled="doLoading" @click="searchDo"><Search /> {{ doLoading ? '...' : 'GET' }}</Button>
                </div>
            </FormField>
            <FormField label="Pilih DO untuk tambah baris">
                <SearchableSelect
                    v-model="selectedDo"
                    :options="doOptions"
                    :loading="doLoading"
                    placeholder="Pilih DO..."
                    @update:model-value="onPickDo"
                    @search="onDoSearch"
                />
            </FormField>
        </div>

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <Input v-model="tableSearch" type="search" placeholder="Cari di tabel..." class="max-w-xs" />
            <div class="flex flex-wrap gap-2">
                <Button variant="destructive" size="sm" :disabled="rows.length === 0" @click="emptyAll"><Eraser /> Kosongkan</Button>
                <Button variant="outline" size="sm" :disabled="rows.length === 0" @click="onCopy"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" :disabled="rows.length === 0" @click="onDownload"><Download /> Unduh CSV</Button>
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
            empty-title="Belum ada baris"
            empty-message="Cari DO lalu pilih untuk menambah baris."
            @update:page="page = $event"
            @update:per-page="perPage = Number($event); page = 1"
        >
            <template v-for="col in columns" :key="col.key" #[`cell-${col.key}`]="{ row }">
                <Input
                    v-if="editing.idx === row._idx && editing.key === col.key"
                    v-model="editValue"
                    class="h-8 min-w-24"
                    @blur="commitEdit"
                    @keydown.enter="commitEdit"
                />
                <button v-else type="button" class="block w-full cursor-pointer truncate text-left" @click="startEdit(row, col.key)">
                    {{ row[col.key] ?? '-' }}
                </button>
            </template>
            <template #actions="{ row }">
                <Button variant="ghost" size="sm" title="Hapus baris" @click="removeRow(row._idx)"><Trash2 class="text-destructive" /></Button>
            </template>
        </DataTable>

        <ConfirmDialog />
    </AppLayout>
</template>
