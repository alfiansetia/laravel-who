<script setup>
import { ref, watch } from 'vue';
import Input from '@/components/ui/Input.vue';

// Input rupiah dengan masking id-ID saat mengetik (1000000 → "1.000.000").
// v-model tetap string digit mentah ("1000000") agar kontrak backend
// (kolom `nilai` string) tidak berubah.
const model = defineModel({ type: [String, Number], default: '' });

defineProps({
    placeholder: { type: String, default: '0' },
    class: { type: String, default: '' },
});

function digitsOf(value) {
    return String(value ?? '').replace(/\D/g, '').replace(/^0+(?=\d)/, '');
}

function mask(value) {
    const digits = digitsOf(value);
    if (digits === '') {
        return '';
    }
    return Number(digits).toLocaleString('id-ID');
}

const display = ref(mask(model.value));

watch(
    () => model.value,
    (value) => {
        const next = mask(value);
        if (next !== display.value) {
            display.value = next;
        }
    },
);

function onInput(event) {
    const formatted = mask(event.target.value);
    display.value = formatted;
    // Kembalikan caret ke akhir agar separator yang baru muncul
    // tidak menggeser posisi ketikan.
    requestAnimationFrame(() => {
        try {
            event.target.setSelectionRange(formatted.length, formatted.length);
        } catch {
            // Abaikan: browser tanpa selection API.
        }
    });
    const raw = digitsOf(event.target.value);
    if (raw !== digitsOf(model.value)) {
        model.value = raw;
    }
}
</script>

<template>
    <Input
        v-model="display"
        :class="$props.class"
        :placeholder="placeholder"
        inputmode="numeric"
        autocomplete="off"
        @input="onInput"
    />
</template>
