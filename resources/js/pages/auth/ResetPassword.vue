<script setup lang="ts">
import NewPasswordController from '@/actions/App/Http/Controllers/Auth/NewPasswordController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, Lock, Mail, ArrowRight, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <HomeLayout>
        <Head title="Reset password" />

        <div class="flex min-h-[90vh] items-center justify-center px-4 sm:px-6 lg:px-8 py-12 bg-slate-50/50">
            <div class="w-full max-w-[480px] space-y-10">
                <!-- Header Section -->
                <div class="text-center space-y-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-xl shadow-slate-200 ring-1 ring-slate-100">
                        <ShieldCheck class="size-8 text-slate-900" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 leading-tight">Secure <span class="text-slate-500">Reset</span></h1>
                        <p class="mt-3 text-base font-bold text-slate-400 uppercase tracking-widest">Update your account credentials</p>
                    </div>
                </div>

                <!-- Main Reset Card -->
                <Card class="overflow-hidden rounded-[2.5rem] border-none bg-white p-2 shadow-2xl shadow-slate-200/60 ring-1 ring-slate-100">
                    <CardContent class="p-8 sm:p-10">
                        <Form
                            v-bind="NewPasswordController.store.form()"
                            :transform="(data) => ({ ...data, token, email })"
                            :reset-on-success="['password', 'password_confirmation']"
                            v-slot="{ errors, processing }"
                            class="space-y-8"
                        >
                            <!-- Email Field (Read Only) -->
                            <div class="space-y-3 opacity-60">
                                <Label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                    Email Address
                                </Label>
                                <div class="relative">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">
                                        <Mail class="size-5" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        v-model="inputEmail"
                                        readonly
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 cursor-not-allowed"
                                    />
                                </div>
                                <InputError :message="errors.email" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-3">
                                <Label for="password" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                    New Password
                                </Label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <Lock class="size-5" />
                                    </div>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autofocus
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.password" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Confirm Password Field -->
                            <div class="space-y-3">
                                <Label for="password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                    Confirm Password
                                </Label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <Lock class="size-5" />
                                    </div>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.password_confirmation" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                class="h-16 w-full rounded-2xl bg-slate-900 text-base font-black uppercase tracking-widest text-white shadow-2xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98] disabled:opacity-50"
                                :disabled="processing"
                                data-test="reset-password-button"
                            >
                                <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                <span v-else class="flex items-center gap-3">
                                    Update Password <ArrowRight class="size-5" />
                                </span>
                            </Button>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Help Tip -->
                <div class="text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed max-w-[320px] mx-auto">
                        Make sure your new password is at least 8 characters long and includes numbers or symbols.
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

