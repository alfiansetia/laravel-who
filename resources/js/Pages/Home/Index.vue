<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Search } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import EmptyState from '@/components/EmptyState.vue';
import Input from '@/components/ui/Input.vue';
import { menuIcon } from '@/lib/menuIcons';

const props = defineProps({
    title: { type: String, default: 'Home' },
    sections: { type: Array, default: () => [] },
});

const keyword = ref('');

const totalMenus = computed(() => props.sections.reduce((n, section) => n + (section.menus?.length ?? 0), 0));

const filteredSections = computed(() => {
    const q = keyword.value.trim().toLowerCase();
    return props.sections
        .map((section) => ({
            ...section,
            menus: section.menus.filter(
                (menu) =>
                    menu.title.toLowerCase().includes(q) ||
                    menu.desc.toLowerCase().includes(q),
            ),
        }))
        .filter((section) => section.menus.length > 0);
});

const resultCount = computed(() => filteredSections.value.reduce((n, section) => n + section.menus.length, 0));
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-6xl space-y-8">
            <div class="space-y-3">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">Selamat Datang Kembali</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Akses cepat ke {{ totalMenus }} modul dan peralatan kerja Anda</p>
                </div>
                <div class="relative max-w-xl">
                    <Search class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="keyword"
                        type="search"
                        autofocus
                        placeholder="Cari menu atau aplikasi (misal: Stock, PO, STT)..."
                        class="h-11 rounded-full pl-11"
                    />
                </div>
                <p v-if="keyword.trim()" class="text-xs text-muted-foreground">
                    Menampilkan {{ resultCount }} hasil untuk &ldquo;{{ keyword.trim() }}&rdquo;
                </p>
            </div>

            <section v-for="section in filteredSections" :key="section.title">
                <div class="mb-3 flex items-center gap-2.5">
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg"
                        :style="{ backgroundColor: section.accent + '1a', color: section.accent }"
                    >
                        <component :is="menuIcon(section.icon)" class="size-4" />
                    </span>
                    <h2 class="text-sm font-semibold">{{ section.title }}</h2>
                    <span class="rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                        {{ section.menus.length }}
                    </span>
                    <span class="ml-1 h-px flex-1 bg-slate-200" aria-hidden="true" />
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="menu in section.menus"
                        :key="menu.title + menu.url"
                        :href="menu.url"
                        class="group flex items-center gap-3 rounded-lg border bg-white p-3 transition-colors hover:border-slate-300 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                            :style="{ backgroundColor: section.accent + '1a', color: section.accent }"
                        >
                            <component :is="menuIcon(menu.icon)" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium">{{ menu.title }}</span>
                            <span class="block truncate text-xs text-muted-foreground">{{ menu.desc }}</span>
                        </span>
                        <ChevronRight class="size-4 shrink-0 text-slate-300 transition-all group-hover:translate-x-0.5 group-hover:text-slate-500" />
                    </Link>
                </div>
            </section>

            <EmptyState
                v-if="filteredSections.length === 0"
                :icon="Search"
                title="Menu tidak ditemukan"
                :message="`Tidak ada menu yang cocok dengan \u201c${keyword}\u201d.`"
            />
        </div>
    </AppLayout>
</template>
