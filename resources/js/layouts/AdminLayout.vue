<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const page = usePage();
const successMessage = ref(page.props.flash.success);
const errorMessage = ref(page.props.flash.error);
const infoMessage = ref(page.props.flash.info);

watch(
    () => page.props.flash.success,
    (val) => {
        successMessage.value = val;
        if (val) {
            setTimeout(() => (successMessage.value = null), 5000);
        }
    },
);

watch(
    () => page.props.flash.error,
    (val) => {
        errorMessage.value = val;
        if (val) {
            setTimeout(() => (errorMessage.value = null), 5000);
        }
    },
);

watch(
    () => page.props.flash.info,
    (val) => {
        infoMessage.value = val;
        if (val) {
            setTimeout(() => (infoMessage.value = null), 5000);
        }
    },
);
</script>

<template>
    <AppSidebarLayout>
        <!-- Success Message -->
        <div
            v-if="successMessage"
            class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-green-500/90 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ successMessage }}</span>
        </div>

        <!-- Error Message -->
        <div
            v-if="errorMessage"
            class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-red-500/90 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ errorMessage }}</span>
        </div>

        <!-- Info Message -->
        <div
            v-if="infoMessage"
            class="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-blue-500/90 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ infoMessage }}</span>
        </div>

        <slot />
    </AppSidebarLayout>
</template>
