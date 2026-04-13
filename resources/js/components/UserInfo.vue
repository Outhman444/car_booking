<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <div class="relative group/avatar inline-flex items-center">
        <div class="relative">
            <Avatar class="h-9 w-9 overflow-hidden rounded-xl ring-2 ring-slate-100 shadow-sm transition-all group-hover/avatar:ring-ring/20 shadow-slate-200/50">
                <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
                <AvatarFallback class="bg-gradient-to-br from-slate-700 to-slate-900 text-white font-black text-[10px] tracking-tighter uppercase shadow-inner">
                    {{ getInitials(user.name) }}
                </AvatarFallback>
            </Avatar>
            <!-- Online Status Indicator -->
            <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3 items-center justify-center rounded-full bg-white">
                <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
            </span>
        </div>

        <div class="grid flex-1 text-left text-sm leading-tight ml-2.5">
            <span class="truncate font-semibold text-slate-900 group-hover:text-primary transition-colors">{{ user.name }}</span>
            <span v-if="showEmail" class="truncate text-[10px] text-slate-500 font-medium tracking-wide">{{
                user.email
            }}</span>
        </div>
    </div>
</template>
