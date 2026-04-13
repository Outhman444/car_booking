<script setup lang="ts">
import PasswordResetLinkController from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController';
import HomeLayout from '@/layouts/HomeLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, Mail, ArrowRight, ChevronLeft, HelpCircle, CheckCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <HomeLayout>
        <Head title="Forgot password" />

        <div class="flex min-h-[90vh] items-center justify-center px-4 sm:px-6 lg:px-8 py-12 bg-slate-50/50">
            <div class="w-full max-w-[480px] space-y-10">
                <!-- Header Section -->
                <div class="text-center space-y-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-xl shadow-slate-200 ring-1 ring-slate-100">
                        <HelpCircle class="size-8 text-slate-900" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 leading-tight">Recover <span class="text-slate-500">Access</span></h1>
                        <p class="mt-3 text-base font-bold text-slate-400 uppercase tracking-widest leading-relaxed">We'll send a reset link to your email</p>
                    </div>
                </div>

                <!-- Status Message -->
                <div
                    v-if="status"
                    class="flex items-center gap-4 rounded-2xl border-none bg-emerald-50 p-5 shadow-sm ring-1 ring-emerald-100 animate-in fade-in slide-in-from-top-2 duration-300"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <CheckCircle class="size-5" />
                    </div>
                    <p class="text-sm font-bold text-emerald-900 leading-relaxed">
                        {{ status }}
                    </p>
                </div>

                <!-- Main Forgot Password Card -->
                <Card class="overflow-hidden rounded-[2.5rem] border-none bg-white p-2 shadow-2xl shadow-slate-200/60 ring-1 ring-slate-100">
                    <CardContent class="p-8 sm:p-10">
                        <Form
                            v-bind="PasswordResetLinkController.store.form()"
                            v-slot="{ errors, processing }"
                            class="space-y-8"
                        >
                            <!-- Email Field -->
                            <div class="space-y-3">
                                <Label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                    Email Address
                                </Label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <Mail class="size-5" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autocomplete="email"
                                        required
                                        autofocus
                                        placeholder="name@example.com"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.email" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                class="h-16 w-full rounded-2xl bg-slate-900 text-base font-black uppercase tracking-widest text-white shadow-2xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98] disabled:opacity-50"
                                :disabled="processing"
                                data-test="email-password-reset-link-button"
                            >
                                <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                <span v-else class="flex items-center gap-3">
                                    Send link <ArrowRight class="size-5" />
                                </span>
                            </Button>

                            <!-- Back to Login Link -->
                            <div class="pt-2 text-center">
                                <TextLink
                                    :href="login()"
                                    class="inline-flex items-center gap-2 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all group"
                                >
                                    <ChevronLeft class="size-4 group-hover:-translate-x-1 transition-transform" />
                                    Back to Login
                                </TextLink>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Help Tip -->
                <div class="text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed max-w-[320px] mx-auto">
                        Cant find the email? Check your spam folder or contact support.
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

