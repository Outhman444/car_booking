<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { book } from '@/routes/fleet';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { login } from '@/routes';
import { 
    ChevronRight, 
    Calendar, 
    MapPin, 
    Clock, 
    CalendarCheck, 
    ReceiptText, 
    TriangleAlert, 
    LogIn, 
    LoaderCircle, 
    CheckCircle,
    Zap,
    Users,
    Cog,
    Gauge,
    Palette,
    ShieldCheck,
    FileCheck,
    IdCard,
    CreditCard
} from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ref } from 'vue';
import HelpTooltip from '@/components/HelpTooltip.vue';
import BookingStepper from '@/components/BookingStepper.vue';
import { getCarColorHex } from '@/lib/colors';

interface Car {
    id: number;
    make: string;
    model: string;
    price_per_day: string;
    image_url: string;
    images: { url: string; alt: string }[];
    fuel_type: string;
    transmission: string;
    year: string;
    description: string;
    status: string;
    seats: number;
    color: string;
    mileage: number;
}

const $page = usePage<any>();
const car = computed<Car>(() => $page.props.car as Car);

const locations = computed(() => $page.props.locations as any[]);
const taxRate = computed(() => ($page.props.taxRate as number) || 0.07);

const form = useForm({
    start_date: '',
    end_date: '',
    pickup_location: '',
    return_location: '',
});

const showRoleError = ref(false);
const roleErrorMessage = ref('');
const showPolicyModal = ref(false);

const acceptExperience = ref(false);
const acceptDocuments = ref(false);
const acceptTerms = ref(false);
const acceptLocation = ref(false);

const policyAccepted = computed(() => {
    return acceptExperience.value && acceptDocuments.value && acceptTerms.value && acceptLocation.value;
});

// Calculate rental details
const rentalDays = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    const diffTime = end.getTime() - start.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return Math.max(1, diffDays + 1); // +1 to include both start and end day
});

const subtotal = computed(() => {
    return rentalDays.value * parseFloat(car.value.price_per_day);
});

const tax = computed(() => {
    return subtotal.value * taxRate.value;
});

const total = computed(() => {
    return subtotal.value + tax.value;
});

const depositPercentage = computed(() => $page.props.settings?.booking_deposit_percentage || 20);
const securityDeposit = computed(() => $page.props.settings?.security_deposit_amount || 0);

const depositAmount = computed(() => {
    return total.value * (depositPercentage.value / 100);
});

const remainingAmount = computed(() => {
    return total.value - depositAmount.value;
});

const canSubmit = computed(() => {
    return (
        form.start_date &&
        form.end_date &&
        form.pickup_location &&
        form.return_location &&
        rentalDays.value > 0
    );
});

const submitBooking = () => {
    const user = $page.props.auth.user;

    if (!user) {
        router.get(login.url());
        return;
    }

    if (user.role === 'admin') {
        roleErrorMessage.value = "Vous ne pouvez pas réserver en tant qu'administrateur.";
        showRoleError.value = true;
        return;
    }

    if (user.role !== 'client') {
        roleErrorMessage.value = "Votre rôle ne permet pas la réservation.";
        showRoleError.value = true;
        return;
    }

    // Show policy modal for acceptance before submitting
    acceptExperience.value = false;
    acceptDocuments.value = false;
    acceptTerms.value = false;
    acceptLocation.value = false;
    showPolicyModal.value = true;
};

const confirmBookingAfterPolicy = () => {
    showPolicyModal.value = false;
    form.post(book.url(car.value.id));
};


// Auto-populate return location when pickup is selected
watch(
    () => form.pickup_location,
    (newLocation) => {
        if (newLocation && !form.return_location) {
            form.return_location = newLocation;
        }
    },
);

const images = computed(() => {
    if (car.value.images && car.value.images.length > 0) {
        return car.value.images;
    }
    return [
        {
            url: car.value.image_url,
            alt: `${car.value.make} ${car.value.model}`,
        },
    ];
});

