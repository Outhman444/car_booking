<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { fleet, contact } from '@/routes';
import { 
    Users, 
    CalendarCheck, 
    Zap, 
    ArrowRight, 
    Handshake,
    ThumbsUp,
    ShieldCheck, 
    Star, 
    Target, 
    CheckCircle2,
    Trophy,
    Globe
} from 'lucide-vue-next';
import { 
    Card, 
    CardContent, 
    CardHeader, 
    CardTitle, 
    CardDescription 
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

</script>

<template>
    <HomeLayout>
        <div class="min-h-screen bg-white">
            <!-- Hero Section -->
            <section class="relative overflow-hidden bg-slate-900 py-32 text-white">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.1) 1px, transparent 0); background-size: 60px 60px;"></div>
                </div>
                <!-- Animated Background Blobs -->
                <div class="absolute left-0 top-0 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[500px] bg-primary/20 blur-[120px] rounded-full"></div>
                <div class="absolute right-0 bottom-0 translate-x-1/2 translate-y-1/2 h-[500px] w-[500px] bg-blue-600/20 blur-[120px] rounded-full"></div>

                <div class="relative mx-auto max-w-7xl px-8 text-center sm:px-10 lg:px-12">
                    <Badge variant="secondary" class="mb-10 rounded-full bg-primary/20 py-2 px-6 text-[10px] font-black uppercase tracking-[0.3em] text-primary border-none shadow-none ring-1 ring-ring/30">
                        Operational Excellence
                    </Badge>
                    <h1 class="mb-10 text-6xl font-black tracking-tighter md:text-8xl lg:text-9xl leading-[0.9]">
                        About <span class="bg-gradient-to-r from-primary to-blue-400 bg-clip-text text-transparent">Real Rent</span>
                    </h1>
                    <p class="mx-auto max-w-3xl text-xl font-bold leading-relaxed text-slate-400 uppercase tracking-tight">
                        Defining the gold standard in premium mobility since 2015. We merge elite assets with sophisticated digital logistics.
                    </p>
                </div>
            </section>

            <div class="mx-auto max-w-7xl px-8 py-32 sm:px-10 lg:px-12">
                <!-- Our Narrative -->
                <div class="mb-40">
                    <div class="grid items-center gap-24 lg:grid-cols-2">
                        <div class="space-y-12">
                            <div class="space-y-6">
                                <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary">The Corporate Narrative</h2>
                                <h3 class="text-5xl font-black tracking-tighter text-slate-900 md:text-6xl leading-tight">Engineering the Future of Elite Mobility</h3>
                            </div>
                            <div class="space-y-8 text-xl font-bold leading-relaxed text-slate-500">
                                <p>
                                    Real Rent Car emerged from a singular imperative: to bridge the gap between traditional car rental and first-class travel experiences. What began as a curated collection of 10 executive vehicles has evolved into a tier-one fleet of over 200 premium assets.
                                </p>
                                <p>
                                    Our reputation is anchored in structural integrity, operational transparency, and a relentless pursuit of client success. Every reservation is treated as a mission-critical journey.
                                </p>
                            </div>
                            <div class="pt-8 scale-110 origin-left">
                                <Button as-child size="lg" class="h-16 rounded-[1.25rem] bg-slate-900 px-12 text-xs font-black uppercase tracking-[0.2em] text-white hover:bg-slate-800 transition-all shadow-2xl shadow-slate-200 border-none group">
                                    <a :href="fleet.url()" class="flex items-center gap-3">
                                        Browse Intelligence <ArrowRight class="size-4 group-hover:translate-x-1 transition-transform" />
                                    </a>
                                </Button>
                            </div>
                        </div>
                        
                        <div class="relative">
                            <div class="absolute -inset-4 bg-primary/5 rounded-[3rem] blur-2xl"></div>
                            <Card class="relative rounded-[3rem] border-none bg-white p-16 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.06)] ring-1 ring-slate-100 overflow-hidden">
                                <div class="absolute top-0 right-0 p-12 opacity-5">
                                    <Trophy class="size-48 text-slate-900" />
                                </div>
                                <div class="grid grid-cols-2 gap-16 text-center relative z-10">
                                    <div class="space-y-4">
                                        <div class="text-6xl font-black text-slate-900 tracking-tighter">200+</div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Elite Assets</div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="text-6xl font-black text-slate-900 tracking-tighter">50K+</div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Vetted Clients</div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="text-6xl font-black text-slate-900 tracking-tighter">15+</div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Global Nodes</div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="text-6xl font-black text-slate-900 tracking-tighter">09</div>
                                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Years Active</div>
                                    </div>
                                </div>
                            </Card>
                        </div>
                    </div>
                </div>

                <!-- Strategic Values -->
                <div class="mb-40">
                    <div class="mb-24 text-center">
                        <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary mb-8">Strategic Pillars</h2>
                        <h3 class="text-5xl font-black tracking-tighter text-slate-900 md:text-6xl">Principles Governing Our Operations</h3>
                    </div>

                    <div class="grid gap-12 md:grid-cols-3">
                        <Card class="group relative overflow-hidden rounded-[2.5rem] border-none bg-slate-50 p-12 shadow-sm ring-1 ring-slate-100 transition-all duration-700 hover:bg-white hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-4">
                            <CardHeader class="items-center pb-8 p-0">
                                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-primary shadow-xl ring-1 ring-slate-100 transition-all duration-700 group-hover:bg-primary group-hover:text-white group-hover:rotate-[360deg]">
                                    <ShieldCheck class="size-10" />
                                </div>
                                <CardTitle class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 group-hover:text-primary transition-colors">Safety Integrity</CardTitle>
                                <h4 class="mt-2 text-2xl font-black text-slate-900 tracking-tight">Reliability</h4>
                            </CardHeader>
                            <CardContent class="text-center p-0">
                                <p class="text-sm font-bold leading-relaxed text-slate-400">
                                    Every vehicle undergoes a 200-point diagnostic inspection before any deployment. Safety is non-negotiable.
                                </p>
                            </CardContent>
                        </Card>

                        <Card class="group relative overflow-hidden rounded-[2.5rem] border-none bg-slate-50 p-12 shadow-sm ring-1 ring-slate-100 transition-all duration-700 hover:bg-white hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-4">
                            <CardHeader class="items-center pb-8 p-0">
                                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-primary shadow-xl ring-1 ring-slate-100 transition-all duration-700 group-hover:bg-primary group-hover:text-white group-hover:scale-110">
                                    <Globe class="size-10" />
                                </div>
                                <CardTitle class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 group-hover:text-primary transition-colors">Global Standards</CardTitle>
                                <h4 class="mt-2 text-2xl font-black text-slate-900 tracking-tight">Transparency</h4>
                            </CardHeader>
                            <CardContent class="text-center p-0">
                                <p class="text-sm font-bold leading-relaxed text-slate-400">
                                    Zero hidden telemetry. We provide absolute clarity in pricing and logistics with zero operational ambiguity.
                                </p>
                            </CardContent>
                        </Card>

                        <Card class="group relative overflow-hidden rounded-[2.5rem] border-none bg-slate-50 p-12 shadow-sm ring-1 ring-slate-100 transition-all duration-700 hover:bg-white hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-4">
                            <CardHeader class="items-center pb-8 p-0">
                                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-primary shadow-xl ring-1 ring-slate-100 transition-all duration-700 group-hover:bg-primary group-hover:text-white group-hover:skew-x-12">
                                    <Star class="size-10" />
                                </div>
                                <CardTitle class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 group-hover:text-primary transition-colors">SaaS Grade Logic</CardTitle>
                                <h4 class="mt-2 text-2xl font-black text-slate-900 tracking-tight">Innovation</h4>
                            </CardHeader>
                            <CardContent class="text-center p-0">
                                <p class="text-sm font-bold leading-relaxed text-slate-400">
                                    Deploying next-gen booking algorithms and real-time fleet management to optimize your journey logistics.
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Distinction Section -->
                <div class="mb-40 rounded-[4rem] bg-slate-900 p-16 md:p-24 relative overflow-hidden text-white shadow-3xl">
                    <div class="absolute top-0 left-0 p-12 opacity-10">
                        <Zap class="size-64" />
                    </div>
                    <div class="relative z-10 grid gap-20 lg:grid-cols-2">
                        <div class="space-y-16">
                            <h3 class="text-5xl font-black tracking-tighter leading-none">Why the Industry <br /> Chooses Real Rent</h3>
                            <div class="space-y-12">
                                <div class="flex items-start gap-8 group">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-primary backdrop-blur-xl ring-1 ring-white/20 transition-all group-hover:bg-primary group-hover:text-white">
                                        <Zap class="size-8" />
                                    </div>
                                    <div>
                                        <h4 class="text-2xl font-black tracking-tight">Curated Fleet</h4>
                                        <p class="text-base font-bold text-slate-400 mt-2">Only the latest high-spec models from German and Italian luxury houses.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-8 group">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-primary backdrop-blur-xl ring-1 ring-white/20 transition-all group-hover:bg-primary group-hover:text-white">
                                        <Users class="size-8" />
                                    </div>
                                    <div>
                                        <h4 class="text-2xl font-black tracking-tight">24/7 Global Liaison</h4>
                                        <p class="text-base font-bold text-slate-400 mt-2">Personalized support agents available at any global coordinate, anytime.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/5 rounded-[3rem] p-12 backdrop-blur-sm ring-1 ring-white/10 flex flex-col justify-center gap-10">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-primary">Operational Metrics</h4>
                            <div class="space-y-10">
                                <div class="flex items-center gap-6">
                                    <div class="bg-primary/20 p-4 rounded-2xl text-primary"><Target class="size-7" /></div>
                                    <p class="text-xl font-bold text-white">Algorithm-Driven Safety Check</p>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="bg-primary/20 p-4 rounded-2xl text-primary"><Star class="size-7" /></div>
                                    <p class="text-xl font-bold text-white">Concierge Fleet Deployment</p>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="bg-primary/20 p-4 rounded-2xl text-primary"><CreditCard class="size-7" /></div>
                                    <p class="text-xl font-bold text-white">Encrypted FinTech Integration</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="text-center">
                    <p class="mb-8 text-[10px] font-black uppercase tracking-[0.5em] text-primary">Next Protocol</p>
                    <h2 class="mb-12 text-5xl font-black tracking-tighter text-slate-900 md:text-7xl">
                        Ready to Initiate <br /> Your Next <span class="text-primary italic">Statement?</span>
                    </h2>
                    <div class="flex flex-col justify-center gap-8 sm:flex-row items-center translate-y-4">
                        <Button as-child size="lg" class="h-20 rounded-[1.5rem] bg-primary px-16 text-xs font-black uppercase tracking-[0.2em] text-white hover:bg-blue-600 hover:scale-105 transition-all shadow-2xl shadow-primary/30 border-none group">
                            <a :href="fleet.url()" class="flex items-center gap-4">
                                Enter Fleet <ArrowRight class="size-5 group-hover:translate-x-1 transition-transform" />
                            </a>
                        </Button>
                        <Button as-child variant="link" class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-900 transition-colors">
                            <a :href="contact.url()">Speak with an Advisor</a>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
</style>
