<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Payments', href: '#' }] } });
const props = defineProps<{ payments: { data: any[]; links: any; meta: any } }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Admin — Payments" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Payments</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Transaction</th><th class="px-4 py-3 text-neutral-500">Customer</th><th class="px-4 py-3 text-neutral-500">Gateway</th><th class="px-4 py-3 text-neutral-500">Status</th><th class="px-4 py-3 text-right text-neutral-500">Amount</th></tr></thead>
                <tbody><tr v-for="p in payments.data" :key="p.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                    <td class="px-4 py-3 text-neutral-900 dark:text-white">{{ p.transaction_id }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ p.invoice?.user?.name }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ p.gateway }}</td>
                    <td class="px-4 py-3"><span :class="p.status === 'paid' ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800' : 'rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-800'">{{ p.status }}</span></td>
                    <td class="px-4 py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(p.amount) }}</td>
                </tr></tbody>
            </table>
        </div>
    </div>
</template>