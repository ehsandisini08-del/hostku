<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Tickets', href: '#' }] } });
const props = defineProps<{ tickets: { data: any[]; links: any; meta: any } }>();
</script>

<template>
    <Head title="Admin — Tickets" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Support Tickets</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Subject</th><th class="px-4 py-3 text-neutral-500">Customer</th><th class="px-4 py-3 text-neutral-500">Priority</th><th class="px-4 py-3 text-neutral-500">Status</th></tr></thead>
                <tbody><tr v-for="t in tickets.data" :key="t.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                    <td class="px-4 py-3"><Link :href="`/admin/tickets/${t.id}`" class="text-blue-600 dark:text-blue-400">{{ t.subject }}</Link></td>
                    <td class="px-4 py-3 text-neutral-500">{{ t.user?.name }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ t.priority }}</td>
                    <td class="px-4 py-3"><span :class="t.status === 'open' ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800' : 'rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-800'">{{ t.status }}</span></td>
                </tr></tbody>
            </table>
        </div>
    </div>
</template>