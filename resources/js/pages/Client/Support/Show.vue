<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { index, reply } from '@/routes/client/support';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { ChevronLeft, Send, Clock, User, ShieldCheck, Hash, MessageCircle } from 'lucide-vue-next';

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
    status: string;
    created_at: string;
    messages: Message[];
    user?: {
        name: string;
    };
}

const props = defineProps<{
    ticket: Ticket;
}>();

const form = useForm<{
    message: string;
}>({
    message: '',
});

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

const canSend = computed(
    () => form.message.trim().length > 0 && !form.processing && props.ticket.status !== 'closed',
);

const messagesEndRef = ref<HTMLElement | null>(null);
const scrollToBottom = async () => {
    await nextTick();
    if (messagesEndRef.value) {
        messagesEndRef.value.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
};

const submitReply = async () => {
    if (!form.message || form.message.trim().length === 0) return;

    try {
        await form.post(reply(props.ticket.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('message');
                router.reload({ only: ['ticket'] });
                scrollToBottom();
            },
            onError: (errors) => {
                console.error('Failed to send message:', errors);
            },
        });
    } catch (error) {
        console.error('An error occurred while sending the message:', error);
    }
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

onMounted(() => {
    scrollToBottom();
});

watch(
    () => props.ticket.messages?.length,
    () => scrollToBottom(),
);
</script>

<template>
    <Head :title="`Ticket #${ticket.id}`" />
    <ClientLayout>
        <div class="space-y-8 p-4 lg:p-8 pb-12">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <Badge 
                            :class="['rounded-full py-1 px-4 text-[10px] font-black uppercase tracking-widest border shadow-none ring-0', getStatusStyle(ticket.status)]"
                        >
                            {{ formatStatus(ticket.status) }}
                        </Badge>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                            <Hash class="size-3" /> {{ ticket.id }}
                        </span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">{{ ticket.subject }}</h1>
                    <div class="mt-2 flex items-center gap-4 text-sm font-bold text-slate-400">
                        <div class="flex items-center gap-2">
                            <Clock class="size-4 text-slate-300" />
                            Created on {{ formatDate(ticket.created_at) }}
                        </div>
                    </div>
                </div>
                
                <Button as-child variant="outline" class="h-12 rounded-xl border-none bg-white ring-1 ring-slate-200 hover:bg-slate-50 font-black uppercase tracking-widest text-xs px-6">
                    <Link :href="index().url">
                        <ChevronLeft class="mr-2 size-4" /> Back to History
                    </Link>
                </Button>
            </div>

            <div class="grid gap-8 lg:grid-cols-4">
                <!-- Chat Interface -->
                <Card class="rounded-3xl border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100 lg:col-span-3 flex flex-col h-[700px]">
                    <CardHeader class="px-8 py-6 border-b border-slate-50 flex-row items-center justify-between space-y-0">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                <MessageCircle class="size-5" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-black tracking-tight text-slate-900">Conversation</CardTitle>
                                <CardDescription class="text-xs font-black uppercase tracking-widest text-slate-400">
                                    {{ ticket.messages?.length || 0 }} messages
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    
                    <CardContent class="flex-1 overflow-y-auto p-8 space-y-8 scrollbar-hide">
                        <div v-if="!ticket.messages || ticket.messages.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-[2.5rem] bg-slate-50 text-slate-200 mb-6">
                                <MessageCircle class="size-10" />
                            </div>
                            <h3 class="text-lg font-black text-slate-900">No messages yet</h3>
                            <p class="text-sm font-bold text-slate-400 mt-1">Start the conversation below.</p>
                        </div>

                        <div
                            v-for="message in ticket.messages"
                            :key="message.id"
                            :class="[
                                'flex animate-in fade-in slide-in-from-bottom-2 duration-500',
                                message.is_admin ? 'justify-start' : 'justify-end',
                            ]"
                        >
                            <div :class="['flex gap-4 max-w-[85%]', message.is_admin ? 'flex-row' : 'flex-row-reverse']">
                                <!-- Avatar -->
                                <div :class="[
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-lg',
                                    message.is_admin ? 'bg-slate-900' : 'bg-blue-600'
                                ]">
                                    <ShieldCheck v-if="message.is_admin" class="size-5" />
                                    <User v-else class="size-5 text-xs text-blue-200" />
                                </div>

                                <!-- Message Bubble -->
                                <div class="space-y-2">
                                    <div :class="[
                                        'rounded-2xl p-5 text-sm font-bold leading-relaxed shadow-sm',
                                        message.is_admin 
                                            ? 'rounded-tl-none bg-slate-50 text-slate-700 ring-1 ring-slate-100' 
                                            : 'rounded-tr-none bg-blue-600 text-white shadow-blue-200'
                                    ]">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-[10px] font-black uppercase tracking-widest opacity-60">
                                                {{ message.is_admin ? 'Support Team' : 'You' }}
                                            </span>
                                        </div>
                                        <p class="whitespace-pre-line text-xs">{{ message.message }}</p>
                                    </div>
                                    <p :class="['text-[10px] font-black text-slate-400 uppercase tracking-widest', message.is_admin ? 'text-left' : 'text-right']">
                                        {{ formatDate(message.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div ref="messagesEndRef" class="h-px"></div>
                    </CardContent>

                    <!-- Reply Form -->
                    <div class="p-8 bg-slate-50/50 border-t border-slate-100 rounded-b-3xl">
                        <form
                            v-if="ticket.status !== 'closed'"
                            @submit.prevent="submitReply"
                            class="relative"
                        >
                            <Textarea
                                v-model="form.message"
                                placeholder="Write your reply... (Press Ctrl+Enter to send)"
                                class="min-h-[120px] w-full rounded-2xl border-none bg-white p-6 pb-20 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all resize-none shadow-sm text-xs"
                                @keydown.ctrl.enter.prevent="submitReply"
                            />
                            <div class="absolute bottom-4 right-4 flex items-center gap-4">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:inline">Ctrl + Enter to send</span>
                                <Button 
                                    type="submit" 
                                    class="h-10 rounded-xl bg-slate-900 px-8 text-xs font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all shadow-xl shadow-slate-200"
                                    :disabled="!canSend"
                                >
                                    <span v-if="form.processing">Sending...</span>
                                    <span v-else class="flex items-center gap-2">
                                        <Send class="size-3" /> Send
                                    </span>
                                </Button>
                            </div>
                        </form>
                        <div v-else class="flex flex-col items-center justify-center py-4 bg-slate-100/50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-sm font-black text-slate-500 uppercase tracking-widest">This ticket is closed</p>
                            <p class="text-xs font-bold text-slate-400 mt-1">You can no longer send replies to this conversation.</p>
                        </div>
                    </div>
                </Card>

                <!-- Sidebar Info -->
                <div class="space-y-8">
                    <Card class="rounded-3xl border-none bg-slate-900 p-8 text-white shadow-2xl shadow-slate-200/50">
                        <CardHeader class="px-0 pt-0 pb-6 border-b border-white/10">
                            <CardTitle class="text-xl font-black tracking-tight">Ticket Info</CardTitle>
                        </CardHeader>
                        <CardContent class="pt-8 px-0 space-y-6">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Subject</p>
                                <p class="text-sm font-bold leading-relaxed">{{ ticket.subject }}</p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Status</p>
                                <Badge 
                                    :class="['rounded-full py-1.5 px-5 text-[10px] font-black uppercase tracking-widest border-none ring-1 ring-white/10 shadow-none', getStatusStyle(ticket.status)]"
                                >
                                    {{ formatStatus(ticket.status) }}
                                </Badge>
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Started On</p>
                                <p class="text-sm font-bold">{{ formatDate(ticket.created_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <div class="rounded-3xl bg-blue-50/50 p-8 ring-1 ring-blue-100 border-none shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 mb-4">
                            <ShieldCheck class="size-5" />
                        </div>
                        <h4 class="text-sm font-black text-slate-900 mb-2">Support Guaranteed</h4>
                        <p class="text-xs font-bold text-slate-500 leading-relaxed">Our agents are reviewing your request. We aim for a response time under 24 hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

