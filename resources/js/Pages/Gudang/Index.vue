<script setup>
import { computed, ref } from 'vue';
import { ChevronLeft, ChevronRight, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import TableToolbar from '@/components/TableToolbar.vue';
import EmptyState from '@/components/EmptyState.vue';
import Button from '@/components/ui/Button.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import api from '@/lib/axios';
import { odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Gudang' },
    filters: { type: Object, default: () => ({}) },
});

const perPageOptions = [12, 24, 48, 96];

const query = useTableQuery((p) => api.get('/gudang', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
    perPage: 24,
});

const searchBox = ref(props.filters.search ?? '');

function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
}

function cellText(value, empty = '-') {
    if (value === false || value === null || value === undefined || value === '') {
        return empty;
    }
    return String(value);
}

const pageLabel = computed(() => `Hal ${query.page.value} / ${query.totalPages.value}`);

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" />
        <TableToolbar v-model="searchBox" placeholder="Cari nama / kode gudang..." :active-count="0" @search="query.setSearch" @reset="resetFilter">
            <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
        </TableToolbar>

        <p v-if="query.loading.value" class="py-10 text-center text-sm text-muted-foreground">Memuat data gudang...</p>
        <p v-else-if="query.error.value" class="py-10 text-center text-sm text-red-600">{{ query.error.value }}</p>
        <EmptyState v-else-if="query.rows.value.length === 0" title="Gudang tidak ditemukan" message="Coba kata kunci lain atau segarkan halaman." />
        <div v-else class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-4">
            <div v-for="row in query.rows.value" :key="row.id" class="rounded-lg border bg-white p-3">
                <p class="truncate text-sm font-medium" :title="cellText(row.name, '')">{{ cellText(row.name, '') }}</p>
                <p class="mt-1 font-mono text-xs text-muted-foreground">{{ cellText(row.code, '') }}</p>
                <p class="mt-1 truncate text-xs" :title="odooName(row.lot_stock_id)">{{ odooName(row.lot_stock_id) }}</p>
                <p class="truncate text-[11px] text-muted-foreground" :title="odooName(row.company_id)">{{ odooName(row.company_id) }}</p>
            </div>
        </div>

        <div v-if="!query.loading.value && query.rows.value.length > 0" class="mt-3 flex flex-wrap items-center gap-2">
            <Button variant="outline" size="sm" :disabled="query.page.value <= 1" @click="query.setPage(query.page.value - 1)"><ChevronLeft /> Prev</Button>
            <span class="text-xs text-muted-foreground">{{ pageLabel }} &middot; {{ query.total.value }} gudang</span>
            <Button variant="outline" size="sm" :disabled="query.page.value >= query.totalPages.value" @click="query.setPage(query.page.value + 1)">Next <ChevronRight /></Button>
            <select
                :value="query.perPage.value"
                class="ml-auto h-8 rounded-md border bg-white px-2 text-xs"
                @change="query.setPerPage(Number($event.target.value))"
            >
                <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }} / hal</option>
            </select>
        </div>
    </AppLayout>
</template>
