<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, watch } from 'vue';
import { CreditCard, Wallet, AlertCircle, CheckCircle, XCircle } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

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

const localMethods = ref(JSON.parse(JSON.stringify(props.methods)));

watch(() => props.methods, (newMethods) => {
    localMethods.value = JSON.parse(JSON.stringify(newMethods));
}, { deep: true });

const getMethodIcon = (method: string) => {
    return method === 'paypal' ? Wallet : CreditCard;
};

const toggleMethod = (method: string) => {
    router.post(
        `/admin/payment-methods/${method}/toggle`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                const m = localMethods.value.find(m => m.method === method);
                if (m) m.is_enabled = !m.is_enabled;
            },
        }
    );
};

const saveMethod = (method: string) => {
    const m = localMethods.value.find(m => m.method === method);
    if (m) {
        router.put(
            `/admin/payment-methods/${method}`,
            {
                is_enabled: m.is_enabled,
                is_sandbox: m.is_sandbox,
                display_name: m.display_name,
                description: m.description,
            },
            {
                preserveScroll: true,
            }
        );
    }
};
</script>

<template>
    <Head title="Payment Methods" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">Payment Methods</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Configure and manage your payment gateways
                    </p>
                </div>
            </div>

            <!-- Warning Alert -->
            <Alert v-if="!localMethods.some(m => m.is_enabled)">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>No Payment Methods Enabled</AlertTitle>
                <AlertDescription>
                    Enable at least one payment method to allow clients to pay for reservations.
                </AlertDescription>
            </Alert>

            <!-- Payment Methods Cards -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card v-for="m in localMethods" :key="m.method" class="relative">
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4 flex items-center gap-2">
                        <Badge v-if="m.is_sandbox" variant="outline" class="bg-yellow-50 text-yellow-700 border-yellow-200">
                            Sandbox Mode
                        </Badge>
                        <Badge v-if="m.is_enabled" variant="outline" class="bg-green-50 text-green-700 border-green-200">
                            <CheckCircle class="h-3 w-3 mr-1" />
                            Active
                        </Badge>
                        <Badge v-else variant="outline" class="bg-gray-50 text-gray-500 border-gray-200">
                            <XCircle class="h-3 w-3 mr-1" />
                            Disabled
                        </Badge>
                    </div>

                    <CardHeader class="pr-24">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                <component :is="getMethodIcon(m.method)" class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">{{ m.display_name || (m.method === 'paypal' ? 'PayPal' : 'Credit/Debit Card') }}</CardTitle>
                                <CardDescription>
                                    {{ m.method === 'paypal' ? 'Pay with PayPal account' : 'Pay via Stripe' }}
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-4">
                        <!-- Configuration Status -->
                        <Alert v-if="!m.is_configured" variant="destructive">
                            <AlertCircle class="h-4 w-4" />
                            <AlertTitle>Not Configured</AlertTitle>
                            <AlertDescription>
                                Add {{ m.method === 'paypal' ? 'PAYPAL_CLIENT_ID and PAYPAL_SECRET' : 'STRIPE_KEY and STRIPE_SECRET' }} to your .env file.
                            </AlertDescription>
                        </Alert>

                        <!-- Settings Form -->
                        <div class="space-y-4">
                            <!-- Enable Toggle -->
                            <div class="flex items-center justify-between rounded-lg border p-4">
                                <div class="space-y-0.5">
                                    <Label class="text-base">Enable {{ m.display_name || m.method }}</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Allow clients to pay with this method
                                    </p>
                                </div>
                                <Switch
                                    v-model:checked="m.is_enabled"
                                    :disabled="!m.is_configured"
                                />
                            </div>

                            <!-- Sandbox Mode Toggle -->
                            <div class="flex items-center justify-between rounded-lg border p-4">
                                <div class="space-y-0.5">
                                    <Label class="text-base">Sandbox Mode</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Use test environment (recommended for development)
                                    </p>
                                </div>
                                <Switch
                                    v-model:checked="m.is_sandbox"
                                />
                            </div>

                            <!-- Display Name -->
                            <div class="space-y-2">
                                <Label>Display Name</Label>
                                <Input
                                    v-model="m.display_name"
                                    placeholder="e.g., Pay with PayPal"
                                />
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label>Description</Label>
                                <Input
                                    v-model="m.description"
                                    placeholder="Short description shown to clients"
                                />
                            </div>

                            <!-- Save Button -->
                            <Button
                                @click="saveMethod(m.method)"
                                class="w-full"
                                :disabled="!m.is_configured"
                            >
                                Save Settings
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Info Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Configuration Guide</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="rounded-lg bg-muted p-4">
                        <h4 class="font-medium mb-2">PayPal Setup</h4>
                        <code class="text-xs block bg-background p-2 rounded">
                            PAYPAL_CLIENT_ID=your_client_id<br>
                            PAYPAL_SECRET=your_secret<br>
                            PAYPAL_SANDBOX=true
                        </code>
                        <p class="text-sm text-muted-foreground mt-2">
                            Get credentials from <a href="https://developer.paypal.com" target="_blank" class="text-primary hover:underline">PayPal Developer Portal</a>
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted p-4">
                        <h4 class="font-medium mb-2">Stripe Setup</h4>
                        <code class="text-xs block bg-background p-2 rounded">
                            STRIPE_KEY=pk_test_xxx<br>
                            STRIPE_SECRET=sk_test_xxx<br>
                            STRIPE_WEBHOOK_SECRET=whsec_xxx
                        </code>
                        <p class="text-sm text-muted-foreground mt-2">
                            Get credentials from <a href="https://dashboard.stripe.com" target="_blank" class="text-primary hover:underline">Stripe Dashboard</a>
                        </p>
                    </div>
                </CardContent>
            </Card>
        </main>
    </AdminLayout>
</template>
