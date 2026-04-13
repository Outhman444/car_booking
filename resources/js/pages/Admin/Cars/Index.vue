<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref, watch } from 'vue';
import { Card } from '@/components/ui/card';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import HelpTooltip from '@/components/HelpTooltip.vue';
import { AlertCircle } from 'lucide-vue-next';
import { create, index, destroy, quickUpdate } from '@/routes/admin/cars';
import { 
    Car, 
    Hash, 
    DollarSign, 
    Edit, 
    Trash2, 
    CheckCircle, 
    Wrench, 
    Droplet,
    Search,
    Image as ImageIcon
} from 'lucide-vue-next';

const props = defineProps<{
  cars: {
    data: Array<{
      id: number
      make: string
      model: string
      year: number
      license_plate: string
      price_per_day: string | number
      status: string
      status_label?: string
      status_color?: string
      image_url?: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: { 
    search?: string
    status?: string 
  }
  statuses: Record<string, {
    label: string
    count: number
    color: string
  }>
  currency: { symbol: string; code: string }
}>()

const search = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || 'all')

function doSearch() {
  router.get(index(), { 
    search: search.value,
    status: statusFilter.value === 'all' ? null : statusFilter.value
  }, {
    preserveState: true,
    replace: true,
  })
}

watch(search, (v, ov) => {
  if (v === '' && ov !== '') doSearch()
})

const showDeleteDialog = ref(false);
const carToDelete = ref<number | null>(null);

const openDeleteDialog = (id: number) => {
  carToDelete.value = id;
  showDeleteDialog.value = true;
};

const destroyCar = () => {
  if (!carToDelete.value) return;
  
  router.delete(destroy(carToDelete.value).url, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteDialog.value = false;
      carToDelete.value = null;
    },
  });
};

function doQuickUpdate(id: number, status: string) {
    if (confirm(`Change car status to ${status}?`)) {
        router.post(quickUpdate(id).url, { status }, {
            preserveScroll: true
        });
    }
}
</script>

