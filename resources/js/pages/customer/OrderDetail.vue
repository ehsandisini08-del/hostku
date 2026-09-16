<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Orders', href: '/customer/orders' }, { title: 'Detail', href: '#' }] } });

const props = defineProps<{ order: any }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
const statusColor = (s: string) => {
    const m: Record<string, string> = { active: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', paid: 'bg-green-100 text-green-800', failed: 'bg-red-100 text-red-800', cancelled: 'bg-neutral-100 text-neutral-800', processing: 'bg-blue-100 text-blue-800', provisioning: 'bg-purple-100 text-purple-800', awaiting_payment: 'bg-orange-100 text-orange-800' };
    return m[s] ?? 'bg-neutral-100 text-neutral-800';
};
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Order {{ order.order_number }}</h1>
            <span :class="['rounded-full px-3 py-1 text-sm font-medium', statusColor(order.status)]">{{ order.status }}</span>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Items</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="py-2 text-neutral-500">Product</th>
                        <th class="py-2 text-neutral-500">Cycle</th>
                        <th class="py-2 text-right text-neutral-500">Qty</th>
                        <th class="py-2 text-right text-neutral-500">Price</th>
                        <th class="py-2 text-right text-neutral-500">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in order.items" :key="item.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="py-3 text-neutral-900 dark:text-white">{{ item.description }}</td>
                        <td class="py-3 text-neutral-500">{{ item.billing_cycle }}</td>
                        <td class="py-3 text-right text-neutral-500">{{ item.quantity }}</td>
                        <td class="py-3 text-right text-neutral-500">{{ formatPrice(item.unit_price) }}</td>
                        <td class="py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(item.total) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-4 space-y-1 text-right text-sm">
                <p class="text-neutral-500">Subtotal: {{ formatPrice(order.subtotal) }}</p>
                <p v-if="order.discount_amount > 0" class="text-green-600">Discount: -{{ formatPrice(order.discount_amount) }}</p>
                <p v-if="order.tax_amount > 0" class="text-neutral-500">Tax: {{ formatPrice(order.tax_amount) }}</p>
                <p class="text-lg font-bold text-neutral-900 dark:text-white">Total: {{ formatPrice(order.total) }}</p>
            </div>
        </div>

        <div v-if="order.invoice?.length" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Invoices</h3>
            <div v-for="inv in order.invoice" :key="inv.id" class="flex items-center justify-between border-b border-neutral-100 py-2 last:border-0 dark:border-neutral-800/50">
                <Link :href="`/customer/invoices/${inv.id}`" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">{{ inv.invoice_number }}</Link>
                <div class="text-right text-sm">
                    <span :class="['rounded-full px-2 py-0.5 text-xs', inv.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">{{ inv.status }}</span>
                    <p class="font-medium text-neutral-900 dark:text-white">{{ formatPrice(inv.total) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>