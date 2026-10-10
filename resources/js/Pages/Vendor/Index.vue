<script setup>
import { computed, ref } from 'vue';
import { Copy, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useTableQuery } from '@/composables/useTableQuery';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, copyText } from '@/lib/export';
import { odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Vendor Odoo' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const query = useTableQuery((p) => api.get('/vendor-odoo', { params: p }), {
    search: props.filters.search ?? '',
    page: Number(props.filters.page ?? 1),
});
const detailOpen = ref(false);
const detail = ref(null);
const detailLoading = ref(false);
const activeRow = ref(null);

const activeCount = computed(() => (query.search.value ? 1 : 0));

const searchBox = ref(props.filters.search ?? '');

function onSearchInput(e) {
    searchBox.value = e.target.value;
    query.setSearch(e.target.value);
}

function resetFilter() {
    searchBox.value = '';
    query.setSearch('', 0);
}

function vendorName(row) {
    return row.display_name ?? row.name ?? '-';
}

const columns = [
    { key: 'name', label: 'Vendor' },
    { key: 'phone', label: 'Telepon' },
    { key: 'email', label: 'Email' },
    { key: 'city', label: 'Kota' },
];

const perPageOptions = [10, 25, 50, 100, 500, 1000];

const mainExportRows = computed(() => query.rows.value.map((row) => ({
    name: vendorName(row),
    phone: row.phone ?? '',
    email: row.email ?? '',
    city: row.city ?? '',
})));

const contactText = computed(() => {
    const d = detail.value ?? activeRow.value;
    if (!d) {
        return '';
    }
    const address = [d.street, d.street2, d.city, d.zip].filter(Boolean).join(', ');
    return [vendorName(d), d.phone ?? '', d.email ?? '', address].filter(Boolean).join('\n');
});

async function openDetail(row) {
    activeRow.value = row;
    detailOpen.value = true;
    detailLoading.value = true;
    try {
        const res = await api.get(`/vendor-odoo/${row.id}`, { silent: true, block: true });
        detail.value = res.data?.data ?? res.data;
    } catch (e) {
        detail.value = row;
        toast.error(e.response?.data?.message ?? 'Gagal memuat detail vendor.');
    } finally {
        detailLoading.value = false;
    }
}

query.fetch();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="query.fetch()"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(mainExportRows, ['name', 'phone', 'email', 'city'], 'vendor')"><Copy /> Salin</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Vendor" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari nama / email / telepon..." @input="onSearchInput" />
            </FormField>
        </FilterPanel>
        <DataTable
            :columns="columns"
            :rows="query.rows.value"
            :loading="query.loading.value"
            :error="query.error.value"
            :page="query.page.value"
            :total-pages="query.totalPages.value"
            :total="query.total.value"
            :per-page="query.perPage.value"
            :per-page-options="perPageOptions"
            clickable
            empty-title="Vendor tidak ditemukan"
            empty-message="Ubah kata kunci pencarian."
            @update:page="query.setPage"
            @update:per-page="query.setPerPage"
            @row-click="openDetail"
        >
            <template #cell-name="{ row }"><b>{{ vendorName(row) }}</b></template>
            <template #cell-phone="{ row }">{{ row.phone || row.mobile || '-' }}</template>
            <template #cell-email="{ row }">{{ row.email || '-' }}</template>
            <template #cell-city="{ row }">{{ row.city || '-' }}</template>
        </DataTable>
        <AppModal v-model:open="detailOpen" :title="`Vendor: ${vendorName(detail ?? activeRow ?? {})}`" size="md">
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 5" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="space-y-1 text-sm">
                <p><b>Nama:</b> {{ vendorName(detail ?? {}) }}</p>
                <p><b>Telepon:</b> {{ detail?.phone || detail?.mobile || '-' }}</p>
                <p><b>Email:</b> {{ detail?.email || '-' }}</p>
                <p><b>Website:</b> {{ detail?.website || '-' }}</p>
                <p><b>Alamat:</b> {{ [detail?.street, detail?.street2].filter(Boolean).join(', ') || '-' }}</p>
                <p><b>Kota:</b> {{ [detail?.city, detail?.zip].filter(Boolean).join(' ') || '-' }}</p>
                <p><b>Negara:</b> {{ odooName(detail?.country_id) }}</p>
                <p><b>NPWP/VAT:</b> {{ detail?.vat || '-' }}</p>
            </div>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
                <Button variant="outline" @click="copyText(contactText, 'Kontak vendor disalin ke clipboard.')"><Copy /> Salin Kontak</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
