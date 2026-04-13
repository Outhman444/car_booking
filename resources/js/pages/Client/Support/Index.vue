<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { create, show } from '@/routes/client/support';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { MessageSquare, Plus, ChevronRight, Clock, Hash } from 'lucide-vue-next';

const props = defineProps<{
    tickets: {
        data: Array<{
            id: number;
            subject: string;
            message: string;
            status: string;
            user?: { id: number; name: string; email: string };
            guest_name?: string;
            guest_email?: string;
            created_at: string;
            updated_at: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

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

function goToCreateTicket() {
    router.visit(create().url);
}

const getStatusStyle = (status: string) => {
    if (!status) return 'bg-slate-100/50 text-slate-700 border-slate-200/50';
    switch (status.toLowerCase()) {
        case 'open': return 'bg-emerald-100/50 text-emerald-700 border-emerald-200/50';
        case 'in_progress': return 'bg-amber-100/50 text-amber-700 border-amber-200/50';
        case 'pending': return 'bg-amber-100/50 text-amber-700 border-amber-200/50';
        case 'resolved': return 'bg-blue-100/50 text-blue-700 border-blue-200/50';
        case 'closed': return 'bg-slate-100/50 text-slate-700 border-slate-200/50';
        default: return 'bg-slate-100/50 text-slate-700 border-slate-200/50';
    }
};

function formatStatus(status: string): string {
    return (status || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
</script>

<template>
    <Head title="Support" />
    <ClientLayout>
        <div class="space-y-8 p-4 lg:p-8 pb-12">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Support <span class="text-slate-500">Tickets</span></h1>
                    <p class="mt-2 text-base font-bold text-slate-400 uppercase tracking-widest">Help center & assistance history</p>
                </div>
                
                <Button @click="goToCreateTicket" class="h-12 rounded-xl bg-slate-900 px-8 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 border-none">
                    <Plus class="mr-2 size-4" /> New Ticket
                </Button>
            </div>

            <!-- Tickets Card -->
            <Card class="overflow-hidden rounded-3xl border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-slate-50/50 hover:bg-slate-50/50 border-b border-slate-100">
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Ticket ID</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Subject & Message</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Status</TableHead>
                                    <TableHead class="h-16 px-8 text-xs font-black uppercase tracking-widest text-slate-400">Created At</TableHead>
                                    <TableHead class="h-16 px-8"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="ticket in props.tickets.data" 
                                    :key="ticket.id"
                                    @click="goToTicket(ticket.id)"
                                    class="group cursor-pointer border-b border-slate-50 transition-colors hover:bg-slate-50/50"
                                >
                                    <TableCell class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition-all group-hover:bg-slate-900 group-hover:text-white group-hover:scale-110">
                                                <Hash class="size-5" />
                                            </div>
                                            <span class="text-base font-black text-slate-900">#{{ ticket.id }}</span>
                                        </div>
                                    </TableCell>
                                    
                                    <TableCell class="px-8 py-6">
                                        <div class="space-y-1">
                                            <div class="text-base font-black text-slate-900 line-clamp-1">{{ ticket.subject }}</div>
                                            <div class="text-sm font-bold text-slate-400 line-clamp-1">
                                                {{ ticket.message }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell class="px-8 py-6">
                                        <Badge 
                                            :class="['rounded-full py-1.5 px-5 text-xs font-black uppercase tracking-widest border shadow-none ring-0', getStatusStyle(ticket.status)]"
                                        >
                                            {{ formatStatus(ticket.status) }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="px-8 py-6">
                                        <div class="flex items-center gap-2 text-sm font-bold text-slate-500">
                                            <Clock class="size-4 text-slate-400" />
                                            {{ formatDate(ticket.created_at) }}
                                        </div>
                                    </TableCell>

                                    <TableCell class="px-8 py-6 text-right">
                                        <div class="flex justify-end opacity-0 transition-all group-hover:opacity-100 group-hover:translate-x-1">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-md ring-1 ring-slate-100">
                                                <ChevronRight class="size-5 text-slate-900" />
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                
                                <TableRow v-if="props.tickets.data.length === 0">
                                    <TableCell colspan="5" class="py-24 text-center">
                                        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[3rem] bg-slate-50 text-slate-200">
                                            <MessageSquare class="size-12" />
                                        </div>
                                        <h3 class="mt-8 text-xl font-black text-slate-900 tracking-tight">No tickets found</h3>
                                        <p class="mt-2 text-base font-bold text-slate-400">Need help? We're here to assist you.</p>
                                        <Button @click="goToCreateTicket" class="mt-10 h-12 rounded-xl bg-slate-900 px-10 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 border-none">
                                            <Plus class="mr-2 size-4" /> Create New Ticket
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <nav v-if="props.tickets.links?.length > 3" class="flex items-center justify-center gap-3">
                <Link
                    v-for="(link, i) in props.tickets.links"
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

