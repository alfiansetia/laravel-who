<script setup>
import { computed, ref } from 'vue';
import { Bug, FileDown, Refrigerator, Thermometer } from '@lucide/vue';
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

function onDownloadSuhu() {
    const params = new URLSearchParams({
        gudang: gudang.value,
        bulan: String(bulan.value),
        tahun: String(tahun.value),
    });
    window.open(`/tools/suhu/download?${params.toString()}`, '_blank');
}

function onDownloadRefrigerator() {
    const params = new URLSearchParams({
        bulan: String(bulan.value),
        tahun: String(tahun.value),
    });
    window.open(`/tools/suhu-refrigrator/download?${params.toString()}`, '_blank');
}

function onDownloadHama() {
    const params = new URLSearchParams({
        gudang: gudang.value,
        bulan: String(bulan.value),
        tahun: String(tahun.value),
    });
    window.open(`/tools/suhu-hama/download?${params.toString()}`, '_blank');
}

// Tambah modul baru cukup tambah entri di sini (key, title, desc, icon, pages, onDownload).
const documents = computed(() => [
    {
        key: 'suhu',
        title: 'Pencatatan Suhu',
        desc: 'Satu halaman per lokasi gudang yang dipilih.',
        icon: Thermometer,
        pages: `${pageCount.value} halaman (satu per lokasi)`,
        onDownload: onDownloadSuhu,
    },
    {
        key: 'refrigrator',
        title: 'Suhu Refrigerator',
        desc: 'Satu halaman, tanpa gudang.',
        icon: Refrigerator,
        pages: '1 halaman',
        onDownload: onDownloadRefrigerator,
    },
    {
        key: 'hama',
        title: 'Pengendalian Hama',
        desc: 'Satu halaman per lokasi gudang yang dipilih, tanpa tanggal.',
        icon: Bug,
        pages: `${pageCount.value} halaman (satu per lokasi)`,
        onDownload: onDownloadHama,
    },
]);
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

                <div class="mt-4 rounded-md bg-primary/10 px-3 py-2 text-sm text-muted-foreground">
                    {{ rowCount }} baris terisi tanggal kerja
                    <span class="font-medium text-foreground">{{ monthLabel }} {{ tahun }}</span>,
                    sisanya kosong, Sabtu-Minggu dilewati.
                </div>
            </Card>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card v-for="doc in documents" :key="doc.key" class="flex flex-col gap-3 p-4">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-primary/10 p-2 text-primary"><component :is="doc.icon" class="size-5" /></span>
                        <div>
                            <h3 class="font-semibold leading-tight">{{ doc.title }}</h3>
                            <p class="text-xs text-muted-foreground">{{ doc.desc }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ doc.pages }} x 26 baris</p>
                    <Button size="sm" class="w-fit" @click="doc.onDownload">
                        <FileDown /> Unduh {{ doc.title }}
                    </Button>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
