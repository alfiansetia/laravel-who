<script setup>
import { ref } from 'vue';
import { ChevronDown, Filter, RotateCcw } from '@lucide/vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';

const props = defineProps({
    title: { type: String, default: 'Filter' },
    activeCount: { type: Number, default: 0 },
    defaultOpen: { type: Boolean, default: false },
});

const emit = defineEmits(['reset']);

const open = ref(props.defaultOpen);

function toggle() {
    open.value = !open.value;
}
</script>

<template>
    <Card class="mb-3">
        <button
            type="button"
            class="flex w-full items-center gap-2 px-3 py-2.5 text-left text-sm font-medium"
            @click="toggle"
        >
            <Filter class="size-4 text-muted-foreground" />
            {{ title }}
            <span
                v-if="activeCount > 0"
                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-semibold text-primary-foreground"
            >
                {{ activeCount }}
            </span>
            <span v-if="!open && activeCount === 0" class="text-xs font-normal text-muted-foreground">Klik untuk membuka</span>
            <ChevronDown class="ml-auto size-4 text-muted-foreground transition-transform" :class="open && 'rotate-180'" />
        </button>
        <div v-show="open" class="border-t px-3 py-3">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <slot />
            </div>
            <div v-if="$slots.actions" class="mt-3 flex flex-wrap items-center gap-2">
                <slot name="actions" />
                <Button v-if="$attrs.onReset || true" variant="ghost" size="sm" @click="emit('reset')">
                    <RotateCcw /> Reset
                </Button>
            </div>
        </div>
    </Card>
</template>
