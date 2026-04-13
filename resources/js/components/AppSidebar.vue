<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as carsIndex } from "@/routes/admin/cars/index";
import { index as reservationsIndex } from "@/routes/admin/reservations/index";
import { index as clientsIndex } from "@/routes/admin/clients/index";
import { index as paymentsIndex } from "@/routes/admin/payments/index";
import { index as reportsIndex } from "@/routes/admin/reports/index";
import { index as supportIndex } from "@/routes/admin/support/index";
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Car, Calendar, User, CreditCard, BarChart, LifeBuoy, Wallet, Settings, MapPin, Settings2 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { home } from '@/routes';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index as clientReservationsIndex } from "@/routes/client/reservations/index";
import { index as clientSupportIndex } from "@/routes/client/support/index";

const $page = usePage();
const userRole = computed(() => $page.props.auth.user?.role);

const managementItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: reportsIndex().url,
        icon: BarChart,
    },
    {
        title: 'Cars',
        href: carsIndex().url,
        icon: Car,
    },
    {
        title: 'Reservations',
        href: reservationsIndex().url,
        icon: Calendar,
    },
    {
        title: 'Clients',
        href: clientsIndex().url,
        icon: User,
    },
    {
        title: 'Payments',
        href: paymentsIndex().url,
        icon: CreditCard,
    },
    {
        title: 'Support',
        href: supportIndex().url,
        icon: LifeBuoy,
    },
]);

const systemItems = computed<NavItem[]>(() => [
    {
        title: 'Locations',
        href: '/admin/locations',
        icon: MapPin,
    },
    {
        title: 'Site Settings',
        href: '/admin/settings',
        icon: Settings2,
    },
    {
        title: 'Payment Methods',
        href: '/admin/payment-methods',
        icon: Wallet,
    },
]);

const clientItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: clientReservationsIndex().url,
        icon: BarChart,
    },
    {
        title: 'Support',
        href: clientSupportIndex().url,
        icon: LifeBuoy,
    },
]);

const profileItems = computed<NavItem[]>(() => [
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: Settings,
    },
]);

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="home()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <template v-if="userRole === 'admin'">
                <NavMain title="Management" :items="managementItems" />
                <NavMain title="Configuration" :items="systemItems" />
                <NavMain title="Account" :items="profileItems" />
            </template>
            <template v-else>
                <NavMain title="Platform" :items="clientItems" />
                <NavMain title="Account" :items="profileItems" />
            </template>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
