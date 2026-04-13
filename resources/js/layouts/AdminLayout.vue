<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import FlashNotifications from '@/components/FlashNotifications.vue';
</script>

<template>
    <AppSidebarLayout>
        <FlashNotifications />

        <slot />
    </AppSidebarLayout>
</template>
