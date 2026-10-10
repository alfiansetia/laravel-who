<script setup>
/**
 * TerlampirButton — tombol icon-only "Isi Terlampir" untuk field Tipe/SN-Lot/Qty Form QC.
 *
 * Props: tidak ada.
 * Event: `apply` — dipancarkan saat diklik (parent mengisi field terkait).
 *
 * Contoh pakai:
 *   <TerlampirButton @apply="setTerlampir('type')" />
 */
import { FileText } from '@lucide/vue';
import Button from '@/components/ui/Button.vue';

defineEmits(['apply']);
</script>

<template>
    <Button variant="outline" size="sm" title="Isi Terlampir" @click="$emit('apply')"><FileText /></Button>
</template>
