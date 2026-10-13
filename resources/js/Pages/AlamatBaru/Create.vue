<script setup>
import AppLayout from '@/components/AppLayout.vue';
import AlamatHeaderForm from './partials/AlamatHeaderForm.vue';

defineProps({
    title: { type: String, default: 'Create Alamat Baru' },
});

function onSaved(id) {
    // Detail masih Blade (alamat_baru.show): pindah full-page ke sana.
    if (id) {
        window.location.href = `/alamat-baru/${id}`;
    }
}
</script>

<template>
    <AppLayout>
        <AlamatHeaderForm :title="title" mode="create" @saved="onSaved" />
    </AppLayout>
</template>
