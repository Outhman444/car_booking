<script setup lang="ts">
import { computed } from 'vue';
import { Check, CalendarCheck, ClipboardList, CreditCard, ShieldCheck } from 'lucide-vue-next';

const props = defineProps<{
    currentStep: 1 | 2 | 3 | 4;
    isPaid?: boolean;
    status?: string;
}>();

const steps = [
    { id: 1, title: 'Configure', desc: 'Trip Details', icon: CalendarCheck },
    { id: 2, title: 'Review', desc: 'Verification', icon: ClipboardList },
    { id: 3, title: 'Payment', desc: 'Checkout', icon: CreditCard },
    { id: 4, title: 'Complete', desc: 'Secured', icon: ShieldCheck }
];

const isStepCompleted = (id: number) => {
    if (props.status === 'active' || props.status === 'completed') return id <= 4;
    if (props.status === 'confirmed' || props.isPaid) return id <= 3;
    if (props.status === 'pending') return id < 2;
    return id < props.currentStep;
};

const isStepActive = (id: number) => {
    if (props.status === 'active' || props.status === 'completed') return id === 4;
    if (props.status === 'confirmed' || props.isPaid) return false;
    if (props.status === 'pending') return id === 2;
    return id === props.currentStep;
};
</script>

<template>
    <div class="w-full py-6 md:py-10">
        <div class="relative flex items-start justify-between">
            <!-- Progress Line Container -->
            <div class="absolute left-0 top-6 md:top-7 flex w-full translate-y-[-50%] items-center px-[12%] md:px-[8%]">
                <div v-for="i in 3" :key="i" class="relative h-[3px] flex-1 mx-1 md:mx-2">
                    <div class="absolute inset-0 bg-slate-200/60 rounded-full ring-1 ring-slate-100/50"></div>
                    <div 
                        class="absolute inset-0 bg-primary rounded-full transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(var(--primary-rgb),0.5)]"
                        :class="{ 'opacity-100': isStepCompleted(i + 1) || isStepActive(i + 1), 'opacity-0': !isStepCompleted(i + 1) && !isStepActive(i + 1) }"
                        :style="{ width: isStepCompleted(i + 1) ? '100%' : isStepActive(i + 1) ? '50%' : '0%' }"
                    ></div>
                </div>
            </div>

            <!-- Steps -->
            <div v-for="step in steps" :key="step.id" class="relative z-10 flex flex-col items-center flex-1">
                <!-- Icon Container -->
                <div 
                    class="relative flex h-12 w-12 md:h-14 md:w-14 items-center justify-center rounded-2xl md:rounded-3xl transition-all duration-500 border-4 border-white shadow-sm"
                    :class="[
                        isStepCompleted(step.id) 
                            ? 'bg-primary text-white shadow-primary/20' 
                            : isStepActive(step.id)
                                ? 'bg-slate-900 text-white shadow-xl shadow-slate-200 scale-110' 
                                : 'bg-white text-slate-300 border-slate-50'
                    ]"
                >
                    <transition name="scale" mode="out-in">
                        <Check v-if="isStepCompleted(step.id)" class="size-5 md:size-6 stroke-[3]" />
                        <component v-else :is="step.icon" class="size-5 md:size-6" :class="{ 'animate-pulse': isStepActive(step.id) }" />
                    </transition>

                    <!-- Active Indicator Circle -->
                    <div 
                        v-if="isStepActive(step.id)" 
                        class="absolute -inset-2 rounded-[1.5rem] md:rounded-[2rem] border-2 border-slate-900/5 animate-[ping_3s_infinite]"
                    ></div>
                </div>

                <!-- Labels -->
                <div class="mt-4 text-center px-1 max-w-[80px] md:max-w-none">
                    <p 
                        class="text-[9px] md:text-[10px] font-black uppercase tracking-[0.15em] transition-colors whitespace-nowrap overflow-hidden text-ellipsis"
                        :class="isStepActive(step.id) ? 'text-slate-900' : isStepCompleted(step.id) ? 'text-primary' : 'text-slate-400'"
                    >
                        {{ step.title }}
                    </p>
                    <p 
                        class="hidden md:block mt-1 text-[8.5px] font-bold uppercase tracking-widest text-slate-300 opacity-0 transition-opacity duration-500"
                        :class="{ 'opacity-100': isStepActive(step.id) || isStepCompleted(step.id) }"
                    >
                        {{ step.desc }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scale-enter-active,
.scale-leave-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.scale-enter-from {
  opacity: 0;
  transform: scale(0.5);
}

.scale-leave-to {
  opacity: 0;
  transform: scale(1.5);
}

:root {
  --primary-rgb: 245, 97, 0;
}
</style>
