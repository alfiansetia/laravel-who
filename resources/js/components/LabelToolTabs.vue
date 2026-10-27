<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Navigasi silang antar alat label/cetak (Tempelan Dus, AKL Unit,
// AKL Kemasan, Kartu Stok, Print Label). Tanpa props — tab aktif
// diturunkan dari URL Inertia saat ini.
// Pemakaian (di bawah PageHeader tiap page label):
//   <LabelToolTabs class="mb-4" />

const page = usePage();

const tabs = [
    { label: 'Dus', href: '/tools/tempelan-dus' },
    { label: 'AKL Unit', href: '/tools/tempelan-akl-unit' },
    { label: 'AKL Kemasan', href: '/tools/tempelan-akl-kemasan' },
    { label: 'Kartu Stok', href: '/tools/stiker-kartu-stok' },
    { label: 'Print Label', href: '/tools/print-label' },
];

const currentPath = computed(() => String(page.url ?? '').split('?')[0].replace(/\/$/, ''));

function isActive(href) {
    return currentPath.value === href;
}
</script>

<template>
    <nav aria-label="Navigasi alat label" class="max-w-full overflow-x-auto">
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
