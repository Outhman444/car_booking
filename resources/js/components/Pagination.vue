<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links?: Array<{ url: string | null; label: string; active: boolean }>;
}>();
</script>

<template>
    <nav v-if="links?.length" class="flex flex-wrap gap-2">
        <Link
            v-for="(link, i) in links"
            :key="i"
            :href="link.url || ''"
            :class="[
                'rounded px-3 py-1 text-sm transition-colors',
                link.active
                    ? 'bg-primary text-primary-foreground font-medium'
                    : 'bg-muted text-muted-foreground hover:bg-muted/80',
                !link.url && 'pointer-events-none opacity-50',
            ]"
        >
            <span v-html="link.label" />
        </Link>
    </nav>
</template>
