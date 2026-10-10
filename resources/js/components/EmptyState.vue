<script setup>
import { PackageOpen } from '@lucide/vue';

defineProps({
    icon: { type: null, default: null },
    title: { type: String, default: 'Tidak ada data' },
    message: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-2 px-4 py-10 text-center">
        <component :is="icon ?? PackageOpen" class="size-8 text-muted-foreground" />
        <p class="text-sm font-medium">{{ title }}</p>
        <p v-if="message" class="max-w-sm text-xs text-muted-foreground">{{ message }}</p>
        <div v-if="$slots.action" class="mt-2">
            <slot name="action" />
        </div>
    </div>
</template>
