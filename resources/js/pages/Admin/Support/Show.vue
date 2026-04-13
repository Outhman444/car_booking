<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { 
    ArrowLeft, 
    CheckCircle2, 
    Send, 
    Clock, 
    User, 
    MessageSquare,
    Hash,
    ShieldCheck,
    MoreHorizontal,
    Flag,
    AlertCircle,
    Loader2
} from 'lucide-vue-next';
import { index, reply, close } from '@/routes/admin/support';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';

// Types
interface Message {
    id: number;
    message: string;
    is_admin: boolean;
    created_at: string;
}

interface Ticket {
    id: number;
    subject: string;
    status: TicketStatusType;
    created_at: string;
    guest_name?: string;
    guest_email?: string;
    message?: string;
    messages: Message[];
    user?: {
        name: string;
        avatar?: string;
    };
}

const TicketStatus = {
    OPEN: 'open',
    IN_PROGRESS: 'in_progress',
    CLOSED: 'closed',
} as const;

type TicketStatusType = (typeof TicketStatus)[keyof typeof TicketStatus];

const props = defineProps<{
    ticket: Ticket;
    isGuest?: boolean;
}>();

const form = useForm<{
    message: string;
}>({
    message: '',
});

const statusColors: Record<TicketStatusType, string> = {
    [TicketStatus.OPEN]: 'bg-blue-50 text-blue-600 ring-blue-100',
    [TicketStatus.IN_PROGRESS]: 'bg-amber-50 text-amber-600 ring-amber-100',
    [TicketStatus.CLOSED]: 'bg-slate-100 text-slate-500 ring-slate-200',
} as const;

const canSend = computed(
    () => form.message.trim().length > 0 && !form.processing,
);

const messagesEndRef = ref<HTMLElement | null>(null);
const scrollToBottom = async () => {
    await nextTick();
    messagesEndRef.value?.scrollIntoView({ behavior: 'smooth', block: 'end' });
};

const submitReply = async () => {
    if (props.isGuest) return;
    if (!form.message || form.message.trim().length === 0) return;

    await form.post(reply(props.ticket.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message');
            router.reload({ only: ['ticket'] });
            scrollToBottom();
        },
    });
};

const formatDate = (d: string) => new Date(d).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
});

onMounted(scrollToBottom);
watch(() => props.ticket.messages?.length, scrollToBottom);

const btnProcessing = ref(false);
function closeTicket(){
    if (confirm('Are you sure you want to close this ticket?')) {
        btnProcessing.value = true;
        router.post(close(props.ticket.id).url);
    }
}
</script>

