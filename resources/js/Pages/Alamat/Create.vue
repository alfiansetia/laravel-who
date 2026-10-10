<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import AlamatForm from './partials/AlamatForm.vue';

defineProps({
    title: { type: String, default: 'Create Alamat' },
});

const formRef = ref(null);

function goBack() {
    router.visit('/alamats');
}

async function save() {
    await formRef.value?.save();
}

function onSaved(id) {
    if (id) {
        window.open(`/alamats/${id}/edit`, '_blank');
    }
    router.visit('/alamats');
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack"><ArrowLeft /> Kembali</Button>
                <Button size="sm" @click="save"><Save /> Simpan</Button>
            </template>
        </PageHeader>
        <AlamatForm ref="formRef" mode="create" @saved="onSaved" />
    </AppLayout>
</template>
