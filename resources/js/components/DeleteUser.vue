<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import { TriangleAlert, Trash2, KeyRound, LoaderCircle } from 'lucide-vue-next';

// Components
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<InstanceType<typeof Input> | null>(null);
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col gap-1">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">Executive Exit Protocol</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Permanent account termination and data purge</p>
        </div>

        <div
            class="overflow-hidden rounded-[2rem] border-none bg-rose-50 p-8 ring-1 ring-rose-100 relative group"
        >
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                <Trash2 class="size-32 text-rose-900" />
            </div>

            <div class="flex flex-col sm:flex-row items-start gap-6 relative z-10">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-xl shadow-rose-200/50 text-rose-500">
                    <TriangleAlert class="size-7" />
                </div>
                
                <div class="space-y-6 flex-1">
                    <div class="space-y-2">
                        <p class="text-sm font-black text-rose-900 uppercase tracking-wide italic">Critical Warning</p>
                        <p class="text-sm font-bold text-rose-700/80 leading-relaxed group-hover:text-rose-900 transition-colors">
                            Execution of this protocol will lead to the permanent deletion of all assets, reservation history, and identity metadata associated with your profile. This operation is non-reversible.
                        </p>
                    </div>

                    <Dialog>
                        <DialogTrigger as-child>
                            <Button 
                                variant="destructive" 
                                class="h-12 rounded-xl bg-rose-600 px-8 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-rose-200 hover:bg-rose-700 transition-all border-none active:scale-95"
                                data-test="delete-user-button"
                            >
                                Initiate Deletion
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md rounded-[2.5rem] border-none bg-white p-10 shadow-3xl ring-1 ring-slate-100">
                            <Form
                                v-bind="ProfileController.destroy.form()"
                                reset-on-success
                                @error="() => passwordInput?.$el?.focus()"
                                :options="{ preserveScroll: true }"
                                class="space-y-8"
                                v-slot="{ errors, processing, reset, clearErrors }"
                            >
                                <DialogHeader class="space-y-4">
                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[2rem] bg-rose-50 text-rose-500 shadow-inner">
                                        <Trash2 class="size-10" />
                                    </div>
                                    <DialogTitle class="text-center text-2xl font-black tracking-tight text-slate-900">
                                        Confirm Identity Purge
                                    </DialogTitle>
                                    <DialogDescription class="text-center text-sm font-bold leading-relaxed text-slate-400">
                                        To authorize the permanent termination of your account, please enter your administrative password below.
                                    </DialogDescription>
                                </DialogHeader>

                                <div class="space-y-3">
                                    <Label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Authorization Secret</Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <KeyRound class="size-5" />
                                        </div>
                                        <Input
                                            id="password"
                                            type="password"
                                            name="password"
                                            ref="passwordInput"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all"
                                            placeholder="Enter your password"
                                        />
                                    </div>
                                    <InputError class="text-[10px] font-black text-rose-500 uppercase tracking-wider" :message="errors.password" />
                                </div>

                                <DialogFooter class="flex sm:flex-row flex-col gap-4">
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            class="h-14 flex-1 rounded-2xl bg-slate-50 text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-all border-none"
                                            @click="() => { clearErrors(); reset(); }"
                                        >
                                            Abadon
                                        </Button>
                                    </DialogClose>

                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        :disabled="processing"
                                        class="h-14 flex-1 rounded-2xl bg-rose-600 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-rose-100 hover:bg-rose-700 transition-all border-none active:scale-[0.98]"
                                        data-test="confirm-delete-user-button"
                                    >
                                        <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                        Confirm Termination
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </div>
    </div>
</template>
