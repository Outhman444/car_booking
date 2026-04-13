<script setup lang="ts">
import { computed } from 'vue';
import { Check, CalendarCheck, ClipboardList, CreditCard, ShieldCheck } from 'lucide-vue-next';

const props = defineProps<{
    currentStep: 1 | 2 | 3 | 4;
}>();

const steps = [
    {
        id: 1,
        title: 'Configure',
        description: 'Trip Details',
        icon: CalendarCheck
    },
    {
        id: 2,
        title: 'Review',
        description: 'Verification',
        icon: ClipboardList
    },
    {
        id: 3,
        title: 'Payment',
        description: 'Secure Checkout',
        icon: CreditCard
    },
    {
        id: 4,
        title: 'Complete',
        description: 'Secured',
        icon: ShieldCheck
    }
];

const progressWidth = computed(() => {
    if (props.currentStep === 1) return '0%';
    if (props.currentStep === 2) return '33.33%';
    if (props.currentStep === 3) return '66.66%';
    return '100%';
});
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="relative">
            <!-- Background Line -->
            <div class="absolute top-7 left-0 h-1 w-full rounded-full bg-slate-100"></div>
            
            <!-- Progress Line -->
            <div 
                class="absolute top-7 left-0 h-1 rounded-full bg-primary transition-all duration-1000 ease-[cubic-bezier(0.65,0,0.35,1)]" 
                :style="{ width: progressWidth }"
            ></div>

            <!-- Steps -->
            <div class="relative flex justify-between">
                <div 
                    v-for="step in steps" 
                    :key="step.id" 
                    class="flex flex-col items-center group w-24 sm:w-32"
                >
                    <!-- Icon Circle -->
                    <div 
                        class="relative flex h-14 w-14 items-center justify-center rounded-2xl transition-all duration-700 ease-[cubic-bezier(0.65,0,0.35,1)] ring-4 ring-white"
                        :class="[
                            currentStep > step.id 
                                ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-100' 
                                : currentStep === step.id 
                                    ? 'bg-slate-900 text-white shadow-2xl shadow-slate-900/40 scale-110 -translate-y-1' 
                                    : 'bg-white text-slate-300 ring-1 ring-slate-200 scale-95'
                        ]"
                    >
                        <transition name="fade" mode="out-in">
                            <Check v-if="currentStep > step.id" class="size-6 font-black" />
                            <component v-else :is="step.icon" class="size-6" />
                        </transition>

                        <!-- Active indicator pulse -->
                        <div 
                            v-if="currentStep === step.id" 
                            class="absolute -inset-2 rounded-3xl border-2 border-slate-900/20 animate-[ping_2s_cubic-bezier(0,0,0.2,1)_infinite] -z-10"
                        ></div>
                    </div>

                    <!-- Labels -->
                    <div class="mt-6 text-center transform transition-all duration-500"
                         :class="currentStep === step.id ? 'scale-105' : 'scale-100 opacity-70'">
                        <span 
                            class="block text-[11px] font-black uppercase tracking-widest transition-colors duration-300"
                            :class="currentStep === step.id ? 'text-slate-900' : 'text-slate-400'"
                        >
                            {{ step.title }}
                        </span>
                        <span 
                            class="hidden sm:block text-[10px] font-bold uppercase tracking-[0.2em] mt-1.5 transition-colors duration-300"
                            :class="currentStep === step.id ? 'text-primary' : 'text-slate-300'"
                        >
                            {{ step.description }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.8);
}
</style>
