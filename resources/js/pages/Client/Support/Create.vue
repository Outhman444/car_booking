<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { index, store } from '@/routes/client/support';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ChevronLeft, Send, Info, HelpCircle, Clock } from 'lucide-vue-next';

const form = useForm<{
    subject: string;
    message: string;
}>({
    subject: '',
    message: '',
});

const canSubmit = computed(
    () =>
        form.subject.trim().length > 0 &&
        form.message.trim().length > 0 &&
        !form.processing,
);

const submitTicket = async () => {
    if (!form.subject || form.subject.trim().length === 0) return;
    if (!form.message || form.message.trim().length === 0) return;

    try {
        await form.post(store().url, {
            onSuccess: () => {
                // Redirect will be handled by Inertia after successful creation
            },
            onError: (errors) => {
                console.error('Failed to create ticket:', errors);
            },
        });
    } catch (error) {
        console.error('An error occurred while creating the ticket:', error);
    }
};
</script>

<template>
    <Head title="Create New Ticket" />
    <ClientLayout>
        <div class="space-y-8 p-4 lg:p-8 pb-12">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">New <span class="text-slate-500">Support Ticket</span></h1>
                    <p class="mt-2 text-base font-bold text-slate-400 uppercase tracking-widest">Get assistance from our dedicated team</p>
                </div>
                
                <Button as-child variant="outline" class="h-12 rounded-xl border-none bg-white ring-1 ring-slate-200 hover:bg-slate-50 font-black uppercase tracking-widest text-xs px-6">
                    <Link :href="index().url">
                        <ChevronLeft class="mr-2 size-4" /> Back to Tickets
                    </Link>
                </Button>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Ticket Form -->
                <Card class="rounded-3xl border-none bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100 lg:col-span-2">
                    <CardHeader class="px-0 pt-0 pb-8 border-b border-slate-50">
                        <CardTitle class="text-2xl font-black tracking-tight text-slate-900">Ticket Details</CardTitle>
                        <CardDescription class="text-xs font-black uppercase tracking-widest text-slate-400">Please provide as much information as possible</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-8 px-0">
                        <form @submit.prevent="submitTicket" class="space-y-8">
                            <!-- Subject Field -->
                            <div class="space-y-3">
                                <Label for="subject" class="text-sm font-black uppercase tracking-widest text-slate-900">
                                    Subject <span class="text-rose-500">*</span>
                                </Label>
                                <Input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    class="h-14 rounded-xl border-none bg-slate-50 px-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all"
                                    placeholder="Brief description of your issue"
                                    required
                                    maxlength="255"
                                />
                                <div class="flex justify-between items-center text-xs font-bold text-slate-400">
                                    <span>{{ form.subject.length }}/255 characters</span>
                                    <span v-if="form.errors.subject" class="text-rose-500">{{ form.errors.subject }}</span>
                                </div>
                            </div>

                            <!-- Message Field -->
                            <div class="space-y-3">
                                <Label for="message" class="text-sm font-black uppercase tracking-widest text-slate-900">
                                    Detailed Message <span class="text-rose-500">*</span>
                                </Label>
                                <Textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="10"
                                    class="rounded-xl border-none bg-slate-50 p-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all leading-relaxed"
                                    placeholder="Describe your issue in detail..."
                                    required
                                />
                                <p v-if="form.errors.message" class="text-xs font-bold text-rose-500">{{ form.errors.message }}</p>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end gap-4 pt-4">
                                <Button as-child variant="ghost" class="h-12 rounded-xl font-black uppercase tracking-widest text-xs px-8 text-slate-400 hover:text-slate-600">
                                    <Link :href="index().url">Cancel</Link>
                                </Button>
                                <Button 
                                    type="submit" 
                                    class="h-12 rounded-xl bg-slate-900 px-10 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all shadow-xl shadow-slate-200"
                                    :disabled="!canSubmit"
                                >
                                    <span v-if="form.processing" class="flex items-center gap-2">
                                        <Clock class="size-4 animate-spin text-xs" /> Processing...
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        <Send class="size-4 text-xs" /> Submit Ticket
                                    </span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Help/Tips Section -->
                <div class="space-y-8">
                    <Card class="rounded-3xl border-none bg-slate-900 p-8 text-white shadow-2xl shadow-slate-200/50">
                        <CardHeader class="px-0 pt-0 pb-6 border-b border-white/10 flex-row items-center justify-between space-y-0 text-xs">
                            <div>
                                <CardTitle class="text-xl font-black tracking-tight">Submission Tips</CardTitle>
                                <CardDescription class="text-xs font-black uppercase tracking-widest text-slate-500">How to get faster help</CardDescription>
                            </div>
                            <HelpCircle class="size-6 text-slate-700" />
                        </CardHeader>
                        <CardContent class="pt-8 px-0 space-y-6">
                            <ul class="space-y-6">
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 backdrop-blur-sm">
                                        <span class="text-sm font-black text-slate-400">01</span>
                                    </div>
                                    <p class="text-sm font-bold leading-relaxed text-slate-300">Use a clear and descriptive subject line.</p>
                                </li>
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 backdrop-blur-sm">
                                        <span class="text-sm font-black text-slate-400">02</span>
                                    </div>
                                    <p class="text-sm font-bold leading-relaxed text-slate-300">Include relevant details such as error messages.</p>
                                </li>
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 backdrop-blur-sm">
                                        <span class="text-sm font-black text-slate-400">03</span>
                                    </div>
                                    <p class="text-sm font-bold leading-relaxed text-slate-300">Describe the steps to reproduce the issue.</p>
                                </li>
                            </ul>
                            
                            <div class="mt-8 rounded-2xl bg-white/5 p-6 backdrop-blur-sm border border-white/5">
                                <div class="flex items-center gap-3 mb-2">
                                    <Info class="size-4 text-slate-400 text-xs" />
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Response Time</p>
                                </div>
                                <p class="text-sm font-bold text-slate-200">Our support team typically responds within 24 hours.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

