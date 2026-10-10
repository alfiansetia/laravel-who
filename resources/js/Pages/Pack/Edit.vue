<script setup>
import AppLayout from '@/components/AppLayout.vue';
import PackForm from './partials/PackForm.vue';

defineProps({
    title: { type: String, default: 'Edit Packing List' },
    pack: { type: Object, default: null },
    products: { type: Array, default: () => [] },
    vendors: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout>
        <PackForm :title="title" mode="edit" :pack="pack" :products="products" :vendors="vendors" />
    </AppLayout>
</template>
