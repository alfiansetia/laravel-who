<script setup>
import AppLayout from '@/components/AppLayout.vue';
import PackForm from './partials/PackForm.vue';

defineProps({
    title: { type: String, default: 'Create Packing List' },
    products: { type: Array, default: () => [] },
    vendors: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout>
        <PackForm :title="title" mode="create" :products="products" :vendors="vendors" />
    </AppLayout>
</template>
