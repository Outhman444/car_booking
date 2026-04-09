<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const successMessage = ref((page.props as any).flash?.success || null);
const errorMessage = ref((page.props as any).flash?.error || null);

watch(() => (page.props as any).flash?.success, (val: string | null) => {
    successMessage.value = val;
    if (val) setTimeout(() => (successMessage.value = null), 5000);
});

watch(() => (page.props as any).flash?.error, (val: string | null) => {
    errorMessage.value = val;
    if (val) setTimeout(() => (errorMessage.value = null), 5000);
});
</script>

<template>
    <AppHeaderLayout :breadcrumbs="breadcrumbs">
        <!-- Flash Messages -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="successMessage"
                class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-green-500 px-4 py-3 text-sm font-medium text-white shadow-lg"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ successMessage }}
                <button @click="successMessage = null" class="ml-2 text-white/80 hover:text-white">✕</button>
            </div>
        </Transition>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="errorMessage"
                class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-red-500 px-4 py-3 text-sm font-medium text-white shadow-lg"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ errorMessage }}
                <button @click="errorMessage = null" class="ml-2 text-white/80 hover:text-white">✕</button>
            </div>
        </Transition>

        <slot />
    </AppHeaderLayout>
</template>
