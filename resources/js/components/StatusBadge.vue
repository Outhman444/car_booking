<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: string;
    label?: string;
    color?: string; // Optional hex color code passed from backend statuses
}>();

const calculatedStyle = computed(() => {
    // If a hex color is explicitly provided, we'll use it for the theme
    const hex = props.color?.replace('#', '') || '6B7280';
    const r = parseInt(hex.substring(0, 2), 16) || 107;
    const g = parseInt(hex.substring(2, 4), 16) || 114;
    const b = parseInt(hex.substring(4, 6), 16) || 128;
    
    // Create a very light version for the background
    return {
        bg: `rgba(${r}, ${g}, ${b}, 0.1)`,
        text: `rgb(${r}, ${g}, ${b})`,
        dot: `rgb(${r}, ${g}, ${b})`,
        border: `rgba(${r}, ${g}, ${b}, 0.2)`
    };
});
</script>

<template>
    <span
        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset transition-all"
        :style="{
            backgroundColor: calculatedStyle.bg,
            color: calculatedStyle.text,
            boxShadow: `inset 0 0 0 1px ${calculatedStyle.border}`
        }"
    >
        <span
            class="size-1.5 rounded-full shadow-sm animate-pulse"
            :style="{ backgroundColor: calculatedStyle.dot }"
        />
        {{ label || status }}
    </span>
</template>
