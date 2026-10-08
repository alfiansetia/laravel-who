<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import { menuIcon } from '@/lib/menuIcons';

const props = defineProps({
    title: { type: String, default: 'Home' },
    sections: { type: Array, default: () => [] },
});

const keyword = ref('');

function iconComponent(name) {
    return menuIcon(name);
}

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
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl space-y-8">
            <div class="space-y-4 text-center">
                <h1 class="text-xl font-semibold tracking-tight">Selamat Datang Kembali</h1>
                <p class="text-sm text-muted-foreground">Akses cepat ke modul dan peralatan kerja Anda</p>
                <div class="relative mx-auto max-w-xl">
                    <Search class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="keyword"
                        type="search"
                        autofocus
                        placeholder="Cari menu atau aplikasi (misal: Stock, PO, STT)..."
                        class="h-11 rounded-full pl-11"
                    />
                </div>
            </div>

            <section v-for="section in filteredSections" :key="section.title">
                <h2
                    class="mb-4 flex items-center gap-2 border-l-4 pl-2 text-base font-semibold text-slate-600"
                    :style="{ borderColor: section.accent }"
                >
                    <component :is="iconComponent(section.icon)" class="size-5" :style="{ color: section.accent }" />
                    {{ section.title }}
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    <Link v-for="menu in section.menus" :key="menu.title + menu.url" :href="menu.url">
                        <Card
                            class="group flex min-h-36 flex-col items-center justify-center gap-2 p-5 text-center transition-all hover:-translate-y-1 hover:shadow-md"
                        >
                            <span
                                class="flex size-14 items-center justify-center rounded-2xl transition-transform group-hover:scale-105"
                                :style="{ backgroundColor: section.accent + '1a', color: section.accent }"
                            >
                                <component :is="iconComponent(menu.icon)" class="size-7" />
                            </span>
                            <span class="text-sm font-semibold">{{ menu.title }}</span>
                            <span class="text-xs leading-tight text-muted-foreground">{{ menu.desc }}</span>
                        </Card>
                    </Link>
                </div>
            </section>

            <p v-if="filteredSections.length === 0" class="py-12 text-center text-sm text-muted-foreground">
                Menu tidak ditemukan untuk "{{ keyword }}".
            </p>
        </div>
    </AppLayout>
</template>
