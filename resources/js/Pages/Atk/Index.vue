<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowDownToLine, ArrowUpFromLine, Copy, Eye, Pencil, Plus, RefreshCw, Save, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';
import { copyRows } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Data ATK' },
    filters: { type: Object, default: () => ({}) },
    satuanList: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const query = useTableQuery(
    (p) => api.get('/atk', { params: { ...p, satuan: satuanBox.value || undefined } }),
    { search: props.filters.search ?? '', page: Number(props.filters.page ?? 1) },
);
const selected = ref([]);

const searchBox = ref(props.filters.search ?? '');
const satuanBox = ref(props.filters.satuan ?? '');
function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}
function onSatuan(v) {
    satuanBox.value = v;
    query.fetch();
}
function resetFilter() {
    searchBox.value = '';
    satuanBox.value = '';
    query.setSearch('', 0);
}
const activeCount = computed(() => (query.search.value ? 1 : 0) + (satuanBox.value ? 1 : 0));

const columns = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama' },
    { key: 'satuan', label: 'Satuan', align: 'center' },
    { key: 'stok', label: 'Stok', align: 'center', mono: true },
];
const perPageOptions = [10, 25, 50, 100, 200];
const satuanOptions = computed(() => props.satuanList.map((s) => ({ value: s, label: s })));

const exportRows = computed(() =>
    query.rows.value.map((r) => ({ code: r.code ?? '', name: r.name ?? '', satuan: r.satuan ?? '', stok: r.stok ?? 0 })),
);

// Add / Edit
const formOpen = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const form = ref({ code: '', name: '', satuan: '', desc: '' });
const errors = ref({});

