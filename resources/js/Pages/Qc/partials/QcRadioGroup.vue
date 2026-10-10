<script setup>
/**
 * QcRadioGroup — pilihan Baik/Tidak/N-A (atau Ada/Tidak/N-A) satu baris pemeriksaan Form QC.
 * Tampil sebagai segmented pill ringkas (h-7): yang terpilih berwarna
 * (Baik/Ada hijau, Tidak merah, N/A abu), sisanya netral.
 * Input radio native disembunyikan (sr-only) tapi tetap fokusable,
 * jadi navigasi keyboard (Tab + panah) tetap jalan.
 *
 * Props:
 *   `modelValue: string` — nilai terpilih ('yes' | 'no' | 'other').
 *   `options: Array<{label, value}>` — daftar opsi (fisikRadios / kelRadios).
 *   `name: string` — nama grup radio (unik per baris, mis. `fisik-0`).
 * Event: `update:modelValue`, `change`.
 *
 * Contoh pakai:
 *   <QcRadioGroup v-model="row.radio" :options="fisikRadios" :name="`fisik-${i}`" @change="generatePreview" />
 */
const props = defineProps({
    modelValue: { type: String, default: 'other' },
    options: { type: Array, required: true },
    name: { type: String, required: true },
});

const emit = defineEmits(['update:modelValue', 'change']);

const selectedClasses = {
    yes: 'bg-green-600 text-white shadow-sm',
    no: 'bg-destructive text-destructive-foreground shadow-sm',
    other: 'bg-slate-500 text-white shadow-sm',
};

function pillClass(value) {
    if (props.modelValue === value) {
        return selectedClasses[value] ?? selectedClasses.other;
    }
    return 'text-slate-500 hover:bg-white hover:text-slate-700';
}

function onChange(e) {
    emit('update:modelValue', e.target.value);
    emit('change', e.target.value);
}
</script>

<template>
    <div class="flex items-center justify-center">
        <div class="inline-flex items-center gap-0.5 rounded-md border bg-slate-50 p-0.5" role="radiogroup" :aria-label="props.name">
            <label v-for="opt in props.options" :key="opt.value" class="cursor-pointer">
                <input
                    :checked="props.modelValue === opt.value"
                    type="radio"
                    class="peer sr-only"
                    :name="props.name"
                    :value="opt.value"
                    @change="onChange"
                />
                <span
                    class="flex h-7 items-center rounded px-2.5 text-xs transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-slate-400"
                    :class="pillClass(opt.value)"
                >
                    {{ opt.label }}
                </span>
            </label>
        </div>
    </div>
</template>
