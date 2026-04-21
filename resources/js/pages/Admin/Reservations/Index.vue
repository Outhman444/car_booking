<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { show, index, quickUpdate } from '@/routes/admin/reservations';
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
import HelpTooltip from '@/components/HelpTooltip.vue';
import { 
    CheckCircle, 
    XCircle, 
    Clock, 
    Car, 
    User, 
    Calendar, 
    CreditCard, 
    MoreVertical, 
    ChevronRight,
    Search,
    Fingerprint
} from 'lucide-vue-next';

const props = defineProps<{
    reservations: {
        data: Array<{
            id: number;
            reservation_number: string;
            user: { id: number; name: string; email: string } | null;
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
            is_paid: boolean;
            payment_method_label: string | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search?: string;
        status?: string;
    };
    statuses: Record<string, { label: string; count: number; color: string }>;
    currency: { symbol: string; code: string }
}>();

const totalCount = computed(() =>
  Object.values(props.statuses).reduce((acc, curr) => acc + curr.count, 0)
);

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');

const navigateToReservation = (id: number) => {
    router.visit(show(id).url);
};

function doSearch() {
    router.get(
        index().url,
        {
            search: search.value,
            status: statusFilter.value === 'all' ? null : statusFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function doQuickUpdate(id: number, status: string) {
    if (confirm(`Changer le statut en ${status} ?`)) {
        router.post(quickUpdate(id).url, { status }, {
            preserveScroll: true
        });
    }
}
</script>

<template>
    <Head title="Réservations" />
    <AdminLayout>
        <!-- Main -->
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Gestion des réservations" 
                    description="Suivez, gérez et examinez tous les contrats de location et les états de paiement des clients."
                    size="lg"
                />
            </div>

            <div class="mx-auto max-w-[1400px] flex flex-col gap-8">
                <!-- Toolbar -->
                <div class="flex flex-col xl:flex-row gap-6 items-start xl:items-center justify-between bg-white p-6 rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl">
                    <div class="flex items-center gap-3 w-full xl:max-w-md">
                        <div class="relative flex-1 group">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-slate-400 group-focus-within:text-primary transition-colors" />
                            <div class="absolute -top-6 left-1 flex items-center gap-1.5">
                                <span class="text-[9px] font-black bg-primary/10 text-primary px-1.5 py-0.5 rounded uppercase tracking-tighter">Rq</span>
                                <span class="text-[10px] font-bold text-slate-400">Rechercher par numéro, client (nom/email) ou véhicule</span>
                            </div>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 z-10">
                                <HelpTooltip content="Recherchez par numéro de réservation, nom du client, email ou détails du véhicule pour un filtrage précis des données." />
                            </div>
                            <Input
                              v-model="search"
                              placeholder="Numéro, client, véhicule..."
                              class="pl-12 h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:text-slate-400 w-full"
                              @keyup.enter="doSearch"
                            />
                        </div>
                        <Button @click="doSearch" class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all border-none">Rechercher</Button>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2 rounded-2xl ring-1 ring-slate-200/50 w-full xl:w-auto">
                        <label class="inline-flex items-center">
                            <input
                                type="radio"
                                class="hidden"
                                v-model="statusFilter"
                                value="all"
                                @change="doSearch"
                            />
                            <div 
                                class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="{
                                    'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === 'all',
                                    'text-slate-400 hover:text-slate-600': statusFilter !== 'all'
                                }"
                            >
                                Toutes ({{ Object.values(statuses).reduce((acc, curr) => acc + curr.count, 0) }})
                            </div>
                        </label>

                        <template v-for="(status, key) in statuses" :key="key">
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    class="hidden"
                                    v-model="statusFilter"
                                    :value="key"
                                    @change="doSearch"
                                />
                                <div 
                                    class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2"
                                    :class="{
                                        'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === key,
                                        'text-slate-400 hover:text-slate-600': statusFilter !== key
                                    }"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :style="{ backgroundColor: status.color }"
                                    ></span>
                                    {{ status.label }}
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Table Container -->
                <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="hover:bg-transparent border-b border-slate-50">
                                    <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500">Référence</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Client</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500">Véhicule</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Total</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Statut</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Paiement</TableHead>
                                    <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="res in props.reservations.data"
                                    :key="res.id"
                                    @click="navigateToReservation(res.id)"
                                    class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors cursor-pointer"
                                >
                                    <TableCell class="px-4 py-5">
                                        <div class="font-mono text-xs font-black text-slate-900 group-hover:text-primary transition-colors">
                                            {{ res.reservation_number }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5 grayscale group-hover:grayscale-0 transition-all">
                                            <span class="text-[8px] font-black uppercase tracking-widest text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                {{ new Date(res.start_date).toLocaleDateString() }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col leading-tight">
                                            <span class="text-sm font-black text-slate-900 line-clamp-1">{{ res.user?.name || '—' }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 truncate max-w-[120px]">{{ res.user?.email }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col items-start gap-1 leading-tight">
                                            <span class="text-xs font-black text-slate-900 line-clamp-1">
                                                {{ res.car ? `${res.car.year} ${res.car.make}` : '—' }}
                                            </span>
                                            <span class="text-[9px] font-mono font-black text-slate-400 flex items-center gap-1.5">
                                                <span class="bg-slate-100 px-1.5 py-0.5 rounded">{{ res.car?.license_plate }}</span>
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-xs font-black text-slate-900">
                                            {{ props.currency.symbol }}{{ Number(res.total_amount).toLocaleString() }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge
                                            :status="res.status"
                                            :label="statuses[res.status]?.label || res.status"
                                            :color="statuses[res.status]?.color"
                                            class="scale-90 origin-left"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Badge 
                                            variant="outline"
                                            class="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full border-none ring-1 ring-inset"
                                            :class="res.is_paid 
                                                ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' 
                                                : (res.status === 'confirmed' ? 'bg-amber-50 text-amber-600 ring-amber-200' : 'bg-slate-50 text-slate-500 ring-slate-200')"
                                        >
                                            {{ res.is_paid ? 'Payé' : (res.status === 'confirmed' ? 'ESP' : 'En attente') }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell @click.stop class="px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Button 
                                                v-if="res.status === 'pending'"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all border-none"
                                                @click="doQuickUpdate(res.id, 'confirmed')"
                                            >
                                                Confirmer
                                            </Button>
                                            <Button 
                                                v-if="res.status === 'confirmed'"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-all border-none"
                                                @click="doQuickUpdate(res.id, 'active')"
                                            >
                                                Démarrer
                                            </Button>
                                            <Button 
                                                v-if="res.status === 'active'"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all border-none"
                                                @click="doQuickUpdate(res.id, 'completed')"
                                            >
                                                Terminer
                                            </Button>
                                            <Button
                                                v-if="['pending', 'confirmed'].includes(res.status)"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all border-none"
                                                @click="doQuickUpdate(res.id, 'cancelled')"
                                            >
                                                Annuler
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-slate-100 transition-all border-none">
                                                <ChevronRight class="size-4 text-slate-400 group-hover:text-primary transition-colors" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.reservations.data.length === 0">
                                    <TableCell colspan="7" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center gap-4">
                                            <div class="p-6 rounded-full bg-slate-50 ring-1 ring-slate-100">
                                                <Search class="size-8 text-slate-300" />
                                            </div>
                                            <div class="text-lg font-black text-slate-900">Aucune réservation trouvée</div>
                                            <p class="text-sm font-bold text-slate-400">Essayez d'ajuster votre recherche ou vos filtres.</p>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </Card>

                <div class="mt-8 mb-12">
                    <Pagination :links="props.reservations.links" />
                </div>
            </div>
        </main>
    </AdminLayout>
</template>
