<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Phone, Mail, Clock, Send, CheckCircle, XCircle, MessageSquare, Info, ArrowRight, ShieldCheck, MapPin } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { guestContact } from '@/routes/contact';
import { fleet, about } from '@/routes';

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const showNotification = ref(false);
const notificationMessage = ref('');

const sendTicket = () => {
    form.post(guestContact().url, {
        onSuccess() {
            form.reset();
            showNotification.value = true;
            notificationMessage.value = 'Message sent successfully!';
            setTimeout(() => {
                showNotification.value = false;
            }, 2000);
        },
        onError() {
            showNotification.value = true;
            notificationMessage.value = 'Failed to send message! Please try again.';
            setTimeout(() => {
                showNotification.value = false;
            }, 2000);
        }
    });
}
</script>

<template>
    <HomeLayout>
        <div class="min-h-screen bg-white">
            <!-- Animated Notification -->
            <div class="fixed top-24 right-8 z-[60] flex flex-col gap-4 pointer-events-none">
                <Transition
                    enter-active-class="transform transition ease-out duration-500"
                    enter-from-class="translate-x-[150%] opacity-0"
                    enter-to-class="translate-x-0 opacity-100"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="showNotification"
                        class="pointer-events-auto w-96 rounded-[2rem] border-none bg-slate-900 p-6 shadow-2xl backdrop-blur-xl ring-1 ring-white/10"
                    >
                        <div class="flex items-start gap-5">
                            <div :class="notificationMessage.includes('success') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-rose-500 text-white shadow-lg shadow-rose-500/20'" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                                <CheckCircle v-if="notificationMessage.includes('success')" class="h-6 w-6" />
                                <XCircle v-else class="h-6 w-6" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <h3 class="text-base font-black tracking-tight text-white uppercase italic">
                                    {{ notificationMessage.includes('success') ? 'Transmission Success' : 'System Error' }}
                                </h3>
                                <p class="text-xs font-bold leading-relaxed text-slate-400">
                                    {{ notificationMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Page Header -->
            <section class="relative bg-slate-950 py-32 text-white overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0); background-size: 50px 50px;"></div>
                <div class="absolute -left-20 -top-20 h-[500px] w-[500px] bg-primary/20 blur-[150px] rounded-full"></div>
                
                <div class="relative mx-auto max-w-7xl px-8 text-center sm:px-10 lg:px-12">
                    <Badge variant="secondary" class="mb-10 rounded-full bg-primary/20 py-2 px-6 text-[10px] font-black uppercase tracking-[0.4em] text-primary border-none shadow-none ring-1 ring-ring/30">
                        Global Liaison
                    </Badge>
                    <h1 class="mb-8 text-6xl font-black tracking-tighter md:text-8xl leading-none">
                        Initiate <span class="bg-gradient-to-r from-primary to-blue-400 bg-clip-text text-transparent italic">Contact</span>
                    </h1>
                    <p class="mx-auto max-w-2xl text-xl font-bold leading-relaxed text-slate-400 uppercase tracking-tight">
                        Direct communication channel to our executive support specialists and fleet commanders.
                    </p>
                </div>
            </section>

            <div class="mx-auto max-w-7xl px-8 py-24 sm:px-10 lg:px-12 -mt-16 relative z-10">
                <div class="grid gap-16 lg:grid-cols-3">
                    <!-- Contact Form Card -->
                    <div class="lg:col-span-2">
                        <Card class="overflow-hidden rounded-[3rem] border-none bg-white shadow-[0_50px_100px_-20px_rgba(0,0,0,0.08)] ring-1 ring-slate-100">
                            <CardHeader class="p-12 pb-6">
                                <CardTitle class="text-3xl font-black tracking-tight text-slate-900">Communication Terminal</CardTitle>
                                <CardDescription class="text-xs font-black uppercase tracking-[0.2em] text-primary mt-2">Executive Inquiries Only</CardDescription>
                            </CardHeader>
                            <CardContent class="p-12 pt-6">
                                <form @submit.prevent="sendTicket" class="space-y-10">
                                    <div class="grid gap-10 md:grid-cols-2">
                                        <div class="space-y-4">
                                            <Label for="name" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-1">Identity Name</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                class="h-16 rounded-2xl border-none bg-slate-50 px-6 text-base font-black shadow-inner ring-1 ring-slate-100 focus-visible:ring-ring/20"
                                                placeholder="Legal name here"
                                            />
                                            <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]" v-if="form.errors.name">{{ form.errors.name }}</p>
                                        </div>

                                        <div class="space-y-4">
                                            <Label for="email" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-1">Electronic Mail</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                v-model="form.email"
                                                class="h-16 rounded-2xl border-none bg-slate-50 px-6 text-base font-black shadow-inner ring-1 ring-slate-100 focus-visible:ring-ring/20"
                                                placeholder="your@email.com"
                                            />
                                            <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]" v-if="form.errors.email">{{ form.errors.email }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <Label for="subject" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-1">Subject Matter</Label>
                                        <Input
                                            id="subject"
                                            v-model="form.subject"
                                            class="h-16 rounded-2xl border-none bg-slate-50 px-6 text-base font-black shadow-inner ring-1 ring-slate-100 focus-visible:ring-ring/20"
                                            placeholder="Nature of Inquiry"
                                        />
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]" v-if="form.errors.subject">{{ form.errors.subject }}</p>
                                    </div>

                                    <div class="space-y-4">
                                        <Label for="message" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-1">Detailed Transcript</Label>
                                        <Textarea
                                            id="message"
                                            rows="6"
                                            v-model="form.message"
                                            class="rounded-2xl border-none bg-slate-50 p-6 text-base font-black shadow-inner ring-1 ring-slate-100 focus-visible:ring-ring/20 resize-none"
                                            placeholder="Type your message here..."
                                        />
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]" v-if="form.errors.message">{{ form.errors.message }}</p>
                                    </div>

                                    <Button
                                        type="submit"
                                        size="lg"
                                        :disabled="form.processing"
                                        class="h-20 w-full rounded-[1.5rem] bg-slate-900 text-xs font-black uppercase tracking-[0.3em] text-white shadow-2xl transition-all duration-500 hover:scale-[1.02] hover:bg-black active:scale-[0.98] disabled:opacity-50 border-none group"
                                    >
                                        <Send v-if="!form.processing" class="mr-4 size-5 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                                        {{ form.processing ? 'Transmitting Data...' : 'Execute Transmission' }}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar Stats & Contact -->
                    <div class="space-y-10 lg:col-span-1">
                        <Card class="rounded-[3rem] border-none bg-slate-900 p-10 text-white shadow-2xl relative overflow-hidden">
                            <div class="absolute -right-8 -bottom-8 opacity-5">
                                <ShieldCheck class="size-48" />
                            </div>
                            <div class="relative z-10 space-y-12">
                                <div>
                                    <h4 class="text-sm font-black uppercase tracking-[0.4em] text-primary mb-2">Direct Uplink</h4>
                                    <p class="text-2xl font-black tracking-tight">Rapid Response</p>
                                </div>

                                <div class="space-y-10">
                                    <div class="flex gap-6 items-center group cursor-pointer">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-primary backdrop-blur-xl ring-1 ring-white/20 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                                            <Phone class="size-6" />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Voice Comm</p>
                                            <p class="text-lg font-black text-white">+1 (555) 000-9999</p>
                                        </div>
                                    </div>

                                    <div class="flex gap-6 items-center group cursor-pointer">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-primary backdrop-blur-xl ring-1 ring-white/20 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                                            <Mail class="size-6" />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Secure Mail</p>
                                            <p class="text-lg font-black text-white">ops@realrent.com</p>
                                        </div>
                                    </div>

                                    <div class="flex gap-6 items-center group cursor-pointer">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-primary backdrop-blur-xl ring-1 ring-white/20 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                                            <MapPin class="size-6" />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">HQ Node</p>
                                            <p class="text-lg font-black text-white">Silicon Valley, CA</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <!-- Operational Status -->
                        <Card class="rounded-[3rem] border-none bg-white p-10 shadow-xl ring-1 ring-slate-100">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-8 ml-1">Active Readiness</h4>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-xs font-black text-slate-500">Standard Support</span>
                                    <span class="text-xs font-black text-slate-900 border-l border-slate-200 pl-4 ml-4">24/7 Deployment</span>
                                </div>
                                <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-xs font-black text-slate-500">Response Latency</span>
                                    <span class="text-xs font-black text-primary border-l border-slate-200 pl-4 ml-4">< 60 Minutes</span>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>
