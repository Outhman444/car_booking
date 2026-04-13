<script setup lang="ts">
import { 
  ArrowLeft, 
  Pencil, 
  Printer, 
  User, 
  Car as CarIcon, 
  Calendar, 
  MapPin, 
  Clock, 
  Wallet, 
  Receipt,
  FileText,
  DollarSign
} from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index, edit, print, markAsPaid as markAsPaidRoute } from '@/routes/admin/reservations';

const props = defineProps<{
  reservation: any
  statusMeta: Array<{ value: string; label: string; color: string }>
  paymentStatusMeta: Array<{ value: string; label: string }>
  currency: { symbol: string; code: string }
}>()

const statusMap = computed(() => {
  const map: Record<string, { label: string; color: string }> = {}
  for (const s of props.statusMeta || []) map[s.value] = { label: s.label, color: s.color }
  return map
})

function getStatusStyle(status: string) {
  const meta = statusMap.value[status]
  if (!meta) return { bg: 'rgba(107,114,128,0.1)', text: '#6B7280', dot: '#6B7280', label: status }
  return { text: meta.color, dot: meta.color, label: meta.label }
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }) : '—'
}

function fmtDateTime(d?: string) {
    if (!d) return '—';
    return new Date(d).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function fmtMoney(n?: number | string) {
  const v = Number(n ?? 0)
  return `${props.currency.symbol}${v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

function markAsPaid() {
  if (confirm('Are you sure you want to record a manual cash payment for this reservation?')) {
    router.post(markAsPaidRoute(props.reservation.id).url);
  }
}

const isAlreadyPaid = computed(() => {
  return props.reservation.payments?.some((p: any) => p.status === 'completed');
});
</script>

<template>
  <Head :title="`Booking #${reservation?.reservation_number || ''}`" />
  <AdminLayout>
    <main class="flex-1 p-4 sm:p-8 space-y-8 sm:space-y-12 bg-background min-h-screen pb-32 max-w-[1600px] mx-auto">
      <!-- Header -->
      <div class="flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-4">
            <Link :href="index().url" class="group inline-flex items-center text-xs font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-all">
                <ArrowLeft class="size-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                Back to Reservations
            </Link>
            <div class="flex items-center gap-6">
                <div class="p-4 rounded-3xl bg-blue-50 text-blue-600 shadow-sm shadow-blue-100">
                    <FileText class="size-8" />
                </div>
                <div>
                    <Heading 
                        :title="`Booking #${reservation?.reservation_number}`" 
                        description="Comprehensive audit of the rental agreement, payment history, and schedule."
                        size="lg"
                    />
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <Link :href="edit(reservation.id).url">
                <Button variant="ghost" class="h-14 px-8 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all border border-slate-100 shadow-sm">
                    <Pencil class="size-4 mr-3" /> Edit Agreement
                </Button>
            </Link>
            <a :href="print(reservation.id).url" target="_blank" rel="noopener">
                <Button class="h-14 px-10 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all border-none">
                    <Printer class="size-5 mr-3" /> Print Contract
                </Button>
            </a>
        </div>
      </div>

      <!-- Quick Info Bar -->
      <div class="bg-white rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-10 flex flex-wrap items-center justify-between gap-12">
        <div class="flex flex-wrap items-center gap-16">
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Current Status</div>
                <StatusBadge
                    :status="reservation.status"
                    :label="getStatusStyle(reservation.status).label"
                    :color="getStatusStyle(reservation.status).dot"
                />
            </div>
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Financial Value</div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ fmtMoney(reservation.total_amount) }}</div>
            </div>
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Rental Duration</div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ reservation.total_days }} <span class="text-lg text-slate-400 font-bold uppercase tracking-widest ml-1">Days</span></div>
            </div>
        </div>
        
        <div class="flex items-center gap-12 lg:text-right border-l lg:border-l-slate-100 pl-12">
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Payment Status</div>
                <div class="text-base font-black uppercase tracking-widest" :class="isAlreadyPaid ? 'text-emerald-600' : 'text-amber-600'">{{ isAlreadyPaid ? 'Paid' : 'Unpaid' }}</div>
            </div>
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Timestamp</div>
                <div class="text-base font-black text-slate-900 uppercase tracking-widest">{{ fmtDate(reservation.created_at) }}</div>
            </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-12">
            <!-- Rental Schedule -->
            <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                <CardHeader class="p-8 pb-4">
                    <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-slate-50 text-slate-600">
                            <Calendar class="size-5" />
                        </div>
                        Rental Dates
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-50">
                        <div class="p-10 space-y-6">
                            <div class="flex items-center gap-3 text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 w-fit px-3 py-1 rounded-lg">
                                <ArrowLeft class="size-3.5 rotate-180" /> Pickup Delivery
                            </div>
                            <div class="space-y-2">
                                <div class="text-3xl font-black text-slate-900">{{ fmtDate(reservation.start_date) }}</div>
                                <div class="text-xl font-bold text-slate-400 flex items-center gap-3">
                                    <Clock class="size-6 text-slate-300" /> {{ reservation.pickup_time }}
                                </div>
                            </div>
                            <div class="pt-6 border-t border-slate-50 space-y-3">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Primary Depo / Location</div>
                                <div class="text-base font-black text-slate-700 flex items-start gap-3">
                                    <MapPin class="size-5 text-primary mt-1 shrink-0" />
                                    {{ reservation.pickup_location || 'Not Specified' }}
                                </div>
                            </div>
                        </div>
                        <div class="p-10 space-y-6 bg-slate-50/30">
                            <div class="flex items-center gap-3 text-[10px] font-black text-rose-600 uppercase tracking-widest bg-rose-50 w-fit px-3 py-1 rounded-lg">
                                <ArrowLeft class="size-3.5" /> Return Drop-off
                            </div>
                            <div class="space-y-2">
                                <div class="text-3xl font-black text-slate-900">{{ fmtDate(reservation.end_date) }}</div>
                                <div class="text-xl font-bold text-slate-400 flex items-center gap-3">
                                    <Clock class="size-6 text-slate-300" /> {{ reservation.return_time }}
                                </div>
                            </div>
                            <div class="pt-6 border-t border-slate-100 space-y-3">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Designated Return Point</div>
                                <div class="text-base font-black text-slate-700 flex items-start gap-3">
                                    <MapPin class="size-5 text-rose-500 mt-1 shrink-0" />
                                    {{ reservation.return_location || 'Not Specified' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Cancellation Details -->
            <Card v-if="reservation.status === 'cancelled'" class="shadow-xl shadow-rose-200/20 border-none ring-1 ring-rose-100 bg-rose-50/20 rounded-[2.5rem] overflow-hidden">
                <CardHeader class="p-8 pb-4">
                    <CardTitle class="text-xl font-black tracking-tight text-rose-700">Cancellation Protocol</CardTitle>
                </CardHeader>
                <CardContent class="p-8 pt-4 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3">
                            <div class="text-[10px] font-black text-rose-600/70 uppercase tracking-widest">Termination Date</div>
                            <div class="text-lg font-black text-rose-900">{{ fmtDateTime(reservation.cancelled_at) }}</div>
                        </div>
                        <div class="space-y-3">
                            <div class="text-[10px] font-black text-rose-600/70 uppercase tracking-widest">Stated Reason</div>
                            <div class="text-lg font-black text-rose-900">{{ reservation.cancellation_reason || 'No specific reason provided' }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Payment Logs -->
            <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                <CardHeader class="p-8 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div>
                        <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-slate-50 text-slate-600">
                                <Receipt class="size-5" />
                            </div>
                            Financial Ledger
                        </CardTitle>
                        <CardDescription class="text-xs font-bold uppercase tracking-widest text-slate-400 mt-2">Audit trail of all processed payments.</CardDescription>
                    </div>
                    <Button
                        v-if="!isAlreadyPaid && ['pending', 'confirmed', 'active'].includes(reservation.status)"
                        class="h-12 px-8 rounded-2xl bg-emerald-600 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all border-none"
                        @click="markAsPaid"
                    >
                        <DollarSign class="size-4 mr-2" /> Manual Cash Capture
                    </Button>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow class="hover:bg-transparent border-b border-slate-50">
                                <TableHead class="h-14 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500">Receipt #</TableHead>
                                <TableHead class="h-14 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Amount</TableHead>
                                <TableHead class="h-14 text-[10px] font-black uppercase tracking-widest text-slate-500">Method</TableHead>
                                <TableHead class="h-14 text-[10px] font-black uppercase tracking-widest text-slate-500">Legality</TableHead>
                                <TableHead class="h-14 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500">Timestamp</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="p in (reservation.payments || [])" :key="p.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50 group transition-colors">
                                <TableCell class="px-8 py-5 font-mono text-sm font-black text-slate-900">{{ p.payment_number }}</TableCell>
                                <TableCell class="text-base font-black text-slate-900 text-right">{{ fmtMoney(p.amount) }}</TableCell>
                                <TableCell class="text-xs font-black uppercase tracking-widest text-slate-600">{{ p.payment_method }}</TableCell>
                                <TableCell>
                                    <Badge 
                                        variant="outline" 
                                        class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full ring-1 ring-inset"
                                        :class="p.status === 'completed' ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-50 text-slate-500 ring-slate-200'"
                                    >
                                        {{ p.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="px-8 text-xs font-bold text-slate-400">{{ fmtDateTime(p.processed_at) }}</TableCell>
                            </TableRow>
                            <TableRow v-if="!reservation.payments || reservation.payments.length === 0">
                                <TableCell colspan="5" class="h-32 text-center">
                                    <div class="text-sm font-bold text-slate-400 uppercase tracking-widest">No financial data available.</div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Right Column: Sidebar -->
        <div class="lg:col-span-1 space-y-12">
            <!-- Client Info Card -->
            <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden transition-all hover:shadow-2xl">
                <CardHeader class="p-8 pb-4">
                    <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-slate-50 text-slate-600">
                            <User class="size-5" />
                        </div>
                        Customer Data
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-8 space-y-8 pt-4">
                    <div class="space-y-3">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Full Name</div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight">{{ reservation.user?.name || '—' }}</div>
                    </div>
                    <div class="space-y-3">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Comm. Channel</div>
                        <div class="text-base font-bold text-primary break-all">{{ reservation.user?.email || '—' }}</div>
                    </div>
                    <div v-if="reservation.user?.phone" class="space-y-3">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact Phone</div>
                        <div class="text-lg font-black text-slate-900 tracking-tight">{{ reservation.user?.phone }}</div>
                    </div>
                    <Button variant="ghost" class="w-full h-12 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">View Client Profile</Button>
                </CardContent>
            </Card>

            <!-- Vehicle Info Card -->
            <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                <CardHeader class="p-8 pb-4">
                    <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-slate-50 text-slate-600">
                            <CarIcon class="size-5" />
                        </div>
                        Vehicle Specification
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-8 pt-4">
                    <div v-if="reservation.car" class="space-y-8">
                        <div class="aspect-video rounded-[2rem] overflow-hidden ring-1 ring-slate-100 bg-slate-50 p-6 flex items-center justify-center">
                            <img 
                                v-if="reservation.car.image_url" 
                                :src="reservation.car.image_url" 
                                class="w-full h-full object-contain filter drop-shadow-2xl"
                                alt="Car Image"
                            />
                            <CarIcon v-else class="size-16 text-slate-200 opacity-50" />
                        </div>
                        <div class="space-y-4">
                            <div class="text-3xl font-black text-slate-900 tracking-tight">
                                {{ reservation.car.make }} {{ reservation.car.model }}
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black uppercase tracking-widest bg-slate-100 px-3 py-1 rounded-lg text-slate-500">{{ reservation.car.year }}</span>
                                <span class="text-[10px] font-mono font-black uppercase tracking-[0.2em] bg-slate-900 text-white px-3 py-1 rounded-lg">{{ reservation.car.license_plate }}</span>
                            </div>
                        </div>
                        <Button variant="ghost" class="w-full h-12 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">Vehicle Audit</Button>
                    </div>
                    <div v-else class="py-12 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">
                        Waiting for vehicle assignment.
                    </div>
                </CardContent>
            </Card>

            <!-- Cost Breakdown Card -->
            <Card class="shadow-xl shadow-blue-500/10 border-none ring-1 ring-blue-500/10 bg-primary/[0.02] rounded-[2.5rem] overflow-hidden">
                <CardHeader class="p-8 pb-4">
                    <CardTitle class="text-xl font-black tracking-tight text-blue-900 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-blue-500/10 text-primary">
                            <Wallet class="size-5" />
                        </div>
                        Financial Resolution
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-8 space-y-6 pt-4">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Base Rate ({{ reservation.total_days }}d)</div>
                            <div class="text-sm font-black text-slate-900">{{ fmtMoney(reservation.daily_rate) }}</div>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Gross Rental Value</div>
                            <div class="text-base font-black text-slate-900">{{ fmtMoney(reservation.subtotal) }}</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Statutory Tax & Fees</div>
                            <div class="text-sm font-black text-slate-900">{{ fmtMoney(reservation.tax_amount) }}</div>
                        </div>
                        <div v-if="reservation.discount_amount > 0" class="flex items-center justify-between text-emerald-600 bg-emerald-50/50 p-2 rounded-xl ring-1 ring-emerald-100">
                            <div class="text-[10px] font-black uppercase tracking-widest">Loyalty Deduction</div>
                            <div class="text-sm font-black">-{{ fmtMoney(reservation.discount_amount) }}</div>
                        </div>
                    </div>
                    
                    <div class="pt-6 mt-4 border-t-2 border-dashed border-blue-500/10 flex items-center justify-between">
                        <div class="text-sm font-black text-primary uppercase tracking-[0.2em]">Net Total</div>
                        <div class="text-4xl font-black text-primary tracking-tighter">{{ fmtMoney(reservation.total_amount) }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>
      </div>
    </main>
  </AdminLayout>
</template>
