<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'brand';
}

withDefaults(defineProps<Props>(), {
    size: 'md',
});
</script>

<template>
    <div 
        :class="[
            'space-y-1.5',
            { 'mb-8': size === 'md' || size === 'lg' }
        ]"
    >
        <h2 
            :class="[
                'font-black tracking-tight text-slate-900',
                {
                    'text-xl': size === 'sm' || size === 'md',
                    'text-3xl': size === 'lg',
                    'text-5xl md:text-6xl': size === 'brand',
                }
            ]"
        >
            {{ title }}
        </h2>
        <p 
            v-if="description" 
            :class="[
                'text-muted-foreground',
                {
                    'text-sm font-bold uppercase tracking-widest text-slate-400': size === 'sm',
                    'text-sm': size === 'md',
                    'text-lg font-medium': size === 'lg' || size === 'brand',
                }
            ]"
        >
            {{ description }}
        </p>
    </div>
</template>
