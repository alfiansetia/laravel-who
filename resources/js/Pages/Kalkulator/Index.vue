<script setup>
import { computed, ref } from 'vue';
import { Copy, Plus, Trash2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';

const props = defineProps({
    title: { type: String, default: 'Kalkulator Nilai' },
});

let seq = 0;

function newRow(overrides = {}) {
    seq += 1;
    return {
        id: seq,
        totalNilai: '0',
        jumlahTotal: 1,
        jumlahKirim: 1,
        persenPajak: 11,
        samakan: true,
        hitung: true,
        ...overrides,
    };
}

const rows = ref([newRow()]);

function parseNum(value, fallback = 0) {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : fallback;
    }
    const cleaned = String(value ?? '').replace(/[^0-9.-]/g, '');
    const n = parseFloat(cleaned);
    return Number.isFinite(n) ? n : fallback;
}

function formatRupiah(angka) {
    return `Rp ${Math.round(angka).toLocaleString('id-ID', { maximumFractionDigits: 0 })}`;
}

function formatGrouped(n) {
    return Math.round(n).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function rowTotal(row) {
    const totalNilai = parseNum(row.totalNilai);
    const jumlahTotal = parseNum(row.jumlahTotal, 1) || 1;
    const jumlahKirim = parseNum(row.jumlahKirim);
    const persenPajak = parseNum(row.persenPajak);
    return ((totalNilai / jumlahTotal) * jumlahKirim * (1 + persenPajak / 100)) || 0;
}

const grandTotal = computed(() => rows.value.filter((r) => r.hitung).reduce((sum, r) => sum + rowTotal(r), 0));

function onJumlahTotal(row) {
    if (row.samakan) {
        row.jumlahKirim = row.jumlahTotal;
    }
}

function onToggleSamakan(row) {
    if (row.samakan) {
        row.jumlahKirim = row.jumlahTotal;
    }
}

function onNilaiFocus(row) {
    const n = parseNum(row.totalNilai);
    row.totalNilai = n ? String(n) : '';
}

function onNilaiBlur(row) {
    row.totalNilai = formatGrouped(parseNum(row.totalNilai));
}

function addRow() {
    rows.value.push(newRow());
}

function duplicateRow(id) {
    const src = rows.value.find((r) => r.id === id);
    if (!src) {
        return;
    }
    rows.value.push(newRow({
        totalNilai: src.totalNilai,
        jumlahTotal: src.jumlahTotal,
        jumlahKirim: src.jumlahKirim,
        persenPajak: src.persenPajak,
        samakan: src.samakan,
        hitung: src.hitung,
    }));
}

function removeRow(id) {
    rows.value = rows.value.filter((r) => r.id !== id);
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title">
            <template #actions>
                <Button size="sm" @click="addRow"><Plus /> Tambah</Button>
            </template>
        </PageHeader>

        <div class="space-y-3">
            <Card
                v-for="(row, idx) in rows"
                :key="row.id"
                class="p-4 transition-colors hover:border-primary/50"
                :class="row.hitung ? '' : 'opacity-50'"
            >
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-primary px-2 py-0.5 text-xs font-bold text-primary-foreground">#{{ idx + 1 }}</span>
                        <label class="flex cursor-pointer items-center gap-1.5 text-sm font-medium">
                            <input v-model="row.hitung" type="checkbox" class="size-4 accent-primary" /> Hitung
                        </label>
                    </div>
                    <div class="flex gap-1">
                        <Button variant="outline" size="sm" title="Duplikat baris" @click="duplicateRow(row.id)"><Copy /></Button>
                        <Button variant="destructive" size="sm" title="Hapus baris" @click="removeRow(row.id)"><Trash2 /></Button>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <FormField label="Total Nilai (sebelum pajak)">
                        <Input v-model="row.totalNilai" type="text" inputmode="numeric" @focus="onNilaiFocus(row)" @blur="onNilaiBlur(row)" />
                    </FormField>
                    <FormField label="Jumlah Total">
                        <Input v-model="row.jumlahTotal" type="number" min="1" @input="onJumlahTotal(row)" />
                    </FormField>
                    <FormField label="Jumlah Kirim">
                        <div class="flex items-center gap-2">
                            <Input v-model="row.jumlahKirim" type="number" min="0" :disabled="row.samakan" class="flex-1" />
                            <label class="flex shrink-0 cursor-pointer items-center gap-1 text-xs text-muted-foreground" title="Samakan dengan jumlah total">
                                <input v-model="row.samakan" type="checkbox" class="size-4 accent-primary" @change="onToggleSamakan(row)" /> Sama
                            </label>
                        </div>
                    </FormField>
                    <FormField label="Pajak (%)">
                        <Input v-model="row.persenPajak" type="number" min="0" max="100" step="0.1" />
                    </FormField>
                    <div class="flex flex-col justify-end rounded-md bg-primary/10 px-3 py-2">
                        <span class="text-xs text-muted-foreground">Total:</span>
                        <strong class="text-base">{{ formatRupiah(rowTotal(row)) }}</strong>
                    </div>
                </div>
            </Card>

            <p v-if="rows.length === 0" class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                Belum ada baris. Klik Tambah untuk mulai menghitung.
            </p>

            <Card class="bg-primary p-4 text-primary-foreground">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-semibold">Total Keseluruhan</span>
                    <span class="text-2xl font-bold tabular-nums">{{ formatRupiah(grandTotal) }}</span>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
