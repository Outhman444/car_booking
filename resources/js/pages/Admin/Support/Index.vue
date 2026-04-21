<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { 
    Search, 
    Users, 
    User, 
    MessageSquare, 
    Clock, 
    Hash,
    Activity,
    ChevronRight
} from 'lucide-vue-next';
import { index, show } from '@/routes/admin/support';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import Pagination from '@/components/Pagination.vue';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    tickets: {
        data: Array<{
            id: number
            subject: string
            message: string
            status: string
            user?: { id: number; name: string; email: string }
            guest_name?: string
            guest_email?: string
            created_at: string
            updated_at: string
        }>
        links: Array<{ url: string | null; label: string; active: boolean }>
    }
    filters: {
        search?: string
        status?: string
        type?: 'customer' | 'guest'
    }
    statuses: Record<string, { label: string; color: string }>
    statusCounts: {
        customer: Record<string, number>
        guest: Record<string, number>
    }
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const ticketType = ref<typeof props.filters.type>(props.filters?.type || 'customer');

// Generate status colors based on the colors from the backend
const statusColors = computed(() => {
    const colors: Record<string, { bg: string; text: string; dot: string }> = {};
    for (const [status, data] of Object.entries(props.statuses || {})) {
        const hex = (data as any).color?.replace('#', '') || '6B7280';
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        colors[status] = {
            bg: `rgba(${r}, ${g}, ${b}, 0.1)`,
            text: (data as any).color,
            dot: (data as any).color,
        };
    }
    return colors;
});

const getStatusColor = (status: string) => {
    return statusColors.value[status] || {
        bg: 'rgba(107, 114, 128, 0.1)',
        text: '#6B7280',
        dot: '#6B7280',
    };
};

const getStatusCount = (type: 'customer' | 'guest' | undefined, status: string): number => {
    if (!type) return 0;
    return props.statusCounts?.[type]?.[status] || 0;
};

const getTotalCount = (type: 'customer' | 'guest' | undefined): number => {
    if (!type) return 0;
    return props.statusCounts?.[type]?.all || 0;
};

