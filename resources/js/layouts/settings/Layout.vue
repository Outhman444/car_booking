<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import { edit as editPassword } from '@/routes/password';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { User, Lock, ShieldCheck, Settings } from 'lucide-vue-next';

const sidebarNavItems = [
    {
        title: 'Identity Profile',
        subtitle: 'Personal details & mail',
        href: editProfile(),
        icon: User
    },
    {
        title: 'Access Secret',
        subtitle: 'Update your password',
        href: editPassword(),
        icon: Lock
    },
    {
        title: 'Two-Factor Shield',
        subtitle: 'Deep system security',
        href: show(),
        icon: ShieldCheck
    },
];

const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
</script>

<template>
    <div class="px-6 py-8 lg:px-12 lg:py-12 bg-white min-h-screen">
        <div class="mb-12 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-primary/10 rounded-xl text-primary">
                        <Settings class="size-6" />
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">Control <span class="text-slate-400">Panel</span></h1>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Account Governance & Security Management</p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:space-x-16">
            <aside class="w-full lg:w-72 mb-12 lg:mb-0">
                <nav class="flex flex-col space-y-3">
                    <Link 
                        v-for="item in sidebarNavItems" 
                        :key="toUrl(item.href)"
                        :href="toUrl(item.href)"
                        :class="[
                            'group flex items-center gap-5 p-5 rounded-[1.5rem] transition-all duration-300 ring-1',
                            urlIsActive(item.href, currentPath)
                                ? 'bg-slate-900 text-white ring-slate-900 shadow-2xl shadow-slate-200'
                                : 'bg-white text-slate-500 ring-slate-100 hover:bg-slate-50 hover:ring-slate-200'
                        ]"
                    >
                        <div :class="[
                            'flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.15rem] transition-all duration-300',
                            urlIsActive(item.href, currentPath)
                                ? 'bg-white/10 text-white'
                                : 'bg-slate-100 text-slate-400 group-hover:bg-primary group-hover:text-white group-hover:scale-110'
                        ]">
                            <component :is="item.icon" class="size-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm font-black uppercase tracking-wide', urlIsActive(item.href, currentPath) ? 'text-white' : 'text-slate-900']">{{ item.title }}</p>
                            <p :class="['text-[10px] font-bold uppercase tracking-widest mt-0.5', urlIsActive(item.href, currentPath) ? 'text-white/40' : 'text-slate-400']">{{ item.subtitle }}</p>
                        </div>
                    </Link>
                </nav>

                <div class="mt-12 p-8 rounded-[2rem] bg-slate-50 ring-1 ring-slate-100 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700">
                        <ShieldCheck class="size-32" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-4 relative z-10">Security Tip</p>
                    <p class="text-xs font-bold leading-relaxed text-slate-500 relative z-10">Regularly updating your security protocols ensures the integrity of your executive asset reservations.</p>
                </div>
            </aside>

            <div class="flex-1 pb-20">
                <div class="max-w-3xl">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
