<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Server } from '@lucide/vue';
defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'My Hosting', href: '#' }] } });
const props = defineProps<{ services: { data: any[]; links: any; meta: any } }>();
</script>
<template>
    <Head title="My Hosting" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">My Hosting</h1>
        <div v-if="services.data.length === 0" class="py-16 text-center">
            <Server class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Belum ada layanan hosting.</p>
            <Link href="/hosting" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat paket hosting</Link>
        </div>
        <div v-else class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Domain</th><th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Plan</th><th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Server</th><th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Status</th></tr></thead>
                <tbody><tr v-for="s in services.data" :key="s.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="px-4 py-3"><Link :href="`/customer/hosting/${s.id}`" class="text-blue-600 dark:text-blue-400">{{ s.domain ?? '—' }}</Link></td><td class="px-4 py-3 text-neutral-500">{{ s.plan?.product?.name }}</td><td class="px-4 py-3 text-neutral-500">{{ s.server?.name }}</td><td class="px-4 py-3"><span :class="s.service?.status === 'active' ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800' : 'rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-800'">{{ s.service?.status }}</span></td></tr></tbody>
            </table>
        </div>
    </div>
</template>