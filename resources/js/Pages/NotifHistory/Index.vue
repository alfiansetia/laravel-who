<script setup>
import { computed, ref } from 'vue';
import { Eye, RefreshCw, Send, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import CopyButton from '@/components/CopyButton.vue';
import DataTable from '@/components/DataTable.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';

const props = defineProps({
    title: { type: String, default: 'History Notifikasi DO' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();

const query = useTableQuery((p) => api.get('/notif-histories', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    filters: {
        status: props.filters.status ?? '',
        type: props.filters.type ?? '',
        date_from: props.filters.date_from ?? '',
        date_to: props.filters.date_to ?? '',
    },
});

const searchBox = ref(props.filters.search ?? '');
const statusBox = ref(props.filters.status ?? '');
const typeBox = ref(props.filters.type ?? '');
const dateFromBox = ref(props.filters.date_from ?? '');
const dateToBox = ref(props.filters.date_to ?? '');

const detailOpen = ref(false);
const detailRow = ref(null);
const resendingId = ref(null);
const deletingId = ref(null);
const selected = ref([]);

const statusOptions = [
    { label: 'Semua Status', value: '' },
    { label: 'Success', value: 'success' },
    { label: 'Failed', value: 'failed' },
];

const typeOptions = [
    { label: 'Semua Tipe', value: '' },
    { label: 'DO Baru', value: 'do_baru' },
    { label: 'DO Bulk', value: 'do_bulk' },
    { label: 'Test', value: 'test' },
];

const activeCount = computed(() => [searchBox.value, statusBox.value, typeBox.value, dateFromBox.value, dateToBox.value].filter(Boolean).length);

const columns = [
    { key: 'sent_at', label: 'Waktu Kirim', mono: true },
    { key: 'type', label: 'Tipe', align: 'center' },
    { key: 'title', label: 'Judul / DO' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'source', label: 'Sumber', align: 'center' },
];

function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}

function onStatusChange(value) {
    statusBox.value = value ?? '';
    query.setFilters({ status: statusBox.value });
}

function onTypeChange(value) {
    typeBox.value = value ?? '';
    query.setFilters({ type: typeBox.value });
}

function onDateFromInput(e) {
    dateFromBox.value = e.target.value;
    query.setFilters({ date_from: dateFromBox.value }, 500);
}

function onDateToInput(e) {
    dateToBox.value = e.target.value;
    query.setFilters({ date_to: dateToBox.value }, 500);
}

function resetFilter() {
    searchBox.value = '';
    statusBox.value = '';
    typeBox.value = '';
    dateFromBox.value = '';
    dateToBox.value = '';
    query.setSearch('', 0);
    query.setFilters({ status: '', type: '', date_from: '', date_to: '' });
}

async function deleteRow(row) {
    const ok = await confirm({
        title: 'Hapus history ini?',
        message: 'Catatan notifikasi yang dihapus tidak dapat dikembalikan.',
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    deletingId.value = row.id;
    try {
        await api.delete(`/notif-histories/${row.id}`, { block: true });
        toast.success('History dihapus.');
        if (detailOpen.value && detailRow.value?.id === row.id) {
            detailOpen.value = false;
        }
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus history.');
    } finally {
        deletingId.value = null;
    }
}

async function deleteBatch() {
    if (selected.value.length === 0) {
        toast.warning('Pilih minimal 1 history untuk dihapus.');
        return;
    }
    const ok = await confirm({
        title: `Hapus ${selected.value.length} history?`,
        message: 'Catatan notifikasi yang dihapus tidak dapat dikembalikan.',
        confirmText: 'Ya, hapus',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    try {
        await api.delete('/notif-histories', { data: { ids: selected.value }, block: true });
        toast.success('History dihapus.');
        selected.value = [];
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus history.');
    }
}

function formatDateTime(value) {
    if (!value) {
        return '-';
    }
    const d = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) {
        return String(value);
    }
    return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function openDetail(row) {
    detailRow.value = row;
    detailOpen.value = true;
}

async function resend(row) {
    const ok = await confirm({
        title: 'Blast ulang notifikasi?',
        message: `Kirim ulang "${(row.title ?? '').slice(0, 60)}" via Firebase ke semua perangkat?`,
        confirmText: 'Ya, kirim ulang',
    });
    if (!ok) {
        return;
    }
    resendingId.value = row.id;
    try {
        const res = await api.post(`/notif-histories/${row.id}/resend`, {}, { block: true });
        toast.success(res.data?.message ?? 'Notifikasi berhasil di-blast ulang.');
        query.fetch();
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal blast ulang notifikasi.');
    } finally {
        resendingId.value = null;
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="deleteBatch"><Trash2 /> Hapus Terpilih{{ selected.length ? ` (${selected.length})` : '' }}</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter History Notifikasi" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari judul, DO, SO, partner..." @input="onSearchInput" />
            </FormField>
            <FormField label="Status">
                <SearchableSelect :model-value="statusBox" :options="statusOptions" placeholder="Semua Status" @update:model-value="onStatusChange" />
            </FormField>
            <FormField label="Tipe">
                <SearchableSelect :model-value="typeBox" :options="typeOptions" placeholder="Semua Tipe" @update:model-value="onTypeChange" />
            </FormField>
            <FormField label="Dari Tanggal">
                <Input :model-value="dateFromBox" type="date" @input="onDateFromInput" />
            </FormField>
            <FormField label="Sampai Tanggal">
                <Input :model-value="dateToBox" type="date" @input="onDateToInput" />
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
            clickable
            :selected="selected"
            selectable
            empty-title="History tidak ditemukan"
            empty-message="Belum ada notifikasi tercatat atau ubah filter tanggal."
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @update:selected="selected = $event"
            @row-click="openDetail"
        >
            <template #cell-sent_at="{ row }">{{ formatDateTime(row.sent_at) }}</template>
            <template #cell-type="{ row }">{{ row.type ?? '-' }}</template>
            <template #cell-title="{ row }">
                <b>{{ row.title ?? '-' }}</b>
                <span v-if="row.do_name" class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-slate-700">{{ row.do_name }}</span>
                <p class="truncate text-xs text-muted-foreground">{{ row.body }}</p>
            </template>
            <template #cell-status="{ row }"><StatusBadge :status="row.status" /></template>
            <template #cell-source="{ row }">
                <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ row.source }}{{ row.parent_id ? ` #${row.parent_id}` : '' }}</span>
            </template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1" @click.stop>
                    <Button variant="outline" size="sm" title="Detail" @click.stop="openDetail(row)"><Eye /></Button>
                    <Button variant="outline" size="sm" title="Blast ulang via Firebase" :disabled="resendingId === row.id" @click.stop="resend(row)"><Send /></Button>
                    <Button variant="outline" size="sm" title="Hapus history" :disabled="deletingId === row.id" @click.stop="deleteRow(row)"><Trash2 /></Button>
                </div>
            </template>
        </DataTable>

        <AppModal v-model:open="detailOpen" title="Detail Notifikasi" size="lg" persistent>
            <div v-if="detailRow" class="space-y-2 text-sm">
                <p><b>Waktu kirim:</b> {{ formatDateTime(detailRow.sent_at) }}</p>
                <p><b>Tipe:</b> {{ detailRow.type }} • <b>Status:</b> {{ detailRow.status }} • <b>Sumber:</b> {{ detailRow.source }}{{ detailRow.triggered_by ? ` oleh ${detailRow.triggered_by}` : '' }}</p>
                <p><b>Judul:</b> {{ detailRow.title }} <CopyButton :text="detailRow.title ?? ''" label="Judul" /></p>
                <p class="whitespace-pre-wrap"><b>Isi:</b> {{ detailRow.body }} <CopyButton :text="detailRow.body ?? ''" label="Isi" /></p>
                <p><b>DO:</b> {{ detailRow.do_name ?? '-' }} • <b>SO:</b> {{ detailRow.origin ?? '-' }} • <b>SO ID:</b> {{ detailRow.so_id ?? '-' }}</p>
                <p><b>Partner:</b> {{ detailRow.partner ?? '-' }} • <b>Topic:</b> {{ detailRow.topic }}</p>
                <details v-if="detailRow.payload" class="rounded-lg border">
                    <summary class="cursor-pointer px-3 py-2 text-sm font-medium">Payload mentah</summary>
                    <pre class="max-h-[40vh] overflow-y-auto whitespace-pre-wrap border-t bg-slate-900 p-3 font-mono text-xs text-slate-100"><code>{{ JSON.stringify(detailRow.payload, null, 2) }}</code></pre>
                </details>
            </div>
            <template #footer>
                <Button v-if="detailRow" variant="destructive" size="sm" :disabled="deletingId === detailRow.id" @click="deleteRow(detailRow)"><Trash2 /> Hapus</Button>
                <Button v-if="detailRow" variant="outline" :disabled="resendingId === detailRow.id" @click="resend(detailRow)"><Send /> Blast Ulang</Button>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <ConfirmDialog />
    </AppLayout>
</template>
