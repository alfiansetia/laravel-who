<script setup>
import { CircleCheck, CircleX, Clock, Minus } from '@lucide/vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    status: { type: String, default: 'secondary' },
});

const map = {
    success: { classes: 'bg-green-50 text-green-700 border-green-200', icon: CircleCheck },
    danger: { classes: 'bg-red-50 text-red-700 border-red-200', icon: CircleX },
    warning: { classes: 'bg-amber-50 text-amber-700 border-amber-200', icon: Clock },
    secondary: { classes: 'bg-slate-100 text-slate-600 border-slate-200', icon: Minus },
    done: { classes: 'bg-green-50 text-green-700 border-green-200', icon: CircleCheck },
    pending: { classes: 'bg-red-50 text-red-700 border-red-200', icon: Clock },
    dus: { classes: 'bg-sky-50 text-sky-700 border-sky-200', icon: Minus },
    unit: { classes: 'bg-amber-50 text-amber-700 border-amber-200', icon: Minus },
    stock: { classes: 'bg-green-50 text-green-700 border-green-200', icon: CircleCheck },
    import: { classes: 'bg-slate-100 text-slate-600 border-slate-200', icon: Minus },
};

const label = computed(() => {
    if (props.status === 'done') {
        return 'Done';
    }
    if (props.status === 'pending') {
        return 'Pending';
    }
    return props.status;
});

const current = computed(() => map[props.status] ?? map.secondary);
</script>

<template>
    <span
        :class="cn('inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium', current.classes)"
    >
        <component :is="current.icon" class="size-3.5" />
        <slot>{{ label }}</slot>
    </span>
</template>
