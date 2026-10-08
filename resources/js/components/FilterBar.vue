<script setup>
import { ref, watch } from 'vue';
import Input from '@/components/ui/Input.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: 'Cari...' },
});

const emit = defineEmits(['update:modelValue', 'search']);

const inner = ref(props.modelValue);
watch(() => props.modelValue, (v) => { inner.value = v; });

let t = null;
function onInput(e) {
    inner.value = e.target.value;
    emit('update:modelValue', inner.value);
    clearTimeout(t);
    t = setTimeout(() => emit('search', inner.value), 400);
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Input :model-value="inner" type="search" :placeholder="placeholder" class="h-9 max-w-xs" @input="onInput" />
        <slot />
    </div>
</template>
