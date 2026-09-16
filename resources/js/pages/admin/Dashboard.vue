<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }] } });

const props = defineProps<{
    revenue: { total: number; this_month: number };
    counts: Record<string, number>;
    recentOrders: any[];
}>();

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Admin Dashboard</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Revenue Total</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(revenue.total) }}</p>
                <p class="text-xs text-green-600">+{{ formatPrice(revenue.this_month) }} this month</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Total Orders</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ counts.orders }}</p>
                <p class="text-xs text-neutral-500">{{ counts.active_customers }} active customers</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Active Services</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ counts.active_services }}</p>
                <p class="text-xs text-neutral-500">{{ counts.domains }} TLDs configured</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Pending/Alerts</p>
                <p class="text-2xl font-bold text-red-600">{{ counts.pending_invoices + counts.open_tickets }}</p>
                <p class="text-xs text-neutral-500">{{ counts.pending_invoices }} invoices, {{ counts.open_tickets }} tickets</p>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Recent Orders</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="py-2 text-neutral-500">Order</th><th class="py-2 text-neutral-500">Customer</th><th class="py-2 text-neutral-500">Status</th><th class="py-2 text-right text-neutral-500">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in recentOrders" :key="o.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="py-2"><Link :href="`/admin/orders/${o.id}`" class="text-blue-600 dark:text-blue-400">{{ o.order_number }}</Link></td>
                        <td class="py-2 text-neutral-500">{{ o.user?.name }}</td>
                        <td class="py-2"><span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs text-yellow-800">{{ o.status }}</span></td>
                        <td class="py-2 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(o.total) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>