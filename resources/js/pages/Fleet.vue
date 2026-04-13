<script setup lang="ts">
import CarCard from '@/components/CarCard.vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { 
    Search, 
    Filter, 
    Loader2, 
    Settings2,
    AlertCircle
} from 'lucide-vue-next';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

import { ScrollArea } from '@/components/ui/scroll-area';
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

const $page = usePage<any>();
const cars = computed(() => $page.props.cars);
const filters = computed(() => $page.props.filters);
const makes = computed(() => $page.props.makes);
const transmissions = computed(() => $page.props.transmissions);
const seats = computed(() => $page.props.seats);

// Filter state
const searchQuery = ref(filters.value.search || '');
const selectedMake = ref(filters.value.make || '');
const selectedFuelType = ref(filters.value.fuel_type || '');
const minPrice = ref(filters.value.min_price || '');
const maxPrice = ref(filters.value.max_price || '');
const selectedYear = ref(filters.value.year || '');
const startDate = ref(filters.value.start_date || '');
const endDate = ref(filters.value.end_date || '');
const pickupLocation = ref(filters.value.pickup_location || '');
const selectedTransmission = ref(filters.value.transmission || '');
const selectedSeats = ref(filters.value.seats || '');
const selectedColor = ref(filters.value.color || '');
const maxMileage = ref(filters.value.max_mileage || '');
const sortBy = ref(filters.value.sort || 'make_asc');

// Show/hide filters on mobile
const isLoading = ref(false);

