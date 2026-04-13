<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { fleet } from '@/routes';
import { index } from '@/routes/client/reservations';
import { ref, computed } from 'vue';
import axios from 'axios';
import { Loader2, CreditCard, Banknote, Printer, CheckCircle, TriangleAlert, ClipboardList, LifeBuoy } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import BookingStepper from '@/components/BookingStepper.vue';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const $page = usePage<any>();
const reservation = $page.props.reservation;
const isProcessing = ref(false);

const paymentMethods = computed(() => $page.props.paymentMethods as any[]);
const isAgencyEnabled = computed(() => paymentMethods.value.some(m => m.value === 'agency'));
const hasOnlineMethods = computed(() => paymentMethods.value.some(m => ['stripe', 'paypal'].includes(m.value)));

const showPaymentError = ref(false);
const paymentErrorMessage = ref('');

async function confirmAtAgency() {
    if (isProcessing.value) return;
    
    isProcessing.value = true;
    try {
        const response = await axios.post(`/client/payment/${reservation.id}/agency`);
        if (response.data.redirect_url) {
            router.visit(response.data.redirect_url);
        }
    } catch {
        paymentErrorMessage.value = 'Failed to process request. Please try again.';
        showPaymentError.value = true;
    } finally {
        isProcessing.value = false;
    }
}
</script>

