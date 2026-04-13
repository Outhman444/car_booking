<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronUp,
    LoaderCircle,
    Shield,
    User,
    Info,
    CheckCircle,
    ArrowRight,
    KeyRound,
    Mail,
    Lock
} from 'lucide-vue-next';
import { ref } from 'vue';
import HelpTooltip from '@/components/HelpTooltip.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const isDemoOpen = ref(false);
</script>

<template>
    <HomeLayout>
        <Head title="Log in" />

        <div class="flex min-h-[90vh] items-center justify-center px-4 sm:px-6 lg:px-8 py-12 bg-slate-50/50">
            <div class="w-full max-w-[480px] space-y-10">
                <!-- Header Section -->
                <div class="text-center space-y-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-xl shadow-slate-200 ring-1 ring-slate-100">
                        <KeyRound class="size-8 text-slate-900" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 leading-tight">Welcome <span class="text-slate-500">Back</span></h1>
                        <p class="mt-3 text-base font-bold text-slate-400 uppercase tracking-widest">Sign in to manage your rentals</p>
                    </div>
                </div>

                <!-- Status Message -->
                <div
                    v-if="status"
                    class="flex items-center gap-4 rounded-2xl border-none bg-emerald-50 p-5 shadow-sm ring-1 ring-emerald-100"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <CheckCircle class="size-5" />
                    </div>
                    <p class="text-sm font-bold text-emerald-900">
                        {{ status }}
                    </p>
                </div>

                <!-- Main Login Card -->
                <Card class="overflow-hidden rounded-[2.5rem] border-none bg-white p-2 shadow-2xl shadow-slate-200/60 ring-1 ring-slate-100">
                    <CardContent class="p-8 sm:p-10">
                        <Form
                            v-bind="AuthenticatedSessionController.store.form()"
                            :reset-on-success="['password']"
                            v-slot="{ errors, processing }"
                            class="space-y-8"
                        >
                            <!-- Email Field -->
                            <div class="space-y-3">
                                <Label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-2">
                                    Email Address
                                    <HelpTooltip content="Use the email associated with your account or the one you used during your first booking." />
                                </Label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <Mail class="size-5" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="email"
                                        placeholder="name@example.com"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.email" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between ml-1">
                                    <Label for="password" class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                        Password
                                        <HelpTooltip content="Your secure access key. If you've forgotten it, use the reset link." />
                                    </Label>
                                    <TextLink
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-xs font-black uppercase tracking-widest text-slate-900 hover:text-slate-600 transition-colors"
                                        :tabindex="5"
                                    >
                                        Forgot?
                                    </TextLink>
                                </div>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <Lock class="size-5" />
                                    </div>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        :tabindex="2"
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.password" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center px-1">
                                <Label for="remember" class="flex cursor-pointer items-center gap-3">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        :tabindex="3"
                                        class="size-5 rounded-lg border-slate-200 text-slate-900 focus:ring-slate-900 shadow-none ring-0 data-[state=checked]:bg-slate-900 data-[state=checked]:border-slate-900"
                                    />
                                    <span class="text-sm font-bold text-slate-500">Keep me logged in</span>
                                </Label>
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                class="h-16 w-full rounded-2xl bg-slate-900 text-base font-black uppercase tracking-widest text-white shadow-2xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98] disabled:opacity-50"
                                :tabindex="4"
                                :disabled="processing"
                            >
                                <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                <span v-else class="flex items-center gap-3">
                                    Sign In <ArrowRight class="size-5" />
                                </span>
                            </Button>

                            <!-- Sign Up Link -->
                            <div class="pt-2 text-center">
                                <p class="text-sm font-bold text-slate-400">
                                    No account yet?
                                    <TextLink
                                        :href="register()"
                                        :tabindex="5"
                                        class="ml-2 font-black text-slate-900 hover:text-slate-600 border-b-2 border-slate-900/10 hover:border-slate-900 transition-all"
                                    >
                                        Create Account
                                    </TextLink>
                                </p>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Demo Credentials Section (Premium Collapsible) -->
                <Card class="overflow-hidden rounded-3xl border-none bg-slate-50 ring-1 ring-slate-200/50 shadow-sm transition-all duration-300">
                    <button
                        @click="isDemoOpen = !isDemoOpen"
                        type="button"
                        class="flex w-full items-center justify-between p-6 hover:bg-slate-100/50 transition-colors group"
                    >
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200 group-hover:scale-110 transition-transform">
                                <Info class="size-5 text-slate-500" />
                            </div>
                            <div class="text-left">
                                <span class="block text-sm font-black uppercase tracking-widest text-slate-900">Demo Credentials</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Test access information</span>
                            </div>
                        </div>
                        <ChevronDown
                            v-if="!isDemoOpen"
                            class="size-5 text-slate-400 group-hover:text-slate-900 transition-colors"
                        />
                        <ChevronUp
                            v-else
                            class="size-5 text-slate-900"
                        />
                    </button>

                    <div v-show="isDemoOpen" class="p-6 pt-0 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Client Demo -->
                            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Client Access</span>
                                    <User class="size-4 text-slate-300" />
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Email</p>
                                        <p class="text-xs font-bold text-slate-900 select-all">client@example.com</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Password</p>
                                        <code class="text-xs font-bold text-slate-900 select-all">00000000</code>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Demo -->
                            <div class="rounded-2xl bg-slate-900 p-5 shadow-2xl shadow-slate-200">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Admin Portal</span>
                                    <Shield class="size-4 text-slate-500" />
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 mb-1">Email</p>
                                        <p class="text-xs font-bold text-white select-all">admin@example.com</p>
                                    </div>
                                    <div class="flex items-end justify-between gap-4">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 mb-1">Password</p>
                                            <code class="text-xs font-bold text-white select-all">00000000</code>
                                        </div>
                                        <a
                                            href="/admin-secret-url"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 text-white hover:bg-white/20 transition-all shadow-lg"
                                            title="Go to Admin Panel"
                                        >
                                            <ArrowRight class="size-4" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Footer Info -->
                <div class="pt-4 text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed max-w-[320px] mx-auto">
                        By signing in, you agree to our <a href="#" class="text-slate-900 hover:underline">Terms</a> & <a href="#" class="text-slate-900 hover:underline">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

