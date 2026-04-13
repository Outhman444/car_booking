<script setup lang="ts">
import CarCard from '@/components/CarCard.vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    fleet, 
    about 
} from '@/routes';
import { 
    Sparkles, 
    Zap, 
    Shield, 
    Clock, 
    DollarSign, 
    ArrowRight,
    Car as CarIcon,
    CheckCircle, 
    XCircle, 
    Info, 
    X
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';

interface Car {
    id: number;
    make: string;
    model: string;
    year: number;
    price_per_day: string;
    description: string;
    fuel_type: string;
    transmission: string;
    seats: number;
    color: string;
    mileage: number;
    image_url: string;
}

const props = defineProps<{
    homeCars: Car[];
    makes: string[];
    locations: string[];
}>();

const $page = usePage();
const homeCars = computed(() => props.homeCars);

const successMessage = ref($page.props.flash?.success || null);
const errorMessage = ref($page.props.flash?.error || null);
const infoMessage = ref($page.props.flash?.info || null);
</script>

<template>
    <Head>
        <title>Real Rent Car - Premium Car Rental Service</title>
        <meta
            name="description"
            content="Real Rent Car is a premium car rental platform providing reliable transportation solutions. We offer a wide range of cars for rent, from economy to luxury, for short and long term rentals."
        />
    </Head>

    <HomeLayout>
        <main>
            <!-- Animated Notifications -->
            <div class="fixed top-24 right-8 z-[60] flex flex-col gap-4 pointer-events-none">
                <!-- Success -->
                <Transition
                    enter-active-class="transform transition ease-out duration-500"
                    enter-from-class="translate-x-[150%] opacity-0"
                    enter-to-class="translate-x-0 opacity-100"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="successMessage"
                        class="pointer-events-auto w-96 rounded-[2rem] border-none bg-slate-900 p-6 shadow-2xl backdrop-blur-xl ring-1 ring-white/10 relative overflow-hidden group"
                    >
                         <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                            <CheckCircle class="size-32 text-white" />
                        </div>
                        <div class="flex items-start gap-5 relative z-10">
                            <div class="bg-primary text-white shadow-lg shadow-primary/20 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                                <CheckCircle class="h-6 w-6" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-black tracking-tight text-white uppercase italic">System Success</h3>
                                    <button @click="successMessage = null" class="text-white/40 hover:text-white transition-colors">
                                        <X class="size-4" />
                                    </button>
                                </div>
                                <p class="text-xs font-bold leading-relaxed text-slate-400">
                                    {{ successMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Error -->
                <Transition
                    enter-active-class="transform transition ease-out duration-500"
                    enter-from-class="translate-x-[150%] opacity-0"
                    enter-to-class="translate-x-0 opacity-100"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="errorMessage"
                        class="pointer-events-auto w-96 rounded-[2rem] border-none bg-slate-900 p-6 shadow-2xl backdrop-blur-xl ring-1 ring-white/10 relative overflow-hidden group"
                    >
                        <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                            <XCircle class="size-32 text-white" />
                        </div>
                        <div class="flex items-start gap-5 relative z-10">
                            <div class="bg-rose-500 text-white shadow-lg shadow-rose-500/20 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                                <XCircle class="h-6 w-6" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-black tracking-tight text-white uppercase italic">Critical Failure</h3>
                                    <button @click="errorMessage = null" class="text-white/40 hover:text-white transition-colors">
                                        <X class="size-4" />
                                    </button>
                                </div>
                                <p class="text-xs font-bold leading-relaxed text-slate-400">
                                    {{ errorMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Info -->
                <Transition
                    enter-active-class="transform transition ease-out duration-500"
                    enter-from-class="translate-x-[150%] opacity-0"
                    enter-to-class="translate-x-0 opacity-100"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="infoMessage"
                        class="pointer-events-auto w-96 rounded-[2rem] border-none bg-slate-900 p-6 shadow-2xl backdrop-blur-xl ring-1 ring-white/10 relative overflow-hidden group"
                    >
                         <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                            <Info class="size-32 text-white" />
                        </div>
                        <div class="flex items-start gap-5 relative z-10">
                            <div class="bg-blue-500 text-white shadow-lg shadow-blue-500/20 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                                <Info class="h-6 w-6" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-black tracking-tight text-white uppercase italic">System Broadcast</h3>
                                    <button @click="infoMessage = null" class="text-white/40 hover:text-white transition-colors">
                                        <X class="size-4" />
                                    </button>
                                </div>
                                <p class="text-xs font-bold leading-relaxed text-slate-400">
                                    {{ infoMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Hero Section -->
            <section class="relative overflow-hidden bg-background py-20 lg:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="grid items-center gap-12 lg:grid-cols-2">
                        <!-- Left Content -->
                        <div class="space-y-8">
                            <div class="space-y-6">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <Badge v-if="$page.props.settings.special_offer_text" variant="destructive" class="px-3 py-1 font-medium gap-1 animate-pulse">
                                        <Sparkles class="size-3.5" />
                                        {{ $page.props.settings.special_offer_text }}
                                    </Badge>
                                    <Badge variant="outline" class="px-3 py-1 font-medium gap-1 text-primary bg-primary/5">
                                        <Zap class="size-3.5" />
                                        {{ $page.props.settings.hero_badge || 'Premium Service' }}
                                    </Badge>
                                </div>

                                <h1 class="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-7xl">
                                    {{ $page.props.settings.hero_title || 'Experience Premium Mobility' }}
                                </h1>

                                <p class="max-w-xl text-lg text-muted-foreground leading-relaxed">
                                    {{ $page.props.settings.hero_subtitle || 'Elevate your journey with our exclusive collection of luxury and performance vehicles. Exceptional service, unconditionally guaranteed.' }}
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                <Button as-child size="lg" class="h-14 px-8 text-base font-semibold group w-full sm:w-auto">
                                    <a :href="fleet.url()">
                                        Browse Fleet
                                        <ArrowRight class="ml-2 size-5 transition-transform group-hover:translate-x-1" />
                                    </a>
                                </Button>
                                <Button as-child variant="outline" size="lg" class="h-14 px-8 text-base font-semibold w-full sm:w-auto hover:bg-muted">
                                    <a :href="about.url()">Our Story</a>
                                </Button>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-8 pt-8 mt-8 border-t border-border/60">
                                <div>
                                    <div class="text-3xl font-bold tracking-tight text-primary">1000+</div>
                                    <div class="mt-1 text-sm font-medium text-muted-foreground">Happy Clients</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold tracking-tight text-primary">150+</div>
                                    <div class="mt-1 text-sm font-medium text-muted-foreground">Premium Cars</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold tracking-tight text-primary">24/7</div>
                                    <div class="mt-1 text-sm font-medium text-muted-foreground">Support</div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Image -->
                        <div class="relative flex justify-center lg:justify-end">
                            <div class="absolute inset-0 bg-primary/5 rounded-full blur-3xl w-[120%] h-[120%] -ml-[10%] -mt-[10%] -z-10"></div>
                            <img
                                src="/images/hero_image.png"
                                alt="Premium Car Garage"
                                class="w-full max-w-xl drop-shadow-2xl hover:-translate-y-2 transition-transform duration-700 ease-out"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Cars Section -->
            <section id="fleet" class="bg-muted/30 py-24 border-y border-border/40">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-16 text-center space-y-4">
                        <Badge variant="outline" class="bg-background text-primary font-medium tracking-wide">
                            Our Collection
                        </Badge>
                        <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Discover Our Elite Fleet
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                            Each vehicle in our collection is meticulously maintained and equipped with premium features to ensure your journey is exceptional.
                        </p>
                    </div>

                    <div class="grid gap-6 sm:gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                        <CarCard
                            v-for="car in homeCars"
                            :key="car.id"
                            :car="car"
                        />
                    </div>

                    <div class="mt-16 text-center">
                        <Button as-child size="lg" class="h-12 px-8 font-semibold">
                            <a :href="fleet.url()">
                                <CarIcon class="mr-2 size-5" />
                                View Complete Fleet
                            </a>
                        </Button>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="services" class="bg-background py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-16 text-center space-y-4">
                        <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Why Choose RealRent?
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                            We're committed to providing an unparalleled car rental experience with premium service at every touchpoint.
                        </p>
                    </div>

                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <Card class="bg-muted/20 border-none shadow-none hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Shield class="size-6" />
                                </div>
                                <CardTitle>Premium Quality</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-muted-foreground leading-relaxed">
                                    Every vehicle undergoes comprehensive inspection and maintenance to guarantee your safety, comfort, and peace of mind.
                                </p>
                            </CardContent>
                        </Card>

                        <Card class="bg-muted/20 border-none shadow-none hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Clock class="size-6" />
                                </div>
                                <CardTitle>24/7 Support</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-muted-foreground leading-relaxed">
                                    Our dedicated support team is available around the clock to assist you with any questions or concerns during your rental.
                                </p>
                            </CardContent>
                        </Card>

                        <Card class="bg-muted/20 border-none shadow-none hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <DollarSign class="size-6" />
                                </div>
                                <CardTitle>Best Value</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-muted-foreground leading-relaxed">
                                    Transparent pricing with no hidden fees. Get premium car rental services at competitive rates with exceptional value.
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>
        </main>
    </HomeLayout>
</template>
