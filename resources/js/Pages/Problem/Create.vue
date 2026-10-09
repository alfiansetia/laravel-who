<script setup>
import AppLayout from '@/components/AppLayout.vue';
import ProblemForm from './partials/ProblemForm.vue';

defineProps({
    title: { type: String, default: 'Tambah Problem' },
    nextNumber: { type: String, default: '' },
    defaultDate: { type: String, default: '' },
    products: { type: Array, default: () => [] },
    picOptions: { type: Array, default: () => [] },
    typeOptions: { type: Array, default: () => [] },
    stockOptions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout>
        <ProblemForm
            :title="title"
            mode="create"
            :products="products"
            :pic-options="picOptions"
            :type-options="typeOptions"
            :stock-options="stockOptions"
            :status-options="statusOptions"
            :next-number="nextNumber"
            :default-date="defaultDate"
        />
    </AppLayout>
</template>