<template>
    <Head :title="`Ticket #${ticket.id}`" />
    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen pb-32">
            <!-- Header -->
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4">
                    <Link :href="index().url" class="group inline-flex items-center text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-all">
                        <ArrowLeft class="size-3.5 mr-2 group-hover:-translate-x-1 transition-transform" />
                        Back to Support Queue
                    </Link>
                    <div class="flex items-center gap-6">
                        <div class="p-4 rounded-[2rem] bg-indigo-50 text-indigo-500 shadow-xl shadow-indigo-100/50 ring-1 ring-indigo-100">
                            <MessageSquare class="size-8" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ ticket.subject }}</h1>
                                <Badge 
                                    variant="outline"
                                    class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full border-none ring-1 ring-inset"
                                    :class="statusColors[ticket.status] || 'bg-slate-50 text-slate-500 ring-slate-200'"
                                >
                                    {{ ticket.status }}
                                </Badge>
                            </div>
                            <div class="text-[10px] font-black tracking-widest text-slate-400 mt-2 flex items-center gap-4">
                                <span class="flex items-center gap-2"><Hash class="size-3" /> TKT-{{ ticket.id.toString().padStart(5, '0') }}</span>
                                <span class="flex items-center gap-2"><Clock class="size-3" /> Received {{ formatDate(ticket.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button
                        v-if="ticket.status !== 'closed'"
                        @click="closeTicket"
                        class="h-14 px-8 rounded-2xl bg-amber-500 text-white hover:bg-amber-600 text-xs font-black uppercase tracking-widest transition-all border-none shadow-xl shadow-amber-200/50 disabled:opacity-50"
                        :disabled="btnProcessing"
                    >
                        <template v-if="btnProcessing">
                            <Loader2 class="size-4 mr-2 animate-spin" /> Closing...
                        </template>
                        <template v-else>
                            <CheckCircle2 class="size-4 mr-2" /> Resolve Ticket
                        </template>
                    </Button>
                    <div v-else class="h-14 px-8 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-black uppercase tracking-widest ring-1 ring-slate-200">
                        Resolved & Closed
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-[1400px] grid lg:grid-cols-12 gap-10">
                
                <!-- Chat Window -->
                <div class="lg:col-span-8 space-y-8">
                    <Card class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 flex flex-col h-[650px] overflow-hidden border-none uppercase-none">
                        <!-- Chat Header -->
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                                    <Activity class="size-5 text-emerald-500 animate-pulse" />
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-900">Communication Steam</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Real-time collaboration active</div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="flex-1 overflow-y-auto p-10 space-y-10 scroll-smooth">
                            <div
                                v-for="message in ticket.messages"
                                :key="message.id"
                                :class="['flex w-full group/msg', message.is_admin ? 'justify-end' : 'justify-start']"
                            >
                                <div class="max-w-[80%] flex flex-col gap-2" :class="message.is_admin ? 'items-end' : 'items-start'">
                                    <div class="flex items-center gap-3 px-1">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">
                                            {{ message.is_admin ? 'Support Agent' : (ticket.user?.name || ticket.guest_name) }}
                                        </span>
                                        <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                        <span class="text-[9px] font-bold text-slate-300">{{ formatDate(message.created_at) }}</span>
                                    </div>
                                    
                                    <div
                                        :class="[
                                            'px-6 py-4 text-[14px] font-medium leading-relaxed rounded-2xl shadow-sm transition-all',
                                            message.is_admin
                                                ? 'bg-slate-900 text-white rounded-tr-sm shadow-slate-900/10'
                                                : 'bg-slate-50 text-slate-800 ring-1 ring-slate-100 rounded-tl-sm',
                                        ]"
                                    >
                                        {{ message.message }}
                                    </div>
                                </div>
                            </div>
                            <div ref="messagesEndRef" class="h-2"></div>
                        </div>

                        <!-- Typing Bar -->
                        <div v-if="ticket.status !== 'closed'" class="p-6 bg-slate-50/50 border-t border-slate-50">
                            <form @submit.prevent="submitReply" class="relative">
                                <textarea
                                    v-model="form.message"
                                    rows="1"
                                    class="w-full rounded-[1.8rem] border-none bg-white p-6 pr-32 text-sm font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring shadow-lg shadow-slate-200/50 resize-none min-h-[5.5rem] transition-all"
                                    placeholder="Compose your professional response..."
                                    @keydown.ctrl.enter.prevent="submitReply"
                                ></textarea>
                                <div class="absolute right-3 bottom-3 flex items-center gap-2">
                                    <Button
                                        type="submit"
                                        class="h-12 px-8 rounded-2xl bg-primary text-white hover:bg-primary/90 text-[11px] font-black uppercase tracking-widest shadow-xl shadow-primary/30 transition-all border-none"
                                        :disabled="!canSend"
                                    >
                                        Reply <Send class="size-3.5 ml-2" />
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </Card>
                </div>

                <!-- Sidebar Metadata -->
                <div class="lg:col-span-4 space-y-8">
                    <!-- Profile Card -->
                    <Card class="rounded-[2.5rem] bg-slate-900 border-none shadow-2xl p-8 overflow-hidden relative group">
                        <div class="absolute -right-10 -top-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
                            <User class="size-48 text-white" />
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-8">
                                <Avatar class="h-12 w-12 rounded-xl ring-2 ring-white/20">
                                    <AvatarImage v-if="ticket.user?.avatar" :src="ticket.user.avatar" />
                                    <AvatarFallback class="bg-primary/20 text-white font-black">
                                        {{ useInitials(ticket.user?.name || ticket.guest_name || 'G').initials.value }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <div class="text-lg font-black text-white leading-tight">{{ ticket.user?.name || ticket.guest_name }}</div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-white/40 group-hover:text-primary transition-colors">
                                        {{ ticket.user ? 'Verified Member' : 'Guest Inquirer' }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="p-5 rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/10 transition-colors">
                                    <div class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Direct Contact</div>
                                    <div class="text-sm font-bold text-white truncate">{{ ticket.user?.email || ticket.guest_email }}</div>
                                </div>
                                <div class="p-5 rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/10 transition-colors">
                                    <div class="text-[9px] font-black uppercase tracking-widest text-white/30 mb-1">Ticket Source</div>
                                    <div class="text-sm font-bold text-white">{{ ticket.user ? 'Customer Portal' : 'Public Contact Form' }}</div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Case Intel -->
                    <div class="p-8 rounded-[2.5rem] bg-indigo-50/30 ring-1 ring-indigo-100/50 space-y-6">
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="size-4 text-indigo-500" />
                            <h4 class="text-[11px] font-black uppercase tracking-widest text-indigo-400">Case Intel</h4>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">SLA Status</span>
                                <Badge variant="outline" class="bg-emerald-50 text-emerald-600 border-none ring-1 ring-emerald-200 text-[9px] font-black">Normal</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Priority</span>
                                <Badge variant="outline" class="bg-slate-100 text-slate-500 border-none ring-1 ring-slate-200 text-[9px] font-black">Standard</Badge>
                            </div>
                        </div>
                    </div>

                    <!-- Internal Actions -->
                    <div v-if="ticket.status !== 'closed'" class="space-y-4">
                        <Button variant="outline" class="w-full h-14 rounded-2xl border-slate-200 text-slate-500 hover:bg-slate-50 font-black uppercase tracking-widest text-[10px] transition-all">
                            <Flag class="size-3.5 mr-2" /> Flag for Review
                        </Button>
                    </div>
                </div>
            </div>
        </main>
    </AdminLayout>
</template>
