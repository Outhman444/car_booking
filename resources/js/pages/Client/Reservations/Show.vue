<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    CreditCard, 
    AlertCircle, 
    Printer, 
    ChevronLeft, 
    Calendar, 
    MessageSquare, 
    MapPin, 
    User, 
    Car as CarIcon,
    Wallet,
    CheckCircle2,
    Clock,
    FileText,
    ShieldCheck,
    ArrowRight
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { 
    Alert, 
    AlertDescription, 
    AlertTitle 
} from '@/components/ui/alert';
import BookingStepper from '@/components/BookingStepper.vue';

const props = defineProps<{
  reservation: any
  statusMeta: Array<{ value: string; label: string; color: string }>
  paymentStatusMeta: Array<{ value: string; label: string }>
  currency: { symbol: string; code: string }
  hasPayment: boolean
}>()

const $page = usePage();

const getStatusStyle = (status: string) => {
    if (!status) return 'bg-slate-100/50 text-slate-700 ring-1 ring-slate-200';
    switch (status.toLowerCase()) {
        case 'pending': return 'bg-amber-50 text-amber-600 ring-1 ring-amber-200';
        case 'confirmed': return 'bg-primary/5 text-primary ring-1 ring-ring/20';
        case 'active': return 'bg-violet-50 text-violet-600 ring-1 ring-violet-200';
        case 'completed': return 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200';
        case 'cancelled': return 'bg-rose-50 text-rose-600 ring-1 ring-rose-200';
        case 'no_show': return 'bg-slate-100 text-slate-500 ring-1 ring-slate-300';
        default: return 'bg-slate-50 text-slate-600 ring-1 ring-slate-200';
    }
};

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'
}

