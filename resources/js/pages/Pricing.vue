<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Check } from '@lucide/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    hostingPlans: any[];
    vpsPlans: any[];
    domainTlds: any[];
}>();

const formatPrice = (price: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
const formatMb = (mb: number) => (mb >= 1024 ? `${(mb / 1024).toFixed(0)} GB` : `${mb} MB`);
const aMonth = (p: any) => p.prices?.find((x: any) => x.billing_cycle === 'monthly');
</script>

<template>
    <Head title="Pricing — Harga Hosting, Domain & VPS" />

    <section class="bg-gradient-to-b from-blue-50 to-white py-16 dark:from-blue-950 dark:to-neutral-950">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Harga Transparan</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Tidak ada biaya tersembunyi. Pilih sesuai kebutuhan Anda.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-2xl font-bold text-neutral-900 dark:text-white">Web Hosting</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800">
                            <th class="pb-3 text-left font-semibold text-neutral-900 dark:text-white">Fitur</th>
                            <th v-for="plan in hostingPlans" :key="plan.id" class="pb-3 px-4 text-center font-semibold text-neutral-900 dark:text-white">{{ plan.name }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">Harga /bulan</td>
                            <td v-for="plan in hostingPlans" :key="'p-'+plan.id" class="py-3 px-4 text-center font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</td>
                        </tr>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">SSD Storage</td>
                            <td v-for="plan in hostingPlans" :key="'s-'+plan.id" class="py-3 px-4 text-center text-neutral-600 dark:text-neutral-400">{{ formatMb(plan.hosting_plan?.disk_space_mb ?? 0) }}</td>
                        </tr>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">Website</td>
                            <td v-for="plan in hostingPlans" :key="'w-'+plan.id" class="py-3 px-4 text-center text-neutral-600 dark:text-neutral-400">{{ plan.hosting_plan?.max_websites === 0 ? 'Unlimited' : plan.hosting_plan?.max_websites }}</td>
                        </tr>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">Database</td>
                            <td v-for="plan in hostingPlans" :key="'d-'+plan.id" class="py-3 px-4 text-center text-neutral-600 dark:text-neutral-400">{{ plan.hosting_plan?.max_databases === 0 ? 'Unlimited' : plan.hosting_plan?.max_databases }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2 class="mb-8 mt-16 text-2xl font-bold text-neutral-900 dark:text-white">Cloud VPS</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800">
                            <th class="pb-3 text-left font-semibold text-neutral-900 dark:text-white">Spesifikasi</th>
                            <th v-for="plan in vpsPlans" :key="plan.id" class="pb-3 px-4 text-center font-semibold text-neutral-900 dark:text-white">{{ plan.name }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">Harga /bulan</td>
                            <td v-for="plan in vpsPlans" :key="'vp-'+plan.id" class="py-3 px-4 text-center font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</td>
                        </tr>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">CPU</td>
                            <td v-for="plan in vpsPlans" :key="'vc-'+plan.id" class="py-3 px-4 text-center text-neutral-600 dark:text-neutral-400">{{ plan.vps_plan?.cpu_cores }} Core</td>
                        </tr>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">RAM</td>
                            <td v-for="plan in vpsPlans" :key="'vr-'+plan.id" class="py-3 px-4 text-center text-neutral-600 dark:text-neutral-400">{{ formatMb(plan.vps_plan?.ram_mb ?? 0) }}</td>
                        </tr>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 text-neutral-600 dark:text-neutral-400">SSD</td>
                            <td v-for="plan in vpsPlans" :key="'vd-'+plan.id" class="py-3 px-4 text-center text-neutral-600 dark:text-neutral-400">{{ formatMb(plan.vps_plan?.disk_mb ?? 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2 class="mb-8 mt-16 text-2xl font-bold text-neutral-900 dark:text-white">Domain</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800">
                            <th class="pb-3 text-left font-semibold text-neutral-900 dark:text-white">TLD</th>
                            <th class="pb-3 text-center font-semibold text-neutral-900 dark:text-white">Registrasi</th>
                            <th class="pb-3 text-center font-semibold text-neutral-900 dark:text-white">Perpanjang</th>
                            <th class="pb-3 text-center font-semibold text-neutral-900 dark:text-white">Transfer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tld in domainTlds" :key="tld.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                            <td class="py-3 font-medium text-neutral-900 dark:text-white">{{ tld.tld }}</td>
                            <td class="py-3 text-center text-neutral-600 dark:text-neutral-400">{{ formatPrice(tld.registration_price) }}</td>
                            <td class="py-3 text-center text-neutral-600 dark:text-neutral-400">{{ formatPrice(tld.renewal_price) }}</td>
                            <td class="py-3 text-center text-neutral-600 dark:text-neutral-400">{{ formatPrice(tld.transfer_price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>