const doSearch = () => {
    router.get(
        index().url,
        {
            search: search.value,
            status: statusFilter.value === 'all' ? null : statusFilter.value,
            type: ticketType.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Watch for changes in search and ticket type to trigger search
watch(search, (newVal, oldVal) => {
    if (newVal === '' && oldVal !== '') doSearch();
});

const handleTabChange = (value: any) => {
    ticketType.value = value as 'customer' | 'guest';
    doSearch();
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

function goToTicket(id: number) {
    router.visit(show(id).url);
}
</script>

<template>
    <Head title="Support Tickets" />
    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Support Hub" 
                    description="Manage and respond to customer inquiries and guest messages."
                    size="lg"
                />
            </div>

            <div class="mx-auto max-w-[1400px] flex flex-col gap-8">
                <!-- Ticket Type Toggle -->
                <Tabs :model-value="ticketType" @update:model-value="handleTabChange" class="w-full max-w-2xl bg-white p-2 rounded-[2rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50">
                    <TabsList class="grid w-full grid-cols-2 p-0 bg-transparent h-auto">
                        <TabsTrigger 
                            value="customer" 
                            class="flex flex-col items-center py-4 h-auto rounded-[1.5rem] data-[state=active]:bg-primary data-[state=active]:text-white data-[state=active]:shadow-lg data-[state=active]:shadow-primary/20 transition-all text-slate-500 hover:text-slate-900 border-none"
                        >
                            <div class="flex items-center justify-center gap-3 w-full">
                                <Users class="size-5" />
                                <span class="font-black tracking-tight text-lg">Customer</span>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest mt-1 opacity-80">
                                {{ getTotalCount('customer') }} Total Requests
                            </span>
                        </TabsTrigger>
                        <TabsTrigger 
                            value="guest" 
                            class="flex flex-col items-center py-4 h-auto rounded-[1.5rem] data-[state=active]:bg-primary data-[state=active]:text-white data-[state=active]:shadow-lg data-[state=active]:shadow-primary/20 transition-all text-slate-500 hover:text-slate-900 border-none"
                        >
                            <div class="flex items-center justify-center gap-3 w-full">
                                <User class="size-5" />
                                <span class="font-black tracking-tight text-lg">Guest</span>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest mt-1 opacity-80">
                                {{ getTotalCount('guest') }} Total Messages
                            </span>
                        </TabsTrigger>
                    </TabsList>
                </Tabs>

                <!-- Toolbar -->
                <div class="flex flex-col xl:flex-row gap-6 items-start xl:items-center justify-between bg-white p-6 rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl">
                    <div class="flex items-center gap-3 w-full xl:max-w-md">
                        <div class="relative flex-1 group">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-slate-400 group-focus-within:text-primary transition-colors" />
                            <div class="absolute -top-6 left-1 flex items-center gap-1.5">
                                <span class="text-[9px] font-black bg-primary/10 text-primary px-1.5 py-0.5 rounded uppercase tracking-tighter">Rq</span>
                                <span class="text-[10px] font-bold text-slate-400">Filtrer par sujet, nom de l'expéditeur ou email</span>
                            </div>
                            <Input
                                v-model="search"
                                :placeholder="`Search ${ticketType} tickets...`"
                                class="pl-12 h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:text-slate-400 w-full shadow-inner"
                                @keyup.enter="doSearch"
                            />
                        </div>
                        <Button @click="doSearch" class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all border-none">Search</Button>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2 rounded-2xl ring-1 ring-slate-200/50 w-full xl:w-auto">
                        <label class="inline-flex items-center">
                            <input type="radio" class="hidden" v-model="statusFilter" value="all" @change="doSearch" />
                            <div 
                                class="cursor-pointer px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="{
                                    'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === 'all',
                                    'text-slate-400 hover:text-slate-600': statusFilter !== 'all'
                                }"
                            >
                                All ({{ getTotalCount(ticketType) }})
                            </div>
                        </label>

                        <template v-for="(status, key) in statuses" :key="key">
                            <label class="inline-flex items-center">
                                <input type="radio" class="hidden" v-model="statusFilter" :value="key" @change="doSearch" />
                                <div 
                                    class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2"
                                    :class="{
                                        'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === key,
                                        'text-slate-400 hover:text-slate-600': statusFilter !== key
                                    }"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: status.color }" />
                                    {{ status.label }}
                                    <span class="opacity-60 ml-1">({{ getStatusCount(ticketType, key) }})</span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Table Container -->
                <Card class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 border-none overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="hover:bg-transparent border-b border-slate-50">
                                    <TableHead class="h-16 px-8 w-[100px] text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[100px]">
                                        <div class="flex items-center gap-3"><Hash class="size-4" /> Ref</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[200px]">
                                        <div class="flex items-center gap-3"><User class="size-4" /> {{ ticketType === 'customer' ? 'Customer' : 'Guest' }}</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[300px]">
                                        <div class="flex items-center gap-3"><MessageSquare class="size-4" /> Message</div>
                                    </TableHead>
                                    <TableHead v-if="ticketType === 'customer'" class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[120px]">
                                        <div class="flex items-center gap-3"><Activity class="size-4" /> Status</div>
                                    </TableHead>
                                    <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[180px]">
                                        <div class="flex items-center gap-3"><Clock class="size-4" /> Timestamp</div>
                                    </TableHead>
                                    <TableHead class="h-16 px-8 text-right min-w-[100px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="ticket in props.tickets.data" 
                                    :key="ticket.id"
                                    class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors cursor-pointer"
                                    @click="goToTicket(ticket.id)"
                                >
                                    <TableCell class="px-8 py-5">
                                        <span class="font-mono text-sm font-black text-slate-900 group-hover:text-primary transition-colors">
                                            #{{ ticket.id }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-black text-sm text-slate-900">
                                            {{ ticketType === 'customer' ? ticket.user?.name : ticket.guest_name }}
                                        </div>
                                        <div class="text-[10px] font-bold text-slate-400 mt-1">
                                            {{ ticketType === 'customer' ? ticket.user?.email : ticket.guest_email }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-black text-sm text-slate-700 truncate max-w-[250px]">{{ ticket.subject }}</div>
                                        <div class="text-[11px] font-medium text-slate-400 truncate mt-1 max-w-[250px]">
                                            {{ ticket.message }}
                                        </div>
                                    </TableCell>
                                    <TableCell v-if="ticketType === 'customer'">
                                        <Badge 
                                            variant="outline"
                                            class="text-[9px] font-black uppercase tracking-widest px-3 py-0.5 rounded-full border-none ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: getStatusColor(ticket.status).bg,
                                                color: getStatusColor(ticket.status).text,
                                                boxShadow: `0 0 0 1px ${getStatusColor(ticket.status).dot}40 inset`
                                            }"
                                        >
                                            {{ statuses[ticket.status]?.label || ticket.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="px-8 text-[11px] font-bold text-slate-400 whitespace-nowrap">
                                        {{ formatDate(ticket.created_at) }}
                                    </TableCell>
                                    <TableCell class="text-right px-8">
                                        <ChevronRight class="size-5 text-slate-300 group-hover:text-primary transition-colors" />
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.tickets.data.length === 0">
                                    <TableCell colspan="6" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center gap-4">
                                            <div class="p-6 rounded-full bg-slate-50 ring-1 ring-slate-100">
                                                <MessageSquare class="size-8 text-slate-300" />
                                            </div>
                                            <div class="text-lg font-black text-slate-900">No Tickets Found</div>
                                            <p class="text-sm font-bold text-slate-400">There are no support requests matching your current filters.</p>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </Card>

                <div class="mt-8 mb-12">
                    <Pagination :links="props.tickets.links" />
                </div>
            </div>
        </main>
    </AdminLayout>
</template>
