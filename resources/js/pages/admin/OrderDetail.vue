<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Orders', href: '/admin/orders' }, { title: 'Detail', href: '#' }] } });
const props = defineProps<{ order: any }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head :title="`Admin — Order ${order.order_number}`" />
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between"><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Order {{ order.order_number }}</h1><span class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800">{{ order.status }}</span></div>
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <p class="text-sm text-neutral-500">Customer: <span class="text-neutral-900 dark:text-white">{{ order.user?.name }} ({{ order.user?.email }})</span></p>
            <table class="mt-4 w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="py-2 text-neutral-500">Item</th><th class="py-2 text-right text-neutral-500">Qty</th><th class="py-2 text-right text-neutral-500">Price</th><th class="py-2 text-right text-neutral-500">Total</th></tr></thead><tbody><tr v-for="item in order.items" :key="item.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="py-3 text-neutral-900 dark:text-white">{{ item.description }}</td><td class="py-3 text-right text-neutral-500">{{ item.quantity }}</td><td class="py-3 text-right text-neutral-500">{{ formatPrice(item.unit_price) }}</td><td class="py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(item.total) }}</td></tr></tbody></table>
            <div class="mt-4 text-right text-sm"><p class="text-neutral-500">Subtotal: {{ formatPrice(order.subtotal) }}</p><p v-if="order.discount_amount" class="text-green-600">Discount: -{{ formatPrice(order.discount_amount) }}</p><p class="text-lg font-bold text-neutral-900 dark:text-white">Total: {{ formatPrice(order.total) }}</p></div>
        </div>
    </div>
</template>