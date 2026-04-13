<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, XCircle, Info, X } from 'lucide-vue-next';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';

const page = usePage<any>();

// Local state for the notifications
const successMessage = ref<string | null>(page.props.flash?.success);
const errorMessage = ref<string | null>(page.props.flash?.error);
const infoMessage = ref<string | null>(page.props.flash?.info);

// Watch for changes in flash props
watch(
    () => page.props.flash?.success,
    (val) => {
        if (val) {
            successMessage.value = val;
            setTimeout(() => (successMessage.value = null), 5000);
        }
    },
);

watch(
    () => page.props.flash?.error,
    (val) => {
        if (val) {
            errorMessage.value = val;
            setTimeout(() => (errorMessage.value = null), 5000);
        }
    },
);

watch(
    () => page.props.flash?.info,
    (val) => {
        if (val) {
            infoMessage.value = val;
            setTimeout(() => (infoMessage.value = null), 5000);
        }
    },
);
</script>

<template>
    <div class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none">
        <!-- Success Message -->
        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-x-[120%] opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Alert
                v-if="successMessage"
                class="pointer-events-auto w-[350px] rounded-2xl border-green-200 bg-white/95 p-5 shadow-2xl backdrop-blur-xl ring-1 ring-black/5"
            >
                <div class="flex items-start gap-4">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100/50 text-green-600">
                        <CheckCircle class="h-6 w-6" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <AlertTitle class="text-[15px] font-black text-gray-900 tracking-tight">Success!</AlertTitle>
                        <AlertDescription class="text-xs font-bold leading-relaxed text-gray-500">{{ successMessage }}</AlertDescription>
                    </div>
                    <button @click="successMessage = null" class="text-gray-400 hover:text-gray-900 transition-all hover:scale-110">
                        <X class="size-4" />
                    </button>
                </div>
            </Alert>
        </Transition>

        <!-- Error Message -->
        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-x-[120%] opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition, ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Alert
                v-if="errorMessage"
                class="pointer-events-auto w-[350px] rounded-2xl border-red-200 bg-white/95 p-5 shadow-2xl backdrop-blur-xl ring-1 ring-black/5"
            >
                <div class="flex items-start gap-4">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100/50 text-red-600">
                        <XCircle class="h-6 w-6" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <AlertTitle class="text-[15px] font-black text-gray-900 tracking-tight">Attention</AlertTitle>
                        <AlertDescription class="text-xs font-bold leading-relaxed text-gray-500">{{ errorMessage }}</AlertDescription>
                    </div>
                    <button @click="errorMessage = null" class="text-gray-400 hover:text-gray-900 transition-all hover:scale-110">
                        <X class="size-4" />
                    </button>
                </div>
            </Alert>
        </Transition>

        <!-- Info Message -->
        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-x-[120%] opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Alert
                v-if="infoMessage"
                class="pointer-events-auto w-[350px] rounded-2xl border-blue-200 bg-white/95 p-5 shadow-2xl backdrop-blur-xl ring-1 ring-black/5"
            >
                <div class="flex items-start gap-4">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100/50 text-blue-600">
                        <Info class="h-6 w-6" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <AlertTitle class="text-[15px] font-black text-gray-900 tracking-tight">Notification</AlertTitle>
                        <AlertDescription class="text-xs font-bold leading-relaxed text-gray-500">{{ infoMessage }}</AlertDescription>
                    </div>
                    <button @click="infoMessage = null" class="text-gray-400 hover:text-gray-900 transition-all hover:scale-110">
                        <X class="size-4" />
                    </button>
                </div>
            </Alert>
        </Transition>
    </div>
</template>
