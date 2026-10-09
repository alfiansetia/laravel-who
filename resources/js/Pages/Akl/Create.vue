<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import AklForm from './Form.vue';

defineProps({
    title: { type: String, default: 'Upload Lampiran' },
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Upload lampiran AKL baru (reg_no boleh sama untuk perpanjangan)" />
        <AklForm mode="create" :record="null" />
    </AppLayout>
</template>
