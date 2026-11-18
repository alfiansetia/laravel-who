<script setup>
import { computed, ref } from 'vue';
import { BookOpen, ClipboardList, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import EmptyState from '@/components/EmptyState.vue';
import Button from '@/components/ui/Button.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import api from '@/lib/axios';

const props = defineProps({
    title: { type: String, default: 'L&D' },
});

const query = useTableQuery((p) => api.get('/lnd', { params: p }), {
    search: '',
    page: 1,
    perPage: 100,
});

const searchBox = ref('');

const filteredRows = computed(() => {
    const kw = String(searchBox.value ?? '').trim().toLowerCase();
    if (kw === '') {
        return query.rows.value;
    }
    return query.rows.value.filter((row) => String(row.title ?? '').toLowerCase().includes(kw));
});

function resetFilter() {
    searchBox.value = '';
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari program..." :active-count="0" @reset="resetFilter">
            <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
        </TableToolbar>

        <p v-if="query.loading.value" class="py-10 text-center text-sm text-muted-foreground">Memuat program L&D...</p>
        <p v-else-if="query.error.value" class="py-10 text-center text-sm text-red-600">{{ query.error.value }}</p>
        <EmptyState v-else-if="filteredRows.length === 0" title="Program tidak ditemukan" message="Coba kata kunci lain atau segarkan halaman." />
        <div v-else class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="row in filteredRows" :key="row.title" class="flex flex-col rounded-lg border bg-white p-4">
                <p class="text-sm font-medium">{{ row.title }}</p>
                <div class="mt-3 flex flex-wrap gap-2 pt-1">
                    <Button variant="outline" size="sm" as="a" :href="row.materi_url" target="_blank" rel="noopener"><BookOpen /> Materi</Button>
                    <Button variant="outline" size="sm" as="a" :href="row.post_test_url" target="_blank" rel="noopener"><ClipboardList /> Post-Test</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
