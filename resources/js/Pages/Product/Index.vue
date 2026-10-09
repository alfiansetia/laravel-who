<script setup>
import { computed, ref } from 'vue';
import { Copy, Download, FileArchive, Images, Info, Move, Printer, RefreshCw } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import AppModal from '@/components/AppModal.vue';
import FilterPanel from '@/components/FilterPanel.vue';
import FormField from '@/components/FormField.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useClientTable } from '@/composables/useClientTable';
import { useBlock } from '@/composables/useBlock';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import { copyRows, copyText, downloadCsv } from '@/lib/export';
import { odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Data Product' },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const { confirm } = useConfirm();
const { withBlock } = useBlock();

const table = useClientTable((p) => api.get('/products', { params: p }), {
    searchKeys: ['code', 'name', 'akl'],
});

const boolOptions = (yes, no) => [
    { label: 'Semua', value: '' },
    { label: yes, value: 'yes' },
    { label: no, value: 'no' },
];
const filterPltbb = ref('');
const filterImage = ref('');
const filterPl = ref('');
const filterSop = ref('');
const searchBox = ref(props.filters.search ?? '');

const pltbbOptions = [
    { label: 'Semua', value: '' },
    { label: 'Ada', value: 'ada' },
    { label: 'Lengkap', value: 'lengkap' },
    { label: 'Tidak ada', value: 'tidak_ada' },
];

const filteredAll = computed(() => {
    let out = table.filtered.value;
    if (filterPltbb.value === 'ada') {
        out = out.filter((r) => r.has_pltbb);
    } else if (filterPltbb.value === 'lengkap') {
        out = out.filter((r) => r.pltbb_complete);
    } else if (filterPltbb.value === 'tidak_ada') {
        out = out.filter((r) => !r.has_pltbb);
    }
    if (filterImage.value === 'yes') {
        out = out.filter((r) => r.has_image);
    } else if (filterImage.value === 'no') {
        out = out.filter((r) => !r.has_image);
    }
    if (filterPl.value === 'yes') {
        out = out.filter((r) => r.has_pl);
    } else if (filterPl.value === 'no') {
        out = out.filter((r) => !r.has_pl);
    }
    if (filterSop.value === 'yes') {
        out = out.filter((r) => r.has_sop);
    } else if (filterSop.value === 'no') {
        out = out.filter((r) => !r.has_sop);
    }
    return out;
});

const totalFiltered = computed(() => filteredAll.value.length);
const pageRows = computed(() => {
    const start = (table.page.value - 1) * table.perPage.value;
    return filteredAll.value.slice(start, start + table.perPage.value);
});
const totalPagesFiltered = computed(() => Math.max(1, Math.ceil(totalFiltered.value / table.perPage.value)));

const activeCount = computed(
    () =>
        (table.search.value ? 1 : 0) +
        (filterPltbb.value ? 1 : 0) +
        (filterImage.value ? 1 : 0) +
        (filterPl.value ? 1 : 0) +
        (filterSop.value ? 1 : 0),
);

function onSearchInput(e) {
    searchBox.value = e.target.value;
    table.setSearch(e.target.value);
    table.setPage(1);
}

function resetFilter() {
    searchBox.value = '';
    table.setSearch('', 0);
    filterPltbb.value = '';
    filterImage.value = '';
    filterPl.value = '';
    filterSop.value = '';
    table.setPage(1);
}

async function load() {
    await withBlock(() => table.fetch());
}

const ALL_COLUMNS = [
    { key: 'code', label: 'Kode', mono: true },
    { key: 'name', label: 'Nama' },
    { key: 'akl', label: 'AKL', mono: true },
    { key: 'akl_exp', label: 'AKL Exp', mono: true },
    { key: 'pltbb_display', label: 'PLTBB' },
    { key: 'images_count', label: 'Img', align: 'center' },
    { key: 'packs_count', label: 'PL', align: 'center' },
    { key: 'has_sop', label: 'SOP', align: 'center' },
];

const HIDDEN_DEFAULT = ['pltbb_display', 'images_count', 'packs_count', 'has_sop'];

const visibleCols = ref(ALL_COLUMNS.map((c) => c.key).filter((k) => !HIDDEN_DEFAULT.includes(k)));

const columns = computed(() => ALL_COLUMNS.filter((c) => visibleCols.value.includes(c.key)));

const columnOptions = computed(() => ALL_COLUMNS.map((c) => ({ label: c.label, value: c.key })));

const perPageOptions = [10, 25, 50, 100];

const mainExportRows = computed(() =>
    filteredAll.value.map((r) => ({
        code: r.code ?? '',
        name: r.name ?? '',
        akl: r.akl ?? '',
        akl_exp: r.akl_exp ?? '',
        pltbb: r.pltbb_display ?? '',
        images: r.images_count ?? 0,
        pl: r.packs_count ?? 0,
        sop: r.has_sop ? 'Ada' : '-',
    })),
);

function copyRow(row) {
    copyText(`${row.code ?? ''}\t${row.name ?? ''}`, 'Kode + nama disalin.');
}

function isExpired(date) {
    if (!date) {
        return false;
    }
    return new Date(date) < new Date(new Date().toDateString());
}

async function syncProduct() {
    const ok = await confirm({
        title: 'Sinkron product?',
        message: 'Ambil ulang data product dari Odoo.',
        confirmText: 'Ya, sinkron',
    });
    if (!ok) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await api.post('/product-sync', {}, { block: true });
            toast.success(res.data?.message ?? 'Sinkron selesai.');
            await table.fetch();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal menyinkron product.');
        }
    });
}

