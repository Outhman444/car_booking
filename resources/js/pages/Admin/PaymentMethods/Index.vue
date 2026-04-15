<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, reactive } from 'vue';
import { CreditCard, Wallet, Banknote, AlertCircle, CheckCircle, XCircle, Settings, ShieldCheck, HelpCircle, Loader2 } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    methods: Array<{
        id: number;
        method: string;
        is_enabled: boolean;
        is_sandbox: boolean;
        display_name: string | null;
        description: string | null;
        sort_order: number;
        is_configured: boolean;
    }>;
}>();

// Each method gets its own independent reactive form state
const forms = ref<Record<string, any>>({});
const savingMethod = ref<string | null>(null);

// Initialize forms from props
function initForms() {
    const f: Record<string, any> = {};
    for (const m of props.methods) {
        f[m.method] = {
            is_enabled: m.is_enabled,
            is_sandbox: m.is_sandbox,
            display_name: m.display_name,
            description: m.description,
            is_configured: m.is_configured,
        };
    }
    forms.value = f;
}
initForms();

const getMethodIcon = (method: string) => {
    if (method === 'paypal') return Wallet;
    return CreditCard;
};

const saveMethod = (method: string) => {
    const f = forms.value[method];
    if (!f) return;

    savingMethod.value = method;

    router.put(
        `/admin/payment-methods/${method}`,
        {
            is_enabled: !!f.is_enabled,
            is_sandbox: !!f.is_sandbox,
            display_name: f.display_name || '',
            description: f.description || '',
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Re-init forms from fresh props after successful save
                initForms();
            },
            onError: (errors) => {
                console.error('Failed to update payment method:', errors);
            },
            onFinish: () => {
                savingMethod.value = null;
            },
        }
    );
};
</script>


