<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { computed } from 'vue';

interface Props {
    title: string;
    value: string | number;
    description?: string;
    icon?: any;
    trend?: {
        value: string | number;
        type: 'up' | 'down' | 'neutral';
    };
    variant?: 'primary' | 'accent' | 'success' | 'warning' | 'destructive' | 'neutral';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'neutral',
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'from-blue-50 to-white ring-blue-100 dark:from-blue-950/20';
        case 'accent':
            return 'from-amber-50 to-white ring-amber-100 dark:from-amber-950/20';
        case 'success':
            return 'from-emerald-50 to-white ring-emerald-100 dark:from-emerald-950/20';
        case 'warning':
            return 'from-orange-50 to-white ring-orange-100 dark:from-orange-950/20';
        case 'destructive':
            return 'from-rose-50 to-white ring-rose-100 dark:from-rose-950/20';
        default:
            return 'from-slate-50 to-white ring-slate-100 dark:from-slate-900/20';
    }
});

const iconClasses = computed(() => {
    switch (props.variant) {
        case 'primary': return 'bg-blue-500/10 text-blue-600';
        case 'accent': return 'bg-amber-500/10 text-amber-600';
        case 'success': return 'bg-emerald-500/10 text-emerald-600';
        case 'warning': return 'bg-orange-500/10 text-orange-600';
        case 'destructive': return 'bg-rose-500/10 text-rose-600';
        default: return 'bg-slate-500/10 text-slate-600';
    }
});
</script>

<template>
    <Card 
        :class="cn(
            'overflow-hidden border-none shadow-xl shadow-slate-200/50 bg-gradient-to-br ring-1 rounded-[2rem] transition-all hover:shadow-2xl hover:shadow-slate-200/60',
            variantClasses
        )"
    >
        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0 p-6 pt-7">
            <CardTitle class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">
                {{ title }}
            </CardTitle>
            <div v-if="icon" :class="cn('h-10 w-10 rounded-2xl flex items-center justify-center', iconClasses)">
                <component :is="icon" class="h-5 w-5" />
            </div>
        </CardHeader>
        <CardContent class="p-6 pt-0 pb-8">
            <div class="text-3xl font-black tracking-tight text-slate-900">{{ value }}</div>
            
            <div v-if="description || trend" class="flex items-center gap-2 mt-2">
                <span 
                    v-if="trend" 
                    :class="cn(
                        'text-xs font-black px-2 py-0.5 rounded-full',
                        trend.type === 'up' ? 'bg-emerald-100 text-emerald-700' : 
                        trend.type === 'down' ? 'bg-rose-100 text-rose-700' : 
                        'bg-slate-100 text-slate-600'
                    )"
                >
                    {{ trend.type === 'up' ? '↑' : trend.type === 'down' ? '↓' : '' }} {{ trend.value }}
                </span>
                <p v-if="description" class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">
                    {{ description }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
