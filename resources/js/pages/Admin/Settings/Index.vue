<script setup lang="ts">
import { watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { 
    Save, Mail, Globe, LayoutDashboard, Calendar, Settings, Sparkles, ShieldCheck, 
    Link as LinkIcon, Phone, DollarSign, Hash, Type, FileText, Info, AlertTriangle, Loader2,
    HelpCircle
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import HelpTooltip from '@/components/HelpTooltip.vue';

import { update } from '@/routes/admin/settings';

const props = defineProps<{
    settings: Array<{
        key: string;
        value: string;
        display_name: string;
        group: string;
        type: string;
        description?: string;
    }>;
    groups: string[];
}>();

const form = useForm(
    Object.fromEntries(props.settings.map(s => [s.key, s.value]))
);

// Watch for prop changes (e.g. after a successful save redirect) and sync the form
watch(() => props.settings, (newSettings) => {
    Object.assign(form, Object.fromEntries(newSettings.map(s => [s.key, s.value])));
}, { deep: true });

const submit = () => {
    form.post(update().url, {
        preserveScroll: true,
    });
};

const groupMap = {
    general: { label: 'General', icon: Globe, color: 'text-blue-500', bg: 'bg-blue-50' },
    contact: { label: 'Contact', icon: Mail, color: 'text-emerald-500', bg: 'bg-emerald-50' },
    booking: { label: 'Booking Rules', icon: Calendar, color: 'text-amber-500', bg: 'bg-amber-50' },
    appearance: { label: 'Appearance', icon: LayoutDashboard, color: 'text-indigo-500', bg: 'bg-indigo-50' },
};

const getGroupInfo = (group: string) => {
    return groupMap[group as keyof typeof groupMap] || { label: group, icon: Settings, color: 'text-slate-500', bg: 'bg-slate-50' };
};

const getSettingIcon = (key: string) => {
    const k = key.toLowerCase();
    if (k.includes('email')) return Mail;
    if (k.includes('url')) return LinkIcon;
    if (k.includes('phone')) return Phone;
    if (k.includes('currency_symbol')) return DollarSign;
    if (k.includes('currency_code')) return Hash;
    if (k.includes('name')) return Type;
    if (k.includes('description') || k.includes('footer')) return FileText;
    return Info;
};

const getPlaceholder = (key: string) => {
    const k = key.toLowerCase();
    if (k.includes('app_name')) return "e.g. Real Rent Car";
    if (k.includes('app_url')) return "e.g. https://yourdomain.com";
    if (k.includes('email')) return "e.g. contact@yourbusiness.com";
    if (k.includes('phone')) return "e.g. +212 600 000 000";
    if (k.includes('currency_symbol')) return "e.g. DH or $";
    if (k.includes('currency_code')) return "e.g. MAD or USD";
    if (k.includes('meta_description')) return "e.g. Premium car rental services in Morocco...";
    if (k.includes('footer')) return "e.g. © 2024 Real Rent Car. All rights reserved.";
    if (k.includes('min_rental')) return "e.g. 1";
    if (k.includes('max_rental')) return "e.g. 30";
    if (k.includes('tax')) return "e.g. 20";
    return "Enter value...";
};
</script>

<template>
    <Head title="System Configuration" />

    <AdminLayout>
        <main class="w-full p-4 sm:p-8 space-y-8 sm:space-y-10 bg-background min-h-screen pb-32">
            <div class="mx-auto max-w-[1400px] flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <Heading 
                    title="System Configuration" 
                    description="Tailor the application logic, contact details, and visual appearance to your brand."
                    size="lg"
                />
            </div>

            <form @submit.prevent="submit" class="mx-auto max-w-[1400px] space-y-10">
                <Tabs default-value="general" class="w-full space-y-12">
                    <!-- Tab Bar & Save Button -->
                    <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-8 bg-white/80 backdrop-blur-md p-5 rounded-[2.5rem] ring-1 ring-slate-100 shadow-xl shadow-slate-200/40 sticky top-4 z-50 transition-all">
                        <TabsList class="flex flex-wrap h-auto p-1.5 gap-2 bg-slate-100/50 rounded-[1.8rem] ring-1 ring-slate-200/50 w-full xl:w-auto overflow-visible border-none">
                            <TabsTrigger 
                                v-for="group in groups" 
                                :key="group" 
                                :value="group" 
                                class="flex items-center gap-3 px-6 h-12 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all data-[state=active]:bg-white data-[state=active]:text-primary data-[state=active]:shadow-md data-[state=active]:ring-1 data-[state=active]:ring-slate-100 border-none group relative overflow-hidden"
                            >
                                <component :is="getGroupInfo(group).icon" class="w-4 h-4 opacity-40 group-data-[state=active]:opacity-100 group-data-[state=active]:text-primary transition-all group-data-[state=active]:scale-110" />
                                {{ getGroupInfo(group).label }}
                                <div v-if="form.isDirty && settings.some(s => s.group === group && form[s.key] !== s.value)" class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                            </TabsTrigger>
                        </TabsList>

                        <div class="flex items-center gap-4 w-full xl:w-auto">
                            <div v-if="form.isDirty" class="hidden xl:flex items-center gap-2 text-amber-600 bg-amber-50 px-4 py-2 rounded-xl ring-1 ring-amber-100">
                                <AlertTriangle class="size-4" />
                                <span class="text-[10px] font-black uppercase tracking-widest">Unsaved Changes</span>
                            </div>
                            <Button 
                                type="submit" 
                                :disabled="form.processing || !form.isDirty"
                                class="h-14 px-10 w-full xl:w-auto rounded-2xl bg-primary text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-primary/25 hover:bg-primary/90 transition-all border-none active:scale-[0.98] disabled:opacity-50 group"
                            >
                                <template v-if="form.processing">
                                    <Loader2 class="w-5 h-5 mr-3 animate-spin" />
                                    Synchronizing...
                                </template>
                                <template v-else>
                                    <Save class="w-5 h-5 mr-3 group-hover:rotate-12 transition-transform" />
                                    Save Preferences
                                </template>
                            </Button>
                        </div>
                    </div>

                    <TabsContent v-for="group in groups" :key="group" :value="group" class="mt-0 focus-visible:outline-none focus-visible:ring-0 outline-none">
                        <div class="grid lg:grid-cols-12 gap-12">
                            <!-- Sidebar Helper -->
                            <div class="lg:col-span-4 space-y-8">
                                <Card class="rounded-[2.5rem] bg-slate-900 border-none shadow-2xl p-10 overflow-hidden relative group">
                                    <!-- Dynamic Gradient Background -->
                                    <div class="absolute -right-20 -top-20 opacity-20 blur-3xl w-64 h-64 rounded-full bg-primary/40 group-hover:scale-110 transition-transform duration-1000"></div>
                                    <div class="absolute -left-20 -bottom-20 opacity-10 blur-3xl w-48 h-48 rounded-full bg-indigo-400 group-hover:scale-125 transition-transform duration-1000"></div>
                                    
                                    <div class="relative z-10 space-y-8">
                                        <div class="p-4 rounded-[1.5rem] bg-white/10 w-fit backdrop-blur-md ring-1 ring-white/10 shadow-inner">
                                            <component :is="getGroupInfo(group).icon" class="h-8 w-8 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-black text-white capitalize tracking-tight">{{ group }} Hub</h3>
                                            <p class="text-white/50 font-bold mt-3 text-sm leading-relaxed">
                                                These settings control the engine of your rental platform. Every change is tracked and applied instantly upon saving.
                                            </p>
                                        </div>
                                        <div class="pt-8 border-t border-white/5">
                                            <div class="flex items-center gap-4 py-3 px-4 rounded-2xl bg-white/5 ring-1 ring-white/10">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                                                    <ShieldCheck class="size-6" />
                                                </div>
                                                <div>
                                                    <div class="text-[10px] font-black uppercase tracking-widest text-white/40">Security Status</div>
                                                    <div class="text-xs font-black text-white">Encrypted & Validated</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </Card>

                                <div class="p-8 rounded-[2.5rem] bg-indigo-50/30 ring-1 ring-indigo-100/40 border-none">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="p-2 rounded-xl bg-indigo-100 text-indigo-500">
                                            <Sparkles class="size-4" />
                                        </div>
                                        <h4 class="text-[11px] font-black uppercase tracking-widest text-indigo-400">Pro Tip</h4>
                                    </div>
                                    <p class="text-xs font-bold text-slate-500 leading-relaxed">Ensure your contact information is up to date to maintain trust with your customers and improve communication efficiency.</p>
                                </div>
                            </div>

                            <!-- Form Main Area -->
                            <Card class="lg:col-span-8 rounded-[2.5rem] bg-white border-none ring-1 ring-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
                                <CardHeader class="p-10 border-b border-slate-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <CardTitle class="text-2xl font-black text-slate-900 capitalize">{{ group }} Global Variables</CardTitle>
                                            <CardDescription class="font-bold text-slate-400 mt-2">
                                                Update the core parameters for the <span class="text-primary font-black uppercase tracking-widest text-[10px]">{{ group }}</span> group.
                                            </CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent class="p-10 space-y-12">
                                    <div v-for="setting in settings.filter(s => s.group === group)" :key="setting.key" class="group/field animate-in fade-in slide-in-from-bottom-2 duration-500">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center gap-2">
                                                <Label 
                                                    :for="setting.key" 
                                                    class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1 group-focus-within/field:text-primary transition-colors"
                                                >
                                                    {{ setting.display_name }}
                                                </Label>
                                                
                                                <HelpTooltip 
                                                    v-if="setting.description" 
                                                    :content="setting.description" 
                                                />
                                            </div>
                                            <div class="h-px flex-1 mx-6 bg-slate-50"></div>
                                            <div class="p-2 rounded-xl bg-slate-50 text-slate-400 group-hover/field:bg-primary/10 group-hover/field:text-primary transition-all">
                                                <component :is="getSettingIcon(setting.key)" class="size-4" />
                                            </div>
                                        </div>
                                        
                                        <div class="relative group/input">
                                            <template v-if="setting.type === 'number'">
                                                <Input 
                                                    type="number" 
                                                    step="any"
                                                    v-model="form[setting.key]"
                                                    :placeholder="getPlaceholder(setting.key)"
                                                    class="h-16 rounded-2xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all text-xl pl-6 pr-12 shadow-sm placeholder:text-slate-300"
                                                />
                                            </template>
                                            
                                            <template v-else-if="setting.type === 'textarea'">
                                                <Textarea 
                                                    v-model="form[setting.key]"
                                                    :placeholder="getPlaceholder(setting.key)"
                                                    class="min-h-[180px] rounded-2xl bg-slate-50 border-none font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all p-6 text-base shadow-sm placeholder:text-slate-300"
                                                />
                                            </template>
                                            
                                            <template v-else>
                                                <Input 
                                                    type="text" 
                                                    v-model="form[setting.key]"
                                                    :placeholder="getPlaceholder(setting.key)"
                                                    class="h-16 rounded-2xl bg-slate-50 border-none font-black text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-ring transition-all text-base pl-6 pr-12 shadow-sm placeholder:text-slate-300"
                                                />
                                            </template>

                                            <!-- Field Status Indicator -->
                                            <div class="absolute right-5 top-1/2 -translate-y-1/2 flex items-center gap-2 pointer-events-none opacity-0 group-focus-within/input:opacity-100 transition-opacity">
                                                <div class="h-1.5 w-1.5 rounded-full bg-primary animate-pulse"></div>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-primary">Editing</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>
                </Tabs>
            </form>
        </main>
    </AdminLayout>
</template>

<style scoped>
/* Scoped styles removed to avoid Tailwind 4 @reference issues */
</style>
