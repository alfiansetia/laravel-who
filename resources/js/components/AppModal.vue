<script setup>
import { onBeforeUnmount, watch } from 'vue';
import {
    DialogClose,
    DialogContent,
    DialogPortal,
    DialogRoot,
    DialogTitle,
} from 'radix-vue';
import { X } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    title: { type: String, default: '' },
    size: { type: String, default: 'md' },
});

const open = defineModel('open', { type: Boolean, default: false });

const sizes = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-lg',
    lg: 'sm:max-w-2xl',
    xl: 'sm:max-w-4xl',
};

// Dialog dijalankan non-modal: panel SearchableSelect di-teleport ke body
// (di luar DialogContent) agar tidak terpotong, tapi mode modal radix
// mematikan panel itu (focus-trap + pointer-events none + dismiss saat
// diklik). Sebagai gantinya backdrop + scroll-lock ditangani manual;
// ESC tetap menutup via DismissableLayer.
// Klik di dalam panel SearchableSelect (yang di-teleport ke body, di luar
// DialogContent) dianggap "outside" oleh DismissableLayer radix dan akan
// menutup modal saat user klik input search / opsi. Cegah dismiss khusus
// untuk target di dalam panel tersebut.
function isInsideSearchablePanel(target) {
    return target?.closest?.('[data-searchable-select-panel]') != null;
}

function handlePointerDownOutside(e) {
    if (isInsideSearchablePanel(e.target)) {
        e.preventDefault();
    }
}

function handleFocusOutside(e) {
    if (isInsideSearchablePanel(e.target)) {
        e.preventDefault();
    }
}

function handleInteractOutside(e) {
    if (isInsideSearchablePanel(e.target)) {
        e.preventDefault();
    }
}

function close() {
    open.value = false;
}

let scrollLockCount = 0;

function lockScroll() {
    scrollLockCount += 1;
    document.body.style.overflow = 'hidden';
}

function unlockScroll() {
    scrollLockCount = Math.max(0, scrollLockCount - 1);
    if (scrollLockCount === 0) {
        document.body.style.overflow = '';
    }
}

watch(open, (value) => (value ? lockScroll() : unlockScroll()));

onBeforeUnmount(() => {
    if (open.value) {
        unlockScroll();
    }
});
</script>

<template>
    <DialogRoot v-model:open="open" :modal="false">
        <DialogPortal>
            <div
                v-if="open"
                class="fixed inset-0 z-50 bg-black/60"
                @click="close"
            />
            <DialogContent
                :class="cn('fixed left-1/2 top-1/2 z-50 grid max-h-[90vh] w-[calc(100%-2rem)] -translate-x-1/2 -translate-y-1/2 gap-4 overflow-y-auto rounded-lg border bg-white p-6 shadow-lg data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95', sizes[size] ?? sizes.md)"
                @pointer-down-outside="handlePointerDownOutside"
                @focus-outside="handleFocusOutside"
                @interact-outside="handleInteractOutside"
            >
                <div class="flex items-center justify-between gap-2">
                    <DialogTitle class="text-base font-semibold">
                        <slot name="title">{{ title }}</slot>
                    </DialogTitle>
                    <DialogClose
                        class="rounded-md p-1 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        aria-label="Tutup"
                    >
                        <X class="size-4" />
                    </DialogClose>
                </div>
                <slot />
                <div v-if="$slots.footer" class="flex justify-end gap-2">
                    <slot name="footer" />
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
