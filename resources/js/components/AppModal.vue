<script setup>
import {
    DialogClose,
    DialogContent,
    DialogOverlay,
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
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-50 bg-black/60 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
            <DialogContent
                :class="cn('fixed left-1/2 top-1/2 z-50 grid max-h-[90vh] w-[calc(100%-2rem)] -translate-x-1/2 -translate-y-1/2 gap-4 overflow-y-auto rounded-lg border bg-white p-6 shadow-lg data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95', sizes[size] ?? sizes.md)"
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
