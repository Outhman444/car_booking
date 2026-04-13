<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck, Lock, Smartphone, Shield, Info } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-Factor Authentication',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />
        <SettingsLayout>
            <div class="flex flex-col space-y-8 max-w-2xl">
                <Heading
                    size="sm"
                    title="Two-Factor Authentication"
                    description="Secure your account with an extra layer of protection"
                />

                <Card class="overflow-hidden border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100 rounded-[2rem]">
                    <CardContent class="p-8 sm:p-10">
                        <div v-if="!twoFactorEnabled" class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                                        <ShieldBan class="size-6" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-widest text-slate-900">Protection Disabled</p>
                                        <p class="text-xs font-bold text-slate-400">Account is currently less secure</p>
                                    </div>
                                </div>
                                <Badge variant="destructive" class="px-3 py-1 rounded-full font-black uppercase tracking-widest text-[10px]">Disabled</Badge>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-6 space-y-4 ring-1 ring-slate-100">
                                <div class="flex gap-3">
                                    <Smartphone class="size-5 text-slate-400 shrink-0" />
                                    <p class="text-sm font-bold text-slate-600 leading-relaxed">
                                        When enabled, you will be prompted for a secure pin during login from your authenticator app (TOTP).
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100">
                                <Button
                                    v-if="hasSetupData"
                                    @click="showSetupModal = true"
                                    class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98]"
                                >
                                    <ShieldCheck class="mr-2 size-5" /> Continue Setup
                                </Button>
                                <Form
                                    v-else
                                    v-bind="enable.form()"
                                    @success="showSetupModal = true"
                                    #default="{ processing }"
                                >
                                    <Button
                                        type="submit"
                                        :disabled="processing"
                                        class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98]"
                                    >
                                        <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                        <ShieldCheck v-else class="mr-2 size-5" />
                                        Enable Security
                                    </Button>
                                </Form>
                            </div>
                        </div>

                        <div v-else class="space-y-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                        <ShieldCheck class="size-6" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-widest text-slate-900">Protection Active</p>
                                        <p class="text-xs font-bold text-slate-400">Account is fully secured</p>
                                    </div>
                                </div>
                                <Badge variant="default" class="px-3 py-1 rounded-full font-black uppercase tracking-widest text-[10px] bg-emerald-500 hover:bg-emerald-600">Enabled</Badge>
                            </div>

                            <div class="rounded-2xl bg-emerald-50/50 p-6 space-y-4 ring-1 ring-emerald-100/50">
                                <p class="text-sm font-bold text-slate-600 leading-relaxed">
                                    Two-factor authentication is active. You will be prompted for a secure pin during login using your phone's authenticator app.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                    <Lock class="size-3" /> Backup Recovery
                                </div>
                                <TwoFactorRecoveryCodes />
                            </div>

                            <div class="pt-6 border-t border-slate-100">
                                <Form v-bind="disable.form()" #default="{ processing }">
                                    <Button
                                        variant="destructive"
                                        type="submit"
                                        :disabled="processing"
                                        class="h-12 px-6 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-rose-100 transition-all active:scale-[0.98]"
                                    >
                                        <LoaderCircle v-if="processing" class="mr-2 size-4 animate-spin" />
                                        <ShieldBan v-else class="mr-2 size-4" />
                                        Disable 2FA
                                    </Button>
                                </Form>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Help Tip -->
                <div class="flex gap-4 p-6 rounded-2xl bg-slate-50 ring-1 ring-slate-100 max-w-sm">
                    <Info class="size-5 text-slate-400 shrink-0" />
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">
                        Setting up 2FA requires an authenticator app like Google Authenticator or 1Password on your mobile device.
                    </p>
                </div>
            </div>

            <TwoFactorSetupModal
                v-model:isOpen="showSetupModal"
                :requiresConfirmation="requiresConfirmation"
                :twoFactorEnabled="twoFactorEnabled"
            />
        </SettingsLayout>
    </AppLayout>
</template>

