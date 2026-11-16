<script setup>
import { computed, ref } from 'vue';
import { FileDown } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';

const props = defineProps({
    title: { type: String, default: 'Form Ruangan' },
    gudangOptions: { type: Array, default: () => [] },
    lokasiCount: { type: Object, default: () => ({}) },
    defaults: { type: Object, default: () => ({}) },
});

const MONTHS = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const bulanOptions = MONTHS.map((label, idx) => ({ value: idx + 1, label }));
const currentYear = Number(props.defaults?.tahun) || new Date().getFullYear();
const tahunOptions = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1, currentYear + 2]
    .map((y) => ({ value: y, label: String(y) }));

const gudang = ref('gudang_a');
const bulan = ref(Number(props.defaults?.bulan) || new Date().getMonth() + 1);
const tahun = ref(currentYear);

function workdayCount(month, year) {
    const total = new Date(year, month, 0).getDate();
    let count = 0;
    for (let day = 1; day <= total; day++) {
        const dow = new Date(year, month - 1, day).getDay();
        if (dow !== 0 && dow !== 6) {
            count += 1;
        }
    }
    return count;
}

const pageCount = computed(() => Number(props.lokasiCount?.[gudang.value]) || 0);
const rowCount = computed(() => workdayCount(Number(bulan.value), Number(tahun.value)));
const monthLabel = computed(() => MONTHS[Number(bulan.value) - 1] || '');

function onDownload() {
    const params = new URLSearchParams({
        gudang: gudang.value,
        bulan: String(bulan.value),
        tahun: String(tahun.value),
    });
    window.open(`/tools/suhu/download?${params.toString()}`, '_blank');
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" />

        <div class="space-y-4">
            <Card class="p-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <FormField label="Gudang" required>
                        <SearchableSelect v-model="gudang" :options="props.gudangOptions" placeholder="Pilih gudang..." :clearable="false" />
                    </FormField>
                    <FormField label="Bulan" required>
                        <SearchableSelect v-model="bulan" :options="bulanOptions" placeholder="Pilih bulan..." :clearable="false" />
                    </FormField>
                    <FormField label="Tahun" required>
                        <SearchableSelect v-model="tahun" :options="tahunOptions" placeholder="Pilih tahun..." :clearable="false" />
                    </FormField>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-md bg-primary/10 px-3 py-2">
                    <span class="text-sm text-muted-foreground">
                        {{ pageCount }} halaman (satu per lokasi) x 26 baris
                        ({{ rowCount }} terisi tanggal kerja
                        <span class="font-medium text-foreground">{{ monthLabel }} {{ tahun }}</span>,
                        sisanya kosong, Sabtu-Minggu dilewati)
                    </span>
                    <Button size="sm" @click="onDownload"><FileDown /> Unduh Pencatatan Suhu</Button>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
