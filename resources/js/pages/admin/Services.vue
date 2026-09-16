<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Services', href: '#' }] } });
const props = defineProps<{ services: { data: any[]; links: any; meta: any } }>();
</script>

<template>
    <Head title="Admin — Services" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Services</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">ID</th><th class="px-4 py-3 text-neutral-500">Customer</th><th class="px-4 py-3 text-neutral-500">Type</th><th class="px-4 py-3 text-neutral-500">Status</th><th class="px-4 py-3 text-neutral-500">Expires</th></tr></thead>
                <tbody><tr v-for="s in services.data" :key="s.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                    <td class="px-4 py-3 text-neutral-900 dark:text-white">#{{ s.id }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ s.user?.name }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ s.serviceable_type?.split('\\').pop() }}</td>
                    <td class="px-4 py-3"><span :class="s.status === 'active' ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800' : 'rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-800'">{{ s.status }}</span></td>
                    <td class="px-4 py-3 text-neutral-500">{{ s.expired_at ?? '—' }}</td>
                </tr></tbody>
            </table>
        </div>
    </div>
</template>