<template>
    <HomeLayout>
        <div class="min-h-screen bg-white py-12">
            <div class="mx-auto max-w-7xl px-6 pt-4">
                <!-- Status Stepper -->
                <BookingStepper :current-step="2" class="mb-12" />

                <!-- Clean success header -->
                <div class="mb-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full" :class="reservation.status === 'pending' ? 'bg-primary/10' : 'bg-green-100'">
                        <component :is="reservation.status === 'pending' ? ClipboardList : CheckCircle" class="h-8 w-8" :class="reservation.status === 'pending' ? 'text-primary' : 'text-emerald-600'" />
                    </div>
                    <h1 class="mb-2 text-3xl font-black tracking-tight text-slate-900">
                        <template v-if="reservation.status === 'pending'">
                            Review Your <span class="text-primary">Booking</span>
                        </template>
                        <template v-else>
                            Booking <span class="text-primary">Confirmed</span>
                        </template>
                    </h1>
                    <p class="text-sm font-bold text-slate-400 text-center uppercase tracking-widest">
                        <template v-if="reservation.status === 'pending'">
                            Please verify the details below before proceeding to payment
                        </template>
                        <template v-else>
                            Reservation #{{ reservation.reservation_number }}
                        </template>
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Main Details -->
                    <div class="space-y-8 lg:col-span-2">
                        <!-- Car Information -->
                        <div class="rounded-3xl border-none bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Vehicle Details</h2>
                            <div class="flex flex-col sm:flex-row items-start gap-8">
                                <div class="relative group">
                                    <div class="absolute inset-0 bg-primary/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <img
                                        :src="reservation.car.image_url"
                                        :alt="`${reservation.car.make} ${reservation.car.model}`"
                                        class="h-32 w-48 rounded-2xl object-cover relative ring-1 ring-slate-100 shadow-lg"
                                    />
                                </div>
                                <div class="space-y-4 flex-1">
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900">
                                            {{ reservation.car.make }}
                                            <span class="text-primary">{{ reservation.car.model }}</span>
                                        </h3>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">
                                            Model Year {{ reservation.car.year }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="bg-primary/5 text-primary px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest ring-1 ring-ring/10">
                                            {{ reservation.car.fuel_type }}
                                        </span>
                                        <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                            Premium Fleet
                                        </span>
                                    </div>
                                    <p class="text-sm leading-relaxed text-slate-500 font-medium">
                                        {{ reservation.car.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rental Details -->
                        <div class="rounded-3xl border-none bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Logistics Information</h2>
                            <div class="grid gap-12 md:grid-cols-2">
                                <div class="space-y-6">
                                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary"></div> Schedule
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Departure:</span>
                                            <span class="font-black text-slate-900">{{ new Date(reservation.start_date).toLocaleDateString() }}</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Arrival:</span>
                                            <span class="font-black text-slate-900">{{ new Date(reservation.end_date).toLocaleDateString() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-6">
                                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary"></div> Venues
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Collection:</span>
                                            <span class="font-black text-slate-900">{{ reservation.pickup_location }}</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Return:</span>
                                            <span class="font-black text-slate-900">{{ reservation.return_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="rounded-3xl border-none bg-slate-900 p-8 shadow-xl shadow-slate-200 text-white">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-500">Client Details</h2>
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Full Name</span>
                                    <span class="text-lg font-black">{{ reservation.user.name }}</span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Email Address</span>
                                    <span class="text-lg font-black text-primary">{{ reservation.user.email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Price Summary -->
                        <div class="rounded-3xl border-none bg-white p-8 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Financial Summary</h2>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Current Status:</span>
                                    <span class="rounded-full bg-amber-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-600 ring-1 ring-amber-200">
                                        {{ reservation.status }}
                                    </span>
                                </div>
                                <div class="border-t-2 border-slate-50 pt-6">
                                    <div class="flex items-end justify-between">
                                        <div class="space-y-1">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Amount</span>
                                            <p class="text-4xl font-black text-slate-900">
                                                {{ $page.props.currency.symbol }}{{ Math.floor(parseFloat(reservation.total_amount)) }}<span class="text-lg opacity-30">.{{ parseFloat(reservation.total_amount).toFixed(2).split('.')[1] }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Support Assistance -->
                        <div class="rounded-3xl border-none bg-sky-500/5 p-8 shadow-xl shadow-sky-500/5 ring-1 ring-sky-500/20">
                            <h2 class="mb-4 flex items-center text-sm font-black uppercase tracking-widest text-sky-600 gap-3">
                                <div class="p-2 bg-sky-500/10 rounded-xl"><LifeBuoy class="size-4" /></div> Need Assistance?
                            </h2>
                            <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                Our support team is available 24/7. If you have questions about your reservation or need to request changes, please don't hesitate to contact us.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-4 pt-4">
                            <template v-if="reservation.status === 'pending'">
                                <a v-if="hasOnlineMethods" :href="`/client/payment/${reservation.id}`" class="h-16 flex w-full items-center justify-center gap-3 rounded-[1.25rem] bg-primary px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-white transition-all duration-300 hover:bg-blue-700 shadow-2xl shadow-primary/20 hover:-translate-y-1">
                                    <CreditCard class="h-4 w-4" /> Secure Online Payment
                                </a>
                                <button v-if="isAgencyEnabled" @click="confirmAtAgency" :disabled="isProcessing" class="h-16 flex w-full items-center justify-center gap-3 rounded-[1.25rem] bg-white border-none px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-slate-900 transition-all duration-300 ring-1 ring-slate-200 hover:bg-slate-50 shadow-lg disabled:opacity-50">
                                    <Loader2 v-if="isProcessing" class="h-4 w-4 animate-spin text-primary" />
                                    <Banknote v-else class="h-4 w-4 text-primary" /> Pay at Branch (Cash)
                                </button>
                            </template>
                            <a :href="index.url()" class="h-16 flex items-center justify-center w-full rounded-[1.25rem] bg-slate-900 px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-white transition-all duration-300 hover:bg-black shadow-2xl shadow-slate-200">
                                Dashboard Access
                            </a>
                            <a :href="fleet.url()" class="h-16 flex items-center justify-center w-full rounded-[1.25rem] border-none bg-white px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-slate-400 transition-all duration-300 hover:bg-slate-50 ring-1 ring-slate-100">
                                Continue Browsing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Error Dialog -->
        <Dialog :open="showPaymentError" @update:open="showPaymentError = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-red-600">
                        <TriangleAlert class="size-5" /> Operation Failed
                    </DialogTitle>
                    <DialogDescription class="text-gray-600 pt-2 font-medium">
                        {{ paymentErrorMessage }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="sm:justify-start pt-4">
                    <Button type="button" variant="secondary" @click="showPaymentError = false" class="rounded-xl font-bold bg-gray-100 hover:bg-gray-200">
                        Try Again
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </HomeLayout>
</template>
