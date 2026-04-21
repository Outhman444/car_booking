<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, Info, X, Sparkles, AlertTriangle } from 'lucide-vue-next';

const page = usePage<any>();

const successMessage = ref<string | null>(page.props.flash?.success);
const errorMessage = ref<string | null>(page.props.flash?.error);
const infoMessage = ref<string | null>(page.props.flash?.info);

const progress = ref(100);

const startTimer = (messageRef: any) => {
    progress.value = 100;
    const duration = 5000;
    const interval = 10;
    const step = (interval / duration) * 100;

    const timer = setInterval(() => {
        progress.value -= step;
        if (progress.value <= 0) {
            clearInterval(timer);
            messageRef.value = null;
        }
    }, interval);
};

watch(
    () => page.props.flash?.success,
    (val) => {
        if (val) {
            successMessage.value = val;
            progress.value = 100;
            setTimeout(() => (successMessage.value = null), 5000);
        }
    },
);

watch(
    () => page.props.flash?.error,
    (val) => {
        if (val) {
            errorMessage.value = val;
            setTimeout(() => (errorMessage.value = null), 8000);
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
    <div class="fixed top-6 right-6 z-[9999] flex flex-col gap-4 pointer-events-none w-full max-w-[400px]">
        <!-- Success Message -->
        <Transition
            enter-active-class="transform transition ease-out duration-500"
            enter-from-class="translate-x-[120%] scale-90 opacity-0"
            enter-to-class="translate-x-0 scale-100 opacity-100"
            leave-active-class="transition ease-in duration-300"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-[120%] opacity-0"
        >
            <div
                v-if="successMessage"
                class="pointer-events-auto relative overflow-hidden rounded-[2.5rem] bg-slate-900 p-1 shadow-[0_20px_50px_rgba(0,0,0,0.3)] ring-1 ring-white/10"
            >
                <div class="relative rounded-[2.25rem] bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 p-6">
                    <!-- Decorative background element -->
                    <div class="absolute -right-6 -bottom-6 opacity-10 rotate-12">
                        <Sparkles class="size-24 text-emerald-500" />
                    </div>
                    
                    <div class="flex items-center gap-5">
                        <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 ring-1 ring-emerald-500/20 shadow-inner">
                            <div class="absolute inset-0 bg-emerald-500 blur-xl opacity-20 animate-pulse"></div>
                            <CheckCircle2 class="h-7 w-7 text-emerald-400 relative z-10" />
                        </div>
                        
                        <div class="flex-1 space-y-1">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-400">Succès Opérationnel</h3>
                            <p class="text-sm font-bold leading-relaxed text-slate-200">{{ successMessage }}</p>
                        </div>

                        <button 
                            @click="successMessage = null" 
                            class="h-10 w-10 shrink-0 flex items-center justify-center rounded-xl bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </div>
                
                <!-- Animated Progress Bar -->
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500/20">
                    <div 
                        class="h-full bg-emerald-500 transition-all ease-linear duration-[5000ms]"
                        :style="{ width: successMessage ? '0%' : '100%' }"
                    ></div>
                </div>
            </div>
        </Transition>

        <!-- Error Message -->
        <Transition
            enter-active-class="transform transition ease-out duration-500"
            enter-from-class="translate-x-[120%] scale-90 opacity-0"
            enter-to-class="translate-x-0 scale-100 opacity-100"
            leave-active-class="transition ease-in duration-300"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-[120%] opacity-0"
        >
            <div
                v-if="errorMessage"
                class="pointer-events-auto relative overflow-hidden rounded-[2.5rem] bg-slate-900 p-1 shadow-[0_20px_50px_rgba(0,0,0,0.3)] ring-1 ring-white/10"
            >
                <div class="relative rounded-[2.25rem] bg-gradient-to-br from-slate-900 via-slate-900 to-rose-950 p-6 text-white">
                    <div class="flex items-center gap-5">
                        <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-rose-500/10 ring-1 ring-rose-500/20 shadow-inner">
                            <div class="absolute inset-0 bg-rose-500 blur-xl opacity-20"></div>
                            <AlertTriangle class="h-7 w-7 text-rose-400 relative z-10" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-rose-400">Attention Requise</h3>
                            <p class="text-sm font-bold leading-relaxed text-slate-200">{{ errorMessage }}</p>
                        </div>
                        <button @click="errorMessage = null" class="h-10 w-10 shrink-0 flex items-center justify-center rounded-xl bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                            <X class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Info Message -->
        <Transition
            enter-active-class="transform transition ease-out duration-500"
            enter-from-class="translate-y-4 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="infoMessage"
                class="pointer-events-auto rounded-3xl bg-slate-900/40 backdrop-blur-2xl border border-white/10 p-5 shadow-2xl ring-1 ring-white/5 flex items-center gap-4"
            >
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                    <Info class="h-5 w-5" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-200 truncate">{{ infoMessage }}</p>
                </div>
                <button @click="infoMessage = null" class="text-slate-500 hover:text-white transition-colors">
                    <X class="size-4" />
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Any additional custom animations if needed */
</style>
