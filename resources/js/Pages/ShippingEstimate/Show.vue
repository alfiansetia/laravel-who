<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import Button from '@/components/ui/Button.vue';
import EstimateSummary from './partials/EstimateSummary.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { calcEstimate, fmtID } from '@/lib/shipping';

const props = defineProps({
    title: { type: String, default: 'Detail Shipping Estimate' },
    recordId: { type: [Number, String], required: true },
});

const toast = useToast();
const estimate = ref(null);
const loading = ref(true);

const itemColumns = [
    { key: 'no', label: 'No', align: 'center' },
    { key: 'item_name', label: 'Nama Item' },
    { key: 'quantity', label: 'Qty', align: 'center' },
    { key: 'total_price', label: 'Total', align: 'right' },
];
const packageColumns = [
    { key: 'no', label: 'No', align: 'center' },
    { key: 'package_name', label: 'Nama Koli' },
    { key: 'weight_actual', label: 'Berat (kg)', align: 'right' },
    { key: 'dimension', label: 'P×L×T' },
    { key: 'dim_reg', label: 'Dim REG', align: 'right' },
    { key: 'dim_darat', label: 'Dim DARAT', align: 'right' },
];
const rateColumns = [
    { key: 'shipper_name', label: 'Shipper' },
    { key: 'shipping_type', label: 'Tipe', align: 'center' },
    { key: 'charged_weight', label: 'Berat Dipakai', align: 'right' },
    { key: 'shipping_cost', label: 'Ongkir', align: 'right' },
    { key: 'insurance_cost', label: 'Asuransi', align: 'right' },
    { key: 'total_cost', label: 'Total', align: 'right' },
    { key: 'estimated_days', label: 'Est. Hari', align: 'center' },
];

const items = computed(() => (estimate.value?.items ?? []).map((r, i) => ({ ...r, no: i + 1 })));
const packages = computed(() => (estimate.value?.packages ?? []).map((r, i) => ({ ...r, no: i + 1 })));
const summary = computed(() => calcEstimate(estimate.value?.items ?? [], estimate.value?.packages ?? [], estimate.value?.rates ?? []));
const rateRows = computed(() =>
    summary.value.rateRows.map((r) => ({
        ...r,
        charged_weight: fmtID(r.charged_weight, 2),
        shipping_cost: fmtID(r.shipping_cost),
        insurance_cost: fmtID(r.insurance_cost),
        total_cost: fmtID(r.total_cost),
    })),
);

function dimText(r) {
    return `${r.dimension_length ?? 0}×${r.dimension_width ?? 0}×${r.dimension_height ?? 0}`;
}

function goBack() {
    router.visit('/shipping-estimate');
}

function goEdit() {
    router.visit(`/shipping-estimate/${props.recordId}/edit`);
}

async function load() {
    loading.value = true;
    try {
        const res = await api.get(`/shipping-estimate/${props.recordId}`, { silent: true });
        estimate.value = res.data?.data?.estimate ?? res.data?.estimate ?? null;
    } catch (e) {
        estimate.value = null;
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail.');
    } finally {
        loading.value = false;
    }
}

load();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" :description="estimate ? `${estimate.no_so} · ${estimate.customer_name}` : ''">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack"><ArrowLeft /> Kembali</Button>
                <Button size="sm" @click="goEdit"><Pencil /> Edit</Button>
            </template>
        </PageHeader>
        <div v-if="loading" class="space-y-2">
            <div v-for="n in 6" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
        </div>
        <div v-else-if="!estimate" class="rounded-lg border bg-white p-8 text-center text-sm text-slate-500">Data tidak ditemukan.</div>
        <div v-else class="space-y-4">
            <div class="rounded-lg border bg-white p-4">
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">No SO</dt><dd class="font-semibold">{{ estimate.no_so }}</dd></div>
                    <div><dt class="text-slate-500">Customer</dt><dd class="font-semibold">{{ estimate.customer_name }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">Alamat</dt><dd class="whitespace-pre-wrap">{{ estimate.shipping_address }}</dd></div>
                </dl>
            </div>
            <div class="rounded-lg border bg-white p-4">
                <p class="mb-2 text-sm font-semibold">Items ({{ items.length }})</p>
                <DataTable :columns="itemColumns" :rows="items" :loading="false" :page="1" :total-pages="1" :total="items.length" :per-page="items.length || 1" :show-footer="false" empty-title="Belum ada item">
                    <template #cell-no="{ row }">{{ row.no }}</template>
                    <template #cell-item_name="{ row }">{{ row.item_name }}</template>
                    <template #cell-quantity="{ row }">{{ row.quantity }}</template>
                    <template #cell-total_price="{ row }">Rp {{ fmtID(row.total_price) }}</template>
                </DataTable>
            </div>
            <div class="rounded-lg border bg-white p-4">
                <p class="mb-2 text-sm font-semibold">Packages ({{ packages.length }})</p>
                <DataTable :columns="packageColumns" :rows="packages" :loading="false" :page="1" :total-pages="1" :total="packages.length" :per-page="packages.length || 1" :show-footer="false" empty-title="Belum ada koli">
                    <template #cell-no="{ row }">{{ row.no }}</template>
                    <template #cell-package_name="{ row }">{{ row.package_name }}</template>
                    <template #cell-weight_actual="{ row }">{{ fmtID(row.weight_actual, 2) }}</template>
                    <template #cell-dimension="{ row }">{{ dimText(row) }}</template>
                    <template #cell-dim_reg="{ row }">{{ fmtID(row.weight_dimension_reg, 2) }}</template>
                    <template #cell-dim_darat="{ row }">{{ fmtID(row.weight_dimension_darat, 2) }}</template>
                </DataTable>
            </div>
            <div class="rounded-lg border bg-white p-4">
                <p class="mb-2 text-sm font-semibold">Rates</p>
                <DataTable :columns="rateColumns" :rows="rateRows" :loading="false" :page="1" :total-pages="1" :total="rateRows.length" :per-page="rateRows.length || 1" :show-footer="false" empty-title="Belum ada rate">
                    <template #cell-shipper_name="{ row }">{{ row.shipper_name }}</template>
                    <template #cell-shipping_type="{ row }">{{ row.shipping_type }}</template>
                    <template #cell-charged_weight="{ row }">{{ row.charged_weight }} kg</template>
                    <template #cell-shipping_cost="{ row }">Rp {{ row.shipping_cost }}</template>
                    <template #cell-insurance_cost="{ row }">Rp {{ row.insurance_cost }}</template>
                    <template #cell-total_cost="{ row }">Rp {{ row.total_cost }}</template>
                    <template #cell-estimated_days="{ row }">{{ row.estimated_days ?? '-' }}</template>
                </DataTable>
            </div>
            <EstimateSummary :summary="summary" />
        </div>
    </AppLayout>
</template>
