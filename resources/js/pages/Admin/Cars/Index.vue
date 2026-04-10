<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref, watch, computed } from 'vue';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { AlertCircle } from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { create } from '@/routes/admin/cars';
import { index } from '@/routes/admin/cars';
import { destroy } from '@/routes/admin/cars';

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


import StatusBadge from '@/components/StatusBadge.vue';
import Pagination from '@/components/Pagination.vue';

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
</script>

<template>
    <Head title="Cars" />
    <AdminLayout>
        <!-- Main -->
        <main class="flex-1 p-8 space-y-6">
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold">Cars</h1>
                <Link :href="create()">
                    <Button >
                        + New Car
                    </Button>
                </Link>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-2">
                    <Input
                      v-model="search"
                      placeholder="Search make, model, plate..."
                      class="max-w-md"
                      @keyup.enter="doSearch"
                    />
                    <Button @click="doSearch">Search</Button>
                </div>
                
                <!-- Status Filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <label class="inline-flex items-center">
                        <input 
                            type="radio" 
                            class="hidden" 
                            v-model="statusFilter" 
                            value="all"
                            @change="doSearch"
                        >
                        <span 
                            class="px-3 py-1.5 text-sm rounded-full cursor-pointer transition-colors"
                            :class="{
                                'bg-primary text-primary-foreground': statusFilter === 'all',
                                'bg-muted text-muted-foreground hover:bg-muted/80': statusFilter !== 'all'
                            }"
                        >
                            All ({{ Object.values(statuses).reduce((acc, curr) => acc + curr.count, 0) }})
                        </span>
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
                            <span 
                                class="px-3 py-1.5 text-sm rounded-full cursor-pointer transition-colors flex items-center gap-1.5"
                                :class="{
                                    'bg-primary text-primary-foreground': statusFilter === key,
                                    'bg-muted text-muted-foreground hover:bg-muted/80': statusFilter !== key
                                }"
                            >
                                <span 
                                    class="w-2 h-2 rounded-full" 
                                    :style="{ backgroundColor: status.color }"
                                ></span>
                                {{ status.label }} ({{ status.count }})
                            </span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto rounded-md border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plate</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/Day</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="car in props.cars.data" :key="car.id">
                            <td class="px-4 py-3">
                                <img :src="car.image_url" alt="Car" class="h-12 w-16 object-cover rounded" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ car.year }} {{ car.make }} {{ car.model }}</div>
                            </td>
                            <td class="px-4 py-3">{{ car.license_plate }}</td>
                            <td class="px-4 py-3">{{ currency.symbol }}{{ Number(car.price_per_day).toFixed(2) }}</td>
                            <td class="px-4 py-3">
                                <StatusBadge
                                    :status="car.status"
                                    :label="car.status_label || statuses[car.status]?.label || car.status"
                                    :color="statuses[car.status]?.color || car.status_color"
                                />
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="`/admin/cars/${car.id}/edit`">
                                    <Button variant="outline" size="sm">Edit</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="openDeleteDialog(car.id)">Delete</Button>
                            </td>
                        </tr>
                        <tr v-if="props.cars.data.length === 0">
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No cars found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="props.cars.links" />
        </main>
        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <AlertCircle class="h-5 w-5 text-destructive" />
                Delete Car
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to delete this car? This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            
            <Alert variant="destructive" class="mt-4">
              <AlertCircle class="h-4 w-4" />
              <AlertDescription>
                This will permanently delete the car and all its associated data.
              </AlertDescription>
            </Alert>
            
            <DialogFooter class="mt-4">
              <DialogClose as-child>
                <Button variant="outline">Cancel</Button>
              </DialogClose>
              <Button 
                type="button" 
                variant="destructive"
                @click="destroyCar"
                :disabled="!carToDelete"
              >
                Delete Car
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
