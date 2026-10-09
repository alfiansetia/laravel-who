<script setup>
import AppLayout from '@/components/AppLayout.vue';
import BastForm from './partials/BastForm.vue';

defineProps({
    title: { type: String, default: 'Edit BAST' },
    bast: { type: Object, default: null },
    products: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout>
        <BastForm :title="title" mode="edit" :bast="bast" :products="products" />
    </AppLayout>
</template>
