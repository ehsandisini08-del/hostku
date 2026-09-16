<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Invoices', href: '/admin/invoices' }, { title: 'Detail', href: '#' }] } });
const props = defineProps<{ invoice: any }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head :title="`Admin — Invoice ${invoice.invoice_number}`" />
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between"><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Invoice {{ invoice.invoice_number }}</h1><span :class="invoice.status === 'paid' ? 'rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800' : 'rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800'">{{ invoice.status }}</span></div>
        <p class="text-sm text-neutral-500">Customer: {{ invoice.user?.name }} • Due: {{ invoice.due_date }}</p>
        <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="py-2 text-neutral-500">Item</th><th class="py-2 text-right text-neutral-500">Qty</th><th class="py-2 text-right text-neutral-500">Price</th><th class="py-2 text-right text-neutral-500">Total</th></tr></thead><tbody><tr v-for="item in invoice.items" :key="item.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="py-3 text-neutral-900 dark:text-white">{{ item.description }}</td><td class="py-3 text-right text-neutral-500">{{ item.quantity }}</td><td class="py-3 text-right text-neutral-500">{{ formatPrice(item.unit_price) }}</td><td class="py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(item.total) }}</td></tr></tbody></table>
        <div class="text-right text-sm"><p class="text-neutral-500">Subtotal: {{ formatPrice(invoice.subtotal) }}</p><p v-if="invoice.discount_amount" class="text-green-600">Discount: -{{ formatPrice(invoice.discount_amount) }}</p><p class="text-lg font-bold text-neutral-900 dark:text-white">Total: {{ formatPrice(invoice.total) }}</p></div>
    </div>
</template>