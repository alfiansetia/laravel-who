<script setup>
import { Copy } from '@lucide/vue';
import { copyText } from '@/lib/export';

// Tombol salin compact untuk nilai info di modal/judul.
// Pemakaian: <CopyButton :text="detail?.name" label="No SO" />
const props = defineProps({
    text: { type: [String, Number], default: '' },
    label: { type: String, default: 'Teks' },
});

function doCopy() {
    copyText(String(props.text ?? '').trim(), `${props.label} disalin ke clipboard.`);
}
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center justify-center rounded-md p-1 align-middle text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
        :title="`Salin ${label}`"
        @click="doCopy"
    >
        <Copy class="size-3.5" />
    </button>
</template>
