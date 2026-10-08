<script setup>
import { computed, ref, watch } from 'vue';
import { ChevronRight, Download, FileText, Folder, Search, Upload } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';

const props = defineProps({
    title: { type: String, default: 'File Search Manager' },
    lastUpdated: { type: String, default: null },
});

const toast = useToast();

const items = ref([]);
const loading = ref(false);
const fetchError = ref('');
const lastUpdatedAt = ref(props.lastUpdated);

const searchBox = ref('');
const typeFilter = ref('all');
const caseSensitive = ref(false);
const currentPath = ref('');

const page = ref(1);
const perPage = ref(50);
const perPageOptions = [25, 50, 100, 250];

const uploadOpen = ref(false);
const selectedFile = ref(null);
const uploading = ref(false);
const uploadError = ref('');

const isSearching = computed(() => searchBox.value.trim() !== '');

const globalStats = computed(() => {
    const folders = items.value.filter((i) => i.type === 'folder').length;
    return { folders, files: items.value.length - folders, total: items.value.length };
});

function matchHaystack(item) {
    const name = caseSensitive.value ? (item.name ?? '') : (item.name ?? '').toLowerCase();
    const path = caseSensitive.value ? (item.path ?? '') : (item.path ?? '').toLowerCase();
    return { name, path };
}

const viewItems = computed(() => {
    const query = caseSensitive.value ? searchBox.value.trim() : searchBox.value.trim().toLowerCase();
    let list;
    if (!query) {
        list = items.value.filter((item) => {
            const path = item.path ?? '';
            if (currentPath.value === '') {
                return !path.includes('/');
            }
            const prefix = `${currentPath.value}/`;
            if (!path.startsWith(prefix) || path === currentPath.value) {
                return false;
            }
            return !path.slice(prefix.length).includes('/');
        });
    } else {
        const keywords = query.split(' ').filter(Boolean);
        list = items.value.filter((item) => {
            if (typeFilter.value !== 'all' && item.type !== typeFilter.value) {
                return false;
            }
            const { name, path } = matchHaystack(item);
            return keywords.every((kw) => name.includes(kw) || path.includes(kw));
        });
    }
    return [...list].sort((a, b) => (b.type === 'folder') - (a.type === 'folder'));
});

const totalPages = computed(() => Math.max(1, Math.ceil(viewItems.value.length / perPage.value)));

const pagedItems = computed(() => {
    const start = (page.value - 1) * perPage.value;
    return viewItems.value.slice(start, start + perPage.value);
});

const viewStats = computed(() => {
    const folders = viewItems.value.filter((i) => i.type === 'folder').length;
    return { folders, files: viewItems.value.length - folders };
});

const breadcrumbs = computed(() => {
    if (currentPath.value === '') {
        return [];
    }
    const segments = currentPath.value.split('/');
    return segments.map((seg, i) => ({ label: seg, path: segments.slice(0, i + 1).join('/') }));
});

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'type', label: 'Tipe', align: 'center' },
    { key: 'last_modified', label: 'Terakhir Diubah', align: 'right' },
];

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlight(text) {
    const safe = escapeHtml(text);
    const query = searchBox.value.trim();
    if (!isSearching.value || !query) {
        return safe;
    }
    const flags = caseSensitive.value ? 'g' : 'gi';
    const keywords = [...new Set(query.split(' ').filter(Boolean))];
    let out = safe;
    for (const kw of keywords) {
        out = out.replace(new RegExp(`(${escapeRegExp(escapeHtml(kw))})`, flags), '<b class="text-primary">$1</b>');
    }
    return out;
}

function goToPath(path) {
    currentPath.value = path;
    searchBox.value = '';
    page.value = 1;
}

function onRowClick(row) {
    if (row.type === 'folder') {
        goToPath(row.path);
    }
}

function setTypeFilter(type) {
    typeFilter.value = type;
    page.value = 1;
}

async function fetchData() {
    loading.value = true;
    fetchError.value = '';
    try {
        const res = await api.get('/tools/file-search/data', { baseURL: '/' });
        items.value = Array.isArray(res.data) ? res.data : [];
    } catch (e) {
        items.value = [];
        fetchError.value = e.response?.status === 404
            ? 'Index tidak ditemukan. Unggah file index JSON.'
            : (e.response?.data?.message ?? 'Gagal memuat index.');
    } finally {
        loading.value = false;
    }
}

function openUpload() {
    selectedFile.value = null;
    uploadError.value = '';
    uploadOpen.value = true;
}

function onFilePick(e) {
    selectedFile.value = e.target.files?.[0] ?? null;
    uploadError.value = '';
}

async function submitUpload() {
    if (!selectedFile.value) {
        uploadError.value = 'Pilih berkas JSON dulu.';
        return;
    }
    uploading.value = true;
    uploadError.value = '';
    try {
        const form = new FormData();
        form.append('json_file', selectedFile.value);
        const res = await api.post('/tools/file-search/upload', form, { baseURL: '/' });
        lastUpdatedAt.value = res.data?.last_updated ?? lastUpdatedAt.value;
        uploadOpen.value = false;
        toast.success(res.data?.message ?? 'File index berhasil diperbarui.');
        fetchData();
    } catch (e) {
        if (e.response?.status === 422) {
            uploadError.value = e.response.data?.errors?.json_file?.[0] ?? e.response.data?.message ?? 'Berkas tidak valid.';
        } else {
            uploadError.value = e.response?.data?.message ?? 'Gagal mengunggah file.';
        }
    } finally {
        uploading.value = false;
    }
}

