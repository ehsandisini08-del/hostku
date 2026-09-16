<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Domains', href: '/admin/domains' }, { title: 'Detail', href: '#' }] } });
const props = defineProps<{ domain: any }>();
</script>
<template>
    <Head :title="`Admin — ${domain.domain_name}`" />
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between"><div><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ domain.domain_name }}</h1><p class="text-xs text-neutral-500">Customer: {{ domain.service?.user?.name }} • Registrar: {{ domain.registrar }}</p></div><span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">{{ domain.status }}</span></div>
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"><p class="text-xs text-neutral-500">Registration</p><p class="font-semibold text-neutral-900 dark:text-white">{{ domain.registration_date ?? 'N/A' }}</p></div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"><p class="text-xs text-neutral-500">Expiration</p><p class="font-semibold text-neutral-900 dark:text-white">{{ domain.expiration_date }}</p></div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"><p class="text-xs text-neutral-500">Auto Renew</p><p class="font-semibold text-neutral-900 dark:text-white">{{ domain.auto_renew ? 'Yes' : 'No' }}</p></div>
        </div>
        <div v-if="domain.nameservers?.length" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="font-semibold text-neutral-900 dark:text-white">Nameservers</h3>
            <ul class="mt-2 space-y-1 text-sm text-neutral-500"><li v-for="(ns, i) in domain.nameservers" :key="i">{{ ns }}</li></ul>
        </div>
        <div v-if="domain.registration_logs?.length" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="mb-3 font-semibold text-neutral-900 dark:text-white">Registrar Logs</h3>
            <div v-for="log in domain.registration_logs" :key="log.id" class="flex justify-between border-b border-neutral-100 py-2 text-sm last:border-0 dark:border-neutral-800/50"><span class="text-neutral-900 dark:text-white">{{ log.action }}</span><span class="text-neutral-500">{{ log.status }}</span><span class="text-neutral-400">{{ new Date(log.created_at).toLocaleString() }}</span></div>
        </div>
    </div>
</template>