<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Bell, BellOff } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Notifications', href: '/customer/notifications' }] } });

const props = defineProps<{ notifications: { data: any[]; links: any; meta: any } }>();
</script>

<template>
    <Head title="Notifications" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Notifications</h1>

        <div v-if="notifications.data.length === 0" class="py-16 text-center">
            <BellOff class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Tidak ada notifikasi.</p>
        </div>

        <div v-else class="space-y-2">
            <div v-for="notif in notifications.data" :key="notif.id" class="flex items-start gap-3 rounded-xl border p-4" :class="notif.read_at ? 'border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950' : 'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950'">
                <Bell class="mt-0.5 h-5 w-5" :class="notif.read_at ? 'text-neutral-400' : 'text-blue-600'" />
                <div class="flex-1">
                    <p class="text-sm text-neutral-900 dark:text-white">{{ notif.data?.message ?? 'Notification' }}</p>
                    <p class="mt-1 text-xs text-neutral-400">{{ new Date(notif.created_at).toLocaleString() }}</p>
                </div>
                <span v-if="!notif.read_at" class="h-2 w-2 rounded-full bg-blue-600"></span>
            </div>
        </div>
    </div>
</template>