function fmtMoney(n?: number | string) {
  const v = Number(n ?? 0)
  return `${props.currency?.symbol || '$'}${v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const isPayAtAgencySelected = computed(() => {
    return props.reservation?.notes && props.reservation.notes.toLowerCase().includes('agency');
});
</script>

<template>
    <Head :title="`Booking #${reservation?.reservation_number}`" />
    <ClientLayout>
        <div class="space-y-12 pb-24 p-6 lg:p-10 max-w-[1600px] mx-auto">
            
            <!-- Booking Stepper -->
            <BookingStepper v-if="['pending', 'confirmed', 'active'].includes(reservation.status)" :current-step="4" class="mb-4 xl:-mt-6 xl:mb-10 max-w-4xl mx-auto" />

            <!-- Alert Section: Confirmed Status -->
            <transition name="slide-up">
                <div v-if="['confirmed', 'pending', 'active'].includes(reservation.status)" class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-primary/10 rounded-[2.5rem] blur opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative rounded-[2.25rem] bg-white ring-1 ring-slate-100 p-10 text-slate-900 shadow-2xl shadow-blue-900/5 overflow-hidden">
                        <!-- Decorative background element -->
                        <div class="absolute -right-16 -top-16 opacity-[0.03]">
                            <ShieldCheck class="size-64 text-primary" />
                        </div>
                        
                        <div class="flex flex-col gap-10 lg:flex-row lg:items-center relative z-10">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-blue-50 text-primary ring-1 ring-blue-100 shadow-inner">
                                <ShieldCheck class="size-10" />
                            </div>
                            <div class="flex-1 space-y-6">
                                <div>
                                    <h2 class="text-3xl font-black tracking-tight mb-2 text-slate-900">Mandatory Agency Rules</h2>
                                    <p class="text-sm font-black text-slate-500 uppercase tracking-[0.2em]">Required for vehicle collection venue</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pt-2">
                                    <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-100 hover:bg-slate-100/50 transition-colors">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-3">Checklist 1</p>
                                        <p class="text-base font-black text-slate-800">Print Formal Statement</p>
                                        <p class="text-xs text-slate-500 mt-1.5 font-semibold leading-relaxed">Please download the formal statement to your device or print it to present at the agency.</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-100 hover:bg-slate-100/50 transition-colors">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-3">Checklist 2</p>
                                        <p class="text-base font-black text-slate-800">Required Documents</p>
                                        <p class="text-xs text-slate-500 mt-1.5 font-semibold whitespace-pre-wrap leading-relaxed">{{ $page.props.settings.rental_terms || 'Valid ID/Passport and Driver\'s License.' }}</p>
                                    </div>
                                    <div v-if="reservation.status === 'pending' && reservation.notes && reservation.notes.includes('Pay at Agency')" class="rounded-2xl bg-yellow-50 p-6 ring-1 ring-yellow-200">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-yellow-600 mb-3">Time Sensitive</p>
                                        <p class="text-base font-black text-yellow-900">Cash Payment Deadline</p>
                                        <p class="text-xs text-yellow-800 mt-1.5 font-semibold leading-relaxed">Attend the agency within <strong class="bg-yellow-200 px-1 rounded">{{ $page.props.settings.cash_reservation_timeout || 24 }} hours</strong> for cash payment or risk auto-cancellation.</p>
                                    </div>
                                </div>
                                <!-- Button removed to prevent duplication: Use the top Action Bar button instead -->
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Action Bar -->
            <div class="flex flex-col gap-8 md:flex-row md:items-end md:justify-between px-2">
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <Link href="/client/reservations" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-white ring-1 ring-slate-100 shadow-sm hover:bg-slate-50 transition-colors">
                            <ChevronLeft class="size-5 text-slate-900" />
                        </Link>
                        <h1 class="text-4xl font-black tracking-tighter text-slate-900">
                            Booking <span class="bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent">Information</span>
                        </h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <Badge variant="outline" class="h-8 rounded-lg border-slate-200 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ref: {{ reservation.reservation_number }}</Badge>
                        <Badge :class="['h-8 rounded-lg px-4 text-[10px] font-black uppercase tracking-[0.2em] border-none shadow-none', getStatusStyle(reservation.status)]">
                            {{ reservation.status }}
                        </Badge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <Button as-child variant="outline" class="h-14 rounded-2xl border-none bg-white ring-1 ring-slate-200 hover:bg-slate-50 font-black uppercase tracking-widest text-[10px] px-8 shadow-sm transition-all hover:shadow-md">
                        <a :href="`/client/reservations/${reservation.id}/print`" target="_blank">
                            <FileText class="mr-3 size-4 text-primary" /> Download Statement
                        </a>
                    </Button>
                    <template v-if="!hasPayment && reservation.status === 'pending' && !isPayAtAgencySelected">
                        <!-- Fresh reservation needing payment -->
                        <Button as-child class="h-14 rounded-2xl bg-slate-900 hover:bg-black font-black uppercase tracking-widest text-[10px] px-10 text-white shadow-xl shadow-slate-200 border-none transition-all hover:-translate-y-1">
                            <Link :href="`/client/payment/${reservation.id}`">
                                <CreditCard class="mr-3 size-4 text-primary" /> Finalize Payment
                            </Link>
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Main Info Grid -->
            <div class="grid gap-10 lg:grid-cols-3">
                <!-- Vehicle Card -->
                <Card class="rounded-[2.5rem] border-none bg-white p-10 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100 overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-[0.03]">
                        <CarIcon class="size-48" />
                    </div>
                    <CardHeader class="px-0 pt-0 pb-8 border-b border-slate-50 flex-row items-center justify-between space-y-0 relative z-10">
                        <div>
                            <CardTitle class="text-2xl font-black tracking-tight text-slate-900">Asset Data</CardTitle>
                            <CardDescription class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Vehicle Specifications</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-10 space-y-10 relative z-10">
                        <div v-if="reservation.car" class="space-y-8 text-center">
                            <div class="group relative">
                                <div class="absolute -inset-2 bg-gradient-to-r from-primary/10 to-blue-600/10 rounded-3xl blur opacity-0 group-hover:opacity-100 transition duration-500"></div>
                                <div class="relative aspect-video overflow-hidden rounded-[2rem] bg-slate-50 ring-1 ring-slate-100 flex items-center justify-center p-6 transition-transform duration-500 group-hover:scale-[1.02]">
                                    <img v-if="reservation.car.image_url" :src="reservation.car.image_url" class="h-full w-full object-contain" />
                                    <CarIcon v-else class="size-24 text-slate-200" />
                                </div>
                            </div>
                            <div class="space-y-3">
                                <h4 class="text-3xl font-black text-slate-900 tracking-tighter">{{ reservation.car.make }} <span class="text-primary">{{ reservation.car.model }}</span></h4>
                                <div class="flex flex-wrap justify-center gap-3">
                                    <Badge variant="secondary" class="rounded-full px-4 py-1 text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 border-none">{{ reservation.car.year }}</Badge>
                                    <Badge variant="secondary" class="rounded-full px-4 py-1 text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 border-none">{{ reservation.car.license_plate }}</Badge>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-20 text-slate-300 font-black uppercase tracking-widest text-xs">No active asset</div>
                    </CardContent>
                </Card>

                <!-- Logistics Card -->
                <Card class="rounded-[2.5rem] border-none bg-white p-10 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100 lg:col-span-2 overflow-hidden">
                    <CardHeader class="px-0 pt-0 pb-8 border-b border-slate-50 flex-row items-center justify-between space-y-0">
                        <div>
                            <CardTitle class="text-2xl font-black tracking-tight text-slate-900">Journey Logistics</CardTitle>
                            <CardDescription class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Schedule & Venues</CardDescription>
                        </div>
                        <Sync class="size-6 text-slate-200" />
                    </CardHeader>
                    <CardContent class="grid gap-12 pt-12 sm:grid-cols-2">
                        <div class="space-y-10 px-4">
                            <div class="relative pl-12 space-y-2">
                                <div class="absolute left-0 top-0 h-8 w-8 rounded-2xl bg-primary/10 flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                                </div>
                                <div class="absolute left-4 top-10 bottom-0 w-[1px] bg-gradient-to-b from-primary/20 to-transparent"></div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Collection Point</p>
                                <p class="text-xl font-black text-slate-900 leading-tight">{{ fmtDate(reservation.start_date) }} <span class="text-primary opacity-50 ml-1">@</span> {{ reservation.pickup_time }}</p>
                                <p class="flex items-center gap-2 text-sm font-bold text-slate-500"><MapPin class="size-3.5 text-primary" /> {{ reservation.pickup_location }}</p>
                            </div>
                            <div class="relative pl-12 space-y-2">
                                <div class="absolute left-0 top-0 h-8 w-8 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-slate-400"></div>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Return Point</p>
                                <p class="text-xl font-black text-slate-900 leading-tight">{{ fmtDate(reservation.end_date) }} <span class="text-primary opacity-50 ml-1">@</span> {{ reservation.return_time }}</p>
                                <p class="flex items-center gap-2 text-sm font-bold text-slate-500"><MapPin class="size-3.5" /> {{ reservation.return_location }}</p>
                            </div>
                        </div>

                        <div class="space-y-8 rounded-[2rem] bg-slate-50 p-10 flex flex-col justify-center text-center ring-1 ring-slate-100 hover:bg-slate-100/50 transition-colors duration-500">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-xl shadow-slate-200/50">
                                <Clock class="size-10 text-primary" />
                            </div>
                            <div>
                                <p class="text-6xl font-black text-slate-900 tracking-tighter">{{ reservation.total_days }}</p>
                                <p class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 mt-2">Days of Rental</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Client Card -->
                <Card class="rounded-[2.5rem] border-none bg-white p-10 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100">
                    <CardHeader class="px-0 pt-0 pb-8 border-b border-slate-50 flex-row items-center justify-between space-y-0">
                        <div>
                            <CardTitle class="text-2xl font-black tracking-tight text-slate-900">Contract Holder</CardTitle>
                            <CardDescription class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Identity Profile</CardDescription>
                        </div>
                        <User class="size-6 text-slate-200" />
                    </CardHeader>
                    <CardContent class="pt-10 space-y-6">
                        <div class="flex items-center gap-6 p-6 rounded-[1.5rem] bg-slate-50 ring-1 ring-slate-100 group hover:bg-white transition-all duration-300">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-md group-hover:scale-110 transition-transform">
                                <User class="size-7 text-primary" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Legal Name</p>
                                <p class="text-lg font-black text-slate-900 truncate">{{ reservation.user?.name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6 p-6 rounded-[1.5rem] bg-slate-50 ring-1 ring-slate-100 group hover:bg-white transition-all duration-300">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-md group-hover:scale-110 transition-transform">
                                <MessageSquare class="size-7 text-primary" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Electronic Address</p>
                                <p class="text-sm font-black text-slate-900 truncate">{{ reservation.user?.email }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Financial Card -->
                <Card class="rounded-[2.5rem] border-none bg-white p-10 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100 lg:col-span-2 overflow-hidden">
                    <CardHeader class="px-0 pt-0 pb-8 border-b border-slate-50 flex-row items-center justify-between space-y-0">
                        <div>
                            <CardTitle class="text-2xl font-black tracking-tight text-slate-900">Financial Ledger</CardTitle>
                            <CardDescription class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Billing Integrity</CardDescription>
                        </div>
                        <Wallet class="size-6 text-slate-200" />
                    </CardHeader>
                    <CardContent class="pt-12 grid gap-12 md:grid-cols-2">
                        <div class="space-y-6">
                            <div class="flex justify-between items-center text-xs font-black uppercase tracking-widest text-slate-500">
                                <span>Base Rental ({{ fmtMoney(reservation.daily_rate) }} × {{ reservation.total_days }})</span>
                                <span class="text-slate-900">{{ fmtMoney(reservation.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs font-black uppercase tracking-widest text-slate-500">
                                <span>Service Charges & Tax</span>
                                <span class="text-slate-900">{{ fmtMoney(reservation.tax_amount) }}</span>
                            </div>
                            <div v-if="Number(reservation.discount_amount) > 0" class="flex justify-between items-center text-xs font-black uppercase tracking-widest text-emerald-500 italic">
                                <span>Loyalty/Promo Discount</span>
                                <span>-{{ fmtMoney(reservation.discount_amount) }}</span>
                            </div>
                            <div class="pt-8 border-t-2 border-slate-50">
                                <div class="flex items-end justify-between">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Settlement Total</p>
                                        <p class="text-5xl font-black text-slate-900 tracking-tighter">{{ fmtMoney(reservation.total_amount) }}</p>
                                    </div>
                                    <Badge :class="['rounded-full py-2 px-6 text-[10px] font-black uppercase tracking-[0.2em] border-none shadow-none', getStatusStyle(reservation.payment_status || 'unpaid')]">
                                        {{ reservation.payment_status || 'unpaid' }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mini Transaction Log -->
                        <div class="space-y-6 rounded-[2rem] bg-slate-900 p-8 text-white shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                           <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform duration-700">
                                <CreditCard class="size-32" />
                           </div>
                           <div class="flex items-center justify-between mb-4 relative z-10">
                                <h5 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">Ledger History</h5>
                                <CheckCircle2 class="size-4 text-primary" />
                            </div>
                            <div v-if="reservation.payments?.length" class="space-y-6 relative z-10">
                                <div v-for="p in reservation.payments" :key="p.id" class="flex items-center justify-between border-b border-white/5 pb-4 last:border-0 last:pb-0 group/tx">
                                    <div>
                                        <p class="text-sm font-black text-white group-hover/tx:text-primary transition-colors">{{ p.payment_method }}</p>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ fmtDate(p.processed_at) }}</p>
                                    </div>
                                    <p class="text-lg font-black text-white group-hover/tx:text-primary transition-colors">{{ fmtMoney(p.amount) }}</p>
                                </div>
                            </div>
                            <div v-else class="text-center py-10 space-y-2 relative z-10">
                                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.3em] italic">No transaction records found</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Cancellation Context -->
            <transition name="slide-up">
                <div v-if="reservation.status === 'cancelled'" class="rounded-[2.5rem] border-none bg-rose-50 p-12 shadow-inner ring-1 ring-rose-100 overflow-hidden relative group">
                     <div class="absolute -right-8 -bottom-8 opacity-[0.03] group-hover:scale-110 transition-transform duration-1000">
                         <AlertCircle class="size-64" />
                     </div>
                     <div class="flex flex-col lg:flex-row items-start gap-10 relative z-10">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-rose-500 text-white shadow-2xl shadow-rose-200">
                            <AlertCircle class="size-10" />
                        </div>
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-3xl font-black text-rose-900 tracking-tight">System Cancellation</h3>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-400 mt-2">Operational Context Recorded</p>
                            </div>
                            <div class="max-w-3xl space-y-6">
                                <p class="text-xl font-bold text-rose-800/80 leading-relaxed italic border-l-4 border-rose-200 pl-6">"{{ reservation.cancellation_reason || 'No specific metadata provided by the facility operator.' }}"</p>
                                <div class="flex items-center gap-4">
                                    <Clock class="size-4 text-rose-300" />
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-400">Timestamp: {{ reservation.cancelled_at ? new Date(reservation.cancelled_at).toLocaleString() : '—' }}</span>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>
            </transition>
        </div>
    </ClientLayout>
</template>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>

