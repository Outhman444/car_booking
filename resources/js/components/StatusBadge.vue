<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: string;
    label?: string;
    color?: string; // Optional hex color code passed from backend statuses
}>();

const calculatedStyle = computed(() => {
    // If a hex color is explicitly provided, convert it to a custom RGBA background
    if (props.color) {
        const hex = props.color.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16) || 107;
        const g = parseInt(hex.substring(2, 4), 16) || 114;
        const b = parseInt(hex.substring(4, 6), 16) || 128;
        
        return {
            bg: `rgba(${r}, ${g}, ${b}, 0.1)`,
            text: `text-[${props.color}]`,
            dot: props.color
        };
    }

    // Default Gray fallback
    return {
        bg: 'rgba(107, 114, 128, 0.1)',
        text: 'text-gray-500',
        dot: '#6B7280'
    };
});
</script>

<template>
    <span
        class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-medium"
        :style="{
            backgroundColor: calculatedStyle.bg,
            color: calculatedStyle.text,
        }"
    >
        <span
            class="size-2 rounded-full"
            :style="{ backgroundColor: calculatedStyle.dot }"
        />
        {{ label || status }}
    </span>
</template>