<template>
    <Head title="Méthodes de paiement" />
    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen pb-32">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Passerelles de paiement" 
                    description="Configurez et gérez vos intégrations financières pour un traitement automatisé."
                    size="lg"
                />
            </div>

            <div class="mx-auto max-w-[1400px] space-y-10">
                <!-- Warning Alert -->
                <Alert v-if="!methods.some(m => m.is_enabled)" class="rounded-[2rem] border-none bg-rose-50 ring-1 ring-rose-100 p-6 shadow-xl shadow-rose-200/20">
                    <AlertCircle class="h-6 w-6 text-rose-600" />
                    <div class="ml-4">
                        <AlertTitle class="text-lg font-black text-rose-900">Aucune passerelle active</AlertTitle>
                        <AlertDescription class="font-bold text-rose-600 opacity-80 mt-1">
                            Activez au moins une méthode de paiement pour permettre aux clients de finaliser leurs réservations.
                        </AlertDescription>
                    </div>
                </Alert>

                <!-- Payment Methods Cards -->
                <div class="grid gap-8 lg:grid-cols-2">
                    <Card v-for="m in methods" :key="m.method" class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 border-none overflow-hidden hover:shadow-2xl transition-all group">
                        <CardHeader class="p-8 pb-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/5 text-primary group-hover:bg-primary transition-colors group-hover:text-white">
                                        <component :is="getMethodIcon(m.method)" class="h-8 w-8" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl font-black text-slate-900">{{ m.display_name || (m.method === 'paypal' ? 'PayPal' : 'Carte Bancaire') }}</CardTitle>
                                        <CardDescription class="font-bold text-slate-400 mt-1">
                                            {{ m.method === 'paypal' ? 'Payer avec un compte PayPal' : 'Intégré via Stripe' }}
                                        </CardDescription>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 items-end">
                                    <Badge v-if="m.is_sandbox" variant="outline" class="bg-amber-50 text-amber-600 border-none ring-1 ring-amber-200 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                                        Sandbox
                                    </Badge>
                                    <Badge v-else-if="m.is_enabled" variant="outline" class="bg-indigo-50 text-indigo-600 border-none ring-1 ring-indigo-200 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                                        Live
                                    </Badge>

                                    <Badge v-if="m.is_enabled" variant="outline" class="bg-emerald-50 text-emerald-600 border-none ring-1 ring-emerald-200 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                                        <CheckCircle class="h-3 w-3 mr-1.5" /> En ligne
                                    </Badge>
                                    <Badge v-else variant="outline" class="bg-slate-50 text-slate-400 border-none ring-1 ring-slate-200 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                                        <XCircle class="h-3 w-3 mr-1.5" /> Hors ligne
                                    </Badge>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent v-if="forms[m.method]" class="p-8 pt-0 space-y-6">
                            <!-- Configuration Status -->
                            <div v-if="!m.is_configured" class="p-4 rounded-2xl bg-rose-50 ring-1 ring-rose-100 flex items-start gap-3">
                                <AlertCircle class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" />
                                <div>
                                    <div class="text-sm font-black text-rose-900">Identifiants d'environnement manquants</div>
                                    <div class="text-[11px] font-bold text-rose-600 opacity-80 mt-0.5">
                                        Veuillez ajouter {{ m.method === 'paypal' ? 'PAYPAL_CLIENT_ID & SECRET' : 'STRIPE_KEY & SECRET' }} à la configuration de votre serveur.
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Form — binds to forms[m.method] -->
                            <div class="space-y-6">
                                <div class="grid gap-4">
                                    <!-- Enable Toggle -->
                                    <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 ring-1 ring-slate-100">
                                        <div class="space-y-0.5">
                                            <Label class="text-sm font-black text-slate-900">Activer la passerelle</Label>
                                            <p class="text-[11px] font-bold text-slate-400">Permettre aux clients d'utiliser cette méthode lors du paiement.</p>
                                        </div>
                                        <Switch
                                            :model-value="Boolean(forms[m.method].is_enabled)"
                                            @update:model-value="(val) => {
                                                if (val && !m.is_configured) {
                                                    // @ts-ignore
                                                    window.alert('❌ Impossible d\'activer la passerelle ! Veuillez d\'abord ajouter les clés API à votre fichier .env.');
                                                    return;
                                                }
                                                forms[m.method].is_enabled = val;
                                            }"
                                            class="data-[state=checked]:bg-primary"
                                        />
                                    </div>

                                    <!-- Sandbox Mode Toggle -->
                                    <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 ring-1 ring-slate-100">
                                        <div class="space-y-0.5">
                                            <Label class="text-sm font-black text-slate-900">Environnement Sandbox (Test)</Label>
                                            <p class="text-[11px] font-bold text-slate-400">Utiliser le mode test pour le traitement des transactions.</p>
                                        </div>
                                        <Switch
                                            :model-value="Boolean(forms[m.method].is_sandbox)"
                                            @update:model-value="(val) => forms[m.method].is_sandbox = val"
                                            class="data-[state=checked]:bg-amber-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Nom affiché</Label>
                                        <Input
                                            v-model="forms[m.method].display_name"
                                            placeholder="ex: Carte Bancaire"
                                            class="h-12 rounded-xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Texte de description</Label>
                                        <Input
                                            v-model="forms[m.method].description"
                                            placeholder="Détails courts affichés aux clients"
                                            class="h-12 rounded-xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                        />
                                    </div>
                                </div>

                                <Button
                                    @click="saveMethod(m.method)"
                                    class="w-full h-14 rounded-2xl text-sm font-black uppercase tracking-widest text-white shadow-xl transition-all border-none active:scale-[0.98]"
                                    :class="
                                        !m.is_configured
                                            ? 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/20 cursor-not-allowed opacity-80'
                                            : 'bg-primary hover:bg-primary/90 shadow-primary/20'
                                    "
                                    :disabled="savingMethod === m.method || !m.is_configured"
                                >
                                    <Loader2 v-if="savingMethod === m.method" class="size-4 mr-2 animate-spin" />
                                    <Settings v-else class="size-4 mr-2" />
                                    <template v-if="savingMethod === m.method">Enregistrement...</template>
                                    <template v-else-if="!m.is_configured">Clés manquantes (Enregistrement désactivé)</template>
                                    <template v-else>Mettre à jour la passerelle</template>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Info Card -->
                <Card class="rounded-[2.5rem] bg-slate-900 border-none shadow-2xl p-8 overflow-hidden relative">
                    <div class="absolute -right-10 -top-10 opacity-10">
                        <ShieldCheck class="size-64 text-white" />
                    </div>
                    <CardHeader class="p-0 mb-8 border-none relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-2xl bg-white/10">
                                <HelpCircle class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-black text-white">Guide de configuration du système</CardTitle>
                                <CardDescription class="text-white/60 font-bold mt-1">Pour finaliser l'intégration, assurez-vous que les variables d'environnement suivantes sont définies :</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid md:grid-cols-2 gap-8 p-0 relative z-10">
                        <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 hover:bg-white/10 transition-all">
                            <h4 class="font-black text-white mb-4 flex items-center gap-2">
                                <Wallet class="size-5 text-primary" /> Intégration PayPal
                            </h4>
                            <div class="bg-black/40 p-4 rounded-xl space-y-2">
                                <code class="text-[11px] block text-emerald-400"><span class="text-white/40"># Identifiants PayPal</span></code>
                                <code class="text-[11px] block text-white">PAYPAL_CLIENT_ID=<span class="text-primary font-bold">votre_client_id</span></code>
                                <code class="text-[11px] block text-white">PAYPAL_SECRET=<span class="text-primary font-bold">votre_secret</span></code>
                                <code class="text-[11px] block text-white">PAYPAL_SANDBOX=<span class="text-amber-400 font-bold">true</span></code>
                            </div>
                            <p class="text-[10px] font-bold text-white/40 mt-4 leading-relaxed">
                                Géré via le <a href="https://developer.paypal.com" target="_blank" class="text-primary hover:underline">Portail Développeur PayPal</a>. Assurez-vous que les écouteurs IPN sont correctement configurés pour les webhooks.
                            </p>
                        </div>
                        <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 hover:bg-white/10 transition-all">
                            <h4 class="font-black text-white mb-4 flex items-center gap-2">
                                <CreditCard class="size-5 text-indigo-400" /> Intégration Stripe
                            </h4>
                            <div class="bg-black/40 p-4 rounded-xl space-y-2">
                                <code class="text-[11px] block text-emerald-400"><span class="text-white/40"># Clés API Stripe</span></code>
                                <code class="text-[11px] block text-white">STRIPE_KEY=<span class="text-indigo-400 font-bold">pk_test_xxx</span></code>
                                <code class="text-[11px] block text-white">STRIPE_SECRET=<span class="text-indigo-400 font-bold">sk_test_xxx</span></code>
                                <code class="text-[11px] block text-white">STRIPE_WEBHOOK_SECRET=<span class="text-indigo-400 font-bold">whsec_xxx</span></code>
                            </div>
                            <p class="text-[10px] font-bold text-white/40 mt-4 leading-relaxed">
                                Accessible via le <a href="https://dashboard.stripe.com" target="_blank" class="text-indigo-400 hover:underline">Tableau de bord Stripe</a>. Le secret du webhook est requis pour la confirmation automatique des paiements.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>
    </AdminLayout>
</template>
