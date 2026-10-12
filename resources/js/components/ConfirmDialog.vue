<script setup>
import Button from '@/components/ui/Button.vue';
import { useConfirmState } from '@/composables/useConfirm';

const { state, resolve } = useConfirmState();
</script>

<template>
    <!-- z-[150]: di atas modal (z-50) + block overlay (z-100) agar confirm dari
         dalam modal (mis. clear log di modal detail) tidak ketutup;
         tetap di bawah panel SearchableSelect (z-200). -->
    <div v-if="state.open" class="fixed inset-0 z-[150] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-lg border bg-white p-6 shadow-lg">
            <h2 class="text-base font-semibold">{{ state.title }}</h2>
            <p v-if="state.message" class="mt-2 text-sm text-muted-foreground">{{ state.message }}</p>
            <div class="mt-6 flex justify-end gap-2">
                <Button variant="ghost" @click="resolve(false)">{{ state.cancelText }}</Button>
                <Button :variant="state.tone === 'destructive' ? 'destructive' : 'default'" @click="resolve(true)">
                    {{ state.confirmText }}
                </Button>
            </div>
        </div>
    </div>
</template>
