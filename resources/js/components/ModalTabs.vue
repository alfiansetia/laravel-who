<script setup>
// Tab gaya shadcn TabsList untuk modal bertab (RI/DO/IT/ProductOdoo).
// Pemakaian:
//   <ModalTabs v-model="detailTab" :tabs="detailTabs" class="mb-2" />
//   const detailTabs = computed(() => [
//       { value: 'product', label: 'Product', count: productTable.total.value },
//       { value: 'lot', label: 'Product Lot', count: lotTable.total.value },
//   ]);

defineProps({
    tabs: { type: Array, default: () => [] },
});

const model = defineModel({ type: String, default: '' });
</script>

<template>
    <div
        role="tablist"
        class="inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-slate-100 p-1 text-sm font-medium text-muted-foreground"
    >
        <button
            v-for="t in tabs"
            :key="t.value"
            type="button"
            role="tab"
            :aria-selected="model === t.value"
            :class="[
                'inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1 transition-colors focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
                model === t.value ? 'bg-white text-foreground shadow-sm' : 'hover:text-foreground',
            ]"
            @click="model = t.value"
        >
            {{ t.label }}
            <span
                v-if="t.count !== undefined && t.count !== null"
                :class="[
                    'rounded-full px-1.5 py-px text-[11px] font-semibold tabular-nums',
                    model === t.value ? 'bg-primary/10 text-primary' : 'bg-slate-200/80 text-slate-600',
                ]"
            >{{ t.count }}</span>
        </button>
    </div>
</template>
