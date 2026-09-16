<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { login, register } from '@/routes';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Search, ArrowRight, Shield, Clock, Zap, Users, Server, Globe } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    hostingPlans: any[];
    vpsPlans: any[];
    popularTlds: any[];
}>();

const domainQuery = ref('');
const aYear = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'annually');
const aMonth = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'monthly');

const formatPrice = (price: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
const formatMb = (mb: number) => mb >= 1024 ? `${(mb / 1024).toFixed(0)} GB` : `${mb} MB`;
</script>

<template>
    <Head title="HostKu — Hosting, Domain & VPS Murah Indonesia" />

    <section class="relative overflow-hidden bg-gradient-to-b from-blue-50 to-white pb-20 pt-16 dark:from-blue-950 dark:to-neutral-950 sm:pt-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl lg:text-6xl dark:text-white">Hosting Cepat, Domain Murah, VPS Handal</h1>
                <p class="mt-6 text-lg leading-relaxed text-neutral-600 lg:text-xl dark:text-neutral-400">Platform hosting Indonesia dengan performa tinggi, provisioning otomatis, dan dukungan 24/7. Mulai dari Rp 25.000/bulan.</p>
                <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <Link href="/hosting" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-medium text-white shadow-sm hover:bg-blue-700">Lihat Paket Hosting <ArrowRight class="h-4 w-4" /></Link>
                    <Link href="/pricing" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-6 py-3 font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">Bandingkan Harga</Link>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-neutral-900 dark:text-white">Cari Domain Impian Anda</h2>
            <p class="mt-2 text-center text-neutral-600 dark:text-neutral-400">Cek ketersediaan domain sebelum orang lain mengambilnya</p>
            <form class="mt-6 flex gap-2" @submit.prevent>
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-neutral-400" />
                    <input v-model="domainQuery" type="text" placeholder="Cari domain anda..." class="w-full rounded-lg border border-neutral-300 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-blue-400" />
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">Cari</button>
            </form>
            <div class="mt-4 flex flex-wrap justify-center gap-3">
                <span v-for="tld in popularTlds" :key="tld.id" class="rounded-full border border-neutral-200 px-3 py-1 text-xs text-neutral-600 dark:border-neutral-700 dark:text-neutral-400">
                    {{ tld.tld }} {{ formatPrice(tld.registration_price) }}
                </span>
            </div>
        </div>
    </section>

    <section class="bg-neutral-50 py-16 dark:bg-neutral-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Paket Web Hosting</h2>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Pilih paket yang sesuai dengan kebutuhan website Anda</p>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <div v-for="plan in hostingPlans" :key="plan.id" class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ plan.name }}</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ plan.description }}</p>
                    <div class="mt-4">
                        <span class="text-3xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">/bulan</span>
                    </div>
                    <ul class="mt-6 space-y-2">
                        <li v-for="(feat, i) in plan.features" :key="i" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <span class="mt-0.5 h-4 w-4 flex-shrink-0 rounded-full bg-green-100 p-0.5 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="h-full w-full"><path d="M5 13l4 4L19 7" /></svg>
                            </span>
                            {{ feat }}
                        </li>
                    </ul>
                    <Link href="/hosting" class="mt-6 block w-full rounded-lg bg-blue-600 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700">Pilih Paket</Link>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Cloud VPS</h2>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">VPS bertenaga dengan KVM virtualization dan full root access</p>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-4">
                <div v-for="plan in vpsPlans" :key="plan.id" class="rounded-xl border border-neutral-200 bg-white p-6 text-center shadow-sm transition-shadow hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">{{ plan.name }}</h3>
                    <div class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                        <p>{{ plan.vps_plan?.cpu_cores }} CPU / {{ formatMb(plan.vps_plan?.ram_mb ?? 0) }} RAM</p>
                        <p>{{ formatMb(plan.vps_plan?.disk_mb ?? 0) }} SSD</p>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">/bln</span>
                    </div>
                    <Link href="/vps" class="mt-4 block w-full rounded-lg border border-blue-600 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950">Detail</Link>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-neutral-50 py-16 dark:bg-neutral-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Kenapa Memilih HostKu?</h2>
            </div>
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <Zap class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">Performa Cepat</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Server enterprise SSD NVMe dengan CDN global.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <Shield class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">Keamanan Maksimal</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">SSL gratis, DDoS protection, dan backup otomatis.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <Clock class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">99.9% Uptime</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Infrastruktur redundant dengan SLA uptime tinggi.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <Users class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">Support 24/7</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Tim support siap membantu kapanpun, via ticket & chat.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Pertanyaan Umum</h2>
            </div>
            <div class="mx-auto mt-10 max-w-2xl divide-y divide-neutral-200 dark:divide-neutral-800">
                <details v-for="faq in [{ q: 'Apakah ada garansi uang kembali?', a: 'Ya, kami menyediakan garansi uang kembali 30 hari untuk layanan shared hosting.' }, { q: 'Bisa transfer domain dari provider lain?', a: 'Tentu! Kami menyediakan layanan transfer domain gratis dengan panduan lengkap.' }, { q: 'OS apa saja yang tersedia untuk VPS?', a: 'Ubuntu 22.04/24.04, Debian 12, CentOS 9 Stream, Rocky Linux 9, dan lainnya.' }]" :key="faq.q" class="group py-4">
                    <summary class="flex cursor-pointer items-center justify-between font-medium text-neutral-900 dark:text-white">
                        {{ faq.q }}
                        <svg class="h-5 w-5 text-neutral-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">{{ faq.a }}</p>
                </details>
            </div>
            <div class="mt-8 text-center">
                <Link href="/faq" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat semua FAQ <ArrowRight class="ml-1 inline h-3 w-3" /></Link>
            </div>
        </div>
    </section>

    <section class="bg-blue-600 py-16 dark:bg-blue-700">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Siap Memulai?</h2>
            <p class="mt-3 text-lg text-blue-100">Dapatkan hosting, domain, atau VPS Anda hari ini. Gratis migrasi!</p>
            <div class="mt-8 flex justify-center gap-4">
                <Link :href="register()" class="rounded-lg bg-white px-8 py-3 font-medium text-blue-600 shadow-sm hover:bg-blue-50">Daftar Sekarang</Link>
                <Link href="/pricing" class="rounded-lg border border-blue-300 px-8 py-3 font-medium text-white hover:bg-blue-500/20">Lihat Harga</Link>
            </div>
        </div>
    </section>
</template>