<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import FileUpload from '@/components/ViltFilePond/FileUpload.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Heading from '@/components/Heading.vue';
import { index, store, update } from '@/routes/admin/cars';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import HelpTooltip from '@/components/HelpTooltip.vue';

const props = defineProps<{
    car: any | null;
    imageFiles: Array<{ id: number; url: string }>;
    enums: {
        colors: Array<{ name: string; value: string; hex: string }>;
        fuelTypes: string[];
        statuses: Array<{ value: string; label: string; color: string }>;
    };
}>();

const isEdit = computed(() => !!props.car);

// Form state
const carColors = computed(() =>
    props.enums.colors.map((color) => ({
        ...color,
        value: color.value.toLowerCase(),
        name: color.name.charAt(0).toUpperCase() + color.name.slice(1),
    })),
);

const fuelTypes = computed(() =>
    props.enums.fuelTypes.map((fuel) => ({
        value: fuel.toLowerCase(),
        label: fuel.charAt(0).toUpperCase() + fuel.slice(1),
    })),
);

const statuses = computed(() =>
    props.enums.statuses
        .filter((status) => ['available', 'maintenance', 'out_of_service'].includes(status.value))
        .map((status) => ({
            value: status.value,
            label: status.label,
            color: status.color,
        })),
);

// Initialize form with default values
const form = useForm({
    make: props.car?.make ?? '',
    model: props.car?.model ?? '',
    year: props.car?.year ?? '',
    license_plate: props.car?.license_plate ?? '',
    color: (props.car?.color || 'white').toLowerCase(),
    price_per_day: props.car?.price_per_day ?? '',
    mileage: props.car?.mileage ?? '',
    transmission: props.car?.transmission ?? 'automatic',
    seats: props.car?.seats ?? '',
    fuel_type: (props.car?.fuel_type || 'gasoline').toLowerCase(),
    description: props.car?.description ?? '',
    status: props.car?.status ?? 'available',
    image: [] as string[],
    image_temp_folders: [] as string[],
    image_removed_files: [] as number[],
});

const fileUploadRef = ref<InstanceType<typeof FileUpload> | null>(null);
const tempFolders = ref<string[]>([]);
const removedFileIds = ref<number[]>([]);

watch(tempFolders, (value) => { form.image_temp_folders = [...value]; }, { deep: true });

function handleFileRemoved(data: { type: string; fileId?: number }) {
    if (data.type === 'existing' && data.fileId) {
        removedFileIds.value.push(data.fileId);
        form.image_removed_files = [...removedFileIds.value];
    }
}

function submit() {
    if (isEdit.value) {
        form.put(update(props.car.id).url);
    } else {
        form.image = [...tempFolders.value];
        form.post(store().url, {
            onSuccess: () => {
                form.reset();
                tempFolders.value = [];
                fileUploadRef.value?.resetFiles();
            },
        });
    }
}
</script>