function openAdd() {
    isEdit.value = false;
    editId.value = null;
    form.value = { code: '', name: '', satuan: '', desc: '' };
    errors.value = {};
    formOpen.value = true;
}
async function openEdit(row) {
    isEdit.value = true;
    editId.value = row.id;
    errors.value = {};
    try {
        const res = await api.get(`/atk/${row.id}`, { silent: true, block: true });
        const d = res.data?.data ?? res.data;
        form.value = { code: d.code ?? '', name: d.name ?? '', satuan: d.satuan ?? '', desc: d.desc ?? '' };
    } catch (e) {
        form.value = { code: row.code ?? '', name: row.name ?? '', satuan: row.satuan ?? '', desc: row.desc ?? '' };
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail ATK.');
    }
    formOpen.value = true;
}
async function saveForm() {
    errors.value = {};
    try {
        if (isEdit.value) {
            await api.put(`/atk/${editId.value}`, { ...form.value }, { block: true });
            toast.success('ATK diperbarui.');
        } else {
            await api.post('/atk', { ...form.value }, { block: true });
            toast.success('ATK ditambah.');
        }
        formOpen.value = false;
        query.fetch();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan ATK.');
    }
}
async function deleteRow(row) {
    const ok = await confirm({ title: `Hapus ${row.code}?`, message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/atk/${row.id}`, { block: true });
        toast.success('ATK dihapus.');
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus ATK.');
    }
}
async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 data untuk dihapus.');
        return;
    }
    const ok = await confirm({ title: `Hapus ${selected.value.length} ATK?`, message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/atk', { data: { ids: selected.value }, block: true });
        toast.success('ATK dihapus.');
        selected.value = [];
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus ATK.');
    }
}

// Transaksi in/out
const trxOpen = ref(false);
const trxRow = ref(null);
const trxForm = ref({ date: new Date().toISOString().slice(0, 10), type: 'in', pic: 'Tika', qty: 1, desc: '' });
const trxErrors = ref({});
function openTrx(row, type) {
    trxRow.value = row;
    trxForm.value = { date: new Date().toISOString().slice(0, 10), type, pic: 'Tika', qty: 1, desc: '' };
    trxErrors.value = {};
    trxOpen.value = true;
}
async function saveTrx() {
    trxErrors.value = {};
    try {
        await api.post('/atk-trx', { atk_id: trxRow.value.id, ...trxForm.value }, { block: true });
        toast.success('Transaksi disimpan.');
        trxOpen.value = false;
        query.fetch();
    } catch (e) {
        if (e.response?.status === 422) {
            trxErrors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan transaksi.');
    }
}

// Detail (kartu stok + saldo running)
const detailOpen = ref(false);
const detailRow = ref(null);
const detailRows = ref([]);
const detailLoading = ref(false);
const detailColumns = [
    { key: 'date', label: 'Tgl', mono: true },
    { key: 'pic', label: 'PIC' },
    { key: 'in', label: 'IN', align: 'right', mono: true },
    { key: 'out', label: 'OUT', align: 'right', mono: true },
    { key: 'saldo', label: 'Saldo', align: 'right', mono: true },
    { key: 'desc', label: 'Keterangan', wrap: true, maxWidth: '200px' },
];
const detailWithSaldo = computed(() => {
    let saldo = 0;
    return detailRows.value.map((t) => {
        const qty = Number(t.qty ?? 0);
        saldo += t.type === 'in' ? qty : -qty;
        return { ...t, saldo };
    });
});
async function openDetail(row) {
    detailRow.value = row;
    detailOpen.value = true;
    detailLoading.value = true;
    try {
        const res = await api.get('/atk-trx', { params: { atk_id: row.id }, silent: true, block: true });
        detailRows.value = res.data?.data ?? res.data ?? [];
    } catch (e) {
        detailRows.value = [];
        toast.error(e.response?.data?.message ?? 'Gagal memuat kartu stok.');
    } finally {
        detailLoading.value = false;
    }
}
async function deleteTrx(t) {
    const ok = await confirm({ title: 'Hapus transaksi?', message: 'Saldo akan dihitung ulang.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/atk-trx/${t.id}`, { block: true });
        toast.success('Transaksi dihapus.');
        openDetail(detailRow.value);
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus transaksi.');
    }
}
function printCard(row) {
    window.open(`/atk-eksport/${row.id}`, '_blank');
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(exportRows, ['code', 'name', 'satuan', 'stok'], 'atk')"><Copy /> Salin</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
                <Button variant="outline" size="sm" @click="router.visit('/atk-import')">Import</Button>
                <Button size="sm" @click="openAdd"><Plus /> Add</Button>
            </template>
        </PageHeader>
        <div class="space-y-4">
            <FilterPanel title="Filter ATK" :active-count="activeCount" @reset="resetFilter">
                <FormField label="Pencarian">
                    <Input :model-value="searchBox" type="search" placeholder="Cari kode, nama, satuan..." @input="onSearchInput" />
                </FormField>
                <FormField label="Satuan">
                    <SearchableSelect :model-value="satuanBox" :options="satuanOptions" placeholder="Semua Satuan" @update:model-value="onSatuan" />
                </FormField>
            </FilterPanel>
            <DataTable
                :columns="columns"
                :rows="query.rows.value"
                :loading="query.loading.value"
                :error="query.error.value"
                :page="query.page.value"
                :total-pages="query.totalPages.value"
                :total="query.total.value"
                :per-page="query.perPage.value"
                :per-page-options="perPageOptions"
                :selected="selected"
                selectable
                clickable
                empty-title="ATK tidak ditemukan"
                empty-message="Ubah kata kunci pencarian."
                @update:page="query.setPage"
                @update:per-page="query.setPerPage"
                @update:selected="selected = $event"
                @row-click="openDetail"
            >
                <template #cell-code="{ row }"><b>{{ row.code }}</b></template>
                <template #cell-name="{ row }">{{ row.name }}</template>
                <template #cell-satuan="{ row }">{{ row.satuan }}</template>
                <template #cell-stok="{ row }"><b>{{ row.stok ?? 0 }}</b></template>
                <template #actions="{ row }">
                    <div class="flex justify-end gap-1" @click.stop>
                        <Button variant="outline" size="sm" title="Stok masuk" @click="openTrx(row, 'in')"><ArrowDownToLine /></Button>
                        <Button variant="outline" size="sm" title="Stok keluar" @click="openTrx(row, 'out')"><ArrowUpFromLine /></Button>
                        <Button variant="ghost" size="sm" title="Kartu stok" @click="openDetail(row)"><Eye /></Button>
                        <Button variant="ghost" size="sm" title="Edit" @click="openEdit(row)"><Pencil /></Button>
                        <Button variant="ghost" size="sm" title="Hapus" @click="deleteRow(row)"><Trash2 /></Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <AppModal v-model:open="formOpen" :title="isEdit ? 'Edit ATK' : 'Tambah ATK'" size="md">
            <div class="grid gap-3 sm:grid-cols-2">
                <FormField label="Kode" required :error="errors.code?.[0]">
                    <Input v-model="form.code" placeholder="Kode barang..." />
                </FormField>
                <FormField label="Satuan" required :error="errors.satuan?.[0]">
                    <SearchableSelect v-model="form.satuan" :options="satuanOptions" placeholder="Pilih satuan..." />
                </FormField>
                <FormField label="Nama" required :error="errors.name?.[0]" class="sm:col-span-2">
                    <Input v-model="form.name" placeholder="Nama barang..." />
                </FormField>
                <FormField label="Deskripsi" :error="errors.desc?.[0]" class="sm:col-span-2">
                    <Input v-model="form.desc" placeholder="Keterangan (opsional)..." />
                </FormField>
            </div>
            <template #footer>
                <Button variant="ghost" @click="formOpen = false">Batal</Button>
                <Button @click="saveForm"><Save /> Simpan</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="trxOpen" :title="`${trxForm.type === 'in' ? 'Stok Masuk' : 'Stok Keluar'}: ${trxRow?.code ?? ''}`" size="md">
            <div class="grid gap-3 sm:grid-cols-2">
                <FormField label="Tanggal" required :error="trxErrors.date?.[0]">
                    <Input v-model="trxForm.date" type="date" />
                </FormField>
                <FormField label="PIC" required :error="trxErrors.pic?.[0]">
                    <Input v-model="trxForm.pic" />
                </FormField>
                <FormField label="Qty" required :error="trxErrors.qty?.[0]">
                    <Input v-model.number="trxForm.qty" type="number" min="1" />
                </FormField>
                <FormField label="Keterangan" :error="trxErrors.desc?.[0]">
                    <Input v-model="trxForm.desc" placeholder="Opsional..." />
                </FormField>
            </div>
            <template #footer>
                <Button variant="ghost" @click="trxOpen = false">Batal</Button>
                <Button @click="saveTrx"><Save /> Simpan</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="detailOpen" :title="`Kartu Stok: ${detailRow?.code ?? ''} - ${detailRow?.name ?? ''}`" size="lg">
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 5" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <DataTable
                v-if="!detailLoading && detailWithSaldo.length > 0"
                :columns="detailColumns"
                :rows="detailWithSaldo"
                :loading="false"
                :page="1"
                :total-pages="1"
                :total="detailWithSaldo.length"
                :per-page="detailWithSaldo.length"
                :show-footer="false"
                empty-title="Belum ada transaksi"
            >
                <template #cell-date="{ row }">{{ row.date }}</template>
                <template #cell-pic="{ row }">{{ row.pic }}</template>
                <template #cell-in="{ row }">{{ row.type === 'in' ? row.qty : '' }}</template>
                <template #cell-out="{ row }">{{ row.type === 'out' ? row.qty : '' }}</template>
                <template #cell-saldo="{ row }"><b>{{ row.saldo }}</b></template>
                <template #cell-desc="{ row }">{{ row.desc || '-' }}</template>
                <template #actions="{ row }">
                    <Button variant="ghost" size="sm" @click="deleteTrx(row)"><Trash2 /></Button>
                </template>
            </DataTable>
            <p v-else-if="!detailLoading" class="py-8 text-center text-sm text-muted-foreground">Belum ada transaksi.</p>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
                <Button variant="outline" @click="printCard(detailRow)">Cetak Kartu</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
