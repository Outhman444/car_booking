<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LogOut, UserIcon, LayoutDashboard, Globe } from 'lucide-vue-next';
import { index as reportsIndex } from "@/routes/admin/reports/index";
import { index as clientReservationsIndex } from "@/routes/client/reservations/index";
import { home } from '@/routes';

interface Props {
    user: User;
}


defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem v-if="user.role === 'admin'" :as-child="true">
            <Link class="block w-full" :href="reportsIndex().url" prefetch as="button">
                <LayoutDashboard class="mr-2 h-4 w-4" />
                Panneau d'Administration
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-else :as-child="true">
            <Link class="block w-full" :href="clientReservationsIndex().url" prefetch as="button">
                <LayoutDashboard class="mr-2 h-4 w-4" />
                Mon Tableau de Bord
            </Link>
        </DropdownMenuItem>

        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="edit().url" prefetch as="button">
                <UserIcon class="mr-2 h-4 w-4" />
                Paramètres
            </Link>
        </DropdownMenuItem>

        <DropdownMenuItem v-if="user.role === 'admin'" :as-child="true">
            <Link class="block w-full" :href="home.url()" prefetch as="button">
                <Globe class="mr-2 h-4 w-4" />
                Visiter le Site
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full"
            :href="logout().url"
            method="post"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Déconnexion
        </Link>
    </DropdownMenuItem>
</template>