<template>
    <Head title="Cars" />
    <AdminLayout>
        <!-- Main -->
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Fleet Management" 
                    description="Manage your vehicles, track status, and update rental rates."
                    size="lg"
                />
                <Link :href="create()">
                    <Button class="h-14 px-8 rounded-2xl bg-primary text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-primary/20 hover:bg-primary/90 transition-all border-none active:scale-[0.98]">
                        <Car class="mr-2 size-5" /> Add New Vehicle
                    </Button>
                </Link>
            </div>

            <div class="mx-auto max-w-[1400px] flex flex-col gap-8">
                <!-- Toolbar -->
                <div class="flex flex-col xl:flex-row gap-6 items-start xl:items-center justify-between bg-white p-6 rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50">
                    <div class="flex items-center gap-3 w-full xl:max-w-md">
                        <div class="relative flex-1 group">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-slate-400 group-focus-within:text-primary transition-colors" />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 z-10">
                                <HelpTooltip content="Filter your fleet by manufacturer, model name, or license plate for rapid inventory access." />
                            </div>
                            <Input
                              v-model="search"
                              placeholder="Search make, model, plate..."
                              class="pl-12 h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:text-slate-400 w-full"
                              @keyup.enter="doSearch"
                            />
                        </div>
                        <Button @click="doSearch" class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all border-none">Search</Button>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-1.5 rounded-2xl ring-1 ring-slate-200/50 w-full xl:w-auto">
                        <label class="inline-flex items-center">
                            <input 
                                type="radio" 
                                class="hidden" 
                                v-model="statusFilter" 
                                value="all"
                                @change="doSearch"
                            >
                            <div 
                                class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="{
                                    'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === 'all',
                                    'text-slate-400 hover:text-slate-600': statusFilter !== 'all'
                                }"
                            >
                                All ({{ Object.values(statuses).reduce((acc, curr) => acc + curr.count, 0) }})
                            </div>
                        </label>
                        
                        <template v-for="(status, key) in statuses" :key="key">
                            <label class="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    class="hidden" 
                                    v-model="statusFilter" 
                                    :value="key"
                                    @change="doSearch"
                                >
                                <div 
                                    class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2"
                                    :class="{
                                        'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === key,
                                        'text-slate-400 hover:text-slate-600': statusFilter !== key
                                    }"
                                >
                                    <span 
                                        class="w-1.5 h-1.5 rounded-full" 
                                        :style="{ backgroundColor: status.color }"
                                    ></span>
                                    {{ status.label }}
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Table Container -->
                <Card class="shadow-xl shadow-slate-200/50 border-none ring-1 ring-slate-100 rounded-[2.5rem] overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="hover:bg-transparent border-b border-slate-50">
                                    <TableHead class="h-16 px-4 text-[10px] font-black uppercase tracking-widest text-slate-500 w-[100px]">
                                        <ImageIcon class="size-4" />
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[160px]">
                                        <div class="flex items-center gap-2"><Car class="size-4" /> Vehicle</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[110px]">
                                        <div class="flex items-center gap-2"><Hash class="size-4" /> Plate</div>
                                    </TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 w-[110px]">Rate</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 w-[110px]">Status</TableHead>
                                    <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[160px]">
                                        <div class="flex items-center gap-2">
                                            Quick Status
                                            <HelpTooltip content="Instant availability management. Use these to quickly cycle cars through cleaning or maintenance states." />
                                        </div>
                                    </TableHead>
                                    <TableHead class="h-16 px-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right w-[100px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="car in props.cars.data" :key="car.id" class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                    <TableCell class="px-4 py-4">
                                        <div class="relative h-12 w-20 overflow-hidden rounded-lg ring-1 ring-slate-100 shadow-sm bg-slate-50">
                                            <img v-if="car.image_url" :src="car.image_url" alt="Car" class="h-full w-full object-cover transition-transform group-hover:scale-110" />
                                            <div v-else class="flex h-full w-full items-center justify-center">
                                                <Car class="size-6 text-slate-300" />
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col leading-tight">
                                            <span class="font-black text-slate-900 text-sm group-hover:text-primary transition-colors line-clamp-1">
                                                {{ car.year }} {{ car.make }}
                                            </span>
                                            <span class="text-[11px] font-bold text-slate-400 line-clamp-1 italic">{{ car.model }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-[10px] font-mono font-black text-slate-400 px-2 py-0.5 rounded bg-slate-100 uppercase tracking-widest">
                                            {{ car.license_plate }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="font-black text-xs text-slate-900">
                                        {{ currency.symbol }}{{ Number(car.price_per_day).toLocaleString() }}
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge
                                            :status="car.status"
                                            :label="car.status_label || statuses[car.status]?.label || car.status"
                                            :color="statuses[car.status]?.color || car.status_color"
                                            class="scale-90 origin-left"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-1.5">
                                            <Button 
                                                v-if="car.status === 'maintenance' || car.status === 'out_of_service'"
                                                @click="doQuickUpdate(car.id, 'available')"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-all border-none"
                                            >
                                                Ready
                                            </Button>
                                            <Button 
                                                v-if="car.status === 'available'"
                                                @click="doQuickUpdate(car.id, 'maintenance')"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all border-none"
                                            >
                                                Maint
                                            </Button>
                                            <Button 
                                                v-if="car.status === 'available'"
                                                @click="doQuickUpdate(car.id, 'out_of_service')"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 px-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all border-none"
                                            >
                                                Disable
                                            </Button>
                                            <span v-if="['rented', 'reserved', 'pending'].includes(car.status)" class="text-[9px] font-black uppercase tracking-widest text-slate-400 px-2 py-1 bg-slate-50 rounded-lg ring-1 ring-slate-100">
                                                In Use
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button variant="ghost" size="icon" as-child class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary transition-all border-none">
                                                <Link :href="`/admin/cars/${car.id}/edit`">
                                                    <Edit class="size-3.5" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all border-none" @click="openDeleteDialog(car.id)">
                                                <Trash2 class="size-3.5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.cars.data.length === 0">
                                    <TableCell colspan="7" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center gap-4">
                                            <div class="p-6 rounded-full bg-slate-50 ring-1 ring-slate-100">
                                                <Search class="size-8 text-slate-300" />
                                            </div>
                                            <div class="text-lg font-black text-slate-900">No Vehicles Found</div>
                                            <p class="text-sm font-bold text-slate-400">Try adjusting your search or filters.</p>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </Card>

                <div class="mt-8 mb-12">
                    <Pagination :links="props.cars.links" />
                </div>
            </div>
        </main>
        
        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
          <DialogContent class="sm:max-w-[425px] rounded-[2.5rem] border-none shadow-2xl p-8">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-3 text-xl font-black text-slate-900">
                 <div class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <AlertCircle class="h-6 w-6" />
                 </div>
                Delete Vehicle
              </DialogTitle>
              <DialogDescription class="pt-4 text-sm font-bold text-slate-500 leading-relaxed">
                Are you sure you want to delete this vehicle? This action is permanent and will remove all associated booking history.
              </DialogDescription>
            </DialogHeader>
            
            <DialogFooter class="mt-10 flex gap-3">
              <DialogClose as-child>
                <Button variant="ghost" class="flex-1 h-14 rounded-2xl font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all">
                    Cancel
                </Button>
              </DialogClose>
              <Button 
                type="button" 
                variant="destructive"
                @click="destroyCar"
                :disabled="!carToDelete"
                class="flex-1 h-14 rounded-2xl bg-rose-600 text-sm font-black uppercase tracking-widest text-white hover:bg-rose-500 shadow-xl shadow-rose-200 transition-all border-none"
              >
                Confirm Delete
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
