<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    TrendingUp, 
    TrendingDown, 
    DollarSign, 
    Calendar, 
    Car, 
    Users, 
    PieChart, 
    BarChart3, 
    Download,
    Filter,
    ArrowUpRight,
    ArrowDownRight,
    Search
} from 'lucide-vue-next';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ref, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    stats: {
        total_revenue: number;
        revenue_change_percent: number;
        total_reservations: number;
        reservations_change_percent: number;
        average_daily_rate: number;
        adr_change_percent: number;
        occupancy_rate: number;
        occupancy_change_percent: number;
    };
    recent_performance: Array<{
        date: string;
        revenue: number;
        reservations: number;
    }>;
    top_cars: Array<{
        id: number;
        make: string;
        model: string;
        year: number;
        revenue: number;
        bookings: number;
    }>;
    status_distribution: Record<string, number>;
    filters: {
        period?: string;
        start_date?: string;
        end_date?: string;
    };
    currency: { symbol: string; code: string };
}>();

const period = ref(props.filters.period || 'last_30_days');

function doFilter() {
    router.get('/admin/reports', { period: period.value }, {
        preserveState: true,
        replace: true
    });
}

function fmtMoney(v: number) {
    return `${props.currency.symbol}${Number(v).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
}

function exportReport() {
    window.location.href = '/admin/reports/export?' + new URLSearchParams({ period: period.value }).toString();
}

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-600 ring-amber-200',
    confirmed: 'bg-blue-100 text-blue-600 ring-blue-200',
    active: 'bg-indigo-100 text-indigo-600 ring-indigo-200',
    completed: 'bg-emerald-100 text-emerald-600 ring-emerald-200',
    cancelled: 'bg-rose-100 text-rose-600 ring-rose-200',
};
</script>

<template>
    <Head title="Business Intelligence" />
    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen pb-24">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Business Intelligence" 
                    description="Analyze revenue growth, fleet performance, and customer retention metrics."
                    size="lg"
                />
                <Button @click="exportReport" class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all border-none">
                    <Download class="w-5 h-5 mr-2" /> Export PDF
                </Button>
            </div>

            <div class="mx-auto max-w-[1400px] space-y-10">
                <!-- Advanced Filters -->
                <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between bg-white p-6 rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50">
                    <div class="flex items-center gap-4 w-full lg:max-w-xs">
                        <div class="p-3 rounded-2xl bg-primary/5 text-primary shrink-0">
                            <Filter class="size-5" />
                        </div>
                        <Select v-model="period" @update:model-value="doFilter">
                            <SelectTrigger class="h-14 rounded-2xl border-none bg-slate-50 font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all">
                                <SelectValue placeholder="Select Period" />
                            </SelectTrigger>
                            <SelectContent class="rounded-2xl border-none shadow-2xl p-2">
                                <SelectItem value="today" class="rounded-xl font-bold py-3">Today</SelectItem>
                                <SelectItem value="yesterday" class="rounded-xl font-bold py-3">Yesterday</SelectItem>
                                <SelectItem value="last_7_days" class="rounded-xl font-bold py-3">Last 7 Days</SelectItem>
                                <SelectItem value="last_30_days" class="rounded-xl font-bold py-3">Last 30 Days</SelectItem>
                                <SelectItem value="this_month" class="rounded-xl font-bold py-3">This Month</SelectItem>
                                <SelectItem value="last_month" class="rounded-xl font-bold py-3">Last Month</SelectItem>
                                <SelectItem value="this_year" class="rounded-xl font-bold py-3">This Year</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        <div class="flex items-center gap-2 px-6 py-4 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-lg shadow-emerald-200">
                                <DollarSign class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600/60">Profitability</div>
                                <div class="text-sm font-black text-emerald-700 tracking-tight">+{{ stats.revenue_change_percent }}% Variance</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <!-- Total Revenue -->
                    <Card class="rounded-[2.5rem] border-none bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-8 hover:shadow-2xl transition-all">
                        <div class="flex items-start justify-between">
                            <div class="p-4 rounded-3xl bg-indigo-50 text-indigo-600">
                                <DollarSign class="h-8 w-8" />
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black" :class="stats.revenue_change_percent >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                <component :is="stats.revenue_change_percent >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3.5 w-3.5" />
                                {{ Math.abs(stats.revenue_change_percent) }}%
                            </div>
                        </div>
                        <div class="mt-8 space-y-1">
                            <div class="text-[11px] font-black uppercase tracking-widest text-slate-400">Total Revenue</div>
                            <div class="text-3xl font-black text-slate-900 tracking-tighter">{{ fmtMoney(stats.total_revenue) }}</div>
                        </div>
                    </Card>

                    <!-- Reservations -->
                    <Card class="rounded-[2.5rem] border-none bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-8 hover:shadow-2xl transition-all">
                        <div class="flex items-start justify-between">
                            <div class="p-4 rounded-3xl bg-amber-50 text-amber-600">
                                <Calendar class="h-8 w-8" />
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black" :class="stats.reservations_change_percent >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                <component :is="stats.reservations_change_percent >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3.5 w-3.5" />
                                {{ Math.abs(stats.reservations_change_percent) }}%
                            </div>
                        </div>
                        <div class="mt-8 space-y-1">
                            <div class="text-[11px] font-black uppercase tracking-widest text-slate-400">Reservations</div>
                            <div class="text-3xl font-black text-slate-900 tracking-tighter">{{ stats.total_reservations }}</div>
                        </div>
                    </Card>

                    <!-- Average Daily Rate -->
                    <Card class="rounded-[2.5rem] border-none bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-8 hover:shadow-2xl transition-all">
                        <div class="flex items-start justify-between">
                            <div class="p-4 rounded-3xl bg-primary/5 text-primary">
                                <TrendingUp class="h-8 w-8" />
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black" :class="stats.adr_change_percent >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                <component :is="stats.adr_change_percent >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3.5 w-3.5" />
                                {{ Math.abs(stats.adr_change_percent) }}%
                            </div>
                        </div>
                        <div class="mt-8 space-y-1">
                            <div class="text-[11px] font-black uppercase tracking-widest text-slate-400">Avg. Daily Rate</div>
                            <div class="text-3xl font-black text-slate-900 tracking-tighter">{{ fmtMoney(stats.average_daily_rate) }}</div>
                        </div>
                    </Card>

                    <!-- Occupancy -->
                    <Card class="rounded-[2.5rem] border-none bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-8 hover:shadow-2xl transition-all">
                        <div class="flex items-start justify-between">
                            <div class="p-4 rounded-3xl bg-rose-50 text-rose-600">
                                <Car class="h-8 w-8" />
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black" :class="stats.occupancy_change_percent >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                <component :is="stats.occupancy_change_percent >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3.5 w-3.5" />
                                {{ Math.abs(stats.occupancy_change_percent) }}%
                            </div>
                        </div>
                        <div class="mt-8 space-y-1">
                            <div class="text-[11px] font-black uppercase tracking-widest text-slate-400">Occupancy Rate</div>
                            <div class="text-3xl font-black text-slate-900 tracking-tighter">{{ stats.occupancy_rate }}%</div>
                        </div>
                    </Card>
                </div>

                <div class="grid gap-10 lg:grid-cols-3">
                    <!-- Top Performing Vehicles -->
                    <Card class="lg:col-span-2 rounded-[2.5rem] border-none bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
                        <CardHeader class="p-8 border-b border-slate-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="text-xl font-black text-slate-900">Fleet Performance</CardTitle>
                                    <CardDescription class="font-bold text-slate-400 mt-1">Top generating vehicles by total revenue.</CardDescription>
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-50 text-slate-400">
                                    <BarChart3 class="size-6" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow class="hover:bg-transparent border-none">
                                            <TableHead class="h-14 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[250px]">Vehicle Details</TableHead>
                                            <TableHead class="h-14 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[120px]">Bookings</TableHead>
                                            <TableHead class="h-14 px-8 text-right text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">Total Revenue</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="car in top_cars" :key="car.id" class="group border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                                            <TableCell class="px-8 py-5">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-12 w-16 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
                                                        <Car class="size-full p-3 text-slate-400" />
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-black text-slate-900 group-hover:text-primary transition-colors">{{ car.year }} {{ car.make }} {{ car.model }}</div>
                                                        <div class="text-[10px] font-bold text-slate-400 mt-0.5">ID: #{{ car.id }}</div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div class="text-sm font-black text-slate-600">{{ car.bookings }} <span class="text-[10px] opacity-60">Trips</span></div>
                                            </TableCell>
                                            <TableCell class="px-8 text-right">
                                                <div class="text-sm font-black text-slate-900 tracking-tight">{{ fmtMoney(car.revenue) }}</div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Distribution -->
                    <Card class="rounded-[2.5rem] border-none bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 p-8 flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <CardTitle class="text-xl font-black text-slate-900">Distribution</CardTitle>
                                <CardDescription class="font-bold text-slate-400 mt-1">Status allocation breakdown.</CardDescription>
                            </div>
                            <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-500">
                                <PieChart class="size-6" />
                            </div>
                        </div>
                        
                        <div class="flex-1 space-y-6">
                            <div v-for="(count, status) in status_distribution" :key="status" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-2 rounded-full" :class="statusColors[status]?.split(' ')[0] || 'bg-slate-300'"></div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ status }}</span>
                                    </div>
                                    <span class="text-xs font-black text-slate-900">{{ count }}</span>
                                </div>
                                <div class="h-2 w-full bg-slate-50 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-1000" 
                                        :class="statusColors[status]?.split(' ')[0] || 'bg-slate-200'"
                                        :style="{ width: `${(count / Math.max(...Object.values(status_distribution)) * 100)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 p-6 rounded-3xl bg-slate-900 text-white relative overflow-hidden group">
                            <div class="relative z-10">
                                <div class="text-[10px] font-black uppercase tracking-widest opacity-60">Report Summary</div>
                                <div class="text-lg font-black mt-2">Executive Insights</div>
                                <p class="text-[11px] font-bold opacity-40 mt-2 leading-relaxed">System performance is within optimal thresholds for the current fiscal period.</p>
                            </div>
                            <TrendingUp class="absolute -right-4 -bottom-4 size-24 opacity-10 group-hover:scale-110 transition-transform" />
                        </div>
                    </Card>
                </div>
            </div>
        </main>
    </AdminLayout>
</template>
