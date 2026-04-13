<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ref, onMounted } from 'vue';
import { CreditCard, Wallet, Banknote, AlertCircle, Loader2, CheckCircle, ArrowLeft, Info } from 'lucide-vue-next';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';
import HelpTooltip from '@/components/HelpTooltip.vue';
import BookingStepper from '@/components/BookingStepper.vue';

const props = defineProps<{
    reservation: {
        id: number;
        reservation_number: string;
        total_amount: number;
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
                        '::placeholder': { color: '#9ca3af' },
                    },
                },
            });
        }
    }
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
                        errorMessage.value = errors.error || 'Your payment was not finalized. Please try again or contact support if the issue persists.';
                    },
                }
            );
        } else {
             errorMessage.value = 'We could not successfully authenticate your payment. Please try another card or payment method.';
             isProcessing.value = false;
        }

    } catch (e: any) {
        errorMessage.value = e.response?.data?.error || e.message || 'We encountered an error processing your request. Please try again.';
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
            errorMessage.value = 'We received an invalid response from PayPal. Please try again later or contact our team.';
            isProcessing.value = false;
        }
    } catch {
        errorMessage.value = 'We failed to securely connect to PayPal. Please check your internet connection and try again.';
        isProcessing.value = false;
    }
}

async function processAgencyPayment() {
    isProcessing.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(`/client/payment/${props.reservation.id}/agency`);
        
        if (response.data.error) {
            errorMessage.value = response.data.error;
            isProcessing.value = false;
            return;
        }

        if (response.data.redirect_url) {
            router.visit(response.data.redirect_url);
        }
    } catch (e: any) {
        errorMessage.value = e.response?.data?.error || 'Failed to process payment selection.';
        isProcessing.value = false;
    }
}

async function submitPayment() {
    if (!selectedMethod.value) return;

    if (selectedMethod.value === 'stripe') {
        await processStripePayment();
    } else if (selectedMethod.value === 'paypal') {
        await processPayPalPayment();
    } else if (selectedMethod.value === 'agency') {
        await processAgencyPayment();
    }
}
</script>

<template>
    <Head :title="`Pay Reservation ${reservation?.reservation_number || ''}`" />
    <ClientLayout>
        <main class="flex-1 p-8 space-y-6 max-w-3xl mx-auto">
            <!-- Status Stepper -->
            <BookingStepper :current-step="3" class="mb-4" />

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="`/client/reservations/${reservation.id}`">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-semibold">Complete Payment</h1>
                    <p class="text-muted-foreground">Reservation #{{ reservation.reservation_number }}</p>
                </div>
            </div>

            <!-- Reservation Summary -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Reservation Summary</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Car</p>
                            <p class="font-medium">
                                {{ reservation.car ? `${reservation.car.year} ${reservation.car.make} ${reservation.car.model}` : 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-muted-foreground">Duration</p>
                            <p class="font-medium">{{ reservation.total_days }} days</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t flex items-center justify-between">
                        <span class="text-lg font-semibold flex items-center gap-2">
                            Total Amount
                            <HelpTooltip content="This is the final price including all taxes, fees, and the daily rental rate for the full duration." />
                        </span>
                        <span class="text-2xl font-bold text-primary">{{ fmtMoney(reservation.total_amount) }}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- No Payment Methods -->
            <Alert v-if="!paymentMethods || paymentMethods.length === 0" variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>Systems Are Unavailable</AlertTitle>
                <AlertDescription>
                    We're currently unable to process digital payments. Please reach out to our customer support team to finish your reservation.
                </AlertDescription>
            </Alert>

            <!-- Pre-Payment Instructions -->
            <div class="grid gap-4 md:grid-cols-2 mb-2">
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-card border shadow-sm transition-all hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Info class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Next Steps</h3>
                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed">After reservation is confirmed, bring your reference number to the agency to collect your car.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-5 rounded-2xl bg-card border shadow-sm transition-all hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Requirements</h3>
                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed">Bring your ID/Passport and a valid driver's license (min. {{ $page.props.settings.min_driving_experience || 2 }} years exp).</p>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Selection -->
            <div v-if="paymentMethods && paymentMethods.length > 0" class="space-y-4">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    Select Payment Method
                    <HelpTooltip content="Choose a secure payment channel. Digital payments are processed instantly to confirm your booking." />
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
                                    :is="method.icon === 'paypal' ? Wallet : (method.icon === 'banknote' ? Banknote : CreditCard)"
                                    class="h-6 w-6 text-primary"
                                />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">{{ method.label }}</p>
                                <p class="text-sm text-muted-foreground">{{ method.description }}</p>
                            </div>
                            <div v-if="method.is_sandbox" class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
                                Test Mode
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
                                Card Details
                                <HelpTooltip content="Your card data is processed directly by Stripe using bank-level encryption. We never store your card numbers." />
                            </CardTitle>
                            <CardDescription>Enter your card information securely</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div id="card-element" class="min-h-[40px] p-4 border rounded-lg bg-background"></div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Error Message -->
                <Alert v-if="errorMessage" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Payment Error</AlertTitle>
                    <AlertDescription>{{ errorMessage }}</AlertDescription>
                </Alert>

                <!-- Agency Rules Promiment Warning -->
                <div v-if="selectedMethod === 'agency'" class="mt-6 p-5 rounded-2xl border border-yellow-200 bg-yellow-50 flex items-start gap-3 shadow-inner">
                    <AlertCircle class="h-6 w-6 text-yellow-600 shrink-0 mt-0.5" />
                    <div>
                        <h4 class="font-bold text-yellow-900 text-base">Time-Sensitive Requirement</h4>
                        <p class="text-sm text-yellow-800 mt-1.5 leading-relaxed">
                            You must attend the agency personally to pay the total amount in cash within <strong class="text-yellow-950 font-bold bg-yellow-200 px-1.5 py-0.5 rounded">{{ $page.props.settings.cash_reservation_timeout || 24 }} Hours</strong>. 
                            If you fail to do so, your reservation will be automatically cancelled and the car will be given to another customer.
                        </p>
                    </div>
                </div>

                <!-- Pay Button -->
                <Button
                    class="w-full h-12 text-lg"
                    :disabled="!selectedMethod || isProcessing"
                    @click="submitPayment"
                >
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    {{ isProcessing ? 'Processing... ' : (selectedMethod === 'agency' ? 'Confirm Reservation & Pay at Agency' : `Pay ${fmtMoney(reservation.total_amount)}`) }}
                </Button>

                <!-- Security Note -->
                <p class="text-center text-xs text-muted-foreground">
                    <span class="flex items-center justify-center gap-1">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Secure payment powered by {{ selectedMethod === 'paypal' ? 'PayPal' : (selectedMethod === 'agency' ? 'Our Agency' : 'Stripe') }}
                    </span>
                </p>
            </div>
        </main>
    </ClientLayout>
</template>