// Detail modal
const detailOpen = ref(false);
const detail = ref(null);
const detailLoading = ref(false);
const activeRow = ref(null);
const activeTab = ref('sop');

async function openDetail(row) {
    activeRow.value = row;
    detail.value = null;
    detailOpen.value = true;
    detailLoading.value = true;
    activeTab.value = 'sop';
    await withBlock(async () => {
        try {
            const res = await api.get(`/products/${row.id}`, { block: true, silent: true });
            detail.value = res.data?.data ?? res.data;
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat detail product.');
        } finally {
            detailLoading.value = false;
        }
    });
}

function downloadZip() {
    const id = detail.value?.id ?? activeRow.value?.id;
    if (!id) {
        return;
    }
    window.open(`/api/products/${id}/download-zip`, '_blank');
}

function printSop() {
    const id = detail.value?.sop?.id;
    if (id) {
        window.open(`/sops/${id}/print`, '_blank');
    }
}

function printPl(packId) {
    window.open(`/packs/${packId}/print`, '_blank');
}

function printCombined(packId) {
    window.open(`/packs/${packId}/print-combined`, '_blank');
}

function printCollage() {
    const id = detail.value?.id ?? activeRow.value?.id;
    if (id) {
        window.open(`/product_images/${id}/collage`, '_blank');
    }
}

// Move modal
const moveOpen = ref(false);
const moveRows = ref([]);
const moveLoading = ref(false);
const moveTable = useClientTable(null, { searchKeys: ['reference', 'location', 'destination', 'lot', 'customer'] });

async function openMove(row) {
    activeRow.value = row;
    moveOpen.value = true;
    moveTable.setRows([]);
    moveLoading.value = true;
    await withBlock(async () => {
        try {
            const res = await api.get(`/products/${row.id}/move`, { block: true, silent: true });
            const body = res.data?.data ?? res.data ?? [];
            const mapped = (Array.isArray(body) ? body : []).map((m) => ({
                reference: m.reference ?? '-',
                location: odooName(m.location_id),
                destination: odooName(m.location_dest_id),
                date: m.date ?? '-',
                lot: odooName(m.lot_id),
                qty: m.qty_done ?? m.quantity_done ?? 0,
                doc: m.x_studio_no_so ?? '-',
                customer: m.x_studio_customer ?? '-',
            }));
            moveTable.setRows(mapped);
            if (mapped.length === 0) {
                toast.info('Tidak ada data move.');
            }
        } catch (e) {
            moveTable.setRows([]);
            moveTable.error.value = e.response?.data?.message ?? 'Gagal memuat move.';
            toast.error(moveTable.error.value);
        } finally {
            moveLoading.value = false;
        }
    });
}

