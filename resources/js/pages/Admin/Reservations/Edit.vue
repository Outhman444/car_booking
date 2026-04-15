<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { 
    ArrowLeft, 
    Save, 
    Calendar, 
    MapPin, 
    Clock, 
    User, 
    Car as CarIcon, 
    Tag ,
    FileText,
    CreditCard
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index, update } from '@/routes/admin/reservations';
import HelpTooltip from '@/components/HelpTooltip.vue';

const props = defineProps<{
    reservation: any;
    enums: {
        statuses: Array<{ value: string; label: string; color: string }>;
    };
}>();

const statuses = computed(() => props.enums.statuses || []);

const formatDateForInput = (dateString: string | null | undefined): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
};

const form = useForm({
    start_date: formatDateForInput(props.reservation?.start_date) || '',
    end_date: formatDateForInput(props.reservation?.end_date) || '',
    pickup_time: props.reservation?.pickup_time || '09:00',
    return_time: props.reservation?.return_time || '18:00',
    pickup_location: props.reservation?.pickup_location || '',
    return_location: props.reservation?.return_location || '',
    discount_amount: props.reservation?.discount_amount || 0,
    notes: props.reservation?.notes || '',
    status: props.reservation?.status || 'pending',
    cancellation_reason: props.reservation?.cancellation_reason || '',
});

function submit() {
    form.put(update(props.reservation.id).url);
}
</script>

