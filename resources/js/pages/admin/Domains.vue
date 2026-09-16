<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Domains', href: '#' }] } });
const props = defineProps<{ domains: { data: any[]; links: any; meta: any } }>();
</script>
<template>
    <Head title="Admin — Domains" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">All Domains</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Domain</th><th class="px-4 py-3 text-neutral-500">Customer</th><th class="px-4 py-3 text-neutral-500">Status</th><th class="px-4 py-3 text-neutral-500">Expires</th></tr></thead>
                <tbody><tr v-for="d in domains.data" :key="d.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="px-4 py-3"><Link :href="`/admin/domains/${d.id}`" class="text-blue-600 dark:text-blue-400">{{ d.domain_name }}</Link></td><td class="px-4 py-3 text-neutral-500">{{ d.service?.user?.name }}</td><td class="px-4 py-3 text-neutral-500">{{ d.status }}</td><td class="px-4 py-3 text-neutral-500">{{ d.expiration_date }}</td></tr></tbody>
            </table>
        </div>
    </div>
</template>