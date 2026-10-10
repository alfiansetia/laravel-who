<script setup>
import AppLayout from '@/components/AppLayout.vue';
import EstimateForm from './partials/EstimateForm.vue';

defineProps({
    title: { type: String, default: 'Tambah Shipping Estimate' },
});
</script>

<template>
    <AppLayout>
        <EstimateForm :title="title" mode="create" />
    </AppLayout>
</template>
