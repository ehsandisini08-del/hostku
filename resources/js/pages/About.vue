<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Server, Users, Globe, Shield, Rocket } from '@lucide/vue';

defineOptions({ layout: PublicLayout });
</script>

<template>
    <Head title="About — HostKu" />

    <section class="py-16">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Tentang HostKu</h1>
            <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-400">Kami adalah penyedia layanan hosting, domain, dan VPS yang berkomitmen memberikan performa terbaik dengan harga terjangkau untuk pelaku bisnis dan developer di Indonesia.</p>

            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-800">
                    <Server class="h-8 w-8 text-blue-600" />
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">Infrastruktur Modern</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Server enterprise dengan SSD NVMe, Proxmox virtualization, dan jaringan redundant untuk performa maksimal.</p>
                </div>
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-800">
                    <Rocket class="h-8 w-8 text-blue-600" />
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">Provisioning Otomatis</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Layanan aktif dalam hitungan menit setelah pembayaran dikonfirmasi, tanpa intervensi manual.</p>
                </div>
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-800">
                    <Shield class="h-8 w-8 text-blue-600" />
                    <h3 class="mt-4 font-semibold text-neutral-900 dark:text-white">Keamanan Prioritas</h3>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">SSL gratis, DDoS protection, firewall, backup otomatis, dan monitoring 24/7 untuk semua layanan.</p>
                </div>
            </div>
        </div>
    </section>
</template>