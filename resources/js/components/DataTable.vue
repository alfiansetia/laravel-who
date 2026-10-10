<script setup>
import { computed, ref } from 'vue';
import { ChevronLeft, ChevronRight, ChevronsUpDown } from '@lucide/vue';
import Button from '@/components/ui/Button.vue';
import EmptyState from '@/components/EmptyState.vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
    rowKey: { type: String, default: 'id' },
    selectable: { type: Boolean, default: false },
    selected: { type: Array, default: () => [] },
    page: { type: Number, default: 1 },
    totalPages: { type: Number, default: 1 },
    total: { type: Number, default: 0 },
    perPage: { type: Number, default: 10 },
    perPageOptions: { type: Array, default: () => [10, 25, 50, 100] },
    emptyTitle: { type: String, default: 'Tidak ada data' },
    emptyMessage: { type: String, default: '' },
    sortable: { type: Boolean, default: false },
    clickable: { type: Boolean, default: false },
    showFooter: { type: Boolean, default: true },
});

const emit = defineEmits(['update:page', 'update:perPage', 'update:selected', 'sort', 'row-click']);

const start = computed(() => (props.total === 0 ? 0 : (props.page - 1) * props.perPage + 1));
const end = computed(() => Math.min(props.page * props.perPage, props.total));

const allChecked = computed(() => props.rows.length > 0 && props.rows.every((r) => props.selected.includes(String(r[props.rowKey] ?? r.id))));

function toggleAll(e) {
    if (e.target.checked) {
        emit('update:selected', props.rows.map((r) => String(r[props.rowKey] ?? r.id)));
    } else {
        emit('update:selected', []);
    }
}

function toggleOne(id, e) {
    const key = String(id);
    const set = new Set(props.selected.map(String));
    if (e.target.checked) {
        set.add(key);
    } else {
        set.delete(key);
    }
    emit('update:selected', [...set]);
}
</script>

<template>
    <div class="max-w-full overflow-hidden rounded-lg border bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-slate-50 text-left">
                        <th v-if="selectable" class="w-10 px-3 py-2">
                            <input type="checkbox" :checked="allChecked" @change="toggleAll" />
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                            :class="[(col.align === 'center' ? 'text-center' : col.align === 'right' ? 'text-right' : ''), col.wrap ? '' : 'whitespace-nowrap']"
                        >
                            <button
                                v-if="sortable && col.sortable !== false"
                                type="button"
                                class="inline-flex items-center gap-1 uppercase"
                                @click="emit('sort', col.key)"
                            >
                                {{ col.label }} <ChevronsUpDown class="size-3" />
                            </button>
                            <span v-else>{{ col.label }}</span>
                        </th>
                        <th v-if="$slots.actions" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading" v-for="n in 5" :key="'skel-' + n" class="border-b">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-3 py-2">
                            <div class="h-4 animate-pulse rounded bg-slate-100" />
                        </td>
                    </tr>
                    <template v-else-if="rows.length > 0">
                        <tr v-for="row in rows" :key="row[rowKey] ?? row.id" class="border-b transition-colors last:border-0 hover:bg-slate-50" :class="clickable && 'cursor-pointer'" :title="clickable ? 'Klik untuk detail' : undefined" @click="clickable && emit('row-click', row)">
                            <td v-if="selectable" class="px-3 py-2">
                                <input
                                    type="checkbox"
                                    :checked="selected.map(String).includes(String(row[rowKey] ?? row.id))"
                                    @change="toggleOne(row[rowKey] ?? row.id, $event)"
                                />
                            </td>
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="px-3 py-2"
                                :class="cn(col.align === 'center' && 'text-center', col.align === 'right' && 'text-right', col.mono && 'font-mono text-xs', col.wrap ? 'whitespace-normal break-words' : 'whitespace-nowrap')"
                                :style="col.wrap && col.maxWidth ? { maxWidth: col.maxWidth } : undefined"
                            >
                                <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                                    {{ row[col.key] ?? '-' }}
                                </slot>
                            </td>
                            <td v-if="$slots.actions" class="px-3 py-2 text-right" @click.stop>
                                <slot name="actions" :row="row" />
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)">
                            <EmptyState :title="error || emptyTitle" :message="error ? '' : emptyMessage" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="showFooter" class="flex flex-wrap items-center justify-between gap-3 border-t px-3 py-2.5">
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <span>Tampilkan</span>
                <select
                    :value="perPage"
                    class="rounded-md border px-2 py-1 text-xs"
                    @change="emit('update:perPage', $event.target.value)"
                >
                    <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <span>{{ total > 0 ? `Menampilkan ${start}–${end} dari ${Number(total).toLocaleString('id-ID')} data` : 'Tidak ada data' }}</span>
            </div>
            <div v-if="totalPages > 1" class="flex items-center gap-2">
                <Button variant="outline" size="sm" :disabled="page <= 1" @click="emit('update:page', page - 1)">
                    <ChevronLeft class="size-4" /> <span class="hidden sm:inline">Sebelumnya</span>
                </Button>
                <span class="text-xs text-muted-foreground">Halaman {{ page }} dari {{ totalPages }}</span>
                <Button variant="outline" size="sm" :disabled="page >= totalPages" @click="emit('update:page', page + 1)">
                    <span class="hidden sm:inline">Berikutnya</span> <ChevronRight class="size-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