watch([searchBox, typeFilter, caseSensitive], () => {
    page.value = 1;
});

fetchData();
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Pencarian file berbasis index JSON">
            <template #actions>
                <Button variant="outline" size="sm" as="a" href="/tools/file-search/download-script"><Download /> Script</Button>
                <Button size="sm" @click="openUpload"><Upload /> Update Index</Button>
            </template>
        </PageHeader>

        <div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
            <span>Terakhir diperbarui: <b>{{ lastUpdatedAt ?? 'Belum pernah' }}</b></span>
            <span aria-hidden="true">•</span>
            <span>{{ globalStats.folders }} folder, {{ globalStats.files }} file</span>
        </div>

        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="searchBox" type="search" placeholder="Cari file atau folder dalam index..." class="pl-9" />
            </div>
            <div class="flex items-center gap-1 rounded-md border p-1">
                <Button
                    v-for="opt in [{ value: 'all', label: 'Semua' }, { value: 'folder', label: 'Folder' }, { value: 'file', label: 'File' }]"
                    :key="opt.value"
                    :variant="typeFilter === opt.value ? 'default' : 'ghost'"
                    size="sm"
                    @click="setTypeFilter(opt.value)"
                >
                    {{ opt.label }}
                </Button>
            </div>
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input v-model="caseSensitive" type="checkbox" class="size-4 accent-primary" />
                Case Sensitive
            </label>
        </div>

        <div class="mb-3 flex flex-wrap items-center gap-2 text-sm">
            <nav class="flex min-w-0 flex-1 flex-wrap items-center gap-1">
                <button type="button" class="font-medium text-primary hover:underline" @click="goToPath('')">Root</button>
                <template v-for="crumb in breadcrumbs" :key="crumb.path">
                    <ChevronRight class="size-4 shrink-0 text-muted-foreground" />
                    <button type="button" class="font-medium text-primary hover:underline" @click="goToPath(crumb.path)">{{ crumb.label }}</button>
                </template>
                <template v-if="isSearching">
                    <ChevronRight class="size-4 shrink-0 text-muted-foreground" />
                    <span class="text-muted-foreground">Search</span>
                </template>
            </nav>
            <span
                class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium"
                :class="isSearching ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-blue-200 bg-blue-50 text-blue-700'"
            >
                {{ isSearching ? 'Global Search' : 'Browse Mode' }}
            </span>
        </div>

        <DataTable
            :columns="columns"
            :rows="pagedItems"
            :loading="loading"
            :error="fetchError"
            :page="page"
            :total-pages="totalPages"
            :total="viewItems.length"
            :per-page="perPage"
            :per-page-options="perPageOptions"
            row-key="path"
            clickable
            :empty-title="isSearching ? 'Hasil tidak ditemukan' : 'Folder kosong'"
            empty-message="Ubah kata kunci atau filter tipe."
            @update:page="page = $event"
            @update:per-page="perPage = Number($event); page = 1"
            @row-click="onRowClick"
        >
            <template #cell-name="{ row }">
                <div class="flex items-start gap-2">
                    <Folder v-if="row.type === 'folder'" class="mt-0.5 size-4 shrink-0 text-amber-500" />
                    <FileText v-else class="mt-0.5 size-4 shrink-0 text-blue-500" />
                    <div class="min-w-0">
                        <div class="whitespace-normal font-semibold" v-html="highlight(row.name)" />
                        <div class="whitespace-normal break-all text-xs text-muted-foreground" v-html="highlight(row.path)" />
                    </div>
                </div>
            </template>
            <template #cell-type="{ row }">
                <span
                    class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium"
                    :class="row.type === 'folder' ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-blue-200 bg-blue-50 text-blue-700'"
                >
                    {{ row.type === 'folder' ? 'Folder' : 'File' }}
                </span>
            </template>
            <template #cell-last_modified="{ row }">{{ row.last_modified || '-' }}</template>
        </DataTable>

        <p class="mt-2 text-xs text-muted-foreground">
            {{ viewItems.length }} item ({{ viewStats.folders }} folder, {{ viewStats.files }} file) • Sumber index: {{ items.length > 0 ? 'file-index.json' : 'belum diunggah' }}
        </p>

        <AppModal v-model:open="uploadOpen" title="Update Index" size="md">
            <p class="text-sm text-muted-foreground">Jalankan <code class="rounded bg-slate-100 px-1 font-mono text-xs">deployment/generate-index.bat</code> di mesin target, lalu unggah berkas JSON yang dihasilkan.</p>
            <FormField label="Berkas JSON" :error="uploadError" required>
                <Input type="file" accept=".json" :disabled="uploading" @change="onFilePick" />
            </FormField>
            <div v-if="uploading" class="flex items-center gap-2 text-sm text-muted-foreground">
                <span class="size-4 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                Memproses file index...
            </div>
            <template #footer>
                <Button variant="ghost" :disabled="uploading" @click="uploadOpen = false">Batal</Button>
                <Button :disabled="uploading || !selectedFile" @click="submitUpload"><Upload /> {{ uploading ? 'Mengunggah...' : 'Upload' }}</Button>
            </template>
        </AppModal>
    </AppLayout>
</template>
