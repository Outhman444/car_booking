<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import HomeLayout from '@/layouts/HomeLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, UserPlus, Mail, Lock, User, ArrowRight } from 'lucide-vue-next';
import HelpTooltip from '@/components/HelpTooltip.vue';
</script>

<template>
    <HomeLayout>
        <Head title="Register" />

        <div class="flex min-h-[90vh] items-center justify-center px-4 sm:px-6 lg:px-8 py-12 bg-slate-50/50">
            <div class="w-full max-w-[520px] space-y-10">
                <!-- Header Section -->
                <div class="text-center space-y-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-xl shadow-slate-200 ring-1 ring-slate-100">
                        <UserPlus class="size-8 text-slate-900" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 leading-tight">Créer un <span class="text-slate-500">Compte</span></h1>
                        <p class="mt-3 text-base font-bold text-slate-400 uppercase tracking-widest">Rejoignez notre flotte de location de voitures premium</p>
                    </div>
                </div>

                <!-- Main Register Card -->
                <Card class="overflow-hidden rounded-[2.5rem] border-none bg-white p-2 shadow-2xl shadow-slate-200/60 ring-1 ring-slate-100">
                    <CardContent class="p-8 sm:p-10">
                        <Form
                            v-bind="RegisteredUserController.store.form()"
                            :reset-on-success="[
                                'password',
                                'password_confirmation',
                            ]"
                            v-slot="{ errors, processing }"
                            class="space-y-6"
                        >
                            <!-- Name Field -->
                            <div class="space-y-3">
                                <Label for="name" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-2">
                                    Nom Complet
                                    <HelpTooltip content="Entrez votre nom légal tel qu'il apparaît sur vos documents d'identité pour la vérification de l'assurance." />
                                </Label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <User class="size-5" />
                                    </div>
                                    <Input
                                        id="name"
                                        type="text"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="name"
                                        name="name"
                                        placeholder="John Doe"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.name" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <!-- Email Field -->
                            <div class="space-y-3">
                                <Label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-2">
                                    Adresse E-mail
                                    <HelpTooltip content="Ceci sera utilisé pour tous les contrats de location et confirmations de paiement." />
                                </Label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                        <Mail class="size-5" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        required
                                        :tabindex="2"
                                        autocomplete="email"
                                        name="email"
                                        placeholder="name@example.com"
                                        class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                    />
                                </div>
                                <InputError :message="errors.email" class="mt-2 text-xs font-bold text-rose-500" />
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <!-- Password Field -->
                                <div class="space-y-3">
                                    <Label for="password" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-2">
                                        Mot de passe
                                        <HelpTooltip content="Utilisez au moins 8 caractères. Sécurisez votre compte pour protéger votre historique de location." />
                                    </Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <Lock class="size-5" />
                                        </div>
                                        <Input
                                            id="password"
                                            type="password"
                                            required
                                            :tabindex="3"
                                            autocomplete="new-password"
                                            name="password"
                                            placeholder="••••••••"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                        />
                                    </div>
                                    <InputError :message="errors.password" class="mt-2 text-xs font-bold text-rose-500" />
                                </div>

                                <!-- Confirm Password Field -->
                                <div class="space-y-3">
                                    <Label for="password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">
                                        Confirmer
                                    </Label>
                                    <div class="relative group">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                            <Lock class="size-5" />
                                        </div>
                                        <Input
                                            id="password_confirmation"
                                            type="password"
                                            required
                                            :tabindex="4"
                                            autocomplete="new-password"
                                            name="password_confirmation"
                                            placeholder="••••••••"
                                            class="h-14 w-full rounded-2xl border-none bg-slate-50 pl-14 pr-6 font-bold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-slate-900 transition-all placeholder:text-slate-300"
                                        />
                                    </div>
                                    <InputError :message="errors.password_confirmation" class="mt-2 text-xs font-bold text-rose-500" />
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                class="h-16 w-full rounded-2xl bg-slate-900 text-base font-black uppercase tracking-widest text-white shadow-2xl shadow-slate-200 hover:bg-slate-800 transition-all border-none active:scale-[0.98] disabled:opacity-50"
                                tabindex="5"
                                :disabled="processing"
                                data-test="register-user-button"
                            >
                                <LoaderCircle v-if="processing" class="mr-2 size-5 animate-spin" />
                                <span v-else class="flex items-center gap-3">
                                    Create Account <ArrowRight class="size-5" />
                                </span>
                            </Button>

                            <!-- Login Link -->
                            <div class="pt-4 text-center">
                                <p class="text-sm font-bold text-slate-400">
                                    Vous avez déjà un compte ?
                                    <TextLink
                                        :href="login()"
                                        class="ml-2 font-black text-slate-900 hover:text-slate-600 border-b-2 border-slate-900/10 hover:border-slate-900 transition-all"
                                        :tabindex="6"
                                    >
                                        Se Connecter
                                    </TextLink>
                                </p>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Footer Info -->
                <div class="text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed max-w-[360px] mx-auto">
                        En créant un compte, vous acceptez nos <a href="#" class="text-slate-900 hover:underline">Conditions d'Utilisation</a> et notre <a href="#" class="text-slate-900 hover:underline">Politique de Confidentialité</a>
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

