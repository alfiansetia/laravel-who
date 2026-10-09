<script setup>
import { fmtID } from '@/lib/shipping';

const props = defineProps({
    summary: { type: Object, required: true },
});
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border bg-white p-4">
            <p class="mb-2 text-sm font-semibold">Ringkasan</p>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between gap-2"><dt class="text-slate-500">Total Faktur</dt><dd class="font-semibold">Rp {{ fmtID(summary.totalInvoice) }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-500">Berat Aktual</dt><dd class="font-semibold">{{ fmtID(summary.totalWeight, 2) }} kg</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-500">Dimensi REG</dt><dd class="font-semibold">{{ fmtID(summary.totalDimReg, 2) }} kg</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-500">Dimensi DARAT</dt><dd class="font-semibold">{{ fmtID(summary.totalDimDarat, 2) }} kg</dd></div>
            </dl>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <p class="mb-2 text-sm font-semibold">Ongkir per Shipper ({{ summary.rateRows.length }})</p>
            <div v-if="summary.rateRows.length === 0" class="text-sm text-slate-500">Belum ada rate.</div>
            <div v-else class="space-y-2">
                <div v-for="(r, i) in summary.rateRows" :key="i" class="rounded border p-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <b>{{ r.shipper_name || '-' }}</b>
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium">{{ r.shipping_type }}</span>
                    </div>
                    <dl class="mt-1 space-y-0.5 text-xs text-slate-600">
                        <div class="flex justify-between gap-2"><dt>Berat dipakai</dt><dd>{{ fmtID(r.charged_weight, 2) }} kg</dd></div>
                        <div class="flex justify-between gap-2"><dt>Ongkir</dt><dd>Rp {{ fmtID(r.shipping_cost) }}</dd></div>
                        <div class="flex justify-between gap-2"><dt>Asuransi</dt><dd>Rp {{ fmtID(r.insurance_cost) }}</dd></div>
                        <div class="flex justify-between gap-2"><dt>P. Kayu + Admin</dt><dd>Rp {{ fmtID((r.packing_cost ?? 0) + (r.admin_fee ?? 0)) }}</dd></div>
                        <div class="flex justify-between gap-2"><dt>PPN</dt><dd>Rp {{ fmtID(r.ppn_cost) }}</dd></div>
                        <div class="flex justify-between gap-2 border-t pt-0.5 font-semibold text-slate-900"><dt>Total</dt><dd>Rp {{ fmtID(r.total_cost) }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>
