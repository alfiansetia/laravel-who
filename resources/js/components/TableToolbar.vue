<script setup>
import { computed, ref, useSlots } from 'vue';
import { Filter, RotateCcw } from '@lucide/vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';

// Pola toolbar tunggal semua halaman list (pengganti FilterPanel):
// - Search selalu terlihat (di luar panel, bukan di dalam filter).
// - Filter tambahan (select, tanggal, dsb.) default TERSEMBUNYI di Card collapsible.
// - Tombol filter = ikon saja, sejajar Refresh di baris yang sama.
// - Halaman tanpa slot #filters: tombol filter otomatis disembunyikan.
const props = defineProps({
    placeholder: { type: String, default: 'Cari...' },
    activeCount: { type: Number, default: 0 },
    defaultOpen: { type: Boolean, default: false },
    gridClass: { type: String, default: 'grid gap-3 md:grid-cols-2 xl:grid-cols-3' },
    searchClass: { type: String, default: 'h-9 max-w-xs' },
});

const emit = defineEmits(['search', 'reset']);

// v-model search = ref lokal halaman (mis. searchBox). Fetch/debounce tetap
// tugas halaman via @search="query.setSearch" — JANGAN bind ke query.search.value.
const search = defineModel({ type: [String, Number], default: '' });

const slots = useSlots();
const hasFilters = computed(() => Boolean(slots.filters));
const open = ref(props.defaultOpen);

function toggle() {
    open.value = !open.value;
}
</script>

<template>
    <div class="mb-3 space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <Input
                v-model="search"
                type="search"
                :placeholder="placeholder"
                :class="searchClass"
                @update:model-value="emit('search', $event)"
            />
            <span v-if="hasFilters" class="relative inline-flex">
                <Button
                    type="button"
                    :variant="open ? 'secondary' : 'outline'"
                    size="icon"
                    :title="open ? 'Sembunyikan filter' : 'Tampilkan filter'"
                    :aria-label="open ? 'Sembunyikan filter' : 'Tampilkan filter'"
                    :aria-expanded="open"
                    @click="toggle"
                >
                    <Filter />
                </Button>
                <span
                    v-if="activeCount > 0"
                    class="pointer-events-none absolute -right-1.5 -top-1.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-semibold text-primary-foreground"
                >
                    {{ activeCount }}
                </span>
            </span>
            <slot />
        </div>
        <Card v-if="hasFilters" v-show="open">
            <div class="px-3 py-3">
                <div :class="gridClass">
                    <slot name="filters" />
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <slot name="filterActions" />
                    <Button variant="ghost" size="sm" @click="emit('reset')">
                        <RotateCcw /> Reset
                    </Button>
                </div>
            </div>
        </Card>
    </div>
</template>
