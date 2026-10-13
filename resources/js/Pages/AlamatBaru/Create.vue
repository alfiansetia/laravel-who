<script setup>
import AppLayout from '@/components/AppLayout.vue';
import AlamatHeaderForm from './partials/AlamatHeaderForm.vue';

defineProps({
    title: { type: String, default: 'Create Alamat Baru' },
});

function onSaved(id) {
    // Habis create buka edit di tab baru; halaman create dibiarkan.
    if (id) {
        window.open(`/alamat-baru/${id}/edit`, '_blank');
    }
}
</script>

<template>
    <AppLayout>
        <AlamatHeaderForm :title="title" mode="create" @saved="onSaved" />
    </AppLayout>
</template>
