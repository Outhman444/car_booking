<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import FlashNotifications from '@/components/FlashNotifications.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppHeaderLayout :breadcrumbs="breadcrumbs">
        <FlashNotifications />

        <slot />
    </AppHeaderLayout>
</template>
