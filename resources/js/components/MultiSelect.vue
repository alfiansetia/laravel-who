<script setup>
import { computed, ref } from 'vue';
import { Check, ChevronsUpDown } from '@lucide/vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Pilih...' },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const keyword = ref('');

const selectedSet = computed(() => new Set(props.modelValue.map(String)));

const filtered = computed(() => {
    const q = keyword.value.trim().toLowerCase();
    if (!q) {
        return props.options;
    }
    return props.options.filter((o) =>
        String(o[props.labelKey] ?? o.label ?? o.name ?? '').toLowerCase().includes(q),
    );
});

const triggerLabel = computed(() => {
    if (props.modelValue.length === 0) {
        return props.placeholder;
    }
    if (props.modelValue.length === 1) {
        const found = props.options.find((o) => String(o[props.valueKey] ?? o.value) === String(props.modelValue[0]));
        return found ? String(found[props.labelKey] ?? found.label) : `${props.modelValue.length} dipilih`;
    }
    return `${props.modelValue.length} dipilih`;
});

function toggleValue(opt) {
    const key = String(opt[props.valueKey] ?? opt.value ?? opt.id);
    const set = new Set(props.modelValue.map(String));
    if (set.has(key)) {
        set.delete(key);
    } else {
        set.add(key);
    }
    emit('update:modelValue', [...set]);
}

function selectAll() {
    emit(
        'update:modelValue',
        filtered.value.map((o) => String(o[props.valueKey] ?? o.value ?? o.id)),
    );
}

function clearAll() {
    emit('update:modelValue', []);
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-background px-3 text-sm shadow-sm"
            @click="open = !open"
        >
            <span class="truncate" :class="modelValue.length === 0 && 'text-muted-foreground'">{{ triggerLabel }}</span>
            <span class="flex shrink-0 items-center gap-1">
                <span
                    v-if="modelValue.length > 0"
                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-semibold text-primary-foreground"
                >
                    {{ modelValue.length }}
                </span>
                <ChevronsUpDown class="size-4 text-muted-foreground" />
            </span>
        </button>
        <div v-if="open" class="absolute z-50 mt-1 w-full rounded-md border bg-white p-1 shadow-lg">
            <input
                v-model="keyword"
                placeholder="Cari..."
                class="mb-1 h-8 w-full rounded border px-2 text-sm"
            />
            <div class="mb-1 flex gap-1 px-1">
                <button type="button" class="text-xs text-primary hover:underline" @click="selectAll">Pilih semua</button>
                <span class="text-xs text-muted-foreground">·</span>
                <button type="button" class="text-xs text-muted-foreground hover:underline" @click="clearAll">Hapus</button>
                <button type="button" class="ml-auto text-xs text-muted-foreground hover:underline" @click="open = false">Tutup</button>
            </div>
            <div class="max-h-56 overflow-y-auto">
                <button
                    v-for="opt in filtered"
                    :key="String(opt[valueKey] ?? opt.value ?? opt.id)"
                    type="button"
                    class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-sm hover:bg-accent"
                    @click="toggleValue(opt)"
                >
                    <span
                        class="flex size-4 shrink-0 items-center justify-center rounded border"
                        :class="selectedSet.has(String(opt[valueKey] ?? opt.value ?? opt.id)) ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-white'"
                    >
                        <Check v-if="selectedSet.has(String(opt[valueKey] ?? opt.value ?? opt.id))" class="size-3" />
                    </span>
                    <span class="truncate">{{ opt[labelKey] ?? opt.label ?? opt.name }}</span>
                </button>
                <p v-if="filtered.length === 0" class="px-2 py-3 text-center text-xs text-muted-foreground">Data tidak ditemukan.</p>
            </div>
        </div>
    </div>
</template>
