<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Customers', href: '/admin/customers' }, { title: 'Detail', href: '#' }] } });

const props = defineProps<{ customer: any; stats: Record<string, number> }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head :title="`Admin — ${customer.name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ customer.name }}</h1>

        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Orders</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ stats.total_orders }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Services</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ stats.total_services }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Invoices</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ stats.total_invoices }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <p class="text-xs text-neutral-500">Total Spent</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(stats.total_spent) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-3 font-semibold text-neutral-900 dark:text-white">Info</h3>
                <dl class="space-y-1 text-sm"><div class="flex justify-between"><dt class="text-neutral-500">Email</dt><dd class="text-neutral-900 dark:text-white">{{ customer.email }}</dd></div><div class="flex justify-between"><dt class="text-neutral-500">Role</dt><dd class="text-neutral-900 dark:text-white">{{ customer.role?.name }}</dd></div><div class="flex justify-between"><dt class="text-neutral-500">Joined</dt><dd class="text-neutral-900 dark:text-white">{{ new Date(customer.created_at).toLocaleDateString() }}</dd></div></dl>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-3 font-semibold text-neutral-900 dark:text-white">Recent Orders</h3>
                <div v-for="o in customer.orders" :key="o.id" class="flex justify-between border-b border-neutral-100 py-2 text-sm last:border-0 dark:border-neutral-800/50">
                    <Link :href="`/admin/orders/${o.id}`" class="text-blue-600 dark:text-blue-400">{{ o.order_number }}</Link>
                    <span class="text-neutral-500">{{ o.status }}</span>
                </div>
            </div>
        </div>
    </div>
</template>