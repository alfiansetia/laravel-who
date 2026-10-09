<script setup>
import { Loader2 } from '@lucide/vue';
import { useBlock } from '@/composables/useBlock';

const { blocking } = useBlock();
</script>

<template>
    <div>
        <slot />
        <div v-if="blocking > 0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm shadow-lg">
                <Loader2 class="size-4 animate-spin" />
                Memproses...
            </div>
        </div>
    </div>
</template>
