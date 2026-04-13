<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    title: string;
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-3 py-4">
        <SidebarGroupLabel class="text-xs font-black uppercase tracking-widest text-muted-foreground/70 mb-2 px-2">
            {{ title }}
        </SidebarGroupLabel>
        <SidebarMenu class="gap-1">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                    class="h-11 px-3"
                >
                    <Link :href="item.href" class="flex items-center gap-3">
                        <component :is="item.icon" class="size-5" />
                        <span class="text-base font-semibold">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
