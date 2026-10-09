<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/components/AppLayout.vue';
import AlamatHeaderForm from './partials/AlamatHeaderForm.vue';

defineProps({
    title: { type: String, default: 'Create Alamat Baru' },
});

const createdId = ref(null);

function onSaved(id) {
    createdId.value = id;
    if (id) {
        window.open(`/alamat-baru/${id}/edit`, '_blank');
        router.visit('/alamat-baru');
    }
}
</script>

<template>
    <AppLayout>
        <AlamatHeaderForm :title="title" mode="create" @saved="onSaved" />
    </AppLayout>
</template>
