<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Orders', href: '#' }] } });
const props = defineProps<{ orders: { data: any[]; links: any; meta: any } }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Admin — Orders" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Orders</h1>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Order</th><th class="px-4 py-3 text-neutral-500">Customer</th><th class="px-4 py-3 text-neutral-500">Status</th><th class="px-4 py-3 text-right text-neutral-500">Total</th></tr></thead>
                <tbody><tr v-for="o in orders.data" :key="o.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                    <td class="px-4 py-3"><Link :href="`/admin/orders/${o.id}`" class="text-blue-600 dark:text-blue-400">{{ o.order_number }}</Link></td>
                    <td class="px-4 py-3 text-neutral-500">{{ o.user?.name }}</td>
                    <td class="px-4 py-3"><span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs text-yellow-800">{{ o.status }}</span></td>
                    <td class="px-4 py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(o.total) }}</td>
                </tr></tbody>
            </table>
        </div>
    </div>
</template>