<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ plans: any[] }>();

const formatPrice = (price: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
const formatMb = (mb: number) => (mb >= 1024 ? `${(mb / 1024).toFixed(0)} GB` : `${mb} MB`);
const aMonth = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'monthly');
const aYear = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'annually');
</script>

<template>
    <Head title="Web Hosting — Cepat, Aman, Murah" />

    <section class="bg-gradient-to-b from-blue-50 to-white py-16 dark:from-blue-950 dark:to-neutral-950">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Web Hosting Indonesia</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Hosting cepat dengan SSD NVMe, SSL gratis, dan cPanel untuk kemudahan pengelolaan</p>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <div v-for="plan in plans" :key="plan.id" class="flex flex-col rounded-2xl border-2 p-8" :class="plan.sort_order === 1 ? 'border-blue-600 dark:border-blue-400' : 'border-neutral-200 dark:border-neutral-800'">
                    <div v-if="plan.sort_order === 1" class="mb-4">
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Paling Populer</span>
                    </div>
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ plan.name }}</h3>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">{{ plan.description }}</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</span>
                        <span class="text-neutral-500 dark:text-neutral-400">/bulan</span>
                    </div>
                    <p v-if="aYear(plan)" class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ formatPrice(aYear(plan).price) }}/tahun (hemat)</p>
                    <ul class="mt-8 flex-1 space-y-3">
                        <li v-for="(feat, i) in plan.features" :key="i" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <span class="mt-0.5 h-4 w-4 flex-shrink-0 rounded-full bg-green-100 p-0.5 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="h-full w-full"><path d="M5 13l4 4L19 7" /></svg>
                            </span>
                            {{ feat }}
                        </li>
                    </ul>
                    <a href="#" class="mt-8 block w-full rounded-lg bg-blue-600 py-3 text-center font-medium text-white hover:bg-blue-700">Pilih Paket</a>
                </div>
            </div>
        </div>
    </section>
</template>