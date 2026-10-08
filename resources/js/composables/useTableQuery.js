import { ref, watch } from 'vue';

export function useTableQuery(fetcher, initial = {}) {
    const page = ref(initial.page ?? 1);
    const perPage = ref(initial.perPage ?? 10);
    const search = ref(initial.search ?? '');
    const filters = ref({ ...(initial.filters ?? {}) });
    const rows = ref([]);
    const total = ref(0);
    const totalPages = ref(1);
    const loading = ref(false);
    const error = ref('');

    let cancelPrev = false;
    let debounce = null;
    let filterDebounce = null;

    async function fetch() {
        loading.value = true;
        error.value = '';
        cancelPrev = true;
        const myRun = (() => {
            let alive = true;
            return { cancel: () => { alive = false; }, get alive() { return alive; } };
        })();
        const currentCancel = cancelPrev;
        cancelPrev = false;
        try {
            const params = { page: page.value, per_page: perPage.value, search: search.value, ...filters.value };
            const res = await fetcher(params);
            const body = res?.data ?? res;
            rows.value = body.data ?? [];
            total.value = Number(body.total ?? rows.value.length);
            const tp = Number(body.total_pages ?? 1);
            totalPages.value = tp > 0 ? tp : 1;
            if (body.page) {
                page.value = Number(body.page);
            }
        } catch (e) {
            error.value = e?.response?.data?.message ?? 'Gagal memuat data.';
            rows.value = [];
        } finally {
            loading.value = false;
        }
    }

    function setSearch(value, ms = 1000) {
        clearTimeout(debounce);
        if (ms <= 0) {
            search.value = value;
            page.value = 1;
            fetch();
            return;
        }
        debounce = setTimeout(() => {
            search.value = value;
            page.value = 1;
            fetch();
        }, ms);
    }

    function setPage(value) {
        page.value = value;
        fetch();
    }

    function setPerPage(value) {
        perPage.value = Number(value);
        page.value = 1;
        fetch();
    }

    function setFilters(value, ms = 0) {
        clearTimeout(filterDebounce);
        if (ms <= 0) {
            filters.value = { ...filters.value, ...value };
            page.value = 1;
            fetch();
            return;
        }
        filterDebounce = setTimeout(() => {
            filters.value = { ...filters.value, ...value };
            page.value = 1;
            fetch();
        }, ms);
    }

    watch([page, perPage], () => {});

    return { page, perPage, search, filters, rows, total, totalPages, loading, error, fetch, setSearch, setPage, setPerPage, setFilters };
}
