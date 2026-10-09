<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import {
    Bell,
    Cog,
    Database,
    Lock,
    Menu,
    Package,
    Boxes,
    ClipboardCheck,
    Truck,
    FileText,
    ListOrdered,
    Layers,
    X,
} from '@lucide/vue';
import AuthModal from '@/components/AuthModal.vue';
import BlockOverlay from '@/components/BlockOverlay.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import NotifModal from '@/components/NotifModal.vue';
import { useAuthModal } from '@/composables/useAuthModal';
import { useToast } from '@/composables/useToast';
import { initFcm } from '@/lib/fcm';

const page = usePage();
const toast = useToast();
const { open: openAuthModal } = useAuthModal();
const menuOpen = ref(false);
const notifOpen = ref(false);

const envLoggedIn = computed(() => page.props.auth?.envLoggedIn ?? false);
const flash = computed(() => page.props.flash ?? {});

const mainLinks = [
    { label: 'Product', route: 'products.index', match: 'products.*', icon: Package },
    { label: 'Stock', route: 'stock.index', match: 'stock.*', icon: Boxes },
    { label: 'QC', route: 'qc.index', match: 'qc.*', icon: ClipboardCheck },
    { label: 'Alamat Baru', route: 'alamat_baru.index', match: 'alamat_baru.*', icon: Truck },
    { label: 'BAST', route: 'basts.index', match: 'basts.*', icon: FileText },
    { label: 'PL', route: 'packs.index', match: 'packs.*', icon: ListOrdered },
    { label: 'SOP', route: 'sops.index', match: 'sops.*', icon: Layers },
];

const odooLinks = [
    { label: 'Stock', route: 'stock.index', match: 'stock.*' },
    { label: 'Lot / SN', route: 'lots.index', match: 'lots.*' },
    { label: 'Product Odoo', route: 'product_odoo.index', match: 'product_odoo.*' },
    { label: 'Vendor', route: 'vendors.index', match: 'vendors.*' },
    { label: 'Purchase Order (PO)', route: 'po.index', match: 'po.*' },
    { label: 'Reception (RI)', route: 'ri.index', match: 'ri.*' },
    { label: 'Sales Order (SO)', route: 'so.index', match: 'so.*' },
    { label: 'Delivery Order (DO)', route: 'do.index', match: 'do.*' },
    { label: 'Internal Transfer (IT)', route: 'it.index', match: 'it.*' },
];

function isActive(match) {
    try {
        return route().current(match);
    } catch {
        return false;
    }
}

function href(name) {
    try {
        return route(name);
    } catch {
        return '#';
    }
}

function pushFlash(f) {
    if (!f) {
        return;
    }
    if (f.success) {
        toast.success(f.success);
    }
    if (f.message) {
        toast.info(f.message);
    }
    if (f.error) {
        toast.error(f.error);
    }
}

onMounted(() => {
    pushFlash(flash.value);
    if (page.props.firebase?.apiKey) {
        initFcm(page.props.firebase);
    }
});
watch(flash, (f) => pushFlash(f));
</script>

<template>
    <div class="min-h-screen">
        <Toaster position="top-right" rich-colors close-button :gap="8" :expand="false" offset="64px" :visible-toasts="3" />

        <header class="sticky top-0 z-40 border-b bg-[#e3f2fd]">
            <div class="mx-auto flex h-14 max-w-7xl items-center gap-2 px-4">
                <Link :href="href('index')" class="flex shrink-0 items-center">
                    <img src="/images/asa.png" height="35" width="35" alt="ASA Logo" class="h-9 w-9 object-contain" />
                </Link>

                <nav class="hidden flex-1 items-center justify-center gap-1 lg:flex">
                    <Link
                        v-for="link in mainLinks"
                        :key="link.route"
                        :href="href(link.route)"
                        class="flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="isActive(link.match) ? 'text-primary' : 'text-slate-600 hover:text-primary'"
                    >
                        <component :is="link.icon" class="size-4" />
                        {{ link.label }}
                    </Link>
                    <div class="group relative">
                        <button
                            type="button"
                            class="flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:text-primary"
                        >
                            <Database class="size-4" />
                            Odoo
                        </button>
                        <div
                            class="invisible absolute left-0 top-full w-60 rounded-xl border bg-white p-2 opacity-0 shadow-lg transition-all group-hover:visible group-hover:opacity-100"
                        >
                            <Link
                                v-for="link in odooLinks"
                                :key="link.route"
                                :href="href(link.route)"
                                class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-sky-50 hover:text-primary"
                            >
                                {{ link.label }}
                            </Link>
                        </div>
                    </div>
                </nav>

                <div class="ml-auto hidden items-center gap-1 lg:flex">
                    <button
                        type="button"
                        title="Cek Notifikasi"
                        class="rounded-md p-2 text-slate-600 transition-colors hover:text-primary"
                        @click="notifOpen = true"
                    >
                        <Bell class="size-4" />
                    </button>
                    <button
                        v-if="!envLoggedIn"
                        type="button"
                        class="ml-2 flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-primary"
                        @click="openAuthModal"
                    >
                        <Lock class="size-4" /> Login
                    </button>
                    <Link
                        v-else
                        :href="href('settings.index')"
                        class="ml-2 flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-primary"
                    >
                        <Cog class="size-4" /> Server Config
                    </Link>
                </div>

                <button
                    type="button"
                    class="ml-auto rounded-md p-2 text-slate-600 lg:hidden"
                    aria-label="Menu navigasi"
                    @click="menuOpen = !menuOpen"
                >
                    <Menu v-if="!menuOpen" class="size-5" />
                    <X v-else class="size-5" />
                </button>
            </div>

            <nav v-if="menuOpen" class="border-t bg-[#e3f2fd] px-4 py-2 lg:hidden">
                <Link
                    v-for="link in mainLinks"
                    :key="link.route"
                    :href="href(link.route)"
                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm font-medium"
                    :class="isActive(link.match) ? 'text-primary' : 'text-slate-600'"
                >
                    <component :is="link.icon" class="size-4" />
                    {{ link.label }}
                </Link>
                <p class="px-2 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Odoo</p>
                <Link
                    v-for="link in odooLinks"
                    :key="link.route"
                    :href="href(link.route)"
                    class="block rounded-md px-2 py-2 pl-8 text-sm font-medium text-slate-600"
                >
                    {{ link.label }}
                </Link>
                <button
                    type="button"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-slate-600"
                    @click="notifOpen = true; menuOpen = false"
                >
                    <Bell class="size-4" /> Notifikasi
                </button>
                <button
                    v-if="!envLoggedIn"
                    type="button"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-slate-600"
                    @click="openAuthModal(); menuOpen = false"
                >
                    <Lock class="size-4" /> Login
                </button>
                <Link
                    v-else
                    :href="href('settings.index')"
                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-slate-600"
                >
                    <Cog class="size-4" /> Server Config
                </Link>
            </nav>
        </header>

        <AuthModal />
        <NotifModal v-model:open="notifOpen" :env-logged-in="envLoggedIn" />
        <ConfirmDialog />

        <main class="mx-auto w-full max-w-7xl px-4 py-6">
            <BlockOverlay>
                <slot />
            </BlockOverlay>
        </main>
    </div>
</template>
