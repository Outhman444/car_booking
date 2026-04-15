<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { show } from '@/routes/client/reservations';
import { computed } from 'vue';
import {
    Hash,
    Car,
    Calendar,
    ChevronRight,
    Search
} from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { trans } from '@/lib/translations';

const $page = usePage();

const props = defineProps<{
    reservations: {
        data: Array<{
            id: number;
            reservation_number: string;
            car: {
                id: number;
                make: string;
                model: string;
                year: number;
                license_plate: string;
            } | null;
            start_date: string;
            end_date: string;
            total_days: number;
            total_amount: number | string;
            status: string;
            payment_status: string;
            total_price_formatted?: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    currency?: { symbol: string; code: string }
}>();

const currency = computed(() => props.currency || ($page.props as any).currency || { symbol: '$', code: 'USD' });

const navigateToReservation = (id: number) => {
    router.visit(show(id).url);
};

const getStatusStyle = (status: string) => {
    if (!status) return 'bg-slate-100/50 text-slate-700 border-slate-200/50';
    switch (status.toLowerCase()) {
        case 'pending': return 'bg-amber-100/50 text-amber-700 border-amber-200/50';
        case 'confirmed': return 'bg-emerald-100/50 text-emerald-700 border-emerald-200/50';
        case 'active': return 'bg-violet-100/50 text-violet-700 border-violet-200/50';
        case 'completed': return 'bg-blue-100/50 text-blue-700 border-blue-200/50';
        case 'cancelled': return 'bg-rose-100/50 text-rose-700 border-rose-200/50';
        case 'no_show': return 'bg-slate-100/50 text-slate-600 border-slate-300/50';
        default: return 'bg-slate-100/50 text-slate-700 border-slate-200/50';
    }
};

const getPaymentStatusStyle = (status: string) => {
    if (!status) return 'bg-amber-100/50 text-amber-700 border-amber-200/50';
    switch (status.toLowerCase()) {
        case 'paid': return 'bg-emerald-100/50 text-emerald-700 border-emerald-200/50';
        case 'refunded': return 'bg-blue-100/50 text-blue-700 border-blue-200/50';
        case 'partially_refunded': return 'bg-amber-100/50 text-amber-700 border-amber-200/50';
        default: return 'bg-amber-100/50 text-amber-700 border-amber-200/50';
    }
};

function formatStatus(status: string): string {
    return trans('reservation', status);
}

function formatPaymentStatus(status: string): string {
    return trans('payment', status);
}
</script>

<template>
    <Head title="Mes Réservations" />
    <ClientLayout>
        <div class="space-y-8 p-8 overflow-x-hidden">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Mes <span class="text-slate-500">Réservations</span></h1>
                    <p class="mt-2 text-base font-bold text-slate-400 uppercase tracking-widest">Suivez et gérez votre historique de location</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative hidden sm:block">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400" />
                        <Input placeholder="Rechercher une réservation..." class="h-12 w-80 rounded-xl border-none bg-white pl-10 ring-1 ring-slate-200 focus:ring-slate-900 shadow-sm transition-all" />
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <Card class="overflow-hidden rounded-3xl border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100">
                <CardContent class="p-0">
                    <div class="overflow-x-auto min-w-full">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-slate-50/50 hover:bg-slate-50/50 border-b border-slate-100">
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Référence</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Véhicule</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Planification</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Finances</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Statut</TableHead>
                                    <TableHead class="h-16 px-8"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="res in props.reservations.data"
                                    :key="res.id"
                                    @click="navigateToReservation(res.id)"
                                    class="group cursor-pointer border-b border-slate-50 transition-colors hover:bg-slate-50/50"
                                >
                                    <TableCell class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition-all group-hover:bg-slate-900 group-hover:text-white group-hover:scale-110">
                                                <Hash class="size-5" />
                                            </div>
                                            <span class="text-base font-black text-slate-900">#{{ res.reservation_number }}</span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="px-8 py-6">
                                        <div v-if="res.car" class="space-y-1">
                                            <div class="text-base font-black text-slate-900 line-clamp-1">{{ res.car.make }} {{ res.car.model }}</div>
                                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                                                <Badge variant="outline" class="rounded-md border-slate-200 py-0.5 text-[10px] font-black">{{ res.car.license_plate }}</Badge>
                                                <span>{{ res.car.year }}</span>
                                            </div>
                                        </div>
                                        <span v-else class="text-sm font-bold text-slate-400">Données du véhicule manquantes</span>
                                    </TableCell>

                                    <TableCell class="px-8 py-6">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                                <Calendar class="size-4 text-slate-400" />
                                                {{ res.start_date }}
                                            </div>
                                            <div class="flex items-center gap-2 text-sm font-bold text-slate-400">
                                                <div class="h-3 w-[1px] bg-slate-200 ml-2 mr-0.5"></div>
                                                {{ res.end_date }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell class="px-8 py-6">
                                        <div class="space-y-1.5">
                                            <div class="text-base font-black text-slate-900">{{ res.total_price_formatted || `${currency.symbol} ${res.total_amount}` }}</div>
                                            <Badge
                                                variant="outline"
                                                :class="['rounded-full py-0.5 px-3 text-[10px] font-black uppercase tracking-widest border-none ring-1 ring-inset shadow-none', getPaymentStatusStyle(res.payment_status)]"
                                            >
                                                {{ formatPaymentStatus(res.payment_status) }}
                                            </Badge>
                                        </div>
                                    </TableCell>

                                    <TableCell class="px-8 py-6">
                                        <Badge
                                            :class="['rounded-full py-1.5 px-5 text-xs font-black uppercase tracking-widest border shadow-none ring-0', getStatusStyle(res.status)]"
                                        >
                                            {{ formatStatus(res.status) }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="px-8 py-6 text-right">
                                        <div class="flex justify-end opacity-0 transition-all group-hover:opacity-100 group-hover:translate-x-1">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-md ring-1 ring-slate-100">
                                                <ChevronRight class="size-5 text-slate-900" />
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="props.reservations.data.length === 0">
                                    <TableCell colspan="6" class="py-24 text-center">
                                        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[3rem] bg-slate-50 text-slate-200">
                                            <Car class="size-12" />
                                        </div>
                                        <h3 class="mt-8 text-xl font-black text-slate-900 tracking-tight">Aucune réservation trouvée</h3>
                                        <p class="mt-2 text-base font-bold text-slate-400">Prêt pour votre premier voyage premium?</p>
                                        <Button as-child class="mt-10 h-12 rounded-xl bg-slate-900 px-10 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                                            <Link href="/fleet">Trouvez votre voiture</Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <nav v-if="props.reservations.links?.length > 3" class="flex items-center justify-center gap-3">
                <Link
                    v-for="(link, i) in props.reservations.links"
                    :key="i"
                    :href="link.url || ''"
                    :class="[
                        'flex h-11 min-w-11 items-center justify-center rounded-xl px-5 text-xs font-black uppercase tracking-widest transition-all',
                        link.active
                            ? 'bg-slate-900 text-white shadow-xl shadow-slate-200'
                            : 'bg-white text-slate-400 hover:bg-slate-50 ring-1 ring-slate-200',
                        !link.url && 'pointer-events-none opacity-50',
                    ]"
                >
                    <span v-html="link.label"></span>
                </Link>
            </nav>
        </div>
    </ClientLayout>
</template>
