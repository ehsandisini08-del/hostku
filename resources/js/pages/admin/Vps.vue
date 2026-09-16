<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'VPS', href: '#' }] } });
const props = defineProps<{ services: { data: any[]; links: any; meta: any } }>();
</script>
<template>
    <Head title="Admin — VPS" />
    <div class="flex flex-1 flex-col gap-4 p-4"><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">VPS Services</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950"><table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Hostname</th><th class="px-4 py-3 text-neutral-500">Customer</th><th class="px-4 py-3 text-neutral-500">Plan</th><th class="px-4 py-3 text-neutral-500">Status</th><th class="px-4 py-3 text-neutral-500">IP</th></tr></thead>
            <tbody><tr v-for="s in services.data" :key="s.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="px-4 py-3 text-neutral-900 dark:text-white">{{ s.hostname ?? 'VPS #'+s.id }}</td><td class="px-4 py-3 text-neutral-500">{{ s.service?.user?.name }}</td><td class="px-4 py-3 text-neutral-500">{{ s.plan?.product?.name }}</td><td class="px-4 py-3 text-neutral-500">{{ s.vm_status }}</td><td class="px-4 py-3 text-neutral-500">{{ s.ip_address ?? '—' }}</td></tr></tbody></table></div>
    </div>
</template>