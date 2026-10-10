<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronRight, House } from '@lucide/vue';

const props = defineProps({
    /** Daftar crumb: { label, href?, current? } (item terakhir otomatis current). Masih toleran bentuk lama { name, url, active }. */
    items: { type: Array, default: () => [] },
    /** URL Home di depan. Samakan Blade: route('home') = '/'. */
    homeHref: { type: String, default: '/' },
    homeLabel: { type: String, default: 'Home' },
    showHome: { type: Boolean, default: true },
});

const normalized = computed(() =>
    (props.items ?? [])
        .map((raw, index, arr) => {
            const label = raw?.label ?? raw?.name ?? '';
            const href = raw?.href ?? raw?.url ?? null;
            let current;
            if (typeof raw?.current === 'boolean') {
                current = raw.current;
            } else if (typeof raw?.active === 'boolean') {
                current = !raw.active;
            } else {
                current = index === arr.length - 1;
            }
            return { label, href, current, clickable: !current && !!href };
        })
        .filter((item) => item.label !== ''),
);
</script>

<template>
    <nav v-if="showHome || normalized.length" aria-label="breadcrumb">
        <ol class="flex flex-wrap items-center gap-1 text-sm">
            <li v-if="showHome" class="flex items-center gap-1">
                <Link
                    v-if="normalized.length"
                    :href="homeHref"
                    class="flex items-center gap-1 text-muted-foreground transition-colors hover:text-primary"
                >
                    <House class="size-3.5" aria-hidden="true" />
                    {{ homeLabel }}
                </Link>
                <span v-else aria-current="page" class="flex items-center gap-1 font-medium text-foreground">
                    <House class="size-3.5" aria-hidden="true" />
                    {{ homeLabel }}
                </span>
            </li>
            <li
                v-for="(item, i) in normalized"
                :key="`${item.label}-${i}`"
                class="flex min-w-0 items-center gap-1"
            >
                <ChevronRight class="size-3.5 shrink-0 text-muted-foreground" aria-hidden="true" />
                <Link
                    v-if="item.clickable"
                    :href="item.href"
                    class="max-w-56 truncate text-muted-foreground transition-colors hover:text-primary"
                >
                    {{ item.label }}
                </Link>
                <span v-else aria-current="page" class="max-w-56 truncate font-medium text-foreground">
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>
