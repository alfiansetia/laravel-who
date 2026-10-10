<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Check, Clock, Copy, Pencil, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/web';

const props = defineProps({
    title: { type: String, default: 'Detail Problem' },
    record: { type: Object, default: null },
    products: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();

const productOptions = computed(() => props.products.map((p) => ({ value: p.id, label: `[${p.code}] ${p.name}` })));
function itemLabel(it) {
    return productOptions.value.find((o) => String(o.value) === String(it.product_id))?.label ?? it.product_id;
}

const itemColumns = [
    { key: 'product', label: 'Product' },
    { key: 'qty', label: 'Qty', align: 'right', mono: true },
    { key: 'lot', label: 'Lot', mono: true },
    { key: 'desc', label: 'Desc', wrap: true, maxWidth: '260px' },
];

function goList() {
    router.visit('/problems');
}
function goEdit() {
    router.visit(`/problems/${props.record.id}/edit`);
}

async function setStatus(status) {
    try {
        await api.post(`/problem/${props.record.id}/status`, { status }, { block: true });
        toast.success(`Status diubah ke ${status}.`);
        router.reload({ only: ['record'] });
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal ubah status.');
    }
}

async function duplicateRow() {
    const ok = await confirm({ title: 'Duplikasi problem?', message: 'Buat salinan dengan nomor baru.', confirmText: 'Ya, duplikasi' });
    if (!ok) {
        return;
    }
    try {
        const res = await api.post(`/problem/${props.record.id}/duplicate`, {}, { block: true });
        const id = res.data?.data?.id ?? res.data?.id;
        toast.success('Problem diduplikasi.');
        if (id) {
            window.open(`/problems/${id}/edit`, '_blank');
        }
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal duplikasi.');
    }
}

async function deleteRow() {
    const ok = await confirm({ title: 'Hapus problem?', message: 'Data yang dihapus tidak dapat dikembalikan.', confirmText: 'Ya, hapus', tone: 'destructive' });
    if (!ok) {
        return;
    }
    try {
        await api.delete(`/problem/${props.record.id}`, { block: true });
        toast.success('Problem dihapus.');
        router.visit('/problems');
    } catch (e) {
        toast.error(e.response?.data?.message ?? 'Gagal menghapus.');
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="goList"><ArrowLeft /> Kembali</Button>
                <Button variant="outline" size="sm" @click="goEdit"><Pencil /> Edit</Button>
                <Button v-if="record?.status === 'pending'" variant="outline" size="sm" @click="setStatus('done')"><Check /> Selesai</Button>
                <Button v-else variant="outline" size="sm" @click="setStatus('pending')"><Clock /> Pending</Button>
                <Button variant="outline" size="sm" @click="duplicateRow"><Copy /> Duplikasi</Button>
                <Button variant="ghost" size="sm" @click="deleteRow"><Trash2 /> Hapus</Button>
            </template>
        </PageHeader>

        <div class="space-y-4">
            <Card class="grid gap-2 p-4 text-sm sm:grid-cols-3">
                <p><b>Nomor:</b> {{ record?.number ?? '-' }}</p>
                <p><b>Tanggal:</b> {{ record?.date ?? '-' }}</p>
                <p><b>PIC:</b> {{ record?.pic ?? '-' }}</p>
                <p><b>RI/PO:</b> {{ record?.ri_po ?? '-' }}</p>
                <p><b>Tipe:</b> {{ record?.type ?? '-' }}</p>
                <p><b>Stock:</b> {{ record?.stock ?? '-' }}</p>
                <p><b>Email On:</b> {{ record?.email_on ?? '-' }}</p>
                <p><b>Status:</b> <StatusBadge :status="record?.status ?? '-'" /></p>
            </Card>

            <Card class="p-4">
                <p class="mb-2 text-sm font-semibold">Product Bermasalah ({{ record?.items?.length ?? 0 }})</p>
                <DataTable
                    v-if="record?.items?.length"
                    :columns="itemColumns"
                    :rows="record.items"
                    :loading="false"
                    :page="1"
                    :total-pages="1"
                    :total="record.items.length"
                    :per-page="record.items.length"
                    :show-footer="false"
                    empty-title="Tidak ada item"
                >
                    <template #cell-product="{ row }">{{ itemLabel(row) }}</template>
                    <template #cell-qty="{ row }">{{ row.qty }}</template>
                    <template #cell-lot="{ row }">{{ row.lot || '-' }}</template>
                    <template #cell-desc="{ row }">{{ row.desc || '-' }}</template>
                </DataTable>
                <p v-else class="text-sm text-slate-500">Tidak ada item.</p>
            </Card>

            <Card class="p-4">
                <p class="mb-2 text-sm font-semibold">Log Aktivitas ({{ record?.logs?.length ?? 0 }})</p>
                <div v-if="record?.logs?.length" class="space-y-1 text-sm">
                    <div v-for="l in record.logs" :key="l.id" class="flex items-center gap-2">
                        <span class="font-mono text-xs">{{ l.date }}</span>
                        <span class="flex-1">{{ l.desc }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">Belum ada log.</p>
            </Card>
        </div>
    </AppLayout>
</template>
