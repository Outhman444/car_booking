<script setup lang="ts">
import { home, fleet, about, contact, login, register } from '@/routes';
import { index as adminCarsIndex } from '@/routes/admin/cars/index';
import { index as clientReservationsIndex } from '@/routes/client/reservations/index';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { Zap, Phone, Mail, Car as CarIcon } from 'lucide-vue-next';
import FlashNotifications from '@/components/FlashNotifications.vue';
import AppLogo from '@/components/AppLogo.vue';

const $page = usePage();
const auth = computed(() => $page.props.auth);
const role = computed(() => auth.value.user?.role);
const dashboardLink = computed(() => role.value === 'admin' ? adminCarsIndex() : clientReservationsIndex());
const settings = computed(() => ($page.props.settings as Record<string, string>) || {});
const siteName = computed(() => settings.value.site_name || 'REAL RENT CAR');
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
    <div class="relative min-h-screen flex flex-col bg-background font-sans text-foreground">
        <FlashNotifications />
        <header
            class="sticky top-0 z-50 border-b bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav class="flex h-16 items-center justify-between">
                    <!--  Logo -->
                    <div class="flex items-center">
                        <AppLogo class="h-8 w-auto" />
                    </div>

                    <!--  Navigation -->
                    <div class="hidden items-center space-x-8 md:flex">
                        <Link 
                            :href="home()" 
                            :class="{ 'text-primary font-semibold': $page.url === home().url, 'text-muted-foreground': $page.url !== home().url }" 
                            class="text-sm transition-colors hover:text-primary"
                        >
                            Accueil
                        </Link>
                        <Link 
                            :href="fleet()" 
                            :class="{ 'text-primary font-semibold': $page.url.startsWith('/fleet'), 'text-muted-foreground': !$page.url.startsWith('/fleet') }" 
                            class="text-sm transition-colors hover:text-primary"
                        >
                            Nos Voitures
                        </Link>
                        <Link 
                            :href="about()" 
                            :class="{ 'text-primary font-semibold': $page.url === '/about', 'text-muted-foreground': $page.url !== '/about' }" 
                            class="text-sm transition-colors hover:text-primary"
                        >
                            À Propos
                        </Link>
                        <Link 
                            :href="contact()" 
                            :class="{ 'text-primary font-semibold': $page.url === '/contact', 'text-muted-foreground': $page.url !== '/contact' }" 
                            class="text-sm transition-colors hover:text-primary"
                        >
                            Contact
                        </Link>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-4">
                        <div v-if="auth.user" class="flex items-center space-x-4">
                            <Link
                                :href="dashboardLink.url"
                                class="hidden md:inline-flex"
                            >
                                <Button variant="secondary" size="sm">
                                    Tableau de bord
                                </Button>
                            </Link>
                            
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="relative h-10 w-10 rounded-full focus-visible:ring-1 focus-visible:ring-ring"
                                    >
                                        <div class="relative group/avatar">
                                            <Avatar class="h-9 w-9 overflow-hidden rounded-xl ring-2 ring-slate-100 shadow-sm transition-all group-hover/avatar:ring-ring/40 group-hover/avatar:shadow-md">
                                                <AvatarImage
                                                    v-if="auth.user.avatar"
                                                    :src="auth.user.avatar"
                                                    :alt="auth.user.name"
                                                />
                                                <AvatarFallback class="bg-gradient-to-br from-slate-700 to-slate-900 text-white font-black text-xs tracking-tighter uppercase shadow-inner">
                                                    {{ getInitials(auth.user.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <!-- Online Status Indicator -->
                                            <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3 items-center justify-center rounded-full bg-white transition-transform group-hover/avatar:scale-110">
                                                <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                            </span>
                                        </div>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 mt-1">
                                    <UserMenuContent :user="auth.user" />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                            >
                                Se connecter
                            </Link>
                            <Button as-child size="sm" class="font-semibold">
                                <Link :href="register()">
                                    Commencer
                                </Link>
                            </Button>
                        </template>
                    </div>
                </nav>
            </div>
        </header>

        <div class="flex-1">
            <slot />
        </div>

        <!-- Footer -->
        <footer class="border-t bg-muted/40">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-4 lg:gap-8">
                    <!-- Brand Section -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                                <Zap class="size-5 text-primary-foreground" />
                            </div>
                            <div>
                                <h3 class="text-xl font-bold tracking-tight text-foreground">
                                    {{ siteNameParts.first }}<span class="text-primary">{{ siteNameParts.middle }}</span>{{ siteNameParts.last }}
                                </h3>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mt-0.5">Excellence Authentique</p>
                            </div>
                        </div>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            {{ settings.footer_description || "Depuis 2015, nous avons redéfini le transport haut de gamme en mêlant des véhicules performants à un engagement sans compromis envers la satisfaction du client." }}
                        </p>
                    </div>

                    <!-- Services -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-semibold text-foreground">Écosystème de la Flotte</h4>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li><Link :href="fleet.url()" class="transition-colors hover:text-primary">Luxe & Sport</Link></li>
                            <li><Link :href="fleet.url()" class="transition-colors hover:text-primary">Mobilité Executive</Link></li>
                            <li><Link :href="fleet.url()" class="transition-colors hover:text-primary">Locations Longue Durée</Link></li>
                            <li><Link :href="fleet.url()" class="transition-colors hover:text-primary">Conciergerie Aéroport</Link></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-semibold text-foreground">Ressources</h4>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li><Link :href="contact.url()" class="transition-colors hover:text-primary">Support Global</Link></li>
                            <li><a href="#" class="transition-colors hover:text-primary">Conditions de Location</a></li>
                            <li><a href="#" class="transition-colors hover:text-primary">Protocole de Confidentialité</a></li>
                            <li><a href="#" class="transition-colors hover:text-primary">Normes de Sécurité</a></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-semibold text-foreground">Contactez-nous</h4>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                <Phone class="size-4 text-primary" />
                                <span>{{ settings.contact_phone || '+1 (555) 123-4567' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                <Mail class="size-4 text-primary" />
                                <span>{{ settings.contact_email || 'hello@realrent.com' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 border-t pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-muted-foreground">
                        {{ settings.footer_text || `© ${new Date().getFullYear()} ${siteName}. Tous droits réservés.` }}
                    </p>
                    <div class="flex items-center gap-6 text-xs text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            Système Opérationnel
                        </div>
                        <span class="hidden sm:inline-block">Certifié ISO 9001</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
