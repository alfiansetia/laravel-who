<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Check, ChevronsUpDown } from '@lucide/vue';

const props = defineProps({
    modelValue: { type: [String, Number, null], default: '' },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Pilih...' },
    searchPlaceholder: { type: String, default: 'Ketik untuk mencari...' },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    debounce: { type: Number, default: 300 },
    minLength: { type: Number, default: 1 },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'search']);

const open = ref(false);
const keyword = ref('');
let searchTimer = null;

function cancelPendingSearch() {
    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }
}

function onSearch(e) {
    keyword.value = e.target.value;
    cancelPendingSearch();
    const val = keyword.value;
    if (val.length > 0 && val.length < props.minLength) {
        return;
    }
    searchTimer = setTimeout(() => {
        searchTimer = null;
        if (!open.value) {
            return;
        }
        emit('search', val);
    }, props.debounce);
}

const filtered = computed(() => {
    const q = keyword.value.trim().toLowerCase();
    if (!q) {
        return props.options;
    }
    return props.options.filter((o) => String(o[props.labelKey] ?? o.label ?? o.name ?? '').toLowerCase().includes(q));
});

const selectedLabel = computed(() => {
    const found = props.options.find((o) => String(o[props.valueKey] ?? o.value ?? o.id) === String(props.modelValue));
    return found ? (found[props.labelKey] ?? found.label ?? found.name) : '';
});

function choose(opt) {
    cancelPendingSearch();
    emit('update:modelValue', opt[props.valueKey] ?? opt.value ?? opt.id);
    open.value = false;
    keyword.value = '';
}

function clearSelection() {
    cancelPendingSearch();
    emit('update:modelValue', '');
    open.value = false;
    keyword.value = '';
}

watch(open, (v) => {
    if (!v) {
        cancelPendingSearch();
        keyword.value = '';
    }
});

onBeforeUnmount(() => cancelPendingSearch());
</script>

<template>
    <div class="relative">
        <button
            type="button"
            :disabled="disabled"
            class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-background px-3 text-sm shadow-sm disabled:opacity-50"
            @click="open = !open"
        >
            <span class="truncate" :class="selectedLabel ? '' : 'text-muted-foreground'">{{ selectedLabel || placeholder }}</span>
            <ChevronsUpDown class="size-4 shrink-0 text-muted-foreground" />
        </button>
        <div v-if="open" class="absolute z-50 mt-1 w-full rounded-md border bg-white p-1 shadow-lg">
            <input
                :placeholder="searchPlaceholder"
                class="mb-1 h-8 w-full rounded border px-2 text-sm"
                @input="onSearch"
            />
            <div v-if="loading" class="px-1 py-1">
                <div v-for="n in 3" :key="n" class="mb-1 h-8 animate-pulse rounded bg-slate-100" />
            </div>
            <div v-else class="max-h-56 overflow-y-auto">
                <button
                    v-if="clearable && modelValue !== ''"
                    type="button"
                    class="w-full rounded px-2 py-1.5 text-left text-xs text-muted-foreground hover:bg-accent"
                    @click="clearSelection"
                >
                    Hapus pilihan
                </button>
                <button
                    v-for="opt in filtered"
                    :key="String(opt[valueKey] ?? opt.value ?? opt.id)"
                    type="button"
                    class="flex w-full items-center justify-between rounded px-2 py-1.5 text-left text-sm hover:bg-accent"
                    @click="choose(opt)"
                >
                    <span class="truncate">{{ opt[labelKey] ?? opt.label ?? opt.name }}</span>
                    <Check v-if="String(opt[valueKey] ?? opt.value ?? opt.id) === String(modelValue)" class="size-4" />
                </button>
                <p v-if="filtered.length === 0" class="px-2 py-3 text-center text-xs text-muted-foreground">Data tidak ditemukan.</p>
            </div>
        </div>
        <p v-if="error" class="mt-1 text-xs text-destructive">{{ error }}</p>
    </div>
</template>
