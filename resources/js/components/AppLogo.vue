<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const $page = usePage();
const settings = computed(() => ($page.props.settings as Record<string, string>) || {});
const siteName = computed(() => settings.value.site_name || 'Real Rent Car');

const siteNameParts = computed(() => {
    const text = siteName.value;
    if (text.toUpperCase().includes(' RENT ')) {
        const parts = text.split(/ rent /i);
        return { first: parts[0], middle: 'Rent', last: parts[1] };
    }
    return { first: text, middle: '', last: '' };
});
</script>

<template>
    <div class="flex items-center gap-3">
        <AppLogoIcon class="h-10 w-10 text-primary" />
        <div class="flex flex-col">
            <span class="text-xl font-black tracking-tight text-slate-900 leading-none">
                {{ siteNameParts.first.toUpperCase() }}<span class="text-slate-500 underline decoration-slate-200 decoration-4 underline-offset-4">{{ siteNameParts.middle.toUpperCase() }}</span>{{ siteNameParts.last.toUpperCase() }}
            </span>
            <span class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400 leading-none mt-1.5 flex items-center gap-2">
                <span class="h-px w-4 bg-slate-200"></span>
                PREMIUM FLEET
            </span>
        </div>
    </div>
</template>
