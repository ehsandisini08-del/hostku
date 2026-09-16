<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Server } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Services', href: '/customer/services' }] } });

const props = defineProps<{ services: { data: any[]; links: any; meta: any } }>();
const statusColor = (s: string) => {
    const m: Record<string, string> = { active: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', suspended: 'bg-red-100 text-red-800', terminated: 'bg-neutral-100 text-neutral-800', grace_period: 'bg-orange-100 text-orange-800', expiring: 'bg-orange-100 text-orange-800' };
    return m[s] ?? 'bg-neutral-100 text-neutral-800';
};
</script>

<template>
    <Head title="My Services" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">My Services</h1>
        </div>

        <div v-if="services.data.length === 0" class="py-16 text-center">
            <Server class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Anda belum memiliki layanan aktif.</p>
            <Link href="/hosting" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat paket hosting</Link>
        </div>

        <div v-else class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">ID</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Type</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Status</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Created</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Expires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in services.data" :key="s.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="px-4 py-3 text-neutral-900 dark:text-white">#{{ s.id }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ s.serviceable_type?.split('\\').pop() }}</td>
                        <td class="px-4 py-3"><span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(s.status)]">{{ s.status }}</span></td>
                        <td class="px-4 py-3 text-neutral-500">{{ new Date(s.created_at).toLocaleDateString() }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ s.expired_at ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>