<template>
    <Head :title="isEdit ? `Edit ${car.make} ${car.model}` : 'Add New Car'" />
    <AdminLayout>
        <main class="flex-1 p-4 md:p-8 space-y-8 md:space-y-12 bg-background min-h-screen pb-32 max-w-[1600px] mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-4">
                    <Link :href="index()" class="group inline-flex items-center text-xs font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-all">
                        <ArrowLeft class="size-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                        Back to Inventory
                    </Link>
                    <Heading 
                        :title="isEdit ? `Edit Vehicle` : 'Add New Vehicle'" 
                        :description="isEdit ? `Updating ${car.year} ${car.make} ${car.model}` : 'Add a new vehicle to your premium fleet inventory.'"
                        size="lg"
                    />
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="index()">
                        <Button variant="ghost" class="h-14 px-8 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all">Cancel</Button>
                    </Link>
                    <Button 
                        @click="submit" 
                        :disabled="form.processing"
                        class="h-14 px-10 rounded-2xl bg-primary text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-primary/20 hover:bg-primary/90 transition-all border-none active:scale-[0.98]"
                    >
                        <Save class="size-5 mr-3" />
                        {{ isEdit ? 'Update Vehicle' : 'Save Vehicle' }}
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Left Column: Image and Primary Stats -->
                <div class="lg:col-span-1 space-y-10">
                    <!-- Media Card -->
                    <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                        <CardHeader class="p-8 pb-4">
                            <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                                    <ImageIcon class="size-5" />
                                </div>
                                Vehicle Media
                            </CardTitle>
                            <CardDescription class="text-xs font-bold uppercase tracking-widest text-slate-400 pt-2">Upload high-resolution photography.</CardDescription>
                        </CardHeader>
                        <CardContent class="p-8 pt-4">
                            <div class="space-y-6">
                                <div class="rounded-3xl overflow-hidden ring-1 ring-slate-100 transition-all focus-within:ring-2 focus-within:ring-ring">
                                    <FileUpload
                                        ref="fileUploadRef"
                                        v-model="tempFolders"
                                        :initial-files="imageFiles || []"
                                        :allow-multiple="false"
                                        :max-files="1"
                                        collection="image"
                                        theme="light"
                                        width="100%"
                                        @file-removed="handleFileRemoved"
                                    />
                                </div>
                                <p class="text-[10px] text-center font-bold text-slate-400 uppercase tracking-widest">
                                    Recommended: PNG or JPG (Min 1200x800px)
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status and Pricing Card -->
                    <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                        <CardHeader class="p-8 pb-4">
                            <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                                    <Tag class="size-5" />
                                </div>
                                Status & Pricing
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-8 space-y-8 pt-6">
                            <!-- Status -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Current Availability</label>
                                    <HelpTooltip 
                                        content="Set current availability. Use 'Maintenance' or 'Cleaning' for non-rentable states." 
                                    />
                                </div>
                                <!-- System-controlled status warning -->
                                <div v-if="isEdit && ['rented', 'reserved', 'pending'].includes(car?.status)" class="rounded-2xl bg-violet-50 p-4 ring-1 ring-violet-200 space-y-2">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-violet-600">System Controlled</p>
                                    <p class="text-xs font-bold text-violet-500">This vehicle's status is managed by an active reservation. Complete or cancel the reservation to change availability.</p>
                                </div>
                                <Select v-model="form.status" :disabled="isEdit && ['rented', 'reserved', 'pending'].includes(car?.status)">
                                    <SelectTrigger id="status" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-2xl border-none shadow-2xl ring-1 ring-slate-100">
                                        <SelectItem 
                                            v-for="status in statuses" 
                                            :key="status.value" 
                                            :value="status.value"
                                            class="rounded-xl font-bold py-3"
                                        >
                                            {{ status.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>

                            <!-- Price -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Daily Rental Rate</label>
                                    <HelpTooltip 
                                        content="Set the daily rental cost. This amount will be used for auto-calculations." 
                                    />
                                </div>
                                <div class="relative group">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-lg font-black text-slate-400 group-focus-within:text-primary transition-colors">$</span>
                                    <Input
                                        id="price_per_day"
                                        v-model="form.price_per_day"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="h-14 w-full pl-12 rounded-2xl border-none bg-slate-50 font-black text-xl text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:font-medium placeholder:text-slate-300"
                                        placeholder="e.g. 45.00"
                                    />
                                </div>
                                <InputError :message="form.errors.price_per_day" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Specifications -->
                <div class="lg:col-span-2 space-y-10">
                    <!-- Basic Info Card -->
                    <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                        <CardHeader class="p-8 pb-4">
                            <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                                    <Info class="size-5" />
                                </div>
                                General Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-8 space-y-8 pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Make / Manufacturer</label>
                                        <HelpTooltip 
                                            content="The brand of the car (e.g., Mercedes, Audi, BMW)." 
                                        />
                                    </div>
                                    <Input id="make" v-model="form.make" placeholder="e.g. Mercedes-Benz, Toyota..." class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:font-medium placeholder:text-slate-400" />
                                    <InputError :message="form.errors.make" />
                                </div>
                                <div class="space-y-4">
                                        <HelpTooltip 
                                            content="The specific model name (e.g., C-Class, A4, RAV4)." 
                                        />
                                    <Input id="model" v-model="form.model" placeholder="e.g. C-Class, Corolla..." class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:font-medium placeholder:text-slate-400" />
                                    <InputError :message="form.errors.model" />
                                </div>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                        <Calendar class="size-3" /> Production Year
                                        <HelpTooltip 
                                            content="The year the vehicle was manufactured." 
                                        />
                                    </label>
                                    <Input id="year" v-model="form.year" type="number" placeholder="e.g. 2024" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:font-medium placeholder:text-slate-400" />
                                    <InputError :message="form.errors.year" />
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">License Plate</label>
                                        <HelpTooltip 
                                            content="Unique identification plate of the vehicle." 
                                        />
                                    </div>
                                    <Input id="license_plate" v-model="form.license_plate" placeholder="e.g. ABC-[1234]" class="h-14 rounded-2xl border-none bg-slate-50 font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all font-mono uppercase tracking-widest placeholder:font-medium placeholder:text-slate-400" />
                                    <InputError :message="form.errors.license_plate" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Technical Specs Card -->
                    <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                        <CardHeader class="p-8 pb-4">
                            <CardTitle class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-slate-100 text-slate-600">
                                    <Settings2 class="size-5" />
                                </div>
                                Performance & Utility
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-8 space-y-10 pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                            <Fuel class="size-3" /> Fuel Configuration
                                        </label>
                                        <HelpTooltip 
                                            content="Specify the required fuel type (Gasoline, Diesel, Electric, etc.)." 
                                        />
                                    </div>
                                    <Select v-model="form.fuel_type">
                                        <SelectTrigger id="fuel_type" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all">
                                            <SelectValue placeholder="Select fuel type" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-2xl border-none shadow-2xl ring-1 ring-slate-100">
                                            <SelectItem v-for="fuel in fuelTypes" :key="fuel.value" :value="fuel.value" class="rounded-xl font-bold py-3">
                                                {{ fuel.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.fuel_type" />
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                            <Cog class="size-3" /> Drivetrain
                                        </label>
                                        <HelpTooltip 
                                            content="Choose between automatic or manual transmission systems." 
                                        />
                                    </div>
                                    <Select v-model="form.transmission">
                                        <SelectTrigger id="transmission" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all">
                                            <SelectValue placeholder="Select transmission" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-2xl border-none shadow-2xl ring-1 ring-slate-100">
                                            <SelectItem value="automatic" class="rounded-xl font-bold py-3">Automatic</SelectItem>
                                            <SelectItem value="manual" class="rounded-xl font-bold py-3">Manual</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.transmission" />
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                            <Gauge class="size-3" /> Current Odometer (km)
                                        </label>
                                        <HelpTooltip 
                                            content="The current total distance traveled. Important for tracking usage." 
                                        />
                                    </div>
                                    <Input id="mileage" v-model="form.mileage" type="number" placeholder="e.g. 50000" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:font-medium placeholder:text-slate-400" />
                                    <InputError :message="form.errors.mileage" />
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Seating Capacity</label>
                                        <HelpTooltip 
                                            content="Total passenger seats (including driver)." 
                                        />
                                    </div>
                                    <Input id="seats" v-model="form.seats" type="number" placeholder="e.g. 4 or 5" class="h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:font-medium placeholder:text-slate-400" />
                                    <InputError :message="form.errors.seats" />
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Exterior Aesthetic</label>
                                    <HelpTooltip 
                                        content="Select the primary exterior color." 
                                    />
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                                    <div v-for="color in carColors" :key="color.value" class="relative">
                                        <input
                                            type="radio"
                                            :id="'color-' + color.value"
                                            v-model="form.color"
                                            :value="color.value"
                                            class="peer sr-only"
                                        />
                                        <label
                                            :for="'color-' + color.value"
                                            class="flex items-center gap-3 cursor-pointer rounded-[1.25rem] border-none ring-1 ring-slate-100 p-4 transition-all hover:bg-slate-50 peer-checked:ring-2 peer-checked:ring-ring peer-checked:bg-primary/5 shadow-sm"
                                        >
                                            <span
                                                class="size-5 rounded-full ring-1 ring-slate-900/10 shadow-inner"
                                                :style="{ backgroundColor: color.hex }"
                                            ></span>
                                            <span class="text-xs font-black text-slate-900">{{ color.name }}</span>
                                        </label>
                                    </div>
                                </div>
                                <InputError :message="form.errors.color" />
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Vehicle Description</label>
                                    <HelpTooltip 
                                        content="Highlight premium features and condition details for clients." 
                                    />
                                </div>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="8"
                                    class="rounded-3xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all p-6 text-base leading-relaxed placeholder:text-slate-400"
                                    placeholder="Enter premium features, condition details, and high-value selling points..."
                                />
                                <InputError :message="form.errors.description" />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </main>
    </AdminLayout>
</template>
