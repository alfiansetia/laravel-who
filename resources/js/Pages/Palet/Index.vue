<script setup>
import { computed, ref } from 'vue';
import { FileDown } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import SuhuToolTabs from '@/components/SuhuToolTabs.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';

const props = defineProps({
    title: { type: String, default: 'Pembersihan Palet' },
    gudangDefaults: { type: Object, default: () => ({}) },
    defaults: { type: Object, default: () => ({}) },
});

const tanggal = ref(props.defaults?.tanggal || new Date().toISOString().slice(0, 10));
const gudangA = ref(props.gudangDefaults?.a || '');
const gudangB = ref(props.gudangDefaults?.b || '');
const gudangC = ref(props.gudangDefaults?.c || '');

const tanggalLabel = computed(() => {
    const parts = String(tanggal.value || '').split('-');
    return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : '';
});

function onDownload() {
    const params = new URLSearchParams({
        tanggal: tanggal.value,
        gudang_a: gudangA.value,
        gudang_b: gudangB.value,
        gudang_c: gudangC.value,
    });
    window.open(`/tools/pembersihan/download?${params.toString()}`, '_blank');
}
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" />
        <SuhuToolTabs class="mb-4" />

        <div class="space-y-4">
            <Card class="p-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <FormField label="Tanggal" required>
                        <Input v-model="tanggal" type="date" />
                    </FormField>
                    <FormField label="Gudang A" required>
                        <Input v-model="gudangA" type="text" placeholder="Nama gudang A..." />
                    </FormField>
                    <FormField label="Gudang B" required>
                        <Input v-model="gudangB" type="text" placeholder="Nama gudang B..." />
                    </FormField>
                    <FormField label="Gudang C" required>
                        <Input v-model="gudangC" type="text" placeholder="Nama gudang C..." />
                    </FormField>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-md bg-primary/10 px-3 py-2">
                    <span class="text-sm text-muted-foreground">
                        3 halaman (satu per gudang), tanggal
                        <span class="font-medium text-foreground">{{ tanggalLabel }}</span>
                    </span>
                    <Button size="sm" @click="onDownload"><FileDown /> Unduh Pembersihan Palet</Button>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