<template>
    <Head
        :title="`Edit Booking #${reservation?.reservation_number || ''}`"
    />
    <AdminLayout>
        <main class="flex-1 p-4 sm:p-8 space-y-8 sm:space-y-12 bg-background min-h-screen pb-32 max-w-[1200px] mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-4">
                    <Link :href="index().url" class="group inline-flex items-center text-xs font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-all">
                        <ArrowLeft class="size-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                        Retour aux réservations
                    </Link>
                    <Heading
                        :title="`Modifier la réservation #${reservation?.reservation_number}`"
                        description="Modifiez les détails de ce contrat de location."
                        size="lg"
                    />
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="index().url">
                        <Button variant="ghost" class="h-14 px-8 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all">Annuler</Button>
                    </Link>
                    <Button
                        @click="submit"
                        :disabled="form.processing"
                        class="h-14 px-10 rounded-2xl bg-primary text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-primary/20 hover:bg-primary/90 transition-all border-none active:scale-[0.98]"
                    >
                        <Save class="size-5 mr-3" />
                        Enregistrer
                    </Button>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-3xl border-none ring-1 ring-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40 flex flex-col gap-2">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <FileText class="size-4 text-primary" /> Reference
                    </div>
                    <div class="font-mono text-xl font-black text-slate-900 tracking-tight">
                        {{ reservation.reservation_number }}
                    </div>
                </div>
                <div class="rounded-3xl border-none ring-1 ring-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40 flex flex-col gap-2">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <User class="size-4 text-emerald-500" /> Client
                    </div>
                    <div class="text-base font-black text-slate-900 leading-tight">
                        {{ reservation.user?.name }}
                        <div class="text-[10px] font-bold text-slate-400 normal-case tracking-normal mt-1">{{ reservation.user?.email }}</div>
                    </div>
                </div>
                <div class="rounded-3xl border-none ring-1 ring-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40 flex flex-col gap-2">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <CarIcon class="size-4 text-amber-500" /> Vehicle
                    </div>
                    <div class="text-base font-black text-slate-900">
                        {{
                            reservation.car
                                ? `${reservation.car.year} ${reservation.car.make} ${reservation.car.model}`
                                : '—'
                        }}
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-10">
                <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                    <CardHeader class="p-8 pb-4">
                        <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                                <FileText class="size-5" />
                            </div>
                            Agreement Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-8 pt-4 space-y-10">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <!-- Start Date -->
                            <div class="space-y-4">
                                <label for="start_date" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <Calendar class="size-3"/> Date de prise en charge
                                    <HelpTooltip content="La date prévue pour la prise en charge du véhicule. Les modifications peuvent affecter le coût total de la location." />
                                </label>
                                <Input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                />
                                <InputError :message="form.errors.start_date" />
                            </div>

                            <!-- End Date -->
                            <div class="space-y-4">
                                <label for="end_date" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <Calendar class="size-3"/> Date de retour
                                    <HelpTooltip content="La date de retour prévue. Les retours tardifs sont calculés sur la base d'un tarif de 24 heures." />
                                </label>
                                <Input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="date"
                                    class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>

                            <!-- Pickup Time -->
                            <div class="space-y-4">
                                <label for="pickup_time" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <Clock class="size-3"/> Heure de prise en charge
                                    <HelpTooltip content="L'heure spécifique pour la prise en charge du véhicule à l'endroit choisi." />
                                </label>
                                <Input
                                    id="pickup_time"
                                    v-model="form.pickup_time"
                                    type="time"
                                    class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                />
                                <InputError :message="form.errors.pickup_time" />
                            </div>

                            <!-- Return Time -->
                            <div class="space-y-4">
                                <label for="return_time" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <Clock class="size-3"/> Heure de retour
                                    <HelpTooltip content="L'heure prévue pour le retour du véhicule au point de chute." />
                                </label>
                                <Input
                                    id="return_time"
                                    v-model="form.return_time"
                                    type="time"
                                    class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                />
                                <InputError :message="form.errors.return_time" />
                            </div>

                            <!-- Pickup Location -->
                            <div class="space-y-4">
                                <label for="pickup_location" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2"><MapPin class="size-3"/> Lieu de prise en charge</label>
                                <Input
                                    id="pickup_location"
                                    v-model="form.pickup_location"
                                    placeholder="e.g. Main Office"
                                    class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                />
                                <InputError :message="form.errors.pickup_location" />
                            </div>

                            <!-- Return Location -->
                            <div class="space-y-4">
                                <label for="return_location" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <MapPin class="size-3"/> Lieu de retour
                                    <HelpTooltip content="L'endroit spécifié pour retourner le véhicule." />
                                </label>
                                <Input
                                    id="return_location"
                                    v-model="form.return_location"
                                    placeholder="e.g. Airport Terminal"
                                    class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                />
                                <InputError :message="form.errors.return_location" />
                            </div>

                            <!-- Discount Amount -->
                            <div class="space-y-4">
                                <label for="discount_amount" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <CreditCard class="size-3"/> Discount manuel
                                    <HelpTooltip content="Appliquez une réduction directe sur le prix total de la réservation." />
                                </label>
                                <div class="relative group">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg font-black text-slate-400 group-focus-within:text-primary transition-colors">- $</span>
                                    <Input
                                        id="discount_amount"
                                        v-model="form.discount_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="h-14 pl-12 rounded-2xl border-none bg-slate-50 font-black text-xl text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                                    />
                                </div>
                                <InputError :message="form.errors.discount_amount" />
                            </div>

                            <!-- Status -->
                            <div class="space-y-4">
                                <label for="status" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                    <Tag class="size-3"/> Statut de la réservation
                                    <HelpTooltip content="Gérez l'étape actuelle de la réservation." />
                                </label>
                                <Select v-model="form.status">
                                    <SelectTrigger id="status" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-2xl border-none shadow-2xl ring-1 ring-slate-100">
                                        <SelectItem 
                                            v-for="s in statuses" 
                                            :key="s.value" 
                                            :value="s.value"
                                            class="rounded-xl font-bold py-3"
                                        >
                                            {{ s.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2 space-y-4">
                                <label for="notes" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Notes internes</label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="4"
                                    class="rounded-3xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all p-6 text-base leading-relaxed placeholder:text-slate-400"
                                    placeholder="Internal notes visible only to staff..."
                                />
                                <InputError :message="form.errors.notes" />
                            </div>

                            <!-- Cancellation Reason -->
                            <div
                                v-if="form.status === 'cancelled'"
                                class="md:col-span-2 space-y-4 bg-rose-50/50 p-6 rounded-3xl ring-1 ring-rose-100"
                            >
                                <label for="cancellation_reason" class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-500">Raison de l'annulation</label>
                                <Textarea
                                    id="cancellation_reason"
                                    v-model="form.cancellation_reason"
                                    rows="3"
                                    class="rounded-2xl border-none bg-white font-bold text-slate-900 ring-1 ring-rose-200 focus:ring-2 focus:ring-rose-500 transition-all p-4 text-base placeholder:text-slate-400"
                                    placeholder="Why was this reservation cancelled?"
                                />
                                <InputError :message="form.errors.cancellation_reason" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </main>
    </AdminLayout>
</template>
