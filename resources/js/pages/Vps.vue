<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Cpu, HardDrive, Network, Server } from '@lucide/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ plans: any[] }>();

const formatPrice = (price: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
const formatMb = (mb: number) => (mb >= 1024 ? `${(mb / 1024).toFixed(0)} GB` : `${mb} MB`);
const aMonth = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'monthly');
const aYear = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'annually');
</script>

<template>
    <Head title="Cloud VPS — Full Root Access, KVM Virtualization" />

    <section class="bg-gradient-to-b from-blue-50 to-white py-16 dark:from-blue-950 dark:to-neutral-950">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Cloud VPS</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Virtual Private Server dengan KVM virtualization, full root access, dan provisioning otomatis via Proxmox</p>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-4">
                <div v-for="plan in plans" :key="plan.id" class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ plan.name }}</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ plan.description }}</p>

                    <div class="mt-4 space-y-2 text-sm text-neutral-600 dark:text-neutral-400">
                        <div class="flex items-center gap-2">
                            <Cpu class="h-4 w-4 text-blue-600" />
                            <span>{{ plan.vps_plan?.cpu_cores }} vCPU Core</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Server class="h-4 w-4 text-blue-600" />
                            <span>{{ formatMb(plan.vps_plan?.ram_mb ?? 0) }} RAM</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <HardDrive class="h-4 w-4 text-blue-600" />
                            <span>{{ formatMb(plan.vps_plan?.disk_mb ?? 0) }} SSD</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Network class="h-4 w-4 text-blue-600" />
                            <span>{{ plan.vps_plan?.ipv4_count }} IPv4 + {{ plan.vps_plan?.ipv6_count }} IPv6</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <span class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">/bln</span>
                    </div>
                    <p v-if="aYear(plan)" class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatPrice(aYear(plan).price) }}/tahun</p>

                    <a href="#" class="mt-4 block w-full rounded-lg bg-blue-600 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700">Pilih</a>
                </div>
            </div>
        </div>
    </section>
</template>