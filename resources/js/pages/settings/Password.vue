<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Lock, Save, LoaderCircle, CheckCircle, ShieldCheck } from 'lucide-vue-next';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-8 max-w-2xl">
                <Heading
                    size="sm"
                    title="Update Password"
                    description="Ensure your account is using a long, random password"
                />

                <Card class="overflow-hidden border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100 rounded-[2rem]">
                    <CardContent class="p-8 sm:p-10">
                        <Form
                            v-bind="PasswordController.update.form()"
                            :options="{
                                preserveScroll: true,
                            }"
                            reset-on-success
                            :reset-on-error="[
                                'password',
                                'password_confirmation',
                                'current_password',
                            ]"
                            class="space-y-8"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="space-y-6">
                                <!-- Current Password -->
                                <div class="space-y-2">
                                    <Label for="current_password" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                        Current Password
                                    </Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <ShieldCheck class="size-5" />
                                        </div>
                                        <Input
                                            id="current_password"
                                            ref="currentPasswordInput"
                                            name="current_password"
                                            type="password"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                            autocomplete="current-password"
                                            placeholder="••••••••"
                                        />
                                    </div>
                                    <InputError :message="errors.current_password" class="mt-2 text-xs font-bold text-rose-500" />
                                </div>

                                <!-- New Password -->
                                <div class="space-y-2">
                                    <Label for="password" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                        New Password
                                    </Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <Lock class="size-5" />
                                        </div>
                                        <Input
                                            id="password"
                                            ref="passwordInput"
                                            name="password"
                                            type="password"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                            autocomplete="new-password"
                                            placeholder="••••••••"
                                        />
                                    </div>
                                    <InputError :message="errors.password" class="mt-2 text-xs font-bold text-rose-500" />
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <Label for="password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                        Confirm Password
                                    </Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <Lock class="size-5" />
                                        </div>
                                        <Input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                            autocomplete="new-password"
                                            placeholder="••••••••"
                                        />
                                    </div>
                                    <InputError :message="errors.password_confirmation" class="mt-2 text-xs font-bold text-rose-500" />
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-4">
                                    <Button
                                        type="submit"
                                        class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98] disabled:opacity-50"
                                        :disabled="processing"
                                        data-test="update-password-button"
                                    >
                                        <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                        <span v-else class="flex items-center gap-2">
                                            <Save class="size-4" /> Save Password
                                        </span>
                                    </Button>

                                    <Transition
                                        enter-active-class="transition duration-300 ease-out"
                                        enter-from-class="opacity-0 translate-x-2"
                                        leave-active-class="transition duration-200 ease-in"
                                        leave-to-class="opacity-0 -translate-x-2"
                                    >
                                        <div
                                            v-show="recentlySuccessful"
                                            class="flex items-center gap-2 text-sm font-bold text-emerald-600"
                                        >
                                            <CheckCircle class="size-5" />
                                            <span>Saved successfully.</span>
                                        </div>
                                    </Transition>
                                </div>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Help Tip -->
                <div class="text-center max-w-sm">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">
                        For better security, use a combination of letters, numbers, and special characters.
                    </p>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

