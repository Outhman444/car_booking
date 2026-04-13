<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, Lock, Shield } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <div>
        <Head title="Admin Access" />

        <div
            class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-950 via-blue-950 to-slate-950 px-4 sm:px-6 lg:px-8"
        >
            <div class="w-full max-w-md">
                <!-- Background Glows -->
                <div
                    class="pointer-events-none absolute inset-0 overflow-hidden"
                >
                    <div
                        class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-blue-500/10 blur-[120px]"
                    ></div>
                    <div
                        class="absolute -bottom-40 -left-40 h-[500px] w-[500px] rounded-full bg-primary/10 blur-[120px]"
                    ></div>
                </div>

                <div class="relative space-y-10">
                    <!-- Header -->
                    <div class="text-center">
                        <div
                            class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur-xl"
                        >
                            <Shield class="h-10 w-10 text-accent" />
                        </div>
                        <h1 class="mb-3 text-3xl font-black tracking-tight text-white md:text-4xl">
                            Admin <span class="text-primary font-black">Portal</span>
                        </h1>
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-slate-500">
                            Secure Gateway
                        </p>
                    </div>

                    <!-- Login Form -->
                    <div
                        class="overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/5 p-10 shadow-2xl backdrop-blur-2xl ring-1 ring-white/10"
                    >
                        <Form
                            v-bind="AuthenticatedSessionController.storeAdminLogin.form()"
                            :reset-on-success="['password']"
                            v-slot="{ errors, processing }"
                            class="space-y-8"
                        >
                            <!-- Email Field -->
                            <div class="space-y-2">
                                <Label
                                    for="email"
                                    class="ml-1 text-xs font-black uppercase tracking-widest text-slate-400"
                                >
                                    Administrator ID
                                </Label>
                                <div class="relative group">
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="email"
                                        placeholder="admin@realrent.com"
                                        class="h-14 w-full rounded-2xl border-none bg-white/5 px-6 font-bold text-white ring-1 ring-white/10 transition-all focus:bg-white/10 focus:ring-2 focus:ring-ring placeholder:text-slate-600"
                                    />
                                </div>
                                <InputError
                                    :message="errors.email"
                                    class="ml-1 mt-1 text-xs font-bold text-rose-500"
                                />
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-2">
                                <Label
                                    for="password"
                                    class="ml-1 text-xs font-black uppercase tracking-widest text-slate-400"
                                >
                                    Access Key
                                </Label>
                                <div class="relative group">
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        :tabindex="2"
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="h-14 w-full rounded-2xl border-none bg-white/5 px-6 font-bold text-white ring-1 ring-white/10 transition-all focus:bg-white/10 focus:ring-2 focus:ring-ring placeholder:text-slate-600"
                                    />
                                    <Lock class="absolute right-5 top-1/2 -translate-y-1/2 size-5 text-white/20 group-focus-within:text-primary transition-colors" />
                                </div>
                                <InputError
                                    :message="errors.password"
                                    class="ml-1 mt-1 text-xs font-bold text-rose-500"
                                />
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                class="h-16 w-full rounded-2xl bg-primary text-base font-black uppercase tracking-widest text-white shadow-2xl shadow-primary/20 transition-all hover:bg-primary/90 hover:scale-[1.02] active:scale-95 disabled:opacity-50 border-none mt-4"
                                :tabindex="4"
                                :disabled="processing"
                                data-test="admin-login-button"
                            >
                                <LoaderCircle
                                    v-if="processing"
                                    class="mr-2 h-6 w-6 animate-spin"
                                />
                                <span v-else>Authorize Access</span>
                            </Button>

                            <!-- Security Notice -->
                            <div
                                class="border-t border-white/10 pt-8 text-center"
                            >
                                <div
                                    class="flex items-center justify-center space-x-3 text-slate-500"
                                >
                                    <Shield class="h-4 w-4 text-emerald-500" />
                                    <p class="text-[10px] font-black uppercase tracking-widest">
                                        End-to-End Encryption Active
                                    </p>
                                </div>
                                <p class="mt-3 text-[10px] font-bold text-slate-600">
                                    System events are audited and captured in real-time. Unrecognized attempts are logged.
                                </p>
                            </div>
                        </Form>
                    </div>

                    <!-- Footer Alert -->
                    <div class="text-center">
                        <div
                            class="inline-flex items-center space-x-3 rounded-2xl border border-accent/20 bg-accent/5 px-6 py-3 backdrop-blur-sm"
                        >
                            <div
                                class="h-2 w-2 animate-pulse rounded-full bg-accent"
                            ></div>
                            <p class="text-xs font-black uppercase tracking-widest text-accent/80">
                                Restricted Environment
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
