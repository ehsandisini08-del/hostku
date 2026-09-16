<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Users', href: '#' }] } });
const props = defineProps<{ users: { data: any[]; links: any; meta: any } }>();
</script>

<template>
    <Head title="Admin — Users" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Admin Users</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Name</th><th class="px-4 py-3 text-neutral-500">Email</th><th class="px-4 py-3 text-neutral-500">Role</th></tr></thead>
                <tbody><tr v-for="u in users.data" :key="u.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="px-4 py-3 text-neutral-900 dark:text-white">{{ u.name }}</td><td class="px-4 py-3 text-neutral-500">{{ u.email }}</td><td class="px-4 py-3"><span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs text-purple-800">{{ u.role?.name }}</span></td></tr></tbody>
            </table>
        </div>
    </div>
</template>