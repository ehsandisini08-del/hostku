<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Audit Logs', href: '#' }] } });
const props = defineProps<{ logs: { data: any[]; links: any; meta: any } }>();
</script>

<template>
    <Head title="Admin — Audit Logs" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Audit Logs</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Time</th><th class="px-4 py-3 text-neutral-500">User</th><th class="px-4 py-3 text-neutral-500">Action</th><th class="px-4 py-3 text-neutral-500">Resource</th></tr></thead>
                <tbody><tr v-for="log in logs.data" :key="log.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                    <td class="px-4 py-3 text-neutral-500">{{ new Date(log.created_at).toLocaleString() }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ log.user?.name ?? 'System' }}</td>
                    <td class="px-4 py-3 text-neutral-900 dark:text-white">{{ log.action }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ log.resource_type }} #{{ log.resource_id }}</td>
                </tr></tbody>
            </table>
        </div>
    </div>
</template>