import { ref } from 'vue';

export function useSearchable(fetcher, options = {}) {
    const optionsList = ref(options.initial ?? []);
    const loading = ref(false);
    let debounce = null;

    async function search(keyword) {
        clearTimeout(debounce);
        return new Promise((resolve) => {
            debounce = setTimeout(async () => {
                if ((keyword ?? '').length < (options.minLength ?? 0)) {
                    resolve(optionsList.value);
                    return;
                }
                loading.value = true;
                try {
                    const res = await fetcher(keyword);
                    const body = res?.data ?? res;
                    optionsList.value = body.data ?? body ?? [];
                } catch {
                    optionsList.value = [];
                } finally {
                    loading.value = false;
                    resolve(optionsList.value);
                }
            }, options.debounce ?? 300);
        });
    }

    return { options: optionsList, loading, search };
}
