<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Customers', href: '#' }] } });

const props = defineProps<{ customers: { data: any[]; links: any; meta: any } }>();
</script>

<template>
    <Head title="Admin — Customers" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Customers</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="px-4 py-3 text-neutral-500">Name</th><th class="px-4 py-3 text-neutral-500">Email</th><th class="px-4 py-3 text-neutral-500">Role</th><th class="px-4 py-3 text-neutral-500">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in customers.data" :key="c.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="px-4 py-3"><Link :href="`/admin/customers/${c.id}`" class="text-blue-600 dark:text-blue-400">{{ c.name }}</Link></td>
                        <td class="px-4 py-3 text-neutral-500">{{ c.email }}</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-700">{{ c.role?.name }}</span></td>
                        <td class="px-4 py-3 text-neutral-500">{{ new Date(c.created_at).toLocaleDateString() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>