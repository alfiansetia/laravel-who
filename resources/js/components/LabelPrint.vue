<script setup>
import { computed, ref } from 'vue';
import { Printer } from '@lucide/vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { countLabelTotal, printProductLabels } from '@/lib/labelPrint';

// Tombol cetak label tempelan: kode besar di atas, nama kecil di bawah,
// satu label per pcs sesuai qty. Kolom selembar bisa diatur (makin sedikit
// kolom, makin besar labelnya).
// Pemakaian: <LabelPrint :items="labelItems" title="Label PO X" />
// items = [{ code, name, qty }]
const props = defineProps({
    items: { type: Array, default: () => [] },
    title: { type: String, default: 'Label Produk' },
});

const cols = ref(5);
const fillRows = ref(false);
const codeOnly = ref(false);
const fontSel = ref('0');

const fontOptions = [
    { label: 'Auto', value: '0' },
    { label: 'Kecil', value: '14' },
    { label: 'Sedang', value: '18' },
    { label: 'Besar', value: '24' },
];

const total = computed(() => countLabelTotal(props.items, cols.value, fillRows.value));

function onPrint() {
    printProductLabels({ items: props.items, cols: cols.value, title: props.title, fillRows: fillRows.value, codeOnly: codeOnly.value, fontPt: Number(fontSel.value) });
}
</script>

<template>
    <div class="flex items-center gap-1.5">
        <label class="text-xs text-muted-foreground" title="Jumlah kolom label selembar">Kolom</label>
        <Input v-model="cols" type="number" min="1" max="10" class="h-8 w-16 px-2 text-xs" />
        <label class="flex cursor-pointer items-center gap-1 text-xs text-muted-foreground" title="Kunci ukuran font kode (Auto = mengikuti jumlah kolom)">
            Font
            <select v-model="fontSel" class="h-8 rounded-md border border-input bg-background px-1 text-xs shadow-sm">
                <option v-for="o in fontOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
            </select>
        </label>
        <label class="flex cursor-pointer items-center gap-1 text-xs text-muted-foreground" title="Genapkan tiap item menjadi baris penuh (kekurangan sel diisi label yang sama)">
            <input v-model="fillRows" type="checkbox" class="size-3.5 accent-primary" />
            Genapkan
        </label>
        <label class="flex cursor-pointer items-center gap-1 text-xs text-muted-foreground" title="Cetak kode barang saja tanpa nama barang">
            <input v-model="codeOnly" type="checkbox" class="size-3.5 accent-primary" />
            Kode saja
        </label>
        <Button
            variant="outline"
            size="sm"
            :disabled="total === 0"
            title="Cetak label tempelan (kode di atas, nama di bawah, sesuai qty). Centang baris tabel bila hanya sebagian item yang mau dicetak."
            @click="onPrint"
        >
            <Printer /> Label ({{ total }})
        </Button>
    </div>
</template>
