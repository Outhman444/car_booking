<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Clock, AlertCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    expiresAt?: string;
    variant?: 'payment' | 'confirmation';
    isPaid?: boolean;
}>();

const emit = defineEmits(['expired']);

const remainingTime = ref('');
const isExpired = ref(false);
const progress = ref(100);
let timerInterval: any = null;

const calculateTimeLeft = () => {
    const target = props.expiresAt;
    if (!target || props.isPaid) {
        remainingTime.value = '';
        return;
    }

    const expiryTime = new Date(target).getTime();
    if (isNaN(expiryTime)) {
        remainingTime.value = '';
        return;
    }

    const now = new Date().getTime();
    const difference = expiryTime - now;
    
    // Calculate progress based on 60m session
    const totalPotential = 60 * 60 * 1000;
    progress.value = Math.max(0, Math.min(100, (difference / totalPotential) * 100));

    if (difference <= 0) {
        remainingTime.value = '00:00';
        progress.value = 0;
        if (!isExpired.value) {
            isExpired.value = true;
            emit('expired');
        }
        clearInterval(timerInterval);
        return;
    }

    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((difference % (1000 * 60)) / 1000);

    remainingTime.value = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

onMounted(() => {
    calculateTimeLeft();
    timerInterval = setInterval(calculateTimeLeft, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const isUrgent = computed(() => {
    if (!remainingTime.value) return false;
    const [m] = remainingTime.value.split(':').map(Number);
    return m < 5;
});
</script>

<template>
    <transition name="compact-fade">
        <div v-if="!isExpired && expiresAt && !isPaid" class="relative max-w-xl mx-auto mb-8">
            <!-- Compact Pill Style -->
            <div class="group relative flex items-center gap-4 overflow-hidden rounded-full p-2 pr-6 shadow-lg shadow-slate-200/50 backdrop-blur-md transition-all duration-500 hover:scale-[1.02]"
                :class="isUrgent ? 'bg-rose-600 ring-2 ring-rose-500/20' : 'bg-slate-900 ring-1 ring-white/10'"
            >
                <!-- Circular Clock Widget (Compact) -->
                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white shadow-sm overflow-hidden">
                    <svg class="absolute inset-0 h-full w-full -rotate-90 transform p-1.5">
                        <circle
                            class="text-slate-100"
                            stroke-width="3"
                            stroke="currentColor"
                            fill="transparent"
                            r="22"
                            cx="28"
                            cy="28"
                        />
                        <circle
                            :class="isUrgent ? 'text-rose-500' : 'text-primary'"
                            stroke-width="3"
                            :stroke-dasharray="138.23"
                            :stroke-dashoffset="138.23 * (1 - progress / 100)"
                            stroke-linecap="round"
                            stroke="currentColor"
                            fill="transparent"
                            r="22"
                            cx="28"
                            cy="28"
                            class="transition-all duration-1000"
                        />
                    </svg>
                    <Clock class="size-5" :class="isUrgent ? 'text-rose-500 animate-pulse' : 'text-primary'" />
                </div>

                <!-- Text Content (Inline) -->
                <div class="flex-1 flex items-center justify-between gap-6 overflow-hidden">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-[10px] font-black uppercase tracking-[0.25em]" 
                                :class="isUrgent ? 'text-white' : 'text-white/60'">
                                {{ variant === 'payment' ? 'Fenêtre de Paiement' : 'Période de Réservation Sécurisée' }}
                            </h3>
                            <div v-if="!isUrgent" class="h-1 w-1 rounded-full bg-primary animate-pulse"></div>
                        </div>
                        <p class="text-[10px] font-medium leading-tight tracking-wide" 
                           :class="isUrgent ? 'text-white font-bold' : 'text-white/40'">
                            {{ isUrgent 
                                ? 'Urgent : Complétez la transaction maintenant pour garantir votre réservation.' 
                                : variant === 'payment'
                                    ? 'Votre véhicule est verrouillé. Complétez le paiement pour finaliser cette réservation.'
                                    : 'Le véhicule est temporairement retenu pour vous. Procédez pour sécuriser votre réservation.'
                            }}
                        </p>
                    </div>

                    <!-- Large Timer -->
                    <div class="text-right shrink-0">
                        <span class="text-3xl font-black tabular-nums tracking-tighter" 
                              :class="isUrgent ? 'text-white' : 'text-white'">
                            {{ remainingTime }}
                        </span>
                    </div>
                </div>

                <!-- Animated Urgency Glow -->
                <div v-if="isUrgent" class="absolute -inset-2 bg-rose-500/10 animate-pulse-slow"></div>
            </div>
        </div>

        <!-- Expired State (Compact Card) -->
        <div v-else-if="isExpired" class="max-w-md mx-auto my-8">
            <div class="relative bg-rose-50 rounded-[2rem] p-6 border-2 border-dashed border-rose-200 text-center space-y-4 shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-rose-500 shadow-sm">
                    <AlertCircle class="size-8" />
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-rose-900">Selection Expired</h3>
                    <p class="text-[10px] font-bold text-rose-600/70 uppercase tracking-widest leading-relaxed mt-1">
                        Please restart your booking to continue.
                    </p>
                </div>
                <Link href="/fleet" class="block">
                    <button class="h-12 w-full rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all">
                        Return to Fleet
                    </button>
                </Link>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.compact-fade-enter-active, .compact-fade-leave-active { transition: all 0.6s ease; }
.compact-fade-enter-from, .compact-fade-leave-to { opacity: 0; transform: translateY(-10px); }

@keyframes ping-slow {
    0% { transform: scale(1); opacity: 0.1; }
    50% { transform: scale(1.05); opacity: 0.2; }
    100% { transform: scale(1); opacity: 0.1; }
}

.animate-pulse-slow {
    animation: ping-slow 2s infinite ease-in-out;
}
</style>
