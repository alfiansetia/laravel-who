<script setup>
import AppLayout from '@/components/AppLayout.vue';
import BastForm from './partials/BastForm.vue';

defineProps({
    title: { type: String, default: 'Create BAST' },
    products: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout>
        <BastForm :title="title" mode="create" :products="products" />
    </AppLayout>
</template>