const applyFilters = () => {
    isLoading.value = true;
    const params: Record<string, any> = {};

    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
    if (selectedMake.value && selectedMake.value !== ' ') params.make = selectedMake.value;
    if (selectedFuelType.value) params.fuel_type = selectedFuelType.value;
    if (minPrice.value) params.min_price = minPrice.value;
    if (maxPrice.value) params.max_price = maxPrice.value;
    if (selectedYear.value) params.year = selectedYear.value;
    if (startDate.value) params.start_date = startDate.value;
    if (endDate.value) params.end_date = endDate.value;
    if (pickupLocation.value) params.pickup_location = pickupLocation.value;
    if (selectedTransmission.value && selectedTransmission.value !== ' ') params.transmission = selectedTransmission.value;
    if (selectedSeats.value && selectedSeats.value !== ' ') params.seats = selectedSeats.value;
    if (selectedColor.value && selectedColor.value !== ' ') params.color = selectedColor.value;
    if (maxMileage.value) params.max_mileage = maxMileage.value;
    if (sortBy.value && sortBy.value !== 'make_asc') params.sort = sortBy.value;

    router.get('/fleet', params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedMake.value = ' ';
    selectedFuelType.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    selectedYear.value = '';
    startDate.value = '';
    endDate.value = '';
    pickupLocation.value = '';
    selectedTransmission.value = ' ';
    selectedSeats.value = ' ';
    selectedColor.value = ' ';
    maxMileage.value = '';
    sortBy.value = 'make_asc';

    isLoading.value = true;
    router.get(
        '/fleet',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const handleSearch = (event?: Event) => {
    if (event) {
        event.preventDefault();
    }
    applyFilters();
};

// Watch only for sort changes (immediate feedback)
watch(sortBy, () => {
    applyFilters();
});

const goToPage = (url: string) => {
    if (url) {
        isLoading.value = true;
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        });
    }
};

const hasActiveFilters = computed(() => {
    return (
        searchQuery.value.trim() !== '' ||
        (selectedMake.value && selectedMake.value !== ' ') ||
        selectedFuelType.value !== '' ||
        minPrice.value !== '' ||
        maxPrice.value !== '' ||
        selectedYear.value !== '' ||
        startDate.value !== '' ||
        endDate.value !== '' ||
        pickupLocation.value !== '' ||
        (selectedTransmission.value && selectedTransmission.value !== ' ') ||
        (selectedSeats.value && selectedSeats.value !== ' ') ||
        (selectedColor.value && selectedColor.value !== ' ') ||
        maxMileage.value !== '' ||
        (sortBy.value && sortBy.value !== 'make_asc')
    );
});
</script>

<template>
    <HomeLayout>
        <div class="min-h-screen bg-background">
            <!-- Loading Overlay -->
            <div
                v-if="isLoading"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/50 backdrop-blur-sm"
            >
                <div
                    class="flex items-center space-x-4 rounded-xl border bg-card text-card-foreground p-6 shadow-xl"
                >
                    <Loader2 class="h-6 w-6 animate-spin text-primary" />
                    <span class="text-sm font-medium">Refreshing fleet...</span>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                
                <div class="mb-10 flex flex-col md:flex-row items-center justify-between gap-6 border-b pb-8">
                    <div class="w-full md:w-auto space-y-2">
                        <h1 class="text-3xl font-extrabold tracking-tight lg:text-4xl">
                            Elite <span class="text-primary">Fleet</span>
                        </h1>
                        <p class="text-sm text-muted-foreground">Discover your perfect journey from our high-performance collection.</p>
                    </div>

                    <!-- Mobile Filter Trigger and Search -->
                    <div class="flex w-full md:w-1/2 lg:w-1/3 items-center gap-2">
                        <div class="lg:hidden">
                            <Sheet>
                                <SheetTrigger as-child>
                                    <Button variant="outline" size="icon" class="h-10 w-10 relative">
                                        <Filter class="size-4" />
                                        <span v-if="hasActiveFilters" class="absolute -top-1 -right-1 flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                                        </span>
                                    </Button>
                                </SheetTrigger>
                                <SheetContent side="left" class="w-[340px] sm:w-[400px] border-r">
                                    <div class="flex h-full flex-col">
                                        <SheetHeader class="text-left pb-4 border-b">
                                            <SheetTitle class="font-bold flex items-center gap-2">
                                                <Settings2 class="size-5" /> Filters
                                            </SheetTitle>
                                            <SheetDescription>Refine your results</SheetDescription>
                                        </SheetHeader>
                                        <ScrollArea class="flex-1 -mx-4 px-4 py-6">
                                            <!-- Filter Content (Mobile) -->
                                            <!-- (Omitted repetitive filter form logic for brevity, matches desktop below) -->
                                             <div class="space-y-6">
                                                <div class="space-y-3">
                                                    <Label>Search</Label>
                                                    <div class="relative">
                                                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                                        <Input v-model="searchQuery" placeholder="Search..." class="pl-9" />
                                                    </div>
                                                </div>
                                                <div class="space-y-3">
                                                    <Label>Make</Label>
                                                    <Select v-model="selectedMake">
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="All" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value=" ">All</SelectItem>
                                                            <SelectItem v-for="make in makes" :key="make" :value="make">{{ make }}</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                <div class="space-y-3">
                                                    <Label>Transmission</Label>
                                                    <Select v-model="selectedTransmission">
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="All" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value=" ">All</SelectItem>
                                                            <SelectItem v-for="t in transmissions" :key="t" :value="t">{{ t }}</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                <div class="space-y-3">
                                                    <Label>Min. Seats</Label>
                                                    <Select v-model="selectedSeats">
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Any" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value=" ">Any</SelectItem>
                                                            <SelectItem v-for="s in seats" :key="s" :value="s.toString()">{{ s }}+ Seats</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                
                                                <Button size="lg" class="w-full mt-4" @click="applyFilters">Apply</Button>
                                                <Button variant="ghost" class="w-full" @click="clearFilters">Reset</Button>
                                             </div>
                                        </ScrollArea>
                                    </div>
                                </SheetContent>
                            </Sheet>
                        </div>
                        
                        <form @submit.prevent="handleSearch" class="flex-1 relative flex items-center">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground size-4" />
                            <Input 
                                v-model="searchQuery" 
                                placeholder="Search vehicles..." 
                                class="pl-9 pr-4 bg-muted/50 border-none transition-all group-focus-within:bg-background h-10 w-full rounded-md shadow-none focus-visible:ring-1 focus-visible:ring-ring"
                            />
                        </form>
                    </div>
                </div>

                <div class="flex flex-col gap-8 lg:flex-row items-start">
                    <!-- Filters Sidebar (Desktop) -->
                    <div class="hidden lg:block lg:w-64 shrink-0">
                        <div class="sticky top-24 rounded-xl border bg-card text-card-foreground shadow-sm">
                            <div class="p-6 space-y-6">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold tracking-tight">Filters</h3>
                                    <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="clearFilters" class="h-8 px-2 text-xs">
                                        Reset
                                    </Button>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <Label class="text-xs text-muted-foreground uppercase tracking-wider">Make</Label>
                                        <Select v-model="selectedMake">
                                            <SelectTrigger>
                                                <SelectValue placeholder="All" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value=" ">All</SelectItem>
                                                <SelectItem v-for="make in makes" :key="make" :value="make">{{ make }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <Label class="text-xs text-muted-foreground uppercase tracking-wider">Transmission</Label>
                                        <Select v-model="selectedTransmission">
                                            <SelectTrigger>
                                                <SelectValue placeholder="All" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value=" ">All</SelectItem>
                                                <SelectItem v-for="t in transmissions" :key="t" :value="t">{{ t }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <Label class="text-xs text-muted-foreground uppercase tracking-wider">Seats</Label>
                                        <Select v-model="selectedSeats">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Any" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value=" ">Any</SelectItem>
                                                <SelectItem v-for="s in seats" :key="s" :value="s.toString()">{{ s }}+ Seats</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <Label class="text-xs text-muted-foreground uppercase tracking-wider">Price Range</Label>
                                        <div class="flex items-center gap-2">
                                            <Input v-model="minPrice" placeholder="Min" type="number" class="h-9 px-2 text-sm" />
                                            <span class="text-muted-foreground">-</span>
                                            <Input v-model="maxPrice" placeholder="Max" type="number" class="h-9 px-2 text-sm" />
                                        </div>
                                    </div>
                                </div>

                                <Button class="w-full" @click="applyFilters">Apply Filters</Button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content area (Listings + Controls) -->
                    <div class="flex-1 w-full space-y-6">
                        <!-- Top Bar for List -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <span class="font-semibold text-foreground">{{ cars.total }}</span> vehicles found
                            </div>
                            <div class="flex items-center gap-2 ml-auto w-full sm:w-auto">
                                <Label class="text-sm shrink-0">Sort by:</Label>
                                <Select v-model="sortBy">
                                    <SelectTrigger class="w-full sm:w-[180px] h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="make_asc">Make (A-Z)</SelectItem>
                                        <SelectItem value="price_asc">Price (Low to High)</SelectItem>
                                        <SelectItem value="price_desc">Price (High to Low)</SelectItem>
                                        <SelectItem value="year_desc">Newest First</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Cars Grid -->
                        <div v-if="cars.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3">
                            <CarCard
                                v-for="car in cars.data"
                                :key="car.id"
                                :car="car"
                            />
                        </div>

                        <!-- No Results State -->
                        <div v-else class="flex flex-col items-center justify-center p-12 text-center rounded-xl border border-dashed text-muted-foreground bg-muted/20">
                            <AlertCircle class="h-10 w-10 mb-4 opacity-50" />
                            <h3 class="text-lg font-semibold text-foreground mb-1">No vehicles found</h3>
                            <p class="text-sm max-w-sm mb-6">We couldn't find any vehicles matching your criteria. Try adjusting your filters.</p>
                            <Button variant="outline" @click="clearFilters">Clear All Filters</Button>
                        </div>

                        <!-- Pagination -->
                        <div v-if="cars.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 mt-6 border-t">
                            <span class="text-sm text-muted-foreground whitespace-nowrap">
                                Page {{ cars.current_page }} of {{ cars.last_page }}
                            </span>
                            <div class="flex items-center space-x-1">
                                <Button 
                                    v-for="(link, index) in cars.links" 
                                    :key="index"
                                    :variant="link.active ? 'default' : 'outline'"
                                    :class="[
                                        'px-3 py-1 text-sm rounded-md',
                                        { 'pointer-events-none opacity-50': !link.url },
                                        { 'hidden sm:inline-flex': index !== 0 && index !== cars.links.length - 1 && !link.active }
                                    ]"
                                    @click="goToPage(link.url)"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>
