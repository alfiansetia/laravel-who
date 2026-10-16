<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import Input from '@/components/ui/Input.vue';

const props = defineProps({
    value: { type: [String, Number], default: '' },
    display: { type: [String, Number], default: null },
    active: { type: Boolean, default: false },
    type: { type: String, default: 'text' },
});

const emit = defineEmits(['start', 'commit', 'cancel']);

const draft = ref('');
const inputEl = ref(null);
const shown = computed(() => props.display ?? props.value);

watch(() => props.active, async (on) => {
    if (!on) {
        return;
    }
    draft.value = props.display ?? props.value ?? '';
    await nextTick();
    inputEl.value?.$el?.focus?.();
    inputEl.value?.$el?.select?.();
});

function onCommit() {
    emit('commit', draft.value);
}
</script>

<template>
    <Input
        v-if="active"
        ref="inputEl"
        v-model="draft"
        :type="type"
        class="h-8 min-w-24 px-2"
        @blur="onCommit"
        @keydown.enter.prevent="inputEl?.$el?.blur()"
        @keydown.esc="emit('cancel')"
    />
    <span
        v-else
        role="button"
        tabindex="0"
        title="Klik untuk ubah"
        class="-mx-1 block min-h-5 cursor-text rounded px-1 hover:bg-slate-100"
        @click="emit('start')"
        @keydown.enter="emit('start')"
    >{{ shown === '' || shown === null || shown === undefined ? ' ' : shown }}</span>
</template>
