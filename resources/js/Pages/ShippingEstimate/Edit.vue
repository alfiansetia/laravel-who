<script setup>
import AppLayout from '@/components/AppLayout.vue';
import EstimateForm from './partials/EstimateForm.vue';

defineProps({
    title: { type: String, default: 'Edit Shipping Estimate' },
    record: { type: Object, required: true },
});
</script>

<template>
    <AppLayout>
        <EstimateForm :title="title" mode="edit" :record="record" />
    </AppLayout>
</template>
