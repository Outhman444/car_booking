<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { CreditCard, Wallet, AlertCircle, Loader2, CheckCircle, ArrowLeft, Info, Clock } from 'lucide-vue-next';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';
import HelpTooltip from '@/components/HelpTooltip.vue';
import BookingStepper from '@/components/BookingStepper.vue';
import ReservationCountdown from '@/components/ReservationCountdown.vue';


const $page = usePage<any>();

const props = defineProps<{
    reservation: {
        id: number;
        reservation_number: string;
        total_amount: number;
        deposit_amount: number;
        remaining_amount: number;
        security_deposit_amount: number;
        start_date: string;
        end_date: string;
        total_days: number;
        car?: {
            id: number;
            make: string;
            model: string;
            year: number;
            license_plate: string;
        };
        pending_expires_at?: string;
    };
    paymentMethods: Array<{
        value: string;
        label: string;
        description: string;
        icon: string;
        is_sandbox: boolean;
    }>;
    stripeKey?: string;
    currency: { symbol: string; code: string };
}>();

const selectedMethod = ref<string | null>(null);
const isProcessing = ref(false);
const errorMessage = ref<string | null>(null);

// Stripe Elements
let stripeInstance: any = null;
let elements: any = null;
let cardElement: any = null;

const securityDeposit = computed(() => props.reservation.security_deposit_amount || 0);

const isExpired = ref(false);

onMounted(async () => {
    // Load Stripe.js if Stripe is available
    if (props.stripeKey && props.paymentMethods.some(m => m.value === 'stripe')) {
        stripeInstance = await loadStripe(props.stripeKey);
        if (stripeInstance) {
            elements = stripeInstance.elements();
            cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#1f2937',
                        '::placeholder' : { color: '#9ca3af' },
                    },
                },
            });
        }
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

function fmtMoney(n?: number | string) {
    const v = Number(n ?? 0);
    return `${props.currency.symbol}${v.toFixed(2)}`;
}

function selectMethod(method: string) {
    selectedMethod.value = method;
    errorMessage.value = null;

    if (method === 'stripe' && cardElement) {
        setTimeout(() => {
            const container = document.getElementById('card-element');
            if (container && !container.hasChildNodes()) {
                cardElement.mount('#card-element');
            }
        }, 100);
    }
}

async function processStripePayment() {
    if (!stripeInstance || !cardElement) return;

    isProcessing.value = true;
    errorMessage.value = null;

    try {
        // 1. Get Payment Intent from backend
        const intentResponse = await axios.post(`/client/payment/${props.reservation.id}/stripe-intent`);

        if (intentResponse.data.error) {
            errorMessage.value = intentResponse.data.error;
            isProcessing.value = false;
            return;
        }

        const clientSecret = intentResponse.data.client_secret;

        // 2. Confirm card payment with Stripe (Handles 3D Secure modal automatically)
        const { paymentIntent, error } = await stripeInstance.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
            }
        });

        if (error) {
            errorMessage.value = error.message;
            isProcessing.value = false;
            return;
        }

        if (paymentIntent && paymentIntent.status === 'succeeded') {
            // 3. Inform backend of success
            router.post(
                `/client/payment/${props.reservation.id}/stripe-confirm`,
                { payment_intent_id: paymentIntent.id },
                {
                    preserveScroll: true,
                    onFinish: () => {
                        isProcessing.value = false;
                    },
                    onError: (errors: any) => {
                        errorMessage.value = errors.error || "Une erreur est survenue lors du traitement du paiement.";
                    },
                }
            );
        } else {
             errorMessage.value = "L'authentification a échoué.";
             isProcessing.value = false;
        }

    } catch (e: any) {
        errorMessage.value = e.response?.data?.error || e.message || "Erreur lors du traitement.";
        isProcessing.value = false;
    }
}

async function processPayPalPayment() {
    isProcessing.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(`/client/payment/${props.reservation.id}/paypal`);

        const data = response.data;

        if (data.error) {
            errorMessage.value = data.error;
            isProcessing.value = false;
            return;
        }

        if (data.approval_url) {
            window.location.href = data.approval_url;
        } else {
            errorMessage.value = "Réponse PayPal invalide.";
            isProcessing.value = false;
        }
    } catch {
        errorMessage.value = "Échec de la connexion à PayPal.";
        isProcessing.value = false;
    }
}

async function submitPayment() {
    if (!selectedMethod.value) return;

    if (selectedMethod.value === 'stripe') {
        await processStripePayment();
    } else if (selectedMethod.value === 'paypal') {
        await processPayPalPayment();
    }
}

function getPaymentMethodName(value: string): string {
    switch (value) {
        case 'paypal': return 'PayPal';
        case 'stripe': return 'Carte Bancaire';
        default: return value;
    }
}
</script>

