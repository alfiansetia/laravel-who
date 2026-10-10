<script setup>
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import AklForm from './Form.vue';

const props = defineProps({
    title: { type: String, default: 'Edit Lampiran' },
    record: { type: Object, default: null },
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <AklForm mode="edit" :record="record" />
    </AppLayout>
</template>
