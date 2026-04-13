<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref, computed } from 'vue';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { 
  AlertCircle, 
  ArrowLeft, 
  User, 
  Calendar, 
  CreditCard, 
  Ban, 
  CheckCircle,
  Receipt
} from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { index, suspend, activate } from '@/routes/admin/clients';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
  client: { id: number; name: string; email: string; is_active: boolean; created_at?: string };
  stats: { total_reservations: number; total_payments: number; total_spent: number };
  reservations: {
    data: Array<{
      id: number;
      reservation_number: string;
      start_date: string;
      end_date: string;
      total_days?: number;
      total_amount: number | string;
      status: string;
      car?: { year: number; make: string; model: string; license_plate: string } | null;
    }>;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  payments: {
    data: Array<{
      id: number;
      payment_number: string;
      amount: number | string;
      currency?: string;
      payment_method: string;
      status: string;
      processed_at?: string | null;
      reservation?: { id: number; reservation_number: string } | null;
    }>;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  currency: { symbol: string; code: string }
}>()

const showSuspendDialog = ref(false);
const processingSuspend = ref(false);

const showActivateDialog = ref(false);
const processingActivate = ref(false);

function fmtMoney(n?: number | string) {
  const v = Number(n ?? 0);
  return `${props.currency.symbol}${v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }) : '—'
}

function suspendClient() {
  processingSuspend.value = true;
  router.patch(suspend(props.client.id), {}, {
    preserveScroll: true,
    onFinish: () => { processingSuspend.value = false; },
    onSuccess: () => { showSuspendDialog.value = false; },
  });
}

function activateClient() {
  processingActivate.value = true;
  router.patch(activate(props.client.id), {}, {
    preserveScroll: true,
    onFinish: () => { processingActivate.value = false; },
    onSuccess: () => { showActivateDialog.value = false; },
  });
}

const statusStyle = computed(() => {
  const active = props.client.is_active;
  const hex = active ? '#10B981' : '#EF4444';
  const toRgb = (h: string) => [parseInt(h.slice(1,3),16), parseInt(h.slice(3,5),16), parseInt(h.slice(5,7),16)];
  const [r,g,b] = toRgb(hex);
  return { bg: `rgba(${r}, ${g}, ${b}, 0.1)`, dot: hex, text: hex, label: active ? 'Active' : 'Suspended' };
});
</script>

<template>
  <Head :title="`Client ${client.name}`" />
  <AdminLayout>
    <main class="flex-1 p-4 sm:p-8 space-y-8 sm:space-y-12 bg-background min-h-screen pb-32 max-w-[1600px] mx-auto">
      <!-- Header -->
      <div class="flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-4">
            <Link :href="index().url" class="group inline-flex items-center text-xs font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-all">
                <ArrowLeft class="size-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                Back to Clients
            </Link>
            <div class="flex items-center gap-6">
                <div class="p-4 rounded-3xl bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-100">
                    <User class="size-8" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ client.name }}</h1>
                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400 mt-2 flex items-center gap-4">
                        {{ client.email }}
                        <Badge 
                            variant="outline" 
                            class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border-none ring-1 ring-inset"
                            :style="{ backgroundColor: statusStyle.bg, color: statusStyle.text, ringColor: statusStyle.bg }"
                        >
                            {{ statusStyle.label }}
                        </Badge>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
          <Button 
              v-if="client.is_active" 
              variant="destructive" 
              @click="showSuspendDialog = true"
              class="h-14 px-8 rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-black uppercase tracking-widest transition-all border-none"
            >
              <Ban class="size-4 mr-2" /> Suspend Account
          </Button>
          <Button 
              v-else 
              @click="showActivateDialog = true"
              class="h-14 px-8 rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 text-xs font-black uppercase tracking-widest transition-all border-none"
            >
              <CheckCircle class="size-4 mr-2" /> Activate Account
          </Button>
        </div>
      </div>

      <!-- Quick Stats Bar -->
      <div class="bg-white rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-10 flex flex-wrap items-center justify-between gap-12">
        <div class="flex flex-wrap items-center gap-16">
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Spent</div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ fmtMoney(stats.total_spent) }}</div>
            </div>
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Reservations</div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.total_reservations }}</div>
            </div>
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Payments</div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.total_payments }}</div>
            </div>
        </div>
        <div class="flex items-center gap-12 lg:text-right border-l lg:border-l-slate-100 pl-12">
            <div class="space-y-3">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Member Since</div>
                <div class="text-base font-black text-slate-900 uppercase tracking-widest">{{ fmtDate(client.created_at) }}</div>
            </div>
        </div>
      </div>

      <!-- Reservations -->
      <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                <Calendar class="size-5" />
            </div>
            <h2 class="text-xl font-black tracking-tight text-slate-900">Reservation History</h2>
        </div>
        <div class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent border-b border-slate-50">
                            <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500">Ref #</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Vehicle</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Dates</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Amount</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Status</TableHead>
                            <TableHead class="h-16 px-8 text-[right]"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="r in reservations.data" :key="r.id" class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <TableCell class="px-8 py-5 font-mono text-sm font-black text-slate-900">{{ r.reservation_number }}</TableCell>
                            <TableCell>
                                <div class="font-black text-sm text-slate-900">{{ r.car ? `${r.car.year} ${r.car.make} ${r.car.model}` : '—' }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ r.car?.license_plate }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="text-xs font-black text-slate-600 flex flex-col gap-1">
                                    <span>{{ fmtDate(r.start_date) }}</span>
                                    <span class="text-slate-400">To {{ fmtDate(r.end_date) }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="text-base font-black text-slate-900">{{ fmtMoney(r.total_amount) }}</div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline" class="text-[10px] font-black uppercase tracking-widest bg-white shadow-sm ring-1 ring-slate-200">
                                    {{ r.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right px-8">
                                <Link :href="`/admin/reservations/${r.id}`">
                                    <Button variant="ghost" class="h-10 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-colors hover:bg-primary/5">
                                        View
                                    </Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="reservations.data.length === 0">
                            <TableCell colspan="6" class="h-32 text-center">
                                <div class="text-sm font-bold text-slate-400 uppercase tracking-widest">No past reservations.</div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            
            <!-- Pagination area placeholder -->
            <div v-if="reservations.links?.length > 3" class="p-4 border-t border-slate-50 flex gap-2">
                <Link
                    v-for="(link, i) in reservations.links"
                    :key="i"
                    :href="link.url || ''"
                    class="rounded-lg px-4 py-2 text-xs font-black uppercase tracking-widest transition-all"
                    :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                    :style="!link.url ? 'pointer-events: none; opacity: 0.5' : ''"
                >
                    <span v-html="link.label" />
                </Link>
            </div>
        </div>
      </div>

      <!-- Payments -->
      <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                <Receipt class="size-5" />
            </div>
            <h2 class="text-xl font-black tracking-tight text-slate-900">Payment Ledger</h2>
        </div>
        <div class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent border-b border-slate-50">
                            <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500">Receipt #</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Booking</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Amount</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Method</TableHead>
                            <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Status</TableHead>
                            <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500">Processed</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="p in payments.data" :key="p.id" class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <TableCell class="px-8 py-5 font-mono text-sm font-black text-slate-900">{{ p.payment_number }}</TableCell>
                            <TableCell>
                                <div class="font-mono text-xs font-black text-slate-500">{{ p.reservation?.reservation_number || '—' }}</div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="text-base font-black text-slate-900">{{ fmtMoney(p.amount) }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-600">{{ p.payment_method }}</div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline" class="text-[10px] font-black uppercase tracking-widest bg-white shadow-sm ring-1 ring-slate-200">
                                    {{ p.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-8 text-xs font-bold text-slate-400">
                                {{ fmtDate(p.processed_at || undefined) }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="payments.data.length === 0">
                            <TableCell colspan="6" class="h-32 text-center">
                                <div class="text-sm font-bold text-slate-400 uppercase tracking-widest">No payment records.</div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            
            <div v-if="payments.links?.length > 3" class="p-4 border-t border-slate-50 flex gap-2">
                <Link
                    v-for="(link, i) in payments.links"
                    :key="i"
                    :href="link.url || ''"
                    class="rounded-lg px-4 py-2 text-xs font-black uppercase tracking-widest transition-all"
                    :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                    :style="!link.url ? 'pointer-events: none; opacity: 0.5' : ''"
                >
                    <span v-html="link.label" />
                </Link>
            </div>
        </div>
      </div>
    </main>

    <!-- Suspend Confirmation Dialog -->
    <Dialog v-model:open="showSuspendDialog">
      <DialogContent class="sm:max-w-[500px] p-8 rounded-3xl overflow-hidden border-none shadow-2xl">
        <DialogHeader class="mb-6">
          <DialogTitle class="flex items-center gap-3 text-2xl font-black tracking-tight text-slate-900">
            <div class="p-2 rounded-xl bg-rose-50 text-rose-600">
                <AlertCircle class="size-6" />
            </div>
            Suspend User Account
          </DialogTitle>
          <DialogDescription class="text-sm font-bold text-slate-400 leading-relaxed mt-4">
            Are you sure you want to suspend this user? They will not be able to log in until re-activated.
          </DialogDescription>
        </DialogHeader>
        <Alert variant="destructive" class="border-none bg-rose-50 text-rose-900 rounded-2xl p-4">
          <AlertCircle class="size-4" />
          <AlertDescription class="font-bold text-xs">
            This action can be reverted later by an admin, but the user will be blocked immediately.
          </AlertDescription>
        </Alert>
        <DialogFooter class="mt-8 gap-3 sm:gap-0">
          <DialogClose as-child>
            <Button variant="ghost" class="rounded-xl h-12 px-6 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50">Cancel</Button>
          </DialogClose>
          <Button type="button" class="rounded-xl h-12 px-6 text-xs font-black uppercase tracking-widest bg-rose-600 hover:bg-rose-700 text-white border-none shadow-lg shadow-rose-200" :disabled="processingSuspend" @click="suspendClient">
            {{ processingSuspend ? 'Suspending...' : 'Yes, Suspend Account' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Activate Confirmation Dialog -->
    <Dialog v-model:open="showActivateDialog">
      <DialogContent class="sm:max-w-[500px] p-8 rounded-3xl overflow-hidden border-none shadow-2xl">
        <DialogHeader class="mb-6">
          <DialogTitle class="flex items-center gap-3 text-2xl font-black tracking-tight text-slate-900">
            <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                <CheckCircle class="size-6" />
            </div>
            Re-Activate User Account
          </DialogTitle>
          <DialogDescription class="text-sm font-bold text-slate-400 leading-relaxed mt-4">
            Are you sure you want to restore access for this user? They will be able to log in and create reservations again.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="mt-8 gap-3 sm:gap-0">
          <DialogClose as-child>
            <Button variant="ghost" class="rounded-xl h-12 px-6 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50">Cancel</Button>
          </DialogClose>
          <Button type="button" class="rounded-xl h-12 px-6 text-xs font-black uppercase tracking-widest bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-lg shadow-emerald-200" :disabled="processingActivate" @click="activateClient">
            {{ processingActivate ? 'Activating...' : 'Yes, Activate' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>