const moveSearchBox = ref('');
function onMoveSearch(e) {
    moveSearchBox.value = e.target.value;
    moveTable.setSearch(e.target.value);
}

const moveColumns = [
    { key: 'reference', label: 'No DO', mono: true },
    { key: 'location', label: 'From' },
    { key: 'destination', label: 'Destination' },
    { key: 'date', label: 'Date' },
    { key: 'lot', label: 'Lot/SN' },
    { key: 'qty', label: 'Qty', align: 'center' },
    { key: 'doc', label: 'Doc' },
    { key: 'customer', label: 'Customer' },
];

const moveExportRows = computed(() =>
    moveTable.filtered.value.map((r) => ({
        reference: r.reference,
        from: r.location,
        destination: r.destination,
        date: r.date,
        lot: r.lot,
        qty: r.qty,
        doc: r.doc,
        customer: r.customer,
    })),
);

load();
</script>

<template>
    <AppLayout>
        <PageHeader :title="title" description="Daftar product + PLTBB, SOP, PL, image">
            <template #actions>
                <Button variant="outline" size="sm" @click="load"><RefreshCw /> Segarkan</Button>
                <Button variant="outline" size="sm" @click="copyRows(mainExportRows, ['code', 'name', 'akl', 'akl_exp', 'pltbb', 'images', 'pl', 'sop'], 'product')"><Copy /> Salin</Button>
                <Button variant="outline" size="sm" @click="downloadCsv('product', mainExportRows, ['code', 'name', 'akl', 'akl_exp', 'pltbb', 'images', 'pl', 'sop'])"><Download /> CSV</Button>
                <Button variant="destructive" size="sm" @click="syncProduct"><RefreshCw /> Sync</Button>
            </template>
        </PageHeader>
        <FilterPanel title="Filter Product" :active-count="activeCount" @reset="resetFilter">
            <FormField label="Pencarian">
                <Input :model-value="searchBox" type="search" placeholder="Cari kode, nama, AKL..." @input="onSearchInput" />
            </FormField>
            <FormField label="PLTBB">
                <SearchableSelect v-model="filterPltbb" :options="pltbbOptions" placeholder="Semua" />
            </FormField>
            <FormField label="Image">
                <SearchableSelect v-model="filterImage" :options="boolOptions('Ada', 'Tidak ada')" placeholder="Semua" />
            </FormField>
            <FormField label="Packing List">
                <SearchableSelect v-model="filterPl" :options="boolOptions('Ada', 'Tidak ada')" placeholder="Semua" />
            </FormField>
            <FormField label="SOP">
                <SearchableSelect v-model="filterSop" :options="boolOptions('Ada', 'Tidak ada')" placeholder="Semua" />
            </FormField>
            <FormField label="Kolom tampil" :hint="`${visibleCols.length} dari ${ALL_COLUMNS.length} kolom tampil`">
                <MultiSelect v-model="visibleCols" :options="columnOptions" placeholder="Kolom tampil" />
            </FormField>
        </FilterPanel>
        <DataTable
            :columns="columns"
            :rows="pageRows"
            :loading="table.loading.value"
            :error="table.error.value"
            :page="table.page.value"
            :total-pages="totalPagesFiltered"
            :total="totalFiltered"
            :per-page="table.perPage.value"
            :per-page-options="perPageOptions"
            sortable
            clickable
            empty-title="Product tidak ditemukan"
            empty-message="Ubah kata kunci atau filter."
            @update:page="table.setPage"
            @update:per-page="table.setPerPage"
            @sort="table.toggleSort"
            @row-click="openDetail"
        >
            <template #cell-code="{ row }"><b>{{ row.code ?? '-' }}</b></template>
            <template #cell-name="{ row }">{{ row.name ?? '-' }}</template>
            <template #cell-akl="{ row }">{{ row.akl ?? '-' }}</template>
            <template #cell-akl_exp="{ row }">
                <span v-if="!row.akl_exp">-</span>
                <span v-else class="inline-flex items-center rounded-md border px-2 py-0.5 font-mono text-xs" :class="isExpired(row.akl_exp) ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">{{ row.akl_exp }}</span>
            </template>
            <template #cell-pltbb_display="{ row }">
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs" :class="!row.has_pltbb ? 'border-slate-200 bg-slate-50 text-slate-500' : row.pltbb_complete ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'">{{ row.pltbb_display ?? '-' }}</span>
            </template>
            <template #cell-images_count="{ row }">
                <span class="inline-flex min-w-8 items-center justify-center rounded-md px-2 py-0.5 text-xs font-semibold" :class="Number(row.images_count ?? 0) > 0 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400'">{{ row.images_count ?? 0 }}</span>
            </template>
            <template #cell-packs_count="{ row }">
                <span class="inline-flex min-w-8 items-center justify-center rounded-md px-2 py-0.5 text-xs font-semibold" :class="Number(row.packs_count ?? 0) > 0 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400'">{{ row.packs_count ?? 0 }}</span>
            </template>
            <template #cell-has_sop="{ row }">
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs" :class="row.has_sop ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-400'">{{ row.has_sop ? 'Ada' : '-' }}</span>
            </template>
            <template #actions="{ row }">
                <div class="flex justify-end gap-1">
                    <Button variant="outline" size="sm" title="Detail" @click="openDetail(row)"><Info /></Button>
                    <Button variant="outline" size="sm" title="Move" @click="openMove(row)"><Move /></Button>
                    <Button variant="outline" size="sm" title="Salin kode + nama" @click="copyRow(row)"><Copy /></Button>
                </div>
            </template>
        </DataTable>

        <AppModal v-model:open="detailOpen" :title="`${detail?.code ?? activeRow?.code ?? ''} — ${detail?.name ?? activeRow?.name ?? 'Detail Product'}`" size="xl">
            <div v-if="detailLoading" class="space-y-2">
                <div v-for="n in 5" :key="n" class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="space-y-3">
                <p class="text-sm italic text-slate-600">{{ detail?.desc ?? activeRow?.desc ?? '-' }}</p>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="downloadZip"><FileArchive /> Unduh ZIP</Button>
                    <Button variant="outline" size="sm" :disabled="!detail?.sop" @click="printSop"><Printer /> Print SOP</Button>
                    <Button variant="outline" size="sm" :disabled="!(detail?.images?.length > 0)" @click="printCollage"><Images /> Collage</Button>
                </div>
                <div class="flex gap-2 border-b text-sm">
                    <button v-for="t in [['sop', 'SOP QC'], ['pl', 'Packing List'], ['images', 'Images'], ['pltbb', 'PLTBB']]" :key="t[0]" class="px-3 py-2" :class="activeTab === t[0] ? 'border-b-2 border-slate-900 font-semibold' : 'text-slate-500'" @click="activeTab = t[0]">{{ t[1] }}</button>
                </div>
                <div v-if="activeTab === 'sop'">
                    <p class="mb-2 text-sm"><b>Target:</b> {{ detail?.sop?.target ?? '-' }}</p>
                    <ol v-if="detail?.sop?.items?.length" class="list-decimal space-y-1 pl-5 text-sm">
                        <li v-for="(it, i) in detail.sop.items" :key="i">{{ it.item ?? it }}</li>
                    </ol>
                    <p v-else class="text-sm text-slate-500">Tidak ada item SOP.</p>
                </div>
                <div v-if="activeTab === 'pl'" class="space-y-3">
                    <div v-if="!detail?.packs?.length" class="text-sm text-slate-500">Tidak ada packing list.</div>
                    <div v-for="(pack, pi) in detail?.packs ?? []" :key="pack.id ?? pi" class="rounded-lg border p-3">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <b class="text-sm">Pack {{ pi + 1 }}: {{ pack.name ?? '-' }}</b>
                            <div class="flex gap-1">
                                <Button variant="outline" size="sm" @click="printPl(pack.id)"><Printer /> Print</Button>
                                <Button variant="outline" size="sm" :disabled="!detail?.sop" @click="printCombined(pack.id)"><Printer /> Combined</Button>
                            </div>
                        </div>
                        <ol class="list-decimal space-y-1 pl-5 text-sm">
                            <li v-for="(it, i) in pack.items ?? []" :key="i">
                                {{ it.item ?? '-' }} — {{ it.qty ?? '' }}
                                <ul v-if="it.items?.length" class="list-disc pl-5 text-slate-600">
                                    <li v-for="(c, j) in it.items" :key="j">{{ c.item ?? '-' }} — {{ c.qty ?? '' }}</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>
                <div v-if="activeTab === 'images'">
                    <div v-if="!detail?.images?.length" class="text-sm text-slate-500">Tidak ada image.</div>
                    <div v-else class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                        <a v-for="(img, i) in detail.images" :key="i" :href="img.url ?? img.name" target="_blank" class="overflow-hidden rounded-lg border">
                            <img :src="img.url ?? img.name" :alt="img.name ?? detail.code" class="h-24 w-full object-cover" loading="lazy" />
                        </a>
                    </div>
                </div>
                <div v-if="activeTab === 'pltbb'" class="text-sm">
                    <p class="mb-2">
                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs" :class="detail?.pltbb?.is_complete ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'">{{ detail?.pltbb?.is_complete ? 'Complete' : 'Not Complete' }}</span>
                    </p>
                    <ul class="space-y-1">
                        <li><b>P:</b> {{ detail?.pltbb?.p ?? '-' }}</li>
                        <li><b>L:</b> {{ detail?.pltbb?.l ?? '-' }}</li>
                        <li><b>T:</b> {{ detail?.pltbb?.t ?? '-' }}</li>
                        <li><b>B:</b> {{ detail?.pltbb?.b ?? '-' }}</li>
                        <li><b>Note:</b> {{ detail?.pltbb?.note ?? '-' }}</li>
                    </ul>
                </div>
            </div>
            <template #footer>
                <Button variant="ghost" @click="detailOpen = false">Tutup</Button>
            </template>
        </AppModal>

        <AppModal v-model:open="moveOpen" :title="`Move: ${activeRow?.code ?? ''}`" size="xl">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <Input :model-value="moveSearchBox" type="search" placeholder="Cari DO / lot / customer..." class="max-w-xs" @input="onMoveSearch" />
                <Button variant="outline" size="sm" @click="copyRows(moveExportRows, ['reference', 'from', 'destination', 'date', 'lot', 'qty', 'doc', 'customer'], 'move')"><Copy /> Salin</Button>
            </div>
            <DataTable
                :columns="moveColumns"
                :rows="moveTable.rows.value"
                :loading="moveLoading"
                :error="moveTable.error.value"
                :page="moveTable.page.value"
                :total-pages="moveTable.totalPages.value"
                :total="moveTable.total.value"
                :per-page="moveTable.perPage.value"
                sortable
                empty-title="Tidak ada data move"
                @update:page="moveTable.setPage"
                @update:per-page="moveTable.setPerPage"
                @sort="moveTable.toggleSort"
            />
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <FormField label="Ringkasan Lot">
                    <Textarea :model-value="moveTable.filtered.value.map((m) => `${m.lot} = ${m.qty} ea`).join(', ')" rows="2" readonly />
                </FormField>
                <FormField label="Keterangan">
                    <Textarea :model-value="`Total ${moveTable.filtered.value.length} move`" rows="2" readonly />
                </FormField>
            </div>
        </AppModal>
    </AppLayout>
</template>
