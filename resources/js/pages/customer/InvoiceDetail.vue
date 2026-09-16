<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Invoices', href: '/customer/invoices' }, { title: 'Detail', href: '#' }] } });

const props = defineProps<{ invoice: any }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Invoice {{ invoice.invoice_number }}</h1>
            <span :class="invoice.status === 'paid' ? 'rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800' : 'rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800'">{{ invoice.status }}</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-3 font-semibold text-neutral-900 dark:text-white">Detail Invoice</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-neutral-500">Due Date</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ invoice.due_date }}</dd></div>
                    <div v-if="invoice.paid_at" class="flex justify-between"><dt class="text-neutral-500">Paid At</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ new Date(invoice.paid_at).toLocaleString() }}</dd></div>
                    <div v-if="invoice.payment_method" class="flex justify-between"><dt class="text-neutral-500">Method</dt><dd class="font-medium text-neutral-900 dark:text-white">{{ invoice.payment_method }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-3 font-semibold text-neutral-900 dark:text-white">Summary</h3>
                <div class="space-y-1 text-right text-sm">
                    <p class="text-neutral-500">Subtotal: {{ formatPrice(invoice.subtotal) }}</p>
                    <p v-if="invoice.discount_amount > 0" class="text-green-600">Discount: -{{ formatPrice(invoice.discount_amount) }}</p>
                    <p v-if="invoice.tax_amount > 0" class="text-neutral-500">Tax: {{ formatPrice(invoice.tax_amount) }}</p>
                    <p class="text-lg font-bold text-neutral-900 dark:text-white">Total: {{ formatPrice(invoice.total) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Items</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="py-2 text-neutral-500">Description</th>
                        <th class="py-2 text-right text-neutral-500">Qty</th>
                        <th class="py-2 text-right text-neutral-500">Price</th>
                        <th class="py-2 text-right text-neutral-500">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in invoice.items" :key="item.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="py-3 text-neutral-900 dark:text-white">{{ item.description }}</td>
                        <td class="py-3 text-right text-neutral-500">{{ item.quantity }}</td>
                        <td class="py-3 text-right text-neutral-500">{{ formatPrice(item.unit_price) }}</td>
                        <td class="py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(item.total) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="invoice.payment?.length" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Payment History</h3>
            <div v-for="p in invoice.payment" :key="p.id" class="flex justify-between border-b border-neutral-100 py-2 text-sm last:border-0 dark:border-neutral-800/50">
                <span class="text-neutral-900 dark:text-white">{{ p.transaction_id }}</span>
                <span class="text-neutral-500">{{ p.gateway }} — {{ p.payment_channel ?? 'N/A' }}</span>
                <span class="font-medium text-neutral-900 dark:text-white">{{ formatPrice(p.amount) }}</span>
            </div>
        </div>
    </div>
</template>