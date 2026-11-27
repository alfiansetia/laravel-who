<script setup>
import CopyButton from '@/components/CopyButton.vue';

// Grid info generik untuk modal detail Odoo (DO/IT/RI/PO/ITR).
// Pemakaian: <DetailMeta :items="metaItems" />
// items: [{ label, value, mono?, copy? }] — `value` sudah string final,
// kosong tampil `-`; tombol salin muncul bila ada nilai valid.
const props = defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="grid gap-x-6 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="(it, i) in props.items" :key="i" class="min-w-0">
            <div class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground">{{ it.label }}</div>
            <div class="flex items-start gap-1 text-sm">
                <span class="min-w-0 flex-1 break-words" :class="{ 'font-mono': it.mono }">{{ it.value || '-' }}</span>
                <CopyButton v-if="it.value && it.value !== '-' && it.value !== 'False'" :text="it.copy ?? it.value" :label="it.label" />
            </div>
        </div>
    </div>
</template>
