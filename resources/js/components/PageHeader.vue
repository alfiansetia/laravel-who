<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Breadcrumb from '@/components/Breadcrumb.vue';
import { resolveBreadcrumbs } from '@/lib/breadcrumbs';

const props = defineProps({
    title: { type: String, default: '' },
    /** Override eksplisit (frontend): [{ label, href?, current? }]. Bila null, diturunkan dari URL + title via lib/breadcrumbs. */
    breadcrumbs: { type: Array, default: null },
    homeHref: { type: String, default: '/' },
    showHome: { type: Boolean, default: true },
});

const page = usePage();
const crumbs = computed(() => props.breadcrumbs ?? resolveBreadcrumbs({ url: page.url, title: props.title }));
const showCrumbs = computed(() => crumbs.value.length > 0);
</script>

<template>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <Breadcrumb v-if="showCrumbs" :items="crumbs" :home-href="homeHref" :show-home="showHome" />
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
