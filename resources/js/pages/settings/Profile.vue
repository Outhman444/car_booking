<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { User, Mail, Save, LoaderCircle, CheckCircle, Info } from 'lucide-vue-next';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-8 max-w-2xl">
                <Heading
                    size="sm"
                    title="Profile Information"
                    description="Manage your account identity and email"
                />

                <Card class="overflow-hidden border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100 rounded-[2rem]">
                    <CardContent class="p-8 sm:p-10">
                        <Form
                            v-bind="ProfileController.update.form()"
                            class="space-y-8"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="space-y-4">
                                <!-- Name Field -->
                                <div class="space-y-2">
                                    <Label for="name" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                        Full Name
                                    </Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <User class="size-5" />
                                        </div>
                                        <Input
                                            id="name"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                            name="name"
                                            :default-value="user.name"
                                            required
                                            autocomplete="name"
                                            placeholder="Your full name"
                                        />
                                    </div>
                                    <InputError class="mt-2 text-xs font-bold text-rose-500" :message="errors.name" />
                                </div>

                                <!-- Email Field -->
                                <div class="space-y-2">
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
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                            name="email"
                                            :default-value="user.email"
                                            required
                                            autocomplete="username"
                                            placeholder="your@email.com"
                                        />
                                    </div>
                                    <InputError class="mt-2 text-xs font-bold text-rose-500" :message="errors.email" />
                                </div>
                            </div>

                            <!-- Email Verification Notice -->
                            <div v-if="mustVerifyEmail && !user.email_verified_at" class="rounded-2xl bg-amber-50 p-5 ring-1 ring-amber-100 flex gap-4">
                                <Info class="size-5 text-amber-600 shrink-0" />
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-amber-900">
                                        Your email address is unverified.
                                    </p>
                                    <Link
                                        :href="send()"
                                        as="button"
                                        class="text-xs font-black uppercase tracking-widest text-amber-700 hover:text-amber-900 border-b-2 border-amber-900/10 hover:border-amber-900 transition-all"
                                    >
                                        Resend verification email
                                    </Link>

                                    <div
                                        v-if="status === 'verification-link-sent'"
                                        class="mt-3 flex items-center gap-2 text-xs font-bold text-emerald-600 animate-in fade-in"
                                    >
                                        <CheckCircle class="size-3" />
                                        A new verification link has been sent.
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-4">
                                    <Button
                                        type="submit"
                                        class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98] disabled:opacity-50"
                                        :disabled="processing"
                                        data-test="update-profile-button"
                                    >
                                        <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                        <span v-else class="flex items-center gap-2">
                                            <Save class="size-4" /> Save Changes
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

                <div class="pt-8 border-t border-slate-200">
                    <DeleteUser />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

