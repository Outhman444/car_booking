<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { fleet } from '@/routes';
import { index } from '@/routes/client/reservations';
import { ref, computed } from 'vue';
import { Loader2, CreditCard, CheckCircle, ClipboardList, LifeBuoy } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import BookingStepper from '@/components/BookingStepper.vue';
import ReservationCountdown from '@/components/ReservationCountdown.vue';
import { useReservationHelpers } from '@/composables/useReservationHelpers';
import { trans } from '@/lib/translations';
const $page = usePage<any>();
const reservation = computed(() => $page.props.reservation);
const helpers = computed(() => useReservationHelpers(reservation.value.currency || 'USD'));

const isPaid = computed(() => reservation.value.is_paid || ['confirmed', 'active'].includes(reservation.value.status));

// Calculate deposit and remaining from server-provided values
const depositAmount = computed(() => parseFloat(reservation.value.deposit_amount || 0));
const remainingAmount = computed(() => parseFloat(reservation.value.remaining_amount || 0));
const securityDeposit = computed(() => parseFloat(reservation.value.security_deposit_amount || 0));
const depositPercentage = computed(() => $page.props.settings?.booking_deposit_percentage || 20);

const getStatusLabel = (status: string) => trans('reservation', status);
</script>

<template>
    <HomeLayout>
        <div class="min-h-screen bg-white py-12">
            <div class="mx-auto max-w-7xl px-6 pt-4">
                <!-- Expiration Countdown for Pending Reservations -->
                <div v-if="reservation.status === 'pending'" class="mb-8 max-w-2xl mx-auto">
                    <ReservationCountdown 
                        :expires-at="reservation.pending_expires_at" 
                        variant="confirmation"
                    />
                </div>

                <!-- Status Stepper -->
                <BookingStepper :current-step="2" :status="reservation.status" :is-paid="reservation.is_paid" class="mb-12" />

                <!-- Clean success header -->
                <div class="mb-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full" :class="reservation.status === 'pending' ? 'bg-primary/10' : 'bg-green-100'">
                        <component :is="reservation.status === 'pending' ? ClipboardList : CheckCircle" class="h-8 w-8" :class="reservation.status === 'pending' ? 'text-primary' : 'text-emerald-600'" />
                    </div>
                    <h1 class="mb-2 text-3xl font-black tracking-tight text-slate-900">
                        <template v-if="reservation.status === 'pending'">
                            Révisez Votre <span class="text-primary">Réservation</span>
                        </template>
                        <template v-else>
                            Réservation <span class="text-primary">Confirmée</span>
                        </template>
                    </h1>
                    <p class="text-sm font-bold text-slate-400 text-center uppercase tracking-widest">
                        <template v-if="reservation.status === 'pending'">
                            Veuillez vérifier les détails ci-dessous avant de procéder au paiement
                        </template>
                        <template v-else>
                            Réservation #{{ reservation.reservation_number }}
                        </template>
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Main Details -->
                    <div class="space-y-8 lg:col-span-2">
                        <!-- Car Information -->
                        <div class="rounded-3xl border-none bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Détails du Véhicule</h2>
                            <div class="flex flex-col sm:flex-row items-start gap-8">
                                <div class="relative group">
                                    <div class="absolute inset-0 bg-primary/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <img
                                        :src="reservation.car.image_url"
                                        :alt="`${reservation.car.make} ${reservation.car.model}`"
                                        class="h-32 w-48 rounded-2xl object-cover relative ring-1 ring-slate-100 shadow-lg"
                                    />
                                </div>
                                <div class="space-y-4 flex-1">
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900">
                                            {{ reservation.car.make }}
                                            <span class="text-primary">{{ reservation.car.model }}</span>
                                        </h3>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">
                                            Année Modèle {{ reservation.car.year }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="bg-primary/5 text-primary px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest ring-1 ring-ring/10">
                                            {{ trans('fuel', reservation.car.fuel_type) }}
                                        </span>
                                        <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                            Flotte Premium
                                        </span>
                                    </div>
                                    <p class="text-sm leading-relaxed text-slate-500 font-medium">
                                        {{ reservation.car.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rental Details -->
                        <div class="rounded-3xl border-none bg-white p-8 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Informations Logistiques</h2>
                            <div class="grid gap-12 md:grid-cols-2">
                                <div class="space-y-6">
                                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary"></div> Planification
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Départ:</span>
                                            <span class="font-black text-slate-900">{{ new Date(reservation.start_date).toLocaleDateString('fr-FR') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Arrivée:</span>
                                            <span class="font-black text-slate-900">{{ new Date(reservation.end_date).toLocaleDateString('fr-FR') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-6">
                                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary"></div> Lieux
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Collecte:</span>
                                            <span class="font-black text-slate-900">{{ reservation.pickup_location }}</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Retour:</span>
                                            <span class="font-black text-slate-900">{{ reservation.return_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="rounded-3xl border-none bg-slate-900 p-8 shadow-xl shadow-slate-200 text-white">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-500">Détails du Client</h2>
                            <div class="grid gap-8 md:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Nom Complet</span>
                                    <span class="text-lg font-black">{{ reservation.user.name }}</span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Adresse E-mail</span>
                                    <span class="text-lg font-black text-primary">{{ reservation.user.email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Price Summary -->
                        <div class="rounded-3xl border-none bg-white p-8 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100">
                            <h2 class="mb-6 text-sm font-black uppercase tracking-widest text-slate-400">Résumé Financier</h2>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Statut Actuel:</span>
                                    <span class="rounded-full bg-amber-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-600 ring-1 ring-amber-200">
                                        {{ getStatusLabel(reservation.status) }}
                                    </span>
                                </div>
                                
                                <!-- Hero Payment Section -->
                                <div class="bg-primary/5 p-8 rounded-[2rem] border border-primary/20 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-1">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-primary">Acompte - Payer Maintenant</span>
                                            <p class="text-5xl font-black text-primary tracking-tighter">
                                                {{ $page.props.currency.symbol }}{{ depositAmount.toFixed(2) }}
                                            </p>
                                        </div>
                                        <div class="h-14 w-14 rounded-2xl bg-primary/10 flex items-center justify-center">
                                            <CreditCard class="size-6 text-primary" />
                                        </div>
                                    </div>
                                    
                                    <div class="pt-4 border-t border-primary/10 flex justify-between items-center">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-primary/60">Solde à payer en agence</span>
                                        <span class="font-black text-primary">{{ $page.props.currency.symbol }}{{ remainingAmount.toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Transaction breakdown -->
                                <div class="space-y-4 px-2">
                                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        <span>Prix Total du Contrat</span>
                                        <span class="text-slate-900">{{ $page.props.currency.symbol }}{{ parseFloat(reservation.total_amount).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Security Deposit Notice (informational only) -->
                                <div v-if="securityDeposit > 0" class="bg-amber-50 p-4 rounded-2xl border border-amber-200">
                                    <div class="flex items-start gap-2">
                                        <LifeBuoy class="h-4 w-4 text-amber-600 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-[10px] font-black text-amber-900 uppercase">Info Caution</p>
                                            <p class="text-[10px] font-bold text-amber-700 leading-relaxed uppercase tracking-widest mt-1">
                                                Montant remboursable de {{ $page.props.currency.symbol }}{{ securityDeposit.toFixed(2) }} requis à la collecte.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Support Assistance -->
                        <div class="rounded-3xl border-none bg-sky-500/5 p-8 shadow-xl shadow-sky-500/5 ring-1 ring-sky-500/20">
                            <h2 class="mb-4 flex items-center text-sm font-black uppercase tracking-widest text-sky-600 gap-3">
                                <div class="p-2 bg-sky-500/10 rounded-xl"><LifeBuoy class="size-4" /></div> Besoin d'Aide?
                            </h2>
                            <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                Notre équipe de support est disponible 24h/24 et 7j/7. Si vous avez des questions concernant votre réservation ou si vous devez demander des modifications, n'hésitez pas à nous contacter.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-4 pt-4">
                            <template v-if="reservation.status === 'pending' && !isPaid">
                                <a :href="`/client/payment/${reservation.id}`" class="h-16 flex w-full items-center justify-center gap-3 rounded-[1.25rem] bg-primary px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-white transition-all duration-300 hover:bg-blue-700 shadow-2xl shadow-primary/20 hover:-translate-y-1">
                                    <CreditCard class="h-4 w-4" /> Paiement Sécurisé en Ligne
                                </a>
                            </template>
                            <a :href="index.url()" class="h-16 flex items-center justify-center w-full rounded-[1.25rem] bg-slate-900 px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-white transition-all duration-300 hover:bg-black shadow-2xl shadow-slate-200">
                                Accès au Tableau de Bord
                            </a>
                            <a :href="fleet.url()" class="h-16 flex items-center justify-center w-full rounded-[1.25rem] border-none bg-white px-8 text-center text-xs font-black uppercase tracking-[0.2em] text-slate-400 transition-all duration-300 hover:bg-slate-50 ring-1 ring-slate-100">
                                Continuer à Parcourir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </HomeLayout>
</template>
