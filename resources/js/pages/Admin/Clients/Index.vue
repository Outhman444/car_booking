<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';
import { index, show } from '@/routes/admin/clients';
import { Card } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  User,
  Calendar,
  CreditCard,
  Activity,
  Search,
  ChevronRight
} from 'lucide-vue-next';

const props = defineProps<{
  clients: {
    data: Array<{
      id: number;
      name: string;
      email: string;
      reservations_count: number;
      payments_count: number;
      is_active: boolean;
    }>;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  filters: {
    search?: string;
    status?: string;
  };
  statuses: Record<string, { label: string; count: number; color: string }>;
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');

function doSearch() {
  router.get(index().url, {
    search: search.value,
    status: statusFilter.value === 'all' ? null : statusFilter.value,
  }, {
    preserveState: true,
    replace: true,
  });
}

const navigateToClient = (id: number) => {
  router.visit(show(id).url);
};

const totalCount = computed(() =>
  Object.values(props.statuses).reduce((acc, curr) => acc + curr.count, 0)
);
</script>

<template>
  <Head title="Clients" />
  <AdminLayout>
    <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen">
      <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <Heading 
            title="Client Management" 
            description="Manage customer profiles, reservation history, and overall platform engagement."
            size="lg"
        />
      </div>

      <div class="mx-auto max-w-[1400px] flex flex-col gap-8">
        <!-- Toolbar -->
        <div class="flex flex-col xl:flex-row gap-6 items-start xl:items-center justify-between bg-white p-6 rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl">
          <div class="flex items-center gap-3 w-full xl:max-w-md">
            <div class="relative flex-1 group">
              <Search class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-slate-400 group-focus-within:text-primary transition-colors" />
              <Input
                v-model="search"
                placeholder="Search name or email..."
                class="pl-12 h-14 rounded-2xl border-none bg-slate-50 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all placeholder:text-slate-400 w-full shadow-inner"
                @keyup.enter="doSearch"
              />
            </div>
            <Button @click="doSearch" class="h-14 px-8 rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-widest text-white hover:bg-slate-800 transition-all border-none">Search</Button>
          </div>

          <!-- Status Filter -->
          <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2 rounded-2xl ring-1 ring-slate-200/50 w-full xl:w-auto">
            <label class="inline-flex items-center">
              <input type="radio" class="hidden" v-model="statusFilter" value="all" @change="doSearch" />
              <div 
                  class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                  :class="{
                      'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === 'all',
                      'text-slate-400 hover:text-slate-600': statusFilter !== 'all'
                  }"
              >
                All ({{ totalCount }})
              </div>
            </label>

            <template v-for="(status, key) in statuses" :key="key">
              <label class="inline-flex items-center">
                <input type="radio" class="hidden" v-model="statusFilter" :value="key" @change="doSearch" />
                <div 
                    class="cursor-pointer whitespace-nowrap px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2"
                    :class="{
                        'bg-white text-primary shadow-sm shadow-slate-200 ring-1 ring-slate-100': statusFilter === key,
                        'text-slate-400 hover:text-slate-600': statusFilter !== key
                    }"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: status.color }"></span>
                  {{ status.label }}
                </div>
              </label>
            </template>
          </div>
        </div>

        <Card class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 border-none overflow-hidden">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow class="hover:bg-transparent border-b border-slate-50">
                  <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[250px]">
                    <span class="flex items-center gap-3"><User class="size-4" /> Client Profile</span>
                  </TableHead>
                  <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">
                    <span class="flex items-center gap-3"><Calendar class="size-4" /> Res. Volume</span>
                  </TableHead>
                  <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">
                    <span class="flex items-center gap-3"><CreditCard class="size-4" /> Pay Volume</span>
                  </TableHead>
                  <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">
                    <span class="flex items-center gap-3"><Activity class="size-4" /> Account Status</span>
                  </TableHead>
                  <TableHead class="h-16 px-8 text-[right] min-w-[80px]"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="c in props.clients.data"
                  :key="c.id"
                  class="group border-b border-slate-50 hover:bg-slate-50/50 transition-colors cursor-pointer"
                  @click="navigateToClient(c.id)"
                >
                  <TableCell class="px-8 py-5">
                    <div class="font-black text-sm text-slate-900 group-hover:text-primary transition-colors">{{ c.name }}</div>
                    <div class="text-[10px] font-bold text-slate-400 mt-1">{{ c.email }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm font-black text-slate-900">{{ c.reservations_count }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm font-black text-slate-900">{{ c.payments_count }}</div>
                  </TableCell>
                  <TableCell>
                    <Badge 
                      variant="outline"
                      class="text-[9px] font-black uppercase tracking-widest px-3 py-0.5 rounded-full border-none ring-1 ring-inset"
                      :class="c.is_active ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-rose-50 text-rose-600 ring-rose-200'"
                    >
                      {{ c.is_active ? 'Active' : 'Suspended' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right px-8">
                    <div class="flex items-center justify-end">
                      <ChevronRight class="size-5 text-slate-300 group-hover:text-primary transition-colors" />
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="props.clients.data.length === 0">
                  <TableCell colspan="5" class="h-64 text-center">
                      <div class="flex flex-col items-center justify-center gap-4">
                          <div class="p-6 rounded-full bg-slate-50 ring-1 ring-slate-100">
                              <Search class="size-8 text-slate-300" />
                          </div>
                          <div class="text-lg font-black text-slate-900">No Clients Found</div>
                          <p class="text-sm font-bold text-slate-400">Try adjusting your search or filters.</p>
                      </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </Card>

        <div class="mt-8 mb-12">
          <Pagination :links="props.clients.links" />
        </div>
      </div>
    </main>
  </AdminLayout>
</template>
