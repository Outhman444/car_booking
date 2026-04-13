<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { show } from '@/routes/fleet';
import { Fuel, Calendar, Users, Cog, Gauge, ImageOff, ArrowRight } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { getCarColorHex } from '@/lib/colors';

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

interface Props {
    car: Car;
}

const props = defineProps<Props>();
const $page = usePage();

const imageError = ref(false);
const handleImageError = () => {
    imageError.value = true;
};

const currencySymbol = computed(() => ($page.props.currency as any)?.symbol || '$');

const bookCar = (carId: number) => {
    router.get(show(carId).url);
};
</script>

<template>
    <Card
        class="group relative flex flex-col p-0 gap-0 overflow-hidden border border-border/50 bg-card shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1 cursor-pointer rounded-2xl"
        @click="bookCar(car.id)"
    >
        <!-- Image -->
        <div class="relative aspect-[16/10] w-full overflow-hidden bg-muted">
            <template v-if="!imageError && car.image_url">
                <img
                    :src="car.image_url"
                    :alt="`${car.make} ${car.model}`"
                    @error="handleImageError"
                    class="h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-110"
                />
            </template>
            <div v-else class="flex h-full w-full flex-col items-center justify-center text-muted-foreground">
                <ImageOff class="size-10 opacity-30" />
            </div>

            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

            <!-- Year badge -->
            <Badge variant="secondary" class="absolute top-3 left-3 bg-background/90 backdrop-blur-md border-none text-[11px] font-bold shadow-sm">
                {{ car.year }}
            </Badge>

            <!-- Price badge -->
            <div class="absolute bottom-3 right-3">
                <div class="bg-primary text-primary-foreground px-3 py-1.5 rounded-lg text-sm font-black shadow-lg shadow-primary/20 backdrop-blur-sm">
                    {{ currencySymbol }}{{ Number(car.price_per_day).toLocaleString() }}<span class="text-[10px] font-medium opacity-70 ml-0.5">/day</span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <CardContent class="flex flex-col flex-1 p-4 pb-4">
            <!-- Title row -->
            <div class="mb-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary">{{ car.make }}</p>
                <h3 class="text-lg font-extrabold tracking-tight text-foreground line-clamp-1 group-hover:text-primary transition-colors">
                    {{ car.model }}
                </h3>
            </div>

            <!-- Specs pills -->
            <div class="flex flex-wrap gap-1.5 mb-4">
                <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-1 text-[11px] font-semibold text-muted-foreground">
                    <Fuel class="size-3 text-primary/60" />
                    {{ car.fuel_type || 'N/A' }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-1 text-[11px] font-semibold text-muted-foreground">
                    <Cog class="size-3 text-primary/60" />
                    {{ car.transmission || 'Auto' }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-1 text-[11px] font-semibold text-muted-foreground">
                    <Users class="size-3 text-primary/60" />
                    {{ car.seats || 2 }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-1 text-[11px] font-semibold text-muted-foreground">
                    <Gauge class="size-3 text-primary/60" />
                    {{ (car.mileage || 0).toLocaleString() }} km
                </span>
                <span v-if="car.color" class="inline-flex items-center gap-1.5 rounded-md bg-muted px-2 py-1 text-[11px] font-semibold text-muted-foreground">
                    <span class="size-2.5 rounded-full border border-border/50 shadow-sm" :style="{ backgroundColor: getCarColorHex(car.color) }"></span>
                    <span class="capitalize">{{ car.color }}</span>
                </span>
            </div>

            <!-- CTA -->
            <div class="mt-auto pt-3 border-t border-border/50">
                <Button
                    variant="ghost"
                    class="w-full h-9 justify-between px-3 text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-primary/5 group/btn"
                    @click.stop="bookCar(car.id)"
                >
                    View Details
                    <ArrowRight class="size-3.5 transition-transform group-hover/btn:translate-x-1" />
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
