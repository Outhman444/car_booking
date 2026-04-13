<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Plus, Edit2, Trash2, MapPin, Building2, Map } from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';

import { store, update, destroy } from '@/routes/admin/locations';

defineProps<{
    locations: Array<{
        id: number;
        name: string;
        address: string;
        city: string;
        is_active: boolean;
    }>;
}>();

const isDialogOpen = ref(false);
const editingLocation = ref<any>(null);

const form = useForm({
    name: '',
    address: '',
    city: '',
    is_active: true,
});

const openCreate = () => {
    editingLocation.value = null;
    form.reset();
    form.clearErrors();
    isDialogOpen.value = true;
};

const openEdit = (location: any) => {
    editingLocation.value = location;
    form.defaults({
        name: location.name,
        address: location.address,
        city: location.city,
        is_active: !!location.is_active,
    });
    form.reset();
    form.clearErrors();
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingLocation.value) {
        form.put(update(editingLocation.value.id).url, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
        });
    } else {
        form.post(store().url, {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteLocation = (id: number) => {
    if (confirm('Are you sure you want to delete this location?')) {
        router.delete(destroy(id).url, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Locations Management" />

    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="Agency Locations" 
                    description="Manage physical offices where vehicles can be picked up and returned."
                    size="lg"
                />
                <Button @click="openCreate" class="h-14 px-8 rounded-2xl bg-primary text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-primary/20 hover:bg-primary/90 transition-all border-none active:scale-[0.98]">
                    <Plus class="w-5 h-5 mr-2" />
                    Add New Location
                </Button>
            </div>

            <div class="mx-auto max-w-[1400px]">
                <Card class="rounded-[2.5rem] bg-white ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 border-none overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                        <TableHeader>
                            <TableRow class="hover:bg-transparent border-b border-slate-50">
                                <TableHead class="h-16 px-8 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[200px]">
                                    <div class="flex items-center gap-3"><Building2 class="size-4" /> Office Name</div>
                                </TableHead>
                                <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[150px]">
                                    <div class="flex items-center gap-3"><Map class="size-4" /> City</div>
                                </TableHead>
                                <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[250px]">
                                    <div class="flex items-center gap-3"><MapPin class="size-4" /> Address</div>
                                </TableHead>
                                <TableHead class="h-16 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[100px]">Status</TableHead>
                                <TableHead class="h-16 px-8 text-right min-w-[120px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="location in locations" 
                                :key="location.id"
                                class="group border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors"
                            >
                                <TableCell class="font-black text-sm text-slate-900 py-5 px-8 group-hover:text-primary transition-colors">{{ location.name }}</TableCell>
                                <TableCell class="text-sm py-5 px-8 font-bold text-slate-600">{{ location.city }}</TableCell>
                                <TableCell class="text-sm py-5 px-8 font-medium text-slate-400">{{ location.address }}</TableCell>
                                <TableCell class="py-5 px-8">
                                    <Badge 
                                        variant="outline"
                                        class="text-[9px] font-black uppercase tracking-widest px-3 py-0.5 rounded-full border-none ring-1 ring-inset"
                                        :class="location.is_active ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-50 text-slate-500 ring-slate-200'"
                                    >
                                        {{ location.is_active ? 'Active' : 'Offline' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right py-5 px-8">
                                    <div class="flex justify-end gap-2 px-1">
                                        <Button variant="ghost" size="icon" @click="openEdit(location)" class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary transition-all border-none">
                                            <Edit2 class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deleteLocation(location.id)" class="h-9 w-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all border-none">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="locations.length === 0">
                                <TableCell colspan="5" class="h-64 text-center">
                                    <div class="flex flex-col items-center justify-center gap-4">
                                        <div class="p-6 rounded-full bg-slate-50 ring-1 ring-slate-100">
                                            <Building2 class="size-8 text-slate-300" />
                                        </div>
                                        <div class="text-lg font-black text-slate-900">No Locations Found</div>
                                        <p class="text-sm font-bold text-slate-400">Add your first agency office to start accepting bookings.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                        </Table>
                    </div>
                </Card>
            </div>
        </main>

        <!-- Add/Edit Modal -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[480px] rounded-[2.5rem] border-none shadow-2xl p-8">
                <DialogHeader class="mb-4">
                    <DialogTitle class="text-2xl font-black text-slate-900">{{ editingLocation ? 'Edit' : 'Add New' }} Location</DialogTitle>
                    <DialogDescription class="text-sm font-bold text-slate-500 mt-2">
                        Enter the physical details for this agency branch.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-6 pt-2">
                    <div class="space-y-2">
                        <Label for="name" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Office Name</Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            placeholder="e.g. Airport Terminal 1"
                            class="h-12 rounded-xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="city" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">City</Label>
                        <Input 
                            id="city" 
                            v-model="form.city" 
                            placeholder="e.g. Casablanca"
                            class="h-12 rounded-xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="address" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Exact Address</Label>
                        <Input 
                            id="address" 
                            v-model="form.address" 
                            placeholder="Full address details..."
                            class="h-12 rounded-xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all"
                        />
                    </div>
                    <div class="flex items-center space-x-3 p-4 bg-slate-50 rounded-2xl ring-1 ring-slate-100">
                        <Checkbox 
                            id="is_active" 
                            :checked="form.is_active" 
                            @update:checked="form.is_active = $event"
                            class="size-6 border-slate-300 rounded-md"
                        />
                        <div class="grid gap-1 leading-none">
                            <Label for="is_active" class="text-sm font-black text-slate-900 cursor-pointer">Active Office</Label>
                            <p class="text-[10px] font-bold text-slate-400">Available for customers during booking.</p>
                        </div>
                    </div>
                </div>

                <DialogFooter class="mt-8 pt-6 border-t border-slate-100 gap-3">
                    <Button variant="ghost" @click="isDialogOpen = false" class="h-12 rounded-xl font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all border-none flex-1">Cancel</Button>
                    <Button :disabled="form.processing" @click="submit" class="h-12 rounded-xl bg-primary text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-primary/20 hover:bg-primary/90 transition-all border-none flex-1">
                        {{ editingLocation ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