<template>
    <Head :title="`Compléter le paiement ${reservation?.reservation_number || ''}`" />
    <ClientLayout>
        <main class="flex-1 p-8 space-y-6 max-w-3xl mx-auto">
            <!-- Expiration Countdown -->
            <ReservationCountdown 
                :expires-at="reservation.pending_expires_at" 
                variant="payment"
                :is-paid="reservation.is_paid"
                @expired="isExpired = true"
            />

            <!-- Status Stepper -->
            <BookingStepper :current-step="3" class="mb-6" />

            <!-- Header -->
            <div v-if="!isExpired" class="flex items-center gap-4">
                <Link :href="`/client/reservations/${reservation.id}`">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-semibold">Effectuer le paiement</h1>
                    <p class="text-muted-foreground">Référence #{{ reservation.reservation_number }}</p>
                </div>
            </div>

            <!-- Reservation Summary -->
            <Card v-if="!isExpired">
                <CardHeader>
                    <CardTitle class="text-lg">Résumé de la réservation</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Voiture</p>
                            <p class="font-medium">
                                {{ reservation.car ? `${reservation.car.year} ${reservation.car.make} ${reservation.car.model}` : 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-muted-foreground">Durée</p>
                            <p class="font-medium">{{ reservation.total_days }} jours</p>
                        </div>
                    </div>
                    
                    <!-- Optimized Payment Breakdown -->
                    <div class="mt-6 pt-6 border-t space-y-6">
                        <!-- Hero Section for Deposit -->
                        <div class="bg-primary/5 p-6 rounded-2xl border border-primary/10 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-primary">Paiement Immédiat (Acompte)</span>
                                    <p class="text-4xl font-black text-primary tracking-tighter">{{ fmtMoney(props.reservation.deposit_amount) }}</p>
                                </div>
                                <div class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center shadow-sm text-primary">
                                    <Wallet class="size-6" />
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-primary/10 flex items-center justify-between text-xs font-bold uppercase tracking-widest text-primary/60">
                                <span>TOTAL</span>
                                <span>{{ fmtMoney(props.reservation.total_amount) }}</span>
                            </div>
                        </div>

                        <!-- Pay at Agency Info -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex items-start gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-white flex items-center justify-center shadow-sm text-slate-400">
                                <Info class="size-5" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Payer en agence lors de la collecte</p>
                                <p class="text-lg font-black text-slate-900">{{ fmtMoney(props.reservation.remaining_amount) }}</p>
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest leading-tight">
                                    Le solde restant et la caution ({{ fmtMoney(props.reservation.security_deposit_amount) }}) sont dus à l'agence.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- No Payment Methods -->
            <Alert v-if="!paymentMethods || paymentMethods.length === 0" variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>Systèmes de paiement indisponibles</AlertTitle>
                <AlertDescription>
                    Nous ne pouvons pas traiter les paiements pour le moment. Veuillez contacter le support.
                </AlertDescription>
            </Alert>

            <!-- Pre-Payment Instructions -->
            <div class="grid gap-4 md:grid-cols-2 mb-2">
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-card border shadow-sm transition-all hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Info class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Prochaines étapes</h3>
                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed">Une fois l'acompte payé, votre réservation sera instantanément confirmée et un reçu vous sera envoyé.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-5 rounded-2xl bg-card border shadow-sm transition-all hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Conditions requises</h3>
                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed whitespace-pre-wrap">{{ $page.props.settings.rental_terms || 'Permis de conduire valide (min. 2 ans) et pièce d\'identité requis lors de la collecte.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Selection -->
            <div v-if="paymentMethods && paymentMethods.length > 0" class="space-y-4">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    Sélectionner la méthode de paiement
                    <HelpTooltip content="Choisissez un canal de paiement sécurisé. Les paiements numériques sont traités instantanément pour confirmer votre réservation." />
                </h2>

                <div class="grid gap-4">
                    <Card
                        v-for="method in paymentMethods"
                        :key="method.value"
                        class="cursor-pointer transition-all hover:border-primary"
                        :class="{ 'border-primary ring-2 ring-ring': selectedMethod === method.value }"
                        @click="selectMethod(method.value)"
                    >
                        <CardContent class="p-4 flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                <component
                                    :is="method.icon === 'paypal' ? Wallet : CreditCard"
                                    class="h-6 w-6 text-primary"
                                />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">{{ method.label }}</p>
                                <p class="text-sm text-muted-foreground">{{ method.description }}</p>
                            </div>
                            <div v-if="method.is_sandbox" class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
                                Mode TEST
                            </div>
                            <CheckCircle
                                v-if="selectedMethod === method.value"
                                class="h-5 w-5 text-primary"
                            />
                        </CardContent>
                    </Card>
                </div>

                <!-- Stripe Card Element -->
                <div v-if="selectedMethod === 'stripe'" class="mt-4">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base flex items-center gap-2">
                                Détails de la carte
                                <HelpTooltip content="Vos données de carte sont traitées directement par Stripe avec un cryptage de niveau bancaire. Nous ne stockons jamais vos numéros de carte." />
                            </CardTitle>
                            <CardDescription>Veuillez entrer les informations de votre carte ci-dessous</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div id="card-element" class="min-h-[40px] p-4 border rounded-lg bg-background"></div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Error Message -->
                <Alert v-if="errorMessage" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Erreur de paiement</AlertTitle>
                    <AlertDescription>{{ errorMessage }}</AlertDescription>
                </Alert>

                <!-- Pay Button -->
                <Button
                    class="w-full h-12 text-lg"
                    :disabled="!selectedMethod || isProcessing"
                    @click="submitPayment"
                >
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    <template v-if="isProcessing">
                        Traitement...
                    </template>
                    <template v-else>
                        Payer maintenant - {{ fmtMoney(props.reservation.deposit_amount) }}
                    </template>
                </Button>

                <!-- Security Note -->
                <p class="text-center text-xs text-muted-foreground">
                    <span class="flex items-center justify-center gap-1">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Paiement sécurisé via {{ getPaymentMethodName(selectedMethod || '') }}
                    </span>
                </p>
            </div>
        </main>
    </ClientLayout>
</template>
