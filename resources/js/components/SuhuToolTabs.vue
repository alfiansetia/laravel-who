<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Navigasi silang antar form suhu/pembersihan (Form Ruangan,
// Pembersihan Palet). Tanpa props — tab aktif diturunkan dari
// URL Inertia saat ini.
// Pemakaian (di bawah PageHeader tiap page form):
//   <SuhuToolTabs class="mb-4" />

const page = usePage();

const tabs = [
    { label: 'Form Ruangan', href: '/tools/suhu' },
    { label: 'Pembersihan Palet', href: '/tools/pembersihan' },
];

const currentPath = computed(() => String(page.url ?? '').split('?')[0].replace(/\/$/, ''));

function isActive(href) {
    return currentPath.value === href;
}
</script>

<template>
    <nav aria-label="Navigasi form suhu" class="max-w-full overflow-x-auto">
        <div
            role="tablist"
            class="inline-flex h-9 min-w-max items-center justify-center gap-1 rounded-lg bg-slate-100 p-1 text-sm font-medium text-muted-foreground"
        >
            <Link
                v-for="t in tabs"
                :key="t.href"
                :href="t.href"
                role="tab"
                :aria-selected="isActive(t.href)"
                :aria-current="isActive(t.href) ? 'page' : undefined"
                :class="[
                    'inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1 transition-colors focus-visible:outline-none',
                    isActive(t.href) ? 'bg-white text-foreground shadow-sm' : 'hover:text-foreground',
                ]"
            >
                {{ t.label }}
            </Link>
        </div>
    </nav>
</template>
