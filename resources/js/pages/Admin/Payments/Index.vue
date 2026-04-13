<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import Pagination from '@/components/Pagination.vue';
import { 
    Hash, 
    User, 
    Calendar, 
    DollarSign, 
    Wallet, 
    Activity, 
    Clock 
} from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';

const props = defineProps<{
    payments: {
        data: Array<{
            id: number;
            payment_number: string;
            amount: number | string;
            currency?: string;
            payment_method: string;
            status: string;
            processed_at?: string | null;
            user?: { id: number; name: string; email: string } | null;
            reservation?: { id: number; reservation_number: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    statuses: Array<{ value: string; label: string; color: string }>;
    currency: { symbol: string; code: string };
}>();

function fmtMoney(n?: number | string) {
    const v = Number(n ?? 0);
    return `${props.currency.symbol}${v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function fmtDate(d?: string | null) {
  return d ? new Date(d).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }) : '—'
}

// Generate status colors based on the colors from the backend
const statusColors = computed(() => {
    const colors: Record<string, { bg: string; text: string; dot: string }> = {};

    for (const data of (props.statuses || [])) {
        // Convert hex to RGB for the background with opacity
        const hex = data.color?.replace('#', '') || '6B7280';
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);

        colors[data.value] = {
            bg: `rgba(${r}, ${g}, ${b}, 0.1)`,
            text: data.color,
            dot: data.color,
        };
    }

    return colors;
});

const getStatusColor = (status: string) => {
    return (
        statusColors.value[status] || {
            bg: 'rgba(107, 114, 128, 0.1)',
            text: '#6B7280',
            dot: '#6B7280',
        }
    );
};
</script>

<template>
    <Head title="Payments" />
    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen pb-32">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Financial Ledger" 
                    description="Monitor all processed transactions, refunds, and payment statuses across the platform."
                    size="lg"
                />
            </div>

            <div class="mx-auto max-w-[1400px]">
                <Card class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 border-none overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="hover:bg-transparent border-b border-slate-50">
                                    <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">
                                        <div class="flex items-center gap-3"><Hash class="size-4" /> Reference</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[200px]">
                                        <div class="flex items-center gap-3"><User class="size-4" /> Client</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">
                                        <div class="flex items-center gap-3"><Calendar class="size-4" /> Booking</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right min-w-[120px]">
                                        <div class="flex items-center justify-end gap-3"><DollarSign class="size-4" /> Amount</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 pl-8 min-w-[120px]">
                                        <div class="flex items-center gap-3"><Wallet class="size-4" /> Method</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[120px]">
                                        <div class="flex items-center gap-3"><Activity class="size-4" /> Status</div>
                                    </TableHead>
                                    <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[200px]">
                                        <div class="flex items-center gap-3"><Clock class="size-4" /> Timestamp</div>
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="p in props.payments.data" :key="p.id" class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                    <TableCell class="px-8 py-5">
                                        <span class="font-mono text-sm font-black text-slate-900">{{ p.payment_number }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-black text-sm text-slate-900">
                                            {{ p.user?.name || '—' }}
                                        </div>
                                        <div class="text-[10px] font-bold text-slate-400 mt-1">
                                            {{ p.user?.email }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Link
                                            v-if="p.reservation"
                                            :href="`/admin/reservations/${p.reservation.id}`"
                                            class="font-mono text-[11px] font-black text-slate-500 hover:text-primary transition-colors"
                                        >
                                            {{ p.reservation.reservation_number }}
                                        </Link>
                                        <span v-else class="text-[11px] font-bold text-slate-300">—</span>
                                    </TableCell>
                                    <TableCell class="text-right font-black text-sm text-slate-900 tracking-tight">
                                        {{ fmtMoney(p.amount) }}
                                    </TableCell>
                                    <TableCell class="pl-8">
                                        <Badge variant="secondary" class="uppercase text-[9px] font-black tracking-widest px-3 py-0.5 rounded-full bg-slate-100 text-slate-600 border-none">{{ p.payment_method }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge 
                                            variant="outline"
                                            class="text-[9px] font-black uppercase tracking-widest px-3 py-0.5 rounded-full border-none ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: getStatusColor(p.status).bg,
                                                color: getStatusColor(p.status).text,
                                                boxShadow: `0 0 0 1px ${getStatusColor(p.status).dot}40 inset`
                                            }"
                                        >
                                            {{ p.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="px-8 text-[11px] font-bold text-slate-400">
                                        {{ fmtDate(p.processed_at) }}
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.payments.data.length === 0">
                                    <TableCell colspan="7" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center gap-4">
                                            <div class="p-6 rounded-full bg-slate-50 ring-1 ring-slate-100">
                                                <Wallet class="size-8 text-slate-300" />
                                            </div>
                                            <div class="text-lg font-black text-slate-900">No Payments Processed</div>
                                            <p class="text-sm font-bold text-slate-400">There are no financial transactions matching your criteria.</p>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </Card>

                <div class="mt-8 mb-12">
                    <Pagination :links="props.payments.links" />
                </div>
            </div>
        </main>
    </AdminLayout>
</template>
