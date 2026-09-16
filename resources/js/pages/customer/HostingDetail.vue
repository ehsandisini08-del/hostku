<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Server, Globe, Key } from '@lucide/vue';
defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'My Hosting', href: '/customer/hosting' }, { title: 'Detail', href: '#' }] } });
const props = defineProps<{ hosting: any }>();
const pwForm = useForm({ password: '', password_confirmation: '' });
const formatMb = (mb: number) => mb >= 1024 ? `${(mb / 1024).toFixed(0)} GB` : `${mb} MB`;
</script>
<template>
    <Head title="Hosting Detail" />
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between"><div><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ hosting.domain ?? hosting.username }}</h1><p class="text-xs text-neutral-500">{{ hosting.plan?.product?.name }} • {{ hosting.server?.name }}</p></div><span :class="hosting.service?.status === 'active' ? 'rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800' : 'rounded-full bg-neutral-100 px-3 py-1 text-sm font-medium text-neutral-800'">{{ hosting.service?.status }}</span></div>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Server Info</h3>
                <dl class="space-y-2 text-sm"><div class="flex justify-between"><dt class="text-neutral-500">Username</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ hosting.username }}</dd></div><div class="flex justify-between"><dt class="text-neutral-500">IP</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ hosting.server_ip }}</dd></div><div class="flex justify-between"><dt class="text-neutral-500">Panel</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ hosting.panel_url }}</dd></div><div class="flex justify-between"><dt class="text-neutral-500">Plan</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ hosting.plan?.product?.name }}</dd></div></dl>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Change Password</h3>
                <form @submit.prevent="pwForm.post(`/customer/hosting/${hosting.id}/change-password`)" class="space-y-3">
                    <div><input v-model="pwForm.password" type="password" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="New password" /></div>
                    <div><input v-model="pwForm.password_confirmation" type="password" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="Confirm password" /></div>
                    <button type="submit" :disabled="pwForm.processing" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><Key class="h-4 w-4" /> Change</button>
                </form>
            </div>
        </div>
    </div>
</template>