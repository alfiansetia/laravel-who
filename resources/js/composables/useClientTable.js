import { computed, ref } from 'vue';

export function useClientTable(fetcher = null, options = {}) {
    const allRows = ref([]);
    const page = ref(1);
    const perPage = ref(options.perPage ?? 10);
    const search = ref('');
    const sortKey = ref('');
    const sortDir = ref('asc');
    const loading = ref(false);
    const error = ref('');
    let searchDebounce = null;

    const searchKeys = options.searchKeys ?? [];

    async function fetch(params = {}) {
        if (typeof fetcher !== 'function') {
            return;
        }
        loading.value = true;
        error.value = '';
        try {
            const res = await fetcher(params);
            const body = res?.data ?? res;
            const data = body.data ?? body;
            allRows.value = Array.isArray(data) ? data : (data.data ?? []);
            page.value = 1;
        } catch (e) {
            error.value = e?.response?.data?.message ?? 'Gagal memuat data.';
            allRows.value = [];
        } finally {
            loading.value = false;
        }
    }

    const filtered = computed(() => {
        const q = search.value.trim().toLowerCase();
        let out = [...allRows.value];
        if (q) {
            out = out.filter((row) => {
                const keys = searchKeys.length > 0 ? searchKeys : Object.keys(row ?? {});
                return keys.some((k) => String(row?.[k] ?? '').toLowerCase().includes(q));
            });
        }
        if (sortKey.value) {
            out.sort((a, b) => {
                const av = a?.[sortKey.value];
                const bv = b?.[sortKey.value];
                const cmp = String(av ?? '').localeCompare(String(bv ?? ''), 'id-ID', { numeric: true });
                return sortDir.value === 'desc' ? -cmp : cmp;
            });
        }
        return out;
    });

    const total = computed(() => filtered.value.length);
    const totalPages = computed(() => Math.max(1, Math.ceil(total.value / perPage.value)));
    const rows = computed(() => {
        const start = (page.value - 1) * perPage.value;
        return filtered.value.slice(start, start + perPage.value);
    });

    function setPage(v) {
        page.value = Math.min(Math.max(1, Number(v)), totalPages.value);
    }

    function setSearch(value, ms = 1000) {
        clearTimeout(searchDebounce);
        if (ms <= 0) {
            search.value = value;
            page.value = 1;
            return;
        }
        searchDebounce = setTimeout(() => {
            search.value = value;
            page.value = 1;
        }, ms);
    }

    function setPerPage(v) {
        perPage.value = Number(v);
        page.value = 1;
    }

    function setRows(rows) {
        allRows.value = Array.isArray(rows) ? rows : [];
        error.value = '';
        page.value = 1;
    }

    function toggleSort(key) {
        if (sortKey.value !== key) {
            sortKey.value = key;
            sortDir.value = 'asc';
        } else {
            sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
        }
    }

    return { allRows, rows, page, perPage, search, sortKey, sortDir, loading, error, total, totalPages, fetch, setRows, setPage, setPerPage, setSearch, toggleSort, filtered };
}
