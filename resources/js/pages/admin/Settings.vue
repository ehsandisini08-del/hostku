<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Settings', href: '#' }] } });
const props = defineProps<{ settings: Record<string, string|null> }>();
const form = useForm({ company_name: props.settings.company_name || '', company_email: props.settings.company_email || '', currency: props.settings.currency || 'IDR', tax_rate: props.settings.tax_rate || '0', invoice_prefix: props.settings.invoice_prefix || 'INV', grace_period_days: props.settings.grace_period_days || '3' });
</script>

<template>
    <Head title="Admin — Settings" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Settings</h1>
        <form @submit.prevent="form.put('/admin/settings')" class="max-w-xl space-y-4">
            <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Company Name</label><input v-model="form.company_name" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
            <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Company Email</label><input v-model="form.company_email" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
            <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Currency</label><input v-model="form.currency" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
            <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Tax Rate (%)</label><input v-model="form.tax_rate" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
            <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Invoice Prefix</label><input v-model="form.invoice_prefix" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
            <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Grace Period (days)</label><input v-model="form.grace_period_days" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
            <button :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save Settings</button>
        </form>
    </div>
</template>