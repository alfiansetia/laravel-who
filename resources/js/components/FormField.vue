<script setup>
defineProps({
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div class="grid gap-1.5">
        <label v-if="label" class="text-sm font-medium">
            {{ label }} <span v-if="required" class="text-destructive">*</span>
        </label>
        <slot />
        <p v-if="hint && !error" class="text-xs text-muted-foreground">{{ hint }}</p>
        <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
    </div>
</template>
