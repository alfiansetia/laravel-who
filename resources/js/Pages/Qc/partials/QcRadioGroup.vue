<script setup>
/**
 * QcRadioGroup — pilihan Baik/Tidak/N-A (atau Ada/Tidak/N-A) satu baris pemeriksaan Form QC.
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

function onChange(e) {
    emit('update:modelValue', e.target.value);
    emit('change', e.target.value);
}
</script>

<template>
    <div class="flex items-center justify-center gap-4">
        <label
            v-for="opt in props.options"
            :key="opt.value"
            class="flex cursor-pointer items-center gap-1 text-xs"
            :class="opt.value === 'other' ? 'text-muted-foreground' : 'font-bold'"
        >
            <input
                :checked="props.modelValue === opt.value"
                type="radio"
                :name="props.name"
                :value="opt.value"
                class="size-3.5 accent-primary"
                @change="onChange"
            />
            {{ opt.label }}
        </label>
    </div>
</template>