</script>
<template>
    <HomeLayout>
        <div
            class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Status Stepper -->
                <BookingStepper :current-step="1" class="mb-8" />

                <!--  Header -->
                <div class="mb-8">
                    <nav
                        class="mb-6 flex items-center space-x-2 text-sm text-gray-500"
                    >
                        <a
                            href="/fleet"
                            class="font-black uppercase tracking-widest transition-colors duration-200 hover:text-primary"
                            >Flotte</a
                        >
                        <ChevronRight class="size-4 text-gray-400" />
                        <span class="font-medium text-gray-900"
                            >{{ car.make }} {{ car.model }}</span
                        >
                    </nav>
                    <div class="flex items-center space-x-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-sm ring-1 ring-ring/20"
                        >
                            <Calendar class="size-6" />
                        </div>
                        <div>
                            <h1
                                class="text-4xl leading-tight font-black tracking-tight text-slate-900"
                            >
                                Réserver <span class="text-primary">{{ car.make }}</span> {{ car.model }}
                            </h1>
                            <p class="mt-1 text-base font-bold text-slate-400 uppercase tracking-widest">
                                Complétez votre réservation en quelques étapes
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!--  Car Details Section -->
                    <div class="space-y-8 lg:col-span-2">
                        <!--  Car Images -->
                        <div
                            class="overflow-hidden rounded-3xl border-none bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-100"
                        >
                            <div
                                class="relative h-72 bg-gradient-to-br from-gray-100 to-gray-200 sm:h-96"
                            >
                                <img
                                    :src="images[0]?.url"
                                    alt="image de voiture"
                                    class="h-full w-full object-cover transition-all duration-500"
                                />
                            </div>

                            <!--  Car Info -->
                            <div class="p-8">
                                <div
                                    class="mb-6 flex items-start justify-between"
                                >
                                    <div>
                                        <h2
                                            class="mb-2 text-3xl font-bold text-gray-900"
                                        >
                                            {{ car.make }} {{ car.model }}
                                        </h2>
                                        <div
                                            class="flex items-center space-x-6 text-sm text-gray-500"
                                        >
                                            <span
                                                class="flex items-center rounded-full bg-primary/5 px-4 py-1.5 text-primary font-black uppercase tracking-widest text-[10px] ring-1 ring-ring/20"
                                            >
                                                <Calendar class="mr-2 h-3.5 w-3.5" />
                                                {{ car.year }}
                                            </span>
                                            <span
                                                class="flex items-center rounded-full bg-emerald-50 px-4 py-1.5 capitalize text-emerald-700 font-black uppercase tracking-widest text-[10px] ring-1 ring-emerald-200"
                                            >
                                                <Zap class="mr-2 h-3.5 w-3.5" />
                                                {{ car.fuel_type }}
                                            </span>
                                        </div>

                                        <div class="mt-6 flex flex-wrap gap-4">
                                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-2.5 border border-slate-100 shadow-sm transition-all hover:bg-white hover:shadow-md">
                                                <Users class="size-4 text-primary" />
                                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest">{{ car.seats }} Sièges</span>
                                            </div>
                                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-2.5 border border-slate-100 shadow-sm transition-all hover:bg-white hover:shadow-md">
                                                <Cog class="size-4 text-primary" />
                                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest capitalize">{{ car.transmission }}</span>
                                            </div>
                                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-2.5 border border-slate-100 shadow-sm transition-all hover:bg-white hover:shadow-md">
                                                <Gauge class="size-4 text-primary" />
                                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest">{{ car.mileage.toLocaleString() }} KM</span>
                                            </div>
                                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-2.5 border border-slate-100 shadow-sm transition-all hover:bg-white hover:shadow-md">
                                                <span class="size-4 rounded-full border-2 border-slate-200/50 shadow-sm" :style="{ backgroundColor: getCarColorHex(car.color) }"></span>
                                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest capitalize">Couleur {{ car.color }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div
                                            class="rounded-3xl bg-slate-900 px-8 py-5 text-white shadow-2xl shadow-slate-200"
                                        >
                                            <span class="text-4xl font-black"
                                                >{{ $page.props.currency.symbol }}{{ Math.floor(parseFloat(car.price_per_day)) }}<span class="text-lg opacity-40">.{{ parseFloat(car.price_per_day).toFixed(2).split('.')[1] }}</span></span
                                            >
                                            <span
                                                class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mt-1"
                                                >PAR JOUR</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <p class="leading-relaxed text-gray-600">
                                    {{ car.description }}
                                </p>
                            </div>
                        </div>

                        <!--  Booking Form -->
                        <div
                            class="rounded-[2.5rem] border-none bg-white p-10 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100"
                        >
                            <div class="mb-10 flex items-center space-x-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-sm"
                                >
                                    <CalendarCheck class="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black tracking-tight text-slate-900">
                                        Détails de la réservation
                                    </h3>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Configurez votre voyage</p>
                                </div>
                            </div>

                            <form class="space-y-8">
                                <!--  Rental Dates -->
                                <div class="space-y-4">
                                    <h4
                                        class="flex items-center text-sm font-black uppercase tracking-widest text-slate-900 mb-6"
                                    >
                                        <Clock class="mr-3 h-4 w-4 text-primary" />
                                        Dates de location
                                    </h4>
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-3">
                                            <Label class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                                Date de collecte *
                                                <HelpTooltip content="Sélectionnez la date de début de votre voyage. Une réservation précoce garantit une meilleure disponibilité." />
                                            </Label>
                                            <Input
                                                v-model="form.start_date"
                                                type="date"
                                                :min="$page.props.minDate"
                                                :max="$page.props.maxDate"
                                                class="h-14 rounded-2xl border-none bg-slate-50 px-6 font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all shadow-inner"
                                                :class="{ 'ring-rose-500 bg-rose-50': form.errors.start_date }"
                                            />
                                            <span v-if="form.errors.start_date" class="text-[10px] font-black uppercase tracking-widest text-rose-500">{{ form.errors.start_date }}</span>
                                        </div>

                                        <div class="space-y-3">
                                            <Label class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                                Date de retour *
                                                <HelpTooltip content="Sélectionnez votre date de retour. Les tarifs journaliers standard s'appliquent par période de 24 heures." />
                                            </Label>
                                            <Input
                                                v-model="form.end_date"
                                                type="date"
                                                :min="form.start_date || $page.props.minDate"
                                                :max="$page.props.maxDate"
                                                class="h-14 rounded-2xl border-none bg-slate-50 px-6 font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all shadow-inner"
                                                :class="{ 'ring-rose-500 bg-rose-50': form.errors.end_date }"
                                            />
                                            <span v-if="form.errors.end_date" class="text-[10px] font-black uppercase tracking-widest text-rose-500">{{ form.errors.end_date }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!--  Locations -->
                                <div class="space-y-4">
                                    <h4
                                        class="flex items-center text-sm font-black uppercase tracking-widest text-slate-900 mt-12 mb-6"
                                    >
                                        <MapPin class="mr-3 h-4 w-4 text-primary" />
                                        Collecte & Retour
                                    </h4>
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-3">
                                            <Label class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                                Lieu de collecte *
                                                <HelpTooltip content="Choisissez votre point de collecte. Notre service VIP est disponible dans tous les aéroports." />
                                            </Label>
                                            <Select v-model="form.pickup_location">
                                                <SelectTrigger class="h-14 rounded-2xl border-none bg-slate-50 px-6 font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all" :class="{ 'ring-rose-500': form.errors.pickup_location }">
                                                    <SelectValue placeholder="Sélectionner le lieu" />
                                                </SelectTrigger>
                                                <SelectContent class="rounded-2xl border-none shadow-2xl ring-1 ring-slate-100 font-bold">
                                                    <SelectItem v-for="location in locations" :key="location.id" :value="location.name" class="py-3 px-4 rounded-xl cursor-pointer">
                                                        {{ location.name }} ({{ location.city }})
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <span v-if="form.errors.pickup_location" class="text-[10px] font-black uppercase tracking-widest text-rose-500">{{ form.errors.pickup_location }}</span>
                                        </div>

                                        <div class="space-y-3">
                                            <Label class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                                Lieu de retour *
                                                <HelpTooltip content="Précisez où vous rendrez le véhicule. Des locations en aller simple peuvent être disponibles." />
                                            </Label>
                                            <Select v-model="form.return_location">
                                                <SelectTrigger class="h-14 rounded-2xl border-none bg-slate-50 px-6 font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all" :class="{ 'ring-rose-500': form.errors.return_location }">
                                                    <SelectValue placeholder="Sélectionner le lieu" />
                                                </SelectTrigger>
                                                <SelectContent class="rounded-2xl border-none shadow-2xl ring-1 ring-slate-100 font-bold">
                                                    <SelectItem v-for="location in locations" :key="location.id" :value="location.name" class="py-3 px-4 rounded-xl cursor-pointer">
                                                        {{ location.name }} ({{ location.city }})
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <span v-if="form.errors.return_location" class="text-[10px] font-black uppercase tracking-widest text-rose-500">{{ form.errors.return_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!--  Price Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <div
                            class="sticky top-4 rounded-[2.5rem] border-none bg-white p-10 shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100"
                        >
                            <div class="mb-10 flex items-center space-x-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-xl shadow-slate-200"
                                >
                                    <ReceiptText class="h-6 w-6" />
                                </div>
                                <h3 class="text-2xl font-black tracking-tight text-slate-900">
                                    Résumé
                                </h3>
                            </div>

                            <div class="mb-8 space-y-6">
                                <div
                                    class="space-y-4 rounded-[1.5rem] bg-slate-50 p-6 ring-1 ring-slate-100"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-xs font-black uppercase tracking-widest text-slate-400"
                                            >Période</span
                                        >
                                        <span class="text-sm font-black text-slate-900">
                                            {{
                                                rentalDays > 0
                                                    ? `${rentalDays} jour${rentalDays > 1 ? 's' : ''}`
                                                    : '—'
                                            }}
                                        </span>
                                    </div>
 
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-xs font-black uppercase tracking-widest text-slate-400"
                                            >Journalier</span
                                        >
                                        <span class="text-sm font-black text-slate-900"
                                            >{{ $page.props.currency.symbol }}{{ car.price_per_day }}</span
                                        >
                                    </div>
                                </div>
 
                                <!-- Detailed Price Breakdown -->
                                <div class="space-y-4 px-2">
                                    <div class="flex items-center justify-between py-1">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Sélection Totale</span>
                                        <span class="text-sm font-black text-slate-900">{{ $page.props.currency.symbol }}{{ rentalDays > 0 ? total.toFixed(2) : '0.00' }}</span>
                                    </div>
                                    
                                    <!-- Hero Payment Box -->
                                    <div class="bg-primary/5 p-6 rounded-[2rem] border border-primary/20 space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="space-y-1">
                                                <span class="text-[10px] font-black uppercase tracking-widest text-primary">Acompte à payer maintenant</span>
                                                <p class="text-4xl font-black text-primary tracking-tighter">
                                                    {{ $page.props.currency.symbol }}{{ rentalDays > 0 ? depositAmount.toFixed(2) : '0.00' }}
                                                </p>
                                            </div>
                                            <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center">
                                                <CreditCard class="size-5 text-primary" />
                                            </div>
                                        </div>
                                        
                                        <div class="pt-4 border-t border-primary/10 flex justify-between items-center">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-primary/60">Payer en agence à la collecte</span>
                                            <span class="font-black text-primary">{{ $page.props.currency.symbol }}{{ rentalDays > 0 ? remainingAmount.toFixed(2) : '0.00' }}</span>
                                        </div>
                                    </div>

                                    <!-- Security Deposit (Informational) -->
                                    <div v-if="securityDeposit > 0" class="bg-amber-50 p-4 rounded-2xl border border-amber-200">
                                        <div class="flex items-start gap-3">
                                            <ShieldCheck class="size-4 text-amber-600 mt-0.5" />
                                            <p class="text-[9px] font-bold text-amber-800 leading-relaxed uppercase tracking-wider">
                                                Un dépôt de garantie remboursable de {{ $page.props.currency.symbol }}{{ securityDeposit.toFixed(2) }} est requis lors de la collecte.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <!--  Book Now Button -->
                            <Button
                                @click="submitBooking"
                                :disabled="!canSubmit || form.processing"
                                class="h-16 w-full rounded-[1.25rem] text-sm font-black uppercase tracking-[0.2em] transition-all duration-500 shadow-2xl"
                                :class="canSubmit ? 'bg-slate-900 text-white hover:bg-primary shadow-slate-200 hover:-translate-y-1' : 'bg-slate-100 text-slate-400 border-none pointer-events-none'"
                            >
                                <span
                                    v-if="form.processing"
                                    class="flex items-center justify-center gap-3"
                                >
                                    <LoaderCircle class="size-4 animate-spin" />
                                    Confirmation...
                                </span>
                                <span
                                    v-else-if="!$page.props.auth.user"
                                    class="flex items-center justify-center gap-3"
                                >
                                    <LogIn class="size-4" />
                                    Se connecter pour réserver
                                </span>
                                <span
                                    v-else
                                    class="flex items-center justify-center gap-3"
                                >
                                    <CheckCircle class="size-4" />
                                    Réserver ce véhicule
                                </span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rental Policy Acceptance Modal -->
        <Dialog :open="showPolicyModal" @update:open="showPolicyModal = $event">
            <DialogContent class="sm:max-w-xl max-h-[90vh] md:max-h-[85vh] rounded-[2rem] border-none shadow-2xl p-0 overflow-hidden flex flex-col bg-slate-50">
                <!-- Header (Sticky) -->
                <div class="bg-slate-900 px-6 py-8 text-white relative flex-shrink-0">
                    <div class="absolute -right-6 -bottom-6 opacity-5 pointer-events-none">
                        <ShieldCheck class="size-48" />
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md">
                                <ShieldCheck class="size-6 text-white" />
                            </div>
                            <div>
                                <DialogTitle class="text-xl font-black tracking-tight text-white uppercase">Conditions de location critiques</DialogTitle>
                                <DialogDescription class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Reconnaissance obligatoire</DialogDescription>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-slate-300 leading-relaxed max-w-md">
                            Pour garantir un processus de collecte fluide, veuillez revoir et confirmer chacune des exigences opérationnelles suivantes.
                        </p>
                    </div>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
                    
                    <!-- Requirement 1: Age/Experience -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl transition-all cursor-pointer border-2" 
                           :class="acceptExperience ? 'bg-white border-primary/40 shadow-sm' : 'bg-white border-slate-100 hover:border-slate-200'">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors"
                             :class="acceptExperience ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-slate-400'">
                            <Clock class="size-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-black text-slate-900">Expérience minimale vérifiée</h4>
                            <p class="text-xs font-bold text-slate-500 mt-1.5 leading-relaxed">Je confirme posséder un permis de conduire valide avec au moins <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-md font-black">{{ $page.props.settings.min_driving_experience }} ans</span> d'expérience de conduite.</p>
                        </div>
                        <div class="pt-1">
                            <input type="checkbox" v-model="acceptExperience" class="h-6 w-6 rounded-lg border-2 border-slate-300 text-primary focus:ring-primary focus:ring-offset-0 transition-colors" />
                        </div>
                    </label>

                    <!-- Requirement 2: Documents -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl transition-all cursor-pointer border-2" 
                           :class="acceptDocuments ? 'bg-white border-violet-400 shadow-sm' : 'bg-white border-slate-100 hover:border-slate-200'">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors"
                             :class="acceptDocuments ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'bg-slate-100 text-slate-400'">
                            <IdCard class="size-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-black text-slate-900">Préparation des documents requis</h4>
                            <p class="text-xs font-bold text-slate-500 mt-1.5 leading-relaxed">Je comprends que je dois présenter physiquement les documents suivants à mon arrivée, faute de quoi ma réservation sera refusée sans remboursement :</p>
                            <ul class="mt-3 space-y-2">
                                <li class="text-[11px] font-black text-slate-600 uppercase tracking-wider flex items-center gap-2">
                                    <div class="size-1.5 rounded-full bg-violet-400"></div> Pièce d'identité nationale ou Passeport
                                </li>
                                <li class="text-[11px] font-black text-slate-600 uppercase tracking-wider flex items-center gap-2">
                                    <div class="size-1.5 rounded-full bg-violet-400"></div> Permis de conduire original
                                </li>
                                <li class="text-[11px] font-black text-slate-600 uppercase tracking-wider flex items-center gap-2">
                                    <div class="size-1.5 rounded-full bg-violet-400"></div> Carte de crédit pour le dépôt de garantie
                                </li>
                            </ul>
                        </div>
                        <div class="pt-1">
                            <input type="checkbox" v-model="acceptDocuments" class="h-6 w-6 rounded-lg border-2 border-slate-300 text-violet-600 focus:ring-violet-600 focus:ring-offset-0 transition-colors" />
                        </div>
                    </label>

                    <!-- Requirement 3: Pickup Location -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl transition-all cursor-pointer border-2" 
                           :class="acceptLocation ? 'bg-white border-sky-400 shadow-sm' : 'bg-white border-slate-100 hover:border-slate-200'">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors"
                             :class="acceptLocation ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/20' : 'bg-slate-100 text-slate-400'">
                            <MapPin class="size-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-black text-slate-900">Connaissance du lieu de collecte</h4>
                            <p class="text-xs font-bold text-slate-500 mt-1.5 leading-relaxed">Je confirme être responsable de mon arrivée au lieu de collecte sélectionné : <span class="bg-sky-50 text-sky-600 px-1.5 py-0.5 rounded-md font-black">{{ form.pickup_location || 'l\'agence' }}</span> à l'heure prévue.</p>
                        </div>
                        <div class="pt-1">
                            <input type="checkbox" v-model="acceptLocation" class="h-6 w-6 rounded-lg border-2 border-slate-300 text-sky-500 focus:ring-sky-500 focus:ring-offset-0 transition-colors" />
                        </div>
                    </label>

                    <!-- Requirement 4: Rental Terms -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl transition-all cursor-pointer border-2" 
                           :class="acceptTerms ? 'bg-white border-emerald-400 shadow-sm' : 'bg-white border-slate-100 hover:border-slate-200'">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors shrink-0"
                             :class="acceptTerms ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-100 text-slate-400'">
                            <FileCheck class="size-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-black text-slate-900">Conditions générales et responsabilité</h4>
                            <div class="mt-2 text-xs font-bold text-slate-500 leading-relaxed whitespace-pre-wrap bg-slate-50 p-3 rounded-xl border border-slate-100 italic">"{{ $page.props.settings.rental_terms }}"</div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-3">J'accepte ces conditions</p>
                        </div>
                        <div class="pt-1 shrink-0">
                            <input type="checkbox" v-model="acceptTerms" class="h-6 w-6 rounded-lg border-2 border-slate-300 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 transition-colors" />
                        </div>
                    </label>

                </div>

                <!-- Footer (Sticky) -->
                <div class="px-6 py-5 bg-white border-t border-slate-100 flex-shrink-0 flex gap-4">
                    <Button variant="ghost" @click="showPolicyModal = false" class="flex-1 h-14 rounded-xl font-black uppercase tracking-widest text-xs text-slate-400 hover:bg-slate-50 hover:text-slate-600 shrink-0">
                        Annuler
                    </Button>
                    <Button 
                        @click="confirmBookingAfterPolicy" 
                        :disabled="!policyAccepted || form.processing"
                        class="flex-[2] h-14 rounded-xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-slate-900/10 hover:bg-black transition-all border-none disabled:opacity-30 disabled:cursor-not-allowed group relative overflow-hidden"
                    >
                        <!-- Animated background for disabled state -->
                        <div v-if="!policyAccepted" class="absolute inset-0 bg-slate-800 flex items-center justify-center">
                            <span class="text-[10px]">Accepter toutes les conditions pour continuer</span>
                        </div>
                        
                        <div class="flex items-center justify-center" :class="{'opacity-0': !policyAccepted}">
                            <LoaderCircle v-if="form.processing" class="size-4 animate-spin mr-2" />
                            <ShieldCheck v-else class="size-4 mr-2 group-hover:scale-110 transition-transform" />
                            {{ form.processing ? 'Réservation...' : 'Confirmer & Demander' }}
                        </div>
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Role Error Dialog -->
        <Dialog :open="showRoleError" @update:open="showRoleError = $event">
            <DialogContent class="sm:max-w-md rounded-[2rem] border-none shadow-2xl p-8">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-3 text-xl font-black text-rose-600">
                        <div class="p-2 rounded-xl bg-rose-50"><TriangleAlert class="size-5" /></div>
                        Réservation restreinte
                    </DialogTitle>
                    <DialogDescription class="text-sm font-bold text-slate-500 pt-4 leading-relaxed">
                        {{ roleErrorMessage }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="sm:justify-start pt-6">
                    <Button type="button" variant="secondary" @click="showRoleError = false" class="h-12 rounded-2xl font-black uppercase tracking-widest text-xs bg-slate-100 hover:bg-slate-200 px-8">
                        Je comprends
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </HomeLayout>
